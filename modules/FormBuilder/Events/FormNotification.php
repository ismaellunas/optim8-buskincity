<?php

namespace Modules\FormBuilder\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\FormBuilder\Entities\FieldGroupEntry;

class FormNotification
{
    use Dispatchable, SerializesModels;

    public $fieldGroupEntry;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(FieldGroupEntry $fieldGroupEntry)
    {
        $this->fieldGroupEntry = $fieldGroupEntry;
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
