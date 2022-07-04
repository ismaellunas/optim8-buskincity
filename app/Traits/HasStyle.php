<?php

namespace App\Traits;

use App\Contracts\HasStyleInterface;
use App\Helpers\MinifyCss;
use App\Services\PageService;

trait HasStyle
{
    public function generatePageStyle(): void
    {
        $css = "";

        $styledComponents = $this->getStyledComponents(
            $this->data->get('entities')
        );

        foreach ($styledComponents as $styleBlocks) {
            foreach ($styleBlocks as $styleBlock) {
                $css .= $styleBlock->toText();
            }
        }

        $this->generate_style = MinifyCss::minify($css);
        $this->save();
    }

    public function getStyleUrlAttribute()
    {
        $uid = $this->id.$this->unique_key;

        return route('page.css', $uid);
    }

    private function getStyledComponents($entities): array
    {
        return collect($entities)
            ->filter(function ($entity) {
                $className = PageService::getEntityClassName($entity['componentName']);

                if (class_exists($className)) {
                    return in_array(
                        HasStyleInterface::class,
                        class_implements($className)
                    );
                }

                return false;
            })
            ->map(function ($entity) {
                $className = PageService::getEntityClassName($entity['componentName']);

                $entity = new $className($entity);

                return collect($entity->getStyleBlocks())
                    ->filter(function ($styleBlock) {
                        return !$styleBlock->isEmpty();
                    });
            })
            ->all();
    }
}
