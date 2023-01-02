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
}
