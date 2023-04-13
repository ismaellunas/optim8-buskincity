<?php

namespace App\Http\Controllers;

use App\Http\Requests\ErrorLogIndexRequest;
use App\Models\ErrorLog;
use App\Services\ErrorLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ErrorLogController extends Controller
{
    private $errorLogService;
    private $baseRouteName = 'admin.error-log';

    public function __construct(ErrorLogService $errorLogService)
    {
        $this->errorLogService = $errorLogService;
    }

    public function index(ErrorLogIndexRequest $request)
    {
        $user = auth()->user();
        $scopes = [];

        if (is_array($request->dates) && !empty(array_filter($request->dates))) {
            $scopes['dateRange'] = $request->dates;
        }

        return Inertia::render('ErrorLog/Index', [
            'baseRouteName' => $this->baseRouteName,
            'records' => $this->errorLogService->getRecords(
                $request->term,
                $scopes
            ),
            'pageQueryParams' => array_filter($request->only('term', 'dates')),
            'title' => __('Error Log'),
            'can' => [
                'read' => $user->can('error_log.read'),
                'delete' => $user->can('error_log.delete'),
            ],
            'i18n' => $this->translations(),
        ]);
    }

    public function destroy(ErrorLog $errorLog)
    {
        $errorLog->delete();

        return redirect()->back();
    }

    public function destroyAll()
    {
        ErrorLog::truncate();

        return redirect()->back();
    }

    public function destroyChecked(Request $request)
    {
        ErrorLog::whereIn('id', $request->recordIds)->delete();

        return redirect()->back();
    }

    private function translations(): array
    {
        return [
            'search' => __('Search'),
            'delete_all' => __('Delete all'),
            'delete_checked_records' => __('Delete checked records'),
            'error_log_details' => __('Error log details'),
            'created_at' => __('Created at'),
            'url' => __('Url'),
            'file' => __('File'),
            'line' => __('Line'),
            'total_hit' => __('Total hit'),
            'message' => __('Message'),
            'trace' => __('Trace'),
            'actions' => __('Actions'),
            'function' => __('Function'),
            'class' => __('Class'),
            'type' => __('Type'),
            'close' => __('Close'),
        ];
    }
}
