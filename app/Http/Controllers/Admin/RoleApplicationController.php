<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveRoleApplicationRequest;
use App\Http\Requests\RejectRoleApplicationRequest;
use App\Models\RoleApplication;
use App\Services\RoleApplicationService;
use App\Traits\FlashNotifiable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleApplicationController extends Controller
{
    use FlashNotifiable;

    private string $baseRouteName = 'admin.role-applications';

    public function __construct(private RoleApplicationService $roleApplicationService)
    {
        $this->authorizeResource(RoleApplication::class, 'role_application', [
            'except' => ['approve', 'reject'],
        ]);
    }

    public function index(Request $request)
    {
        return Inertia::render('RoleApplication/Admin/Index', [
            'baseRouteName' => $this->baseRouteName,
            'records' => $this->roleApplicationService->getRecords(
                $request->term,
                $request->status
            ),
            'pageQueryParams' => array_filter($request->only('term', 'status')),
            'statusOptions' => RoleApplicationStatus::options(),
            'title' => __('Role applications'),
            'i18n' => [
                'search' => __('Search'),
                'status' => __('Status'),
                'applicant' => __('Applicant'),
                'city' => __('City'),
                'role' => __('Role'),
                'submitted' => __('Submitted'),
                'actions' => __('Actions'),
            ],
        ]);
    }

    public function show(RoleApplication $roleApplication)
    {
        $roleApplication->load([
            'city:id,name,country_code',
            'countrySpace:id,name,country_code',
            'logoMedia',
            'coverMedia',
            'reviewer:id,first_name,last_name,email',
            'replacedUser:id,first_name,last_name,email',
        ]);

        return Inertia::render('RoleApplication/Admin/Show', [
            'baseRouteName' => $this->baseRouteName,
            'application' => $roleApplication,
            'approvalPreview' => $roleApplication->isPending()
                ? $this->roleApplicationService->approvalPreview($roleApplication)
                : null,
            'title' => __('Review application'),
            'can' => [
                'approve' => auth()->user()->can('approve', $roleApplication),
                'reject' => auth()->user()->can('reject', $roleApplication),
            ],
        ]);
    }

    public function approve(ApproveRoleApplicationRequest $request, RoleApplication $roleApplication)
    {
        $this->authorize('approve', $roleApplication);

        $this->roleApplicationService->approve(
            $roleApplication,
            auth()->user(),
            (bool) $request->boolean('confirm_replace')
        );

        $this->generateFlashMessage(__('The application was approved.'));

        return redirect()->route($this->baseRouteName.'.show', $roleApplication);
    }

    public function reject(RejectRoleApplicationRequest $request, RoleApplication $roleApplication)
    {
        $this->authorize('reject', $roleApplication);

        $this->roleApplicationService->reject(
            $roleApplication,
            auth()->user(),
            $request->validated('reject_reason')
        );

        $this->generateFlashMessage(__('The application was rejected.'));

        return redirect()->route($this->baseRouteName.'.show', $roleApplication);
    }
}
