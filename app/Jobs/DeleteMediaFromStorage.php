<?php

namespace App\Jobs;

use App\Entities\CloudinaryStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class DeleteMediaFromStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public $fileName,
        public $fileType
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(CloudinaryStorage::class)->destroy(
            $this->fileName,
            $this->fileType
        );
    }
}
