<?php

namespace Modules\FormBuilder\Services;

use App\Entities\Forms\Fields\FileDragDrop;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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
            ->each(function ($entry) {
                $entry->forceDelete();
            });
    }

    public function getUploadedMedia(FormEntry $formEntry): Collection
    {
        $types = [
            FileDragDrop::TYPE,
        ];

        $keys = $formEntry
            ->form
            ->fieldGroups
            ->map(function ($fieldGroup) use ($types) {
                return collect($fieldGroup->fields)
                    ->filter(fn ($field) => in_array($field['type'], $types))
                    ->pluck('name');
            })
            ->filter()
            ->flatten();

        $mediaIds = $formEntry
            ->metas
            ->filter(function ($meta) use ($keys) {
                return (
                    $keys->contains($meta->key)
                    && array_key_exists('mediaId', $meta->value)
                );
            })
            ->map(function ($meta) {
                return $meta->value['mediaId'];
            })
            ->flatten();

        return Media::whereIn('id', $mediaIds)->get();
    }
}
