<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProdPreparationRemoveUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prod-preparation:remove-user {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Production preparation step, Remove unused user records and profile picture (media) records';

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
            } else {
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
        $mediaIds = User::whereNotNull('profile_photo_media_id')
            ->get(['profile_photo_media_id'])
            ->pluck('profile_photo_media_id');

        $users = User::where('id', '>', 6)->withTrashed()->get();

        $this->info('Delete user records');

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            DB::statement("DELETE FROM public.users WHERE id=?", [$user->id]);

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();

        $this->info('Delete media records');

        $bar = $this->output->createProgressBar(count($mediaIds));
        $bar->start();

        if ($mediaIds->isNotEmpty()) {
            Media::whereIn('id', $mediaIds->all())->delete();

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
    }
}
