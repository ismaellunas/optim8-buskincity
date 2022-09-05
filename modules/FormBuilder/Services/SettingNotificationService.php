<?php

namespace Modules\FormBuilder\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\FormBuilder\Entities\FieldGroupNotificationSetting;

class SettingNotificationService
{
    public function getRecords(
        string $term = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        return FieldGroupNotificationSetting::select([
                'id',
                'name',
                'subject'
            ])
            ->orderBy('id', 'DESC')
            ->when($term, function ($query) use ($term) {
                $query->where('name', 'ILIKE', '%'.$term.'%')
                    ->orWhere('subject', 'ILIKE', '%'.$term.'%');
            })
            ->paginate($perPage);
    }
}