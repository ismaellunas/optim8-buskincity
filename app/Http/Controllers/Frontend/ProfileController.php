<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FormService;
use App\Services\TranslationService;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    private $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    public function show(User $user)
    {
        $role = $user->getRoleNames()->first();

        $viewName = 'profile-'.Str::kebab($role);

        $data = [
            'fieldGroups' => $this->formService->getFieldGroupValues($user),
            'locale' => TranslationService::currentLanguage(),
            'user' => $user,
        ];

        if (view()->exists($viewName)) {
            return view($viewName, $data);
        }

        return view('profile', $data);
    }
}
