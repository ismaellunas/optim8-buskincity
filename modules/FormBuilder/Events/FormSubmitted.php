<?php

namespace Modules\FormBuilder\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\FormBuilder\Entities\FormEntry;

class FormSubmitted
{
    use Dispatchable, SerializesModels;

    public $formEntry;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(FormEntry $formEntry)
    {
        $this->formEntry = $formEntry;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
