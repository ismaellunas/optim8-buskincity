<?php

namespace App\Events;

use App\Models\ErrorLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ErrorReport
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $errorLogs = [];
    public $timeLog = null;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->timeLog = now()->format('d F Y');
        $this->errorLogs = $this->getErrorLogs();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [];
    }

    private function getErrorLogs(): array
    {
        return ErrorLog::select([
                'url',
                'file',
                'line',
                'message',
                'total_hit',
            ])
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->get()
            ->toArray();
    }
}
