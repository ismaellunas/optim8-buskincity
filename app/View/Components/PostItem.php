<?php

namespace App\View\Components;

use App\Models\Post;
use Illuminate\View\Component;

class PostItem extends Component
{
    public Post $post;
    public $link;

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
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.post-item');
    }
}
