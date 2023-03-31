<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
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
        $this->updateAssociatedPage();

        $this->removeProfilePictureMedia();

        $this->removeUsers();

        $this->setExistingUserAsSuperAdmin();

        $this->removeAttachedRoles();
    }

    private function removeUsers()
    {
        $users = User::where('id', '>', 6)->withTrashed()->get();

        $this->info('Delete user records');

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            DB::table('users')->where('id', $user->id)->delete();

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
    }

    private function removeProfilePictureMedia()
    {
        $mediaIds = User::whereNotNull('profile_photo_media_id')
            ->where('id', '>', 6)
            ->withTrashed()
            ->get(['profile_photo_media_id'])
            ->pluck('profile_photo_media_id');
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

    private function updateAssociatedPage()
    {
        $pageIds = Page::where('author_id', '>', 6)->get(['id'])->pluck('id');

        if ($pageIds->isEmpty()) {
            return;
        }

        $this->info('Update page author');

        $bar = $this->output->createProgressBar(count($pageIds));
        $bar->start();

        foreach ($pageIds->all() as $pageId) {
            Page::where('id', $pageId)->update(['author_id' => 1]);

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
    }

    private function setExistingUserAsSuperAdmin()
    {
        $users = User::get();

        $this->info('Delete media records');

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            $user->syncRoles(config('permission.super_admin_role'));

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
    }

    private function removeAttachedRoles()
    {
        $modelHasRoles = DB::table('model_has_roles')
            ->where('model_type', 'App\Models\User')
            ->whereNotExists(function (Builder $query) {
                $query
                    ->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'model_has_roles.model_id');
            })
            ->get();

        $bar = $this->output->createProgressBar(count($modelHasRoles));
        $bar->start();

        foreach ($modelHasRoles as $modelHasRole) {
            DB::table('model_has_roles')
                ->where('model_type', 'App\Models\User')
                ->where('model_id', $modelHasRole->model_id)
                ->delete();

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
    }
}
