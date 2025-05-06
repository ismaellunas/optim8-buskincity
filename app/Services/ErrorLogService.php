<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ErrorLogService
{
    public function getRecords(
        string $term = null,
        ?array $scopes = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $records = ErrorLog::select([
                'id',
                'url',
                'file',
                'line',
                'message',
                'total_hit',
                'trace',
                'created_at',
            ])
            ->orderBy('id', 'DESC')
            ->when($term, function ($query) use ($term) {
                $query->where('url', 'ILIKE', '%'.$term.'%')
                    ->orWhere('message', 'ILIKE', '%'.$term.'%');
            })
            ->when($scopes, function ($query, $scopes) {
                foreach ($scopes as $scopeName => $value) {
                    $query->when($value, function ($query, $value) use ($scopeName) {
                        $query->$scopeName($value);
                    });
                }
            })
            ->paginate($perPage);

        $this->transformRecords($records);

        return $records;
    }

    private function transformRecords(LengthAwarePaginator &$records): void
    {
        $records->getCollection()->transform(function ($record) {
            $record->createdAtFormatted = $record->created_at->format('d F Y');

            return $record;
        });
    }
}