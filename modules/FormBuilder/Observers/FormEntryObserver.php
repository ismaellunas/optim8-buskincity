<?php

namespace Modules\FormBuilder\Observers;

use Modules\FormBuilder\Entities\FormEntry;
use Modules\FormBuilder\Services\FormEntryService;

class FormEntryObserver
{
    public function forceDeleting(FormEntry $formEntry): void
    {
        app(FormEntryService::class)
            ->getUploadedMedia($formEntry)
            ->each(fn ($media) => $media->delete());
    }
}
