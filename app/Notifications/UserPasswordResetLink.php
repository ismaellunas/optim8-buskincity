<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Notifications\ResetPassword as AuthResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class UserPasswordResetLink extends AuthResetPassword implements ShouldQueue
{
    use Queueable;

    public $token;
    public $user;
    public $subject;
    public $content;
    public $expiredAt;

    private $userService;
    private $expiredAtFormat = 'F j, Y \a\t H:i';

    public function __construct(
        string $token,
        User $user,
        string $subject,
        string $content,
        Carbon $expiredAt
    ) {
        $this->userService = app(UserService::class);

        $this->token = $token;
        $this->user = $user;
        $this->subject = $subject;
        $this->content = Purifier::clean($content, 'email');
        $this->expiredAt = $expiredAt;
    }

    private function passwordResetButtonLinkHtml(string $url): string
    {
        return view('vendor.mail.html.button', [
            'url' => $url,
            'color' => 'primary',
            'align' => 'center',
            'slot' => __('Reset Password'),
        ])->render();
    }

    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->markdown('emails.html.user-password-reset-link', [
                'url' => $url,
                'body' => Str::swap(
                    $this->swapLists($url),
                    $this->content
                ),
            ]);
    }

    public function swapLists(string $url): array
    {
        $tags = collect($this->userService->resetPasswordEmailTags($this->user))
            ->mapWithKeys(function ($item, $key) {
                return ['{'.$key.'}' => $item];
            })
            ->all();

        $tags['{password_reset_button_link}'] = $this->passwordResetButtonLinkHtml($url);

        $tags['{expired_on}'] = $this->expiredAt->format($this->expiredAtFormat);

        return $tags;
    }
}
