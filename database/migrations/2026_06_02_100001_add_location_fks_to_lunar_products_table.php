<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Promote pitch geography from free-text meta to real FKs (Phase 3 / T3.1).
 *
 * Additive only: meta `locations` is kept for dual-read during transition.
 *
 * Backfill runs with $withinTransaction = false and per-row error isolation so
 * one bad legacy row cannot abort the entire migration (RDS-safe).
 */
return new class extends Migration
{
    /** @var bool */
    public $withinTransaction = false;

    public function up(): void
    {
        $productsTable = config('lunar.database.table_prefix').'products';

        Schema::table($productsTable, function (Blueprint $table) use ($productsTable) {
            if (! Schema::hasColumn($productsTable, 'city_id')) {
                $table->foreignId('city_id')
                    ->nullable()
                    ->after('productable_id')
                    ->constrained('cities')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn($productsTable, 'location_id')) {
                $table->foreignId('location_id')
                    ->nullable()
                    ->after('city_id')
                    ->constrained('locations')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn($productsTable, 'is_special_event')) {
                $table->boolean('is_special_event')
                    ->default(false)
                    ->after('location_id');
            }
        });

        $this->backfillFromMetaLocations();
        $this->backfillFromProductableSpaces();
    }

    public function down(): void
    {
        $productsTable = config('lunar.database.table_prefix').'products';

        Schema::table($productsTable, function (Blueprint $table) use ($productsTable) {
            if (Schema::hasColumn($productsTable, 'location_id')) {
                $table->dropConstrainedForeignId('location_id');
            }

            if (Schema::hasColumn($productsTable, 'city_id')) {
                $table->dropConstrainedForeignId('city_id');
            }

            if (Schema::hasColumn($productsTable, 'is_special_event')) {
                $table->dropColumn('is_special_event');
            }
        });
    }

    private function backfillFromMetaLocations(): void
    {
        $metaTable = config('lunar.database.table_prefix').'products_meta';
        $productsTable = config('lunar.database.table_prefix').'products';

        if (! Schema::hasTable($metaTable)) {
            return;
        }

        DB::table($metaTable)
            ->where('key', 'locations')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($productsTable) {
                foreach ($rows as $row) {
                    try {
                        $this->backfillProductRow($productsTable, (int) $row->product_id, $this->decodeLocations($row->value));
                    } catch (\Throwable $e) {
                        Log::warning('Pitch FK backfill skipped (meta)', [
                            'product_id' => $row->product_id ?? null,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }

    private function backfillFromProductableSpaces(): void
    {
        $productsTable = config('lunar.database.table_prefix').'products';

        DB::table($productsTable)
            ->whereNull('city_id')
            ->where('productable_type', 'Modules\Space\Entities\Space')
            ->whereNotNull('productable_id')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($productsTable) {
                foreach ($products as $product) {
                    try {
                        if ($product->city_id && $product->location_id) {
                            continue;
                        }

                        $space = DB::table('spaces')
                            ->where('id', $product->productable_id)
                            ->first(['id', 'city', 'country_code', 'latitude', 'longitude', 'address']);

                        if (! $space || empty($space->city) || empty($space->country_code)) {
                            continue;
                        }

                        $this->backfillProductRow($productsTable, (int) $product->id, [
                            'city' => $space->city,
                            'country_code' => $space->country_code,
                            'latitude' => $space->latitude,
                            'longitude' => $space->longitude,
                            'address' => $space->address,
                        ], (int) $space->id);
                    } catch (\Throwable $e) {
                        Log::warning('Pitch FK backfill skipped (space)', [
                            'product_id' => $product->id ?? null,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }

    /**
     * @param  array<string, mixed>|null  $locationData
     */
    private function backfillProductRow(
        string $productsTable,
        int $productId,
        ?array $locationData,
        ?int $spaceId = null
    ): void {
        if (empty($locationData)) {
            return;
        }

        $product = DB::table($productsTable)
            ->where('id', $productId)
            ->whereNull('deleted_at')
            ->first(['id', 'city_id', 'location_id']);

        if (! $product || ($product->city_id && $product->location_id)) {
            return;
        }

        $cityName = trim((string) ($locationData['city'] ?? ''));
        $countryCode = $this->normalizeCountryCode((string) ($locationData['country_code'] ?? ''));

        if ($cityName === '' || $countryCode === '') {
            return;
        }

        $cityId = $this->findOrCreateCityId(
            $cityName,
            $countryCode,
            isset($locationData['latitude']) ? (float) $locationData['latitude'] : null,
            isset($locationData['longitude']) ? (float) $locationData['longitude'] : null,
        );

        if (! $cityId) {
            return;
        }

        if ($spaceId && ! DB::table('spaces')->where('id', $spaceId)->exists()) {
            $spaceId = null;
        }

        $locationId = $this->findOrCreateLocationId($cityId, $locationData, $spaceId);

        if (! $locationId) {
            return;
        }

        DB::table($productsTable)
            ->where('id', $productId)
            ->update([
                'city_id' => $cityId,
                'location_id' => $locationId,
                'updated_at' => now(),
            ]);
    }

    private function decodeLocations(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value[0] ?? null;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded) || empty($decoded[0]) || ! is_array($decoded[0])) {
            return null;
        }

        return $decoded[0];
    }

    private function normalizeCountryCode(string $code): string
    {
        return strtoupper(substr(trim($code), 0, 3));
    }

    private function findOrCreateCityId(
        string $name,
        string $countryCode,
        ?float $latitude,
        ?float $longitude
    ): ?int {
        $name = trim($name);
        $countryCode = $this->normalizeCountryCode($countryCode);

        if ($name === '' || $countryCode === '') {
            return null;
        }

        $existing = DB::table('cities')
            ->where('country_code', $countryCode)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->value('id');

        if ($existing) {
            return (int) $existing;
        }

        try {
            return (int) DB::table('cities')->insertGetId([
                'name' => $name,
                'country_code' => $countryCode,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            $retry = DB::table('cities')
                ->where('country_code', $countryCode)
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->value('id');

            return $retry ? (int) $retry : null;
        }
    }

    /**
     * @param  array<string, mixed>  $locationData
     */
    private function findOrCreateLocationId(int $cityId, array $locationData, ?int $spaceId): ?int
    {
        if ($spaceId) {
            $bySpace = DB::table('locations')->where('space_id', $spaceId)->value('id');

            if ($bySpace) {
                return (int) $bySpace;
            }
        }

        $address = trim((string) ($locationData['address'] ?? ''));
        $name = $address !== ''
            ? Str::limit($address, 128, '')
            : Str::limit(
                trim((string) ($locationData['city'] ?? '')).' location',
                128,
                ''
            );

        $query = DB::table('locations')->where('city_id', $cityId);

        if ($address !== '') {
            $query->whereRaw('LOWER(address) = ?', [strtolower($address)]);
        } else {
            $query->whereRaw('LOWER(name) = ?', [strtolower($name)]);
        }

        $existing = $query->value('id');

        if ($existing) {
            if ($spaceId) {
                DB::table('locations')
                    ->where('id', $existing)
                    ->whereNull('space_id')
                    ->update(['space_id' => $spaceId, 'updated_at' => now()]);
            }

            return (int) $existing;
        }

        try {
            return (int) DB::table('locations')->insertGetId([
                'city_id' => $cityId,
                'name' => $name,
                'address' => $address !== '' ? $address : null,
                'latitude' => isset($locationData['latitude']) ? (float) $locationData['latitude'] : null,
                'longitude' => isset($locationData['longitude']) ? (float) $locationData['longitude'] : null,
                'space_id' => $spaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            $retry = $query->value('id');

            return $retry ? (int) $retry : null;
        }
    }
};
