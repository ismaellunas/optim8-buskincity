<?php
namespace App\View\Components;

use App\Services\SettingService;
use Illuminate\View\Component;

class OgMeta extends Component
{
    public $title = null;
    public $imageUrl = null;
    public $domain = null;
    public $description = null;

    public function __construct(
        ?string $title = null,
        ?string $imageUrl = null,
        ?string $description = null,
    ) {
        $this->title = $title ?? config('app.name');
        $this->imageUrl = $imageUrl ?? $this->getDefaultOgImageUrl();
        $this->domain = config('constants.domain');
        $this->description = $description;
    }

    public function currentUrl(): string
    {
        return url()->current() ?? config('app.url');
    }

    public function imageWidth(): int
    {
        return config('constants.dimensions.open_graph.width');
    }

    public function imageHeight(): int
    {
        return config('constants.dimensions.open_graph.height');
    }

    private function getDefaultOgImageUrl(): string
    {
        return app(SettingService::class)
            ->getOpenGraphImageUrl(
                $this->imageWidth(),
                $this->imageHeight(),
            );
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.og-meta');
    }
}