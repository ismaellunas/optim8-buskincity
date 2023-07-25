<?php

namespace App\Console\Commands;

use App\Entities\Caches\SettingCache;
use App\Models\Media;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixMediaStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:media-structure {--prefix=} {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update media structure based on cloudinary.';

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

        return Command::SUCCESS;
    }

    private function process(): void
    {
        $cloudinaryAssets = $this->getCloudinaryAssets();

        $media = Media::all();
        $prefix = $this->option('prefix');
        $oldPrefix = config('filesystems.folder_prefix');
        $unusedMedia = collect([]);

        $this->info('Update media data:');

        $bar = $this->output->createProgressBar(count($media));
        $bar->start();

        foreach ($media as $medium) {
            $publicId = $medium->assets['public_id'];

            $newPublicId = null;

            if (
                Str::startsWith($publicId, $oldPrefix.'/')
            ) {

                $newPublicId = Str::replaceFirst(
                    $oldPrefix.'/',
                    $prefix,
                    $publicId
                );

            } elseif (Str::startsWith($publicId, $oldPrefix.'_')) {

                $newPublicId = Str::replaceFirst(
                    $oldPrefix.'_',
                    $prefix,
                    $publicId
                );

            } else {
                $newPublicId = $prefix . $publicId;
            }

            $newPublicId = Str::replace(" ", "_", $newPublicId);

            $asset = $cloudinaryAssets->where('public_id', $newPublicId)->first();

            if (! Str::startsWith($publicId, $prefix)) {
                if (! $asset) {

                    $unusedMedia->push($medium);

                } else {
                    $medium->file_name = $asset['public_id'];
                    $medium->version = $asset['version'];
                    $medium->file_url = $asset['secure_url'];
                    $medium->assets = $asset;
                    $medium->save();
                }
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();

        if ($unusedMedia->isNotEmpty()) {
            $this->removeUnusedMedia($unusedMedia);
        }
    }

    private function getCloudinaryAssets(): ?Collection
    {
        $prefix = $this->option('prefix');

        $results = cloudinary()->admin()->assets([
            "type" => "upload",
            "max_results" => 400,
            "prefix" => $prefix
        ]);

        $cloudinaryAssets = null;

        foreach ($results as $result) {
            if (gettype($result) == 'array') {
                $cloudinaryAssets = collect($result);
            }
        }

        return $cloudinaryAssets;
    }

    private function removeUnusedMedia(Collection $unusedMedia): void
    {
        $this->table(
            ['Unused Asset ID'],
            $unusedMedia->map(fn ($media) => ['id' => $media->file_name])->sortBy('id')
        );

        $this->info('Unused assets:'.$unusedMedia->count());

        if (
            ! $this->option('rollback')
            && $this->confirm('Do you want to remove the unused assets from database?')
        ) {
            foreach ($unusedMedia as $media) {
                $media->deleteQuietly();
            }

            $this->line('Successfully deleted assets from database');

            app(SettingCache::class)->flush();
        }
    }
}
