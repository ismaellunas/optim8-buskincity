<?php

namespace Modules\FormBuilder\Services;

use App\Models\User;
use Modules\FormBuilder\Entities\FormEntry;

class FormEntryService
{
    public function readOptions(): array
    {
        return [
            [ 'id' => null, 'value' => 'All' ],
            [ 'id' => 1, 'value' => 'Read' ],
            [ 'id' => 0, 'value' => 'Unread' ],
        ];
    }

    public function transformEntry(FormEntry $entry, User $user)
    {
        if (!empty($entry['user_id'])) {
            $entry->load([
                'user' => function ($query) {
                    $query->select([
                        'id',
                        'first_name',
                        'last_name',
                    ]);
                }
            ]);
        }

        $entry->can = [
            'mark_as_read' => $user->can('markAsRead', $entry),
            'mark_as_unread' => $user->can('markAsUnread', $entry),
            'archive' => $user->can('delete', $entry),
            'restore' => $user->can('restore', $entry),
            'force_delete' => $user->can('forceDelete', $entry),
        ];

        return $entry->toArray();
    }


    public function markAsRead(int $formBuilderId, array $entryIds)
    {
        $readAt = now();

        FormEntry::whereIn('id', $entryIds)
            ->where('form_id', $formBuilderId)
            ->whereNull('read_at')
            ->get()
            ->each(function ($entry) use ($readAt) {
                $entry->read_at = $readAt;
                $entry->save();
            });
    }

    public function markAsUnread(int $formBuilderId, array $entryIds)
    {
        FormEntry::whereIn('id', $entryIds)
            ->where('form_id', $formBuilderId)
            ->whereNotNull('read_at')
            ->get()
            ->each(function ($entry) {
                $entry->read_at = null;
                $entry->save();
            });
    }

    public function archive(int $formBuilderId, array $entryIds)
    {
        FormEntry::whereIn('id', $entryIds)
            ->where('form_id', $formBuilderId)
            ->whereNull('deleted_at')
            ->get()
            ->each(fn ($entry) => $entry->delete());
    }

    public function restore(int $formBuilderId, array $entryIds)
    {
        FormEntry::whereIn('id', $entryIds)
            ->where('form_id', $formBuilderId)
            ->onlyTrashed()
            ->get()
            ->each(fn ($entry) => $entry->restore());
    }

    public function forceDelete(int $formBuilderId, array $entryIds)
    {
        FormEntry::whereIn('id', $entryIds)
            ->where('form_id', $formBuilderId)
            ->onlyTrashed()
            ->get()
            ->each(fn ($entry) => $entry->forceDelete());
    }
}
