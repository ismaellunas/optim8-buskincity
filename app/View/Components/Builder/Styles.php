<?php

namespace App\View\Components\Builder;

use App\Contracts\StyleablePageComponentInterface;
use App\Services\PageService;
use Illuminate\View\Component;

class Styles extends Component
{
    public $entities;
    public $styledComponents = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entities)
    {
        $this->entities = $entities;
        $this->styledComponents = $this->getStyledComponents();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.builder.styles');
    }

    private function getStyledComponents()
    {
        return collect($this->entities)
            ->filter(function ($entity) {
                $className = PageService::getEntityClassName($entity['componentName']);

                if (class_exists($className)) {
                    return in_array(
                        StyleablePageComponentInterface::class,
                        class_implements($className)
                    );
                }

                return false;
            })
            ->map(function ($entity) {
                $className = PageService::getEntityClassName($entity['componentName']);

                $entity = new $className($entity);

                return $entity->getStyleBlocks();
            })
            ->all();
    }
}
