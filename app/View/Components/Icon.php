<?php
namespace App\View\Components;

use Illuminate\View\Component;

class Icon extends Component
{
    public $iconClasses = [];
    public $iconWrapperClasses = [];

    public function __construct(
        private string $icon,
        private ?string $iconClass = '',
        public $class = null,
        private ?string $type = 'fa-light',
        private ?bool $isSmall = false,
    ) {
        $this->iconClasses = $this->getIconClasses();
        $this->iconWrapperClasses = $this->getIconWrapperClasses();
    }

    private function getIconClasses(): array
    {
        $classes = collect([]);
        $classes->push($this->type);
        $classes->push($this->icon);
        $classes->push($this->iconClass);
        return $classes->filter()->all();
    }

    private function getIconWrapperClasses(): array
    {
        $classes = collect([]);
        $classes->push('icon');
        $classes->push($this->isSmall ? 'is-small' : null);
        return $classes->filter()->all();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.icon');
    }
}