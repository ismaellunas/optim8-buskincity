<?php

namespace App\View\Components\Builder\Content;

use App\Services\PageBuilderService;
use Illuminate\View\Component;

abstract class BaseContent extends Component
{
    public $entity;
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
}
