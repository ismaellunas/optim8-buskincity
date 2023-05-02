<?php

namespace App\Console\Commands;

use App\Imports\UserPerformerImport;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;

class ProdPreparationUserPerformer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prod-preparation:import-user-performer {--url=} {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Production preparation, import user performer from csv file on cloudinary';

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

    private function process(): void
    {
        $users = $this->getNewUsers();

        $this->info('Import user data');

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            $this->createUser($user);

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
    }

    private function getCsvFromCloud(): UploadedFile
    {
        $url = $this->option('url') ?? null;

        if (! $url) {
            throw new Exception('Please provide the csv file url');
        }

        $info = pathinfo($url);
        $contents = file_get_contents($url);
        $file = '/tmp/' . $info['basename'];

        file_put_contents($file, $contents);

        return new UploadedFile($file, $info['basename']);
    }

    private function getNewUsers()
    {
        $file = $this->getCsvFromCloud();

        $userImport = new UserPerformerImport();

        $userImport->import(
            $file,
            null,
            Excel::CSV
        );

        return $userImport->data;
    }

    private function createUser(array $data): User
    {
        $newUser = User::factory()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'language_id' => $data['language_id'],
        ]);

        $newUser->password = null;
        $newUser->save();

        $newUser->assignRole(
            config('permission.role_names.performer')
        );

        foreach ($data['metas'] as $key => $meta) {
            $newUser->setMeta($key, $meta);
        }

        $newUser->saveMetas();

        return $newUser;
    }
}
