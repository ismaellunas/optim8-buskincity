<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProdPreparationRemoveUnusedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prod-preparation:remove-unused-data {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Production preparation step, Remove unused records by truncating tables';

    public function isEnabled()
    {
        return env('PRODUCTION_PREPARATION', false);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            $this->process();

            if ($this->option('rollback')) {

                throw new Exception('rollback option is true');

            } elseif ($this->confirm('Do you wish to commit the changes?')) {

                DB::commit();

                $this->info('committed');
            }

            return Command::SUCCESS;

        } catch (\Throwable $th) {

            $this->error($th->getMessage());

            DB::rollBack();

            $this->info('rolled-back');

            return Command::FAILURE;
        }
    }

    private function immunnedTables(): array
    {
        return [
            (new \App\Models\GlobalOption())->getTable(),
            (new \App\Models\Country())->getTable(),
            (new \App\Models\Role())->getTable(),
            (new \App\Models\Permission())->getTable(),
            (new \App\Models\Setting())->getTable(),
            (new \App\Models\Language())->getTable(),
            (new \App\Models\Translation())->getTable(),
            (new \App\Models\Form())->getTable(),
            (new \App\Models\FieldGroup())->getTable(),
            (new \Modules\FormBuilder\Entities\FormNotificationSetting())->getTable(),
            (new \App\Models\Permission())->getTable(),
            (new \App\Models\Role())->getTable(),
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
            (new \App\Models\Setting())->getTable(),
            (new \App\Models\User())->getTable(),
            (new \App\Models\UserMeta())->getTable(),
            (new \App\Models\Page())->getTable(),
            (new \App\Models\PageTranslation())->getTable(),
            (new \App\Models\Menu())->getTable(),
            (new \App\Models\MenuItem())->getTable(),
            (new \App\Models\Media())->getTable(),
            (new \App\Models\MediaTranslation())->getTable(),
            (new \App\Models\Mediable())->getTable(),
            'connected_accounts',
            (new \Lunar\Models\Channel())->getTable(),
            (new \Lunar\Models\Currency())->getTable(),
            (new \Lunar\Models\Language())->getTable(),
            (new \Lunar\Models\ProductType())->getTable(),
            (new \Lunar\Models\TaxClass())->getTable(),
        ];
    }

    private function process()
    {
        $tableNames = collect(
            DB::connection()->getDoctrineSchemaManager()->listTableNames()
        );

        $truncatableTables = $tableNames->diff($this->immunnedTables())->sort();

        $violatedTables = [
            'lunar_attribute_groups',
            'lunar_brands',
            'lunar_attributes',
            'lunar_customer_groups',
            'lunar_customers',
            'lunar_collections',
            'lunar_countries',
            'lunar_collection_groups',
            'lunar_orders',
            'lunar_products',
            'lunar_product_options',
            'lunar_product_variants',
            'lunar_order_lines',
            'lunar_product_option_values',
            'lunar_staff',
            'lunar_states',
            'lunar_tags',
            'lunar_tax_rates',
            'lunar_tax_zones',
            'categories',
            'posts',
            'schedule_rules',
            'schedules',
            'spaces',
            'telescope_entries',
            'space_events',
            'form_entries',
            'lunar_carts',
        ];

        $foreignedTables = [
            'categories',
            'posts',
            'schedule_rules',
            'schedules',
            'spaces',
            'telescope_entries',
            'space_events',
            'form_entries'
        ];

        $truncatableTables = $truncatableTables
            ->filter(function ($table) use ($violatedTables, $foreignedTables) {
                return ! (
                    in_array($table, $violatedTables)
                    || in_array($table, $foreignedTables)
                );
            }
        );

        $numberOfUserWhenStart = \App\Models\User::count();

        $bar = $this->output->createProgressBar(count($truncatableTables));
        $bar->start();

        foreach ($truncatableTables as $table) {
            DB::statement("TRUNCATE TABLE $table RESTART IDENTITY");
            $bar->advance();
        }

        $bar->finish();

        $this->newLine();

        if ($this->getOutput()->isVerbose()) {
            $this->table(
                ['Truncated Table'],
                collect($truncatableTables)->map(fn ($table) => ['table' => $table])
            );
        }

        $bar = $this->output->createProgressBar(count($violatedTables));
        $bar->start();

        foreach ($violatedTables as $table) {
            if (
                Str::startsWith($table, 'lunar_')
                || in_array($table, ['categories', 'posts', 'schedule_rules', 'schedules', 'spaces', 'telescope_entries', 'space_events', 'form_entries'])
            ) {
                DB::statement("TRUNCATE TABLE $table RESTART IDENTITY CASCADE");
            } else {
                DB::statement("TRUNCATE TABLE $table RESTART IDENTITY");
            }
            $bar->advance();
        }

        $bar->finish();

        $this->newLine();

        if ($this->getOutput()->isVerbose()) {
            $this->table(
                ['Violated Table'],
                collect($violatedTables)->map(fn ($table) => ['table' => $table])
            );
        }

        $numberOfUserWhenEnd = \App\Models\User::count();

        $this->info("User count start: $numberOfUserWhenStart ... end: $numberOfUserWhenEnd");
    }
}
