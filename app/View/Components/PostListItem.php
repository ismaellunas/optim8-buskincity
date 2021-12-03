<?php

namespace App\View\Components;

use App\Models\Post;
use Illuminate\View\Component;

class PostListItem extends Component
{
    public Post $post;
    public $link;

    public $hasCategory;
    public $hasCover;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        Post $post,
        string $link
    ) {
        $this->post = $post;
        $this->link = $link;

        $this->hasCategory = $this->getHasCategory();
        $this->hasCover = $this->getHasCover();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.post-list-item');
    }

    private function getHasCover(): bool
    {
        return !empty($this->post->thumbnail_url);
    }

    private function getHasCategory(): bool
    {
        return !$this->post->categories->isEmpty();
    }

    public function firstCategoryName(): string
    {
        if ($this->hasCategory) {
            return $this->post->categories->first()->name;
        }

        return '';
    }
}
