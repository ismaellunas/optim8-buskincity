<?php

namespace App\View\Components\Builder;

use Illuminate\View\Component;

class Columns extends Component
{
    private $maxGrid = 12;

    public $uid;
    public $columns;
    public $entities;
    public $locale;
    public $images;
    public $config;
    public $columnSizes = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($uid, $columns, $entities, $locale, $images = [], $config = [])
    {
        $this->uid = $uid;
        $this->columns = $columns;
        $this->entities = $entities;
        $this->locale = $locale;
        $this->images = $images;
        $this->config = $config;

        $this->setColumnSizes();
    }

    public function getSize(int $index): array
    {
        return $this->columnSizes[$index] ?? [];
    }

    private function setColumnSizes()
    {
        $configColumns = collect($this->config['column'] ?? []);

        $sizes = $configColumns->map(function ($configColumn) {
            return $configColumn['size'];
        });

        $length = $sizes->count();
        $numberOfAuto = $sizes->filter(fn ($size) => $size == 'auto')->count();
        $sumOfNonAutoGrid = $sizes
            ->filter(fn ($size) => $size != 'auto')
            ->sum();

        $autoGridMod = $autoGrid = null;

        if ($numberOfAuto > 0) {
            $autoGrid = ($this->maxGrid - $sumOfNonAutoGrid) / $numberOfAuto;
            $autoGridMod = ($this->maxGrid - $sumOfNonAutoGrid) % $numberOfAuto;
        }

        $columnSizes = $sizes->map(function ($size) use ($length, $autoGrid, $autoGridMod) {
            $columnSize = [];

            // desktop
            if ($size == 'auto') {
                if ($length == 1) {
                    $columnSize['desktop'] = $this->maxGrid;
                } else {
                    if ($autoGridMod == 0) {
                        $columnSize['desktop'] = $autoGrid;
                    } else {
                        $columnSize['desktop'] = null;
                    }
                }
            } else {
                $columnSize['desktop'] = $size;
            }

            // tablet
            if ($size == 'auto') {
                if ($length == 1) {
                    $columnSize['tablet'] = $this->maxGrid;
                } else {
                    if ($autoGridMod == 0) {
                        $columnSize['tablet'] = $autoGrid < 4 ? 4 : $autoGrid;
                    } else {
                        $columnSize['tablet'] = null;
                    }
                }
            } else {
                if ($length == 1) {
                    $columnSize['tablet'] = $size > 6 ? $this->maxGrid : 6;
                } else {
                    $columnSize['tablet'] = $size;
                }
            }

            // mobile
            if ($size == 'auto') {
                if ($length == 1) {
                    $columnSize['mobile'] = $this->maxGrid;
                } else {
                    $columnSize['mobile'] = $autoGrid <= 4 ? 6 : $this->maxGrid;
                }
            } else {
                $columnSize['mobile'] = $size <= 4 ? 6 : $this->maxGrid;
            }

            return $columnSize;
        });

        $columnSizes = $columnSizes->map(function ($columnSize) {
            $sizes = [];
            foreach ($columnSize as $screen => $size) {
                if (is_null($size)) continue;

                $sizes[] = "is-$size-$screen";
            }

            return $sizes;
        });

        $this->columnSizes = $columnSizes;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.builder.columns');
    }
}
