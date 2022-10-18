<?php

namespace App\View\Components\Builder\Content;

use App\Services\PageBuilderService;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Mews\Purifier\Facades\Purifier;

abstract class BaseContent extends Component
{
    public $entity;
    public $uniqueClass;
    public $wrapperClasses = [];

    protected $baseView = 'components.builder.content';
    protected $pageBuilderService;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        $this->pageBuilderService = app(PageBuilderService::class);

        $this->entity = $entity;

        $this->wrapperClasses = $this->getWrapperClasses();

        $this->uniqueClass = $this->getUniqueClass();
    }

    public function entityContentHtml(): string
    {
        $dirtyHtml = $this->entity['content']['html'] ?? '';

        if (!empty($dirtyHtml)) {
            return Purifier::clean($dirtyHtml, 'tinymce');
        }

        return '';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view($this->baseView . '.' . $this->getViewName());
    }

    public function id(): string
    {
        return $this->entity['id'] ?? '';
    }

    protected function getViewName(): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/',
            '-$0',
            (new \ReflectionClass($this))->getShortName()
        ));
    }

    private function getWrapperClasses(): array
    {
        $classes = collect();

        $configWrapper = $this->entity['config']['wrapper'] ?? null;

        if (!empty($configWrapper['margin'])) {
            $classes->push(
                $this
                    ->pageBuilderService
                    ->createMarginClasses($configWrapper['margin'])
            );
        }

        if (!empty($configWrapper['padding'])) {
            $classes->push(
                $this
                    ->pageBuilderService
                    ->createPaddingClasses($configWrapper['padding'])
            );
        }

        return $classes->flatten()->all();
    }

    protected function getUniqueClass(): string
    {
        return 'pb-'.Str::lower($this->id());
    }

    protected function getConfig(): array
    {
        return $this->entity['config'] ?? [];
    }

}
