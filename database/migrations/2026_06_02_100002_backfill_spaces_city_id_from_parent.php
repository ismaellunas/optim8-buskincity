<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill `spaces.city_id` from parent City nodes where missing (T3.3).
 */
return new class extends Migration
{
    /** @var bool */
    public $withinTransaction = false;

    public function up(): void
    {
        DB::table('spaces')
            ->whereNull('city_id')
            ->whereNotNull('parent_id')
            ->orderBy('id')
            ->chunkById(100, function ($spaces) {
                foreach ($spaces as $space) {
                    $parentCityId = DB::table('spaces')
                        ->where('id', $space->parent_id)
                        ->value('city_id');

                    if ($parentCityId) {
                        DB::table('spaces')
                            ->where('id', $space->id)
                            ->update(['city_id' => $parentCityId]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Non-destructive data backfill — no rollback.
    }
};
