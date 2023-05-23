<?php

namespace App\View\Components;

use App\Services\SettingService;
use Illuminate\View\Component;

class Recaptcha extends Component
{
    public $tag;
    public $recaptchaSiteKey = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $tag = 'submit')
    {
        $this->recaptchaSiteKey = app(SettingService::class)->getRecaptchaKeys()
            ['recaptcha_site_key']
            ?? null;

        $this->tag = $tag;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.recaptcha');
    }
}
