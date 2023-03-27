<?php

namespace Modules\Ecommerce\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Lunar\Console\Commands\MigrateGetCandy as LunarMigrationGetCandy;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class MigrateGetCandy extends LunarMigrationGetCandy
{
    protected $signature = 'upgrading-lunar:migrate-getcandy {--rollback}';

    public function handle()
    {
        DB::beginTransaction();

        try {
            $tableNames = collect(
                DB::connection()->getDoctrineSchemaManager()->listTableNames()
            );

            $tables = $tableNames->filter(function ($table) {
                return str_contains($table, 'getcandy_');
            });

            $lunarTables = $tableNames->filter(function ($table) {
                return str_contains($table, 'lunar_');
            });

            if ($tables->count() && ! $lunarTables->count()) {
                $this->migrateTableNames($tables);
            }

            $this->info('Updating Polymorphic relationships');

            $prefix = config('lunar.database.table_prefix');

            // Tables with polymorphic relations...
            $tables = [
                "{$prefix}urls" => [
                    'element_type',
                ],
                "{$prefix}taggables" => [
                    'taggable_type',
                ],
                "{$prefix}prices" => [
                    'priceable_type',
                ],
                "{$prefix}order_lines" => [
                    'purchasable_type',
                ],
                "{$prefix}channelables" => [
                    'channelable_type',
                ],
                "{$prefix}cart_lines" => [
                    'purchasable_type',
                ],
                "{$prefix}attributes" => [
                    'attribute_type',
                    'type',
                ],
                "{$prefix}attribute_groups" => [
                    'attributable_type',
                ],
                "{$prefix}attributables" => [
                    'attributable_type',
                ],
            ];

            foreach ($tables as $table => $rows) {
                $this->line("Updating {$table}");
                DB::transaction(function () use ($table, $rows) {
                    foreach ($rows as $row) {
                        DB::table($table)->update([
                            $row => DB::RAW(
                                "REPLACE({$row}, 'GetCandy', 'Lunar')"
                            ),
                        ]);
                    }
                });
            }

            $this->line('Updating attribute data');

            $tables = [
                'products',
                'product_variants',
                'customers',
                'collections',
            ];

            foreach ($tables as $table) {
                $tableName = $prefix.$table;

                $this->line("Migrating {$tableName}");

                DB::table($tableName)->update([
                    'attribute_data' => DB::RAW(
                        "REPLACE(attribute_data ::TEXT, 'GetCandy', 'Lunar')::JSON"
                    ),
                ]);
            }

            $this->info('Updating Schema Sequences');

            $this->updateSchemaSequences();

            $this->info('Updating Foreign Keys');

            $this->updateForeignKeys();

            $this->info('Remove unused tables');

            $this->removeUnusedTables();

            if ($this->option('rollback')) {
                DB::rollBack();
                $this->line('rollback');
            } else {
                DB::commit();
            }

            return Command::SUCCESS;

        } catch (\Throwable $th) {
            DB::commit();

            return Command::FAILURE;
        }
    }

    private function sortTables($collection)
    {
        return $collection->sort(function ($a, $b) {
            $lengthA = strlen($a);
            $lengthB = strlen($b);
            $valueA = $a;
            $valueB = $b;

            if ($lengthA == $lengthB) {
                if ($valueA == $valueB) return 0;
                return $valueA > $valueB ? 1 : -1;
            }
            return $lengthA > $lengthB ? 1 : -1;
        });
    }

