<?php

namespace App\Entities\Telescope;

use Illuminate\Support\Facades\DB;
use Laravel\Telescope\EntryResult;
use Laravel\Telescope\Storage\DatabaseEntriesRepository as TelescopeDatabaseEntriesRepository;
use Laravel\Telescope\Storage\EntryModel;
use Laravel\Telescope\Storage\EntryQueryOptions;
use Laravel\Telescope\Contracts\EntriesRepository;

class DatabaseEntriesRepository extends TelescopeDatabaseEntriesRepository implements EntriesRepository
{
    public function get($type, EntryQueryOptions $options)
    {
        return EntryModel::on($this->connection)
            ->withTelescopeOptions($type, $options)
            ->take($options->limit)
            ->leftJoin('users', DB::raw("((telescope_entries.content::json ->> 'user')::json ->> 'id')::bigint"), '=', 'users.id')
            ->orderByDesc('sequence')
            ->select(
                'telescope_entries.uuid',
                'telescope_entries.sequence',
                'telescope_entries.batch_id',
                'telescope_entries.type',
                'telescope_entries.family_hash',
                'telescope_entries.content',
                'telescope_entries.created_at',
                'users.first_name',
                'users.last_name',
            )
            ->get()->reject(function ($entry) {
                return ! is_array($entry->content);
            })->map(function ($entry) {

                $content = $entry->content;

                if (!empty($content['user'])) {
                    $content['user']['name'] = $entry->first_name. ' '.$entry->last_name;
                }

                return new EntryResult(
                    $entry->uuid,
                    $entry->sequence,
                    $entry->batch_id,
                    $entry->type,
                    $entry->family_hash,
                    $content,
                    $entry->created_at,
                    []
                );
            })->values();
    }
}
