<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Mediable;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProdPreparationRemoveCloudinary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prod-preparation:remove-cloudinary {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Production preparation step, Remove unused media records and Cloudinary files';

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
            $this->removeUnusedMediaRecords();

            $this->process();

            if ($this->option('rollback')) {
                throw new Exception('rollback option is true');
            } else {
                DB::commit();
                $this->line('committed');
            }

            return Command::SUCCESS;

        } catch (\Throwable $th) {

            $this->error($th->getMessage());

            DB::rollBack();

            $this->line('rolled-back');

            return Command::FAILURE;
        }
    }

    private function platformAssets(): array
    {
        return [
            "biz/biz752-logo",
            "biz/favicon",
            "biz752-hero",
        ];
    }

    private function process()
    {
        $results = cloudinary()->admin()->assets([
            "max_results" => 400
        ]);

        $publicIds = null;

        foreach ($results as $result) {
            if (gettype($result) == 'array') {
                $publicIds = collect($result)->pluck('public_id');
            }
        }

        if ($publicIds) {
            $mediaFileNames = Media::get(['file_name'])->pluck('file_name');
            $platformFileNames = collect($this->platformAssets());

            $unusedPublicIds = $publicIds
                ->filter(function ($publicId) use ($mediaFileNames, $platformFileNames) {
                    return ! (
                        $mediaFileNames->contains($publicId)
                        || $platformFileNames->contains($publicId)
                    );
                });

            if ($this->getOutput()->isVerbose()) {
                $this->table(
                    ['Unused Asset ID'],
                    collect($unusedPublicIds)->map(fn ($publicId) => ['id' => $publicId])
                );
                $this->info('Unused assets:'.$unusedPublicIds->count());
            }

            if (
                ! $this->option('rollback')
                && $this->confirm('Are you sure you want to remove unused assets for good?')
            ) {
                cloudinary()->admin()->deleteAssets($unusedPublicIds);

                $this->line('Successfully deleted assets from Cloudinary');
            }
        }
    }

    private function removeUnusedMediaRecords()
    {
        $deletableTypes = [
            'Modules\Ecommerce\Entities\Product',
            'Modules\Space\Entities\Space',
        ];

        $mediables = Mediable::whereIn('mediable_type', $deletableTypes)->get();

        $unusedMediaIds = $mediables
            ->filter(function ($mediable) use ($deletableTypes) {
                $isExists = Mediable::where('media_id', $mediable->media_id)
                    ->whereNotIn('mediable_type', $deletableTypes)
                    ->exists();

                return ! $isExists;
            })->pluck('media_id');

        Media::whereIn('id', $unusedMediaIds->all())->delete();

        if ($this->getOutput()->isVerbose()) {
            $this->table(
                ['Deleted Media ID'],
                $unusedMediaIds->map(fn ($id) => ['id' => $id])->toArray()
            );

            $this->line('Deleted Media: '.$unusedMediaIds->count());
        }
    }
}
