<?php

namespace App\View\Components;

use App\Services\SettingService;
use Illuminate\View\Component;

class Recaptcha extends Component
{
    public $recaptchaSiteKey = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->recaptchaSiteKey = app(SettingService::class)->getRecaptchaKeys()
            ['recaptcha_site_key']
            ?? null;
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
