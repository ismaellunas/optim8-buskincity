<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendPasswordResetLinkRequest;
use App\Models\User;
use App\Notifications\UserPasswordResetLink;
use App\Services\SettingService;
use App\Services\UserService;
use App\Traits\FlashNotifiable;
use Illuminate\Support\Facades\Password;

class SendUserPasswordResetEmailController extends Controller
{
    use FlashNotifiable;

    private $expiry;
    private $subject;
    private $content;

    public function __construct(
        private SettingService $settingService,
        private UserService $userService
    ) { }

    public function __invoke(SendPasswordResetLinkRequest $request)
    {
        $inputs = $request->validated();
        $statuses = collect();

        $this->expiry = now()->add($inputs['expiry']);
        $this->subject = $inputs['subject'];
        $this->content = $inputs['content'];

        $users = User::whereIn('id', $inputs['users'])
            ->where('is_suspended', false)
            ->whereNull('deleted_at')
            ->get();

        foreach ($users as $user) {
            $statuses->push(
                Password::broker('users:bulk')->sendResetLink(
                    ['email' => $user->email],
                    $this->sendResetLinkCallback(...),
                    $this->expiry
                )
            );
        }

        $sentNumber = $statuses->countBy()->all()[Password::RESET_LINK_SENT] ?? 0;

        $this->generateFlashMessage(
            'Password reset links sent to :number user|Password reset links sent to :number users',
            ['number' => $sentNumber],
            $sentNumber
        );
    }

    public function passwordResetFormData()
    {
        return [
            'defaultSubject' => $this->settingService->defaultPasswordResetEmailSubject(),
            'defaultContent' => $this->settingService->defaultPasswordResetEmailContent(),
            'expiryOptions' => $this->userService->passwordResetExpiryOptions(),
            'emailTags' => array_keys($this->userService->resetPasswordEmailTags()),
        ];
    }

    public function sendResetLinkCallback($user, $token)
    {
        $notification = (new UserPasswordResetLink(
            $token,
            $user,
            $this->subject,
            $this->content,
            $this->expiry,
        ));

        $user->notify($notification);
    }
}