    protected function migrateTableNames($tables)
    {
        $tables = $this->sortTables($tables);

        $violatedTables = [
            "getcandy_urls",
            "getcandy_orders",
            "getcandy_products",
            "getcandy_order_lines",
            "getcandy_products_meta",
            "getcandy_product_variants",
        ];

        $tables = $tables->filter(fn ($table) => !in_array($table, $violatedTables));

        foreach ($violatedTables as $violatedTable) {
            $tables->push($violatedTable);
        }

        try {
            $adminMigrations = collect(File::files(
                __DIR__.'/../../../../admin/database/migrations'
            ));
        } catch (DirectoryNotFoundException $e) {
            $adminMigrations = collect();
        }

        $ecommerceMigrations = collect(File::files(
            Module::find('ecommerce')->getExtraPath('Database/Migrations')
        ))->filter(function ($migration) {
            return !in_array($migration, [
                '2022_09_19_032626_create_product_user_table'
            ]);
        });

        $migrations = collect(File::files(
            base_path('vendor').'/lunarphp/core/database/migrations'
        ))
            ->merge($adminMigrations)
            ->merge($ecommerceMigrations)
            ->map(function ($file) {
                return $file->getBasename('.'.$file->getExtension());
            });

        $this->line('Removing old migrations');

        DB::table('migrations')->whereIn('migration', $migrations)->delete();

        $this->call('migrate');

        Artisan::call('module:migrate Ecommerce');

        foreach ($tables as $table) {
            $old = $table;
            $new = str_replace('getcandy_', 'lunar_', $table);

            if (! Schema::hasTable($old) || ! Schema::hasTable($new)) {
                continue;
            }

            $this->line("Migrating {$old} into {$new}");

            if ($old == 'getcandy_products') {
                if (Schema::hasColumn('getcandy_products', 'brand')) {
                    $brands = DB::table('getcandy_products')->select('brand')->distinct()->get()
                        ->filter(fn($brand) => !empty($brand->brand));

                    if ($brands->isNotEmpty()) {
                        DB::table('lunar_brands')->insert(
                            $brands->filter()->map(function ($brand) {
                                return [
                                    'name' => $brand->brand,
                                ];
                            })->toArray()
                        );
                    }
                }
            }

            $brands = DB::table('lunar_brands')->get();

            DB::table($old)->orderBy('id')->chunk(100, function ($rows) use ($new, $brands) {
                $insert = [];

                $hasBrandColumn = Schema::hasColumn($new, 'brand');

                foreach ($rows as $row) {
                    $data = (array) $row;
                    if (! empty($data['brand'])) {
                        $brand = $brands->first(function ($brand) use ($data) {
                            return $brand->name == $data['brand'];
                        });
                        $data['brand_id'] = $brand?->id ?: $brands->first()->id;
                    }

                    if (! $hasBrandColumn) {
                        unset($data['brand']);
                    }

                    $insert[] = $data;
                }

                DB::table($new)->insert($insert);
            });

            $this->info("Migrated {$new}");
        }

        $this->info('Migration finished, you can safely delete the old getcandy_ tables.');
    }

    private function updateSchemaSequences()
    {
        $tables = collect(DB::connection()->getDoctrineSchemaManager()->listTableNames())
            ->filter(function ($table) {
                return str_contains($table, 'lunar_');
            });

        $tables->merge([
            'events'
        ]);

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'id')) {
                DB::statement(
                    "SELECT setval('{$table}_id_seq', (SELECT MAX(id) FROM public.{$table}))"
                );
            }
        }
    }

    private function updateForeignKeys()
    {
        $foreignKeys = [
            'product_user' => [
                'foreign' => 'product_id',
                'on' => config('lunar.database.table_prefix').'products',
            ],
            'order_check_ins' => [
                'foreign' => 'order_id',
                'on' => config('lunar.database.table_prefix').'orders',
            ],
            'events' => [
                'foreign' => 'order_line_id',
                'on' => config('lunar.database.table_prefix').'order_lines',
            ],
        ];

        foreach ($foreignKeys as $affectedTable => $foreignKey) {
            Schema::table($affectedTable, function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign([$foreignKey['foreign']]);
                $table
                    ->foreign($foreignKey['foreign'])
                    ->references('id')
                    ->on($foreignKey['on'])
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }
    }

    private function removeUnusedTables()
    {
        $unusedTables = collect([
            '2021_08_19_110000_create_staff_table' => 'getcandy_staff',
            '2021_08_19_113700_create_staff_permissions_table' => 'getcandy_staff_permissions',
            '2022_02_25_100000_create_saved_searches_table' => 'getcandy_saved_searches',
        ]);

        DB::table('migrations')
            ->whereIn('migration', $unusedTables->keys()->all())
            ->delete();

        $unusedTables->reverse()->each(function ($table) {
            Schema::dropIfExists($table);
        });
    }
}
