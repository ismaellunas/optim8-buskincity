<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProdPreparationReplaceUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prod-preparation:replace-url {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Production preparation step, Replace Urls to prod server Url (prod\'s domain name)';

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

    private function process()
    {
        $tableColumns = [
            'menu_items' => [
                'url',
            ],
            'page_translations' => [
                'data',
            ],
        ];

        $bar = $this->output->createProgressBar(count($tableColumns) + 1);
        $bar->start();

        foreach ($tableColumns as $table => $columns) {
            foreach ($columns as $column) {
                DB::table($table)->update([
                    $column => DB::raw(
                        "REPLACE({$column}, 'buskincity.herokuapp.com', 'buskincity.com')"
                    ),
                ]);
            }

            $bar->advance();
        }

        DB::table('user_metas')
            ->whereNotIn('key', ['stripe_account'])
            ->update([
                'value' => DB::raw(
                    "REPLACE('value', 'buskincity.herokuapp.com', 'buskincity.com')"
                ),
            ]);

        $bar->advance();

        $bar->finish();

        $this->newLine();

        if ($this->getOutput()->isVerbose()) {
            $this->table(
                ['Affected Table', 'Columns/Keys'],
                collect($tableColumns)
                    ->map(fn ($columns, $table) => ['table' => $table, 'column' => implode(', ', $columns)])
                    ->push(['table' => 'user_metas', 'column' => 'excepted keys: "stripe_account"'])
            );
        }
    }
}
