<?php

namespace App\Console\Commands;

use App\Imports\UserPerformerImport;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Excel;

class ProdImportUserPerformer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prod:import-user-performer {--url=} {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Production import user performer from csv file on cloudinary';

    public function isEnabled()
    {
        return env('PRODUCTION_PREPARATION', true);
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
            $optionCase = $this->choice(
                'Do you want to import data into the database or export data to CSV?',
                [
                    'Database',
                    'Export CSV'
                ],
                0
            );

            switch ($optionCase) {
                case 'Database':
                    $this->process();
                    break;

                case 'Export CSV':
                    # code...
                    break;

                default:
                    throw new Exception('invalid selected index');
                    break;
            }

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

    private function process(): void
    {
        $file = $this->getCsvFromCloud();

        $userImport = new UserPerformerImport();

        $userImport->import(
            $file,
            null,
            Excel::CSV
        );
    }

    private function getCsvFromCloud(): UploadedFile
    {
        $url = $this->option('url') ?? null;
        $info = pathinfo($url);
        $contents = file_get_contents($url);
        $file = '/tmp/' . $info['basename'];

        file_put_contents($file, $contents);

        return new UploadedFile($file, $info['basename']);
    }
}
