<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

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

    public function report(Throwable $exception): void
    {
        $isValid = true;

        $excepts = [
            \Illuminate\Auth\Access\AuthorizationException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
            \Illuminate\Queue\MaxAttemptsExceededException::class,
            \Illuminate\Session\TokenMismatchException::class,
            \Illuminate\Validation\ValidationException::class,
        ];

        foreach ($excepts as $except) {
            if ($exception instanceof $except) {
                $isValid = false;

                break;
            }
        }

        if ($isValid) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $inputs = [
                'url' => url()->full(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage() . '; ' . $userAgent,
                'trace' => $exception->getTrace(),
            ];

            if (
                !$this->isHttpException($exception)
                || $exception->getStatusCode() > 499
            ) {
                try {
                    $errorLog = new ErrorLog();

                    $errorLog->syncErrorLog($inputs);
                } catch (\Throwable $th) {
                    if (!app()->environment('local') || !app()->runningInConsole()) {
                        throw $th;
                    }
                }
            }
        }
    }

    private function isHttpException(Throwable $e)
    {
        return $e instanceof HttpExceptionInterface;
    }
}