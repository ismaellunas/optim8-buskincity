<?php

namespace Modules\Ecommerce\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpgradingLunarRemoveGetCandyTables extends Command
{
    protected $signature = 'upgrading-lunar:remove-getcandy-tables
        {--rollback}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove getcandy tables';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tableNames = collect(
            DB::connection()->getDoctrineSchemaManager()->listTableNames()
        )->sort();

        $violatedTables = [
            'getcandy_attribute_groups',
            'getcandy_collection_groups',
            'getcandy_currencies',
            'getcandy_customer_groups',
            'getcandy_languages',
            'getcandy_orders',
            'getcandy_products',
            'getcandy_states',
            'getcandy_tax_classes',
            'getcandy_channels',
            'getcandy_countries',
            'getcandy_customers',
            'getcandy_product_types',
        ];

        $tables = $tableNames->filter(function ($table) use ($violatedTables) {
            return (
                str_contains($table, 'getcandy_')
                && !in_array($table, $violatedTables)
            );
        });

        $tables = $tables->merge($violatedTables);

        DB::beginTransaction();

        try {
            foreach ($tables as $table) {
                $this->info('Removing '.$table);
                Schema::dropIfExists($table);
            }

            if ($this->option('rollback')) {
                DB::rollBack();

                $this->line('rolled-back');
            } else {
                $this->line('commited');
                DB::commit();
            }
        } catch (\Throwable $th) {
            $this->error($th->getMessage());

            DB::rollBack();
        }
    }
}
