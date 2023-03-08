<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Media;
use App\Models\Mediable;
use Illuminate\Console\Command;
use Modules\Ecommerce\Entities\Product;

class FixMediableRelation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:mediable-relation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix mediable relations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->fixDataSetting();
        $this->fixDataProduct();
        $this->fixDataSpace();

        return Command::SUCCESS;
    }

    public function fixDataSetting(): void
    {
        $settings = Setting::where('key', 'ILIKE', '%media_id')
            ->get();

        foreach ($settings as $setting) {
            if ($setting && $setting->value) {
                $setting->syncMedia([
                    $setting->value
                ]);
            }
        }
    }

    public function fixDataProduct(): void
    {
        $media = Media::where('medially_type', 'Modules\Ecommerce\Entities\Product')
            ->get();

        foreach ($media as $medium) {
            $mediable = new Mediable();

            $mediable->mediable_type = $medium->medially_type;
            $mediable->mediable_id = $medium->medially_id;
            $mediable->media_id = $medium->id;
            $mediable->save();
        }
    }

    public function fixDataSpace(): void
    {
        $media = Media::where('medially_type', 'Modules\Space\Entities\Space')
            ->get();

        foreach ($media as $medium) {
            $mediable = new Mediable();

            $mediable->mediable_type = $medium->medially_type;
            $mediable->mediable_id = $medium->medially_id;
            $mediable->media_id = $medium->id;
            $mediable->save();
        }
    }
}
