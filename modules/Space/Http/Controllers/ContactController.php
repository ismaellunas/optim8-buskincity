<?php

namespace Modules\Space\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Space\Http\Requests\ContactRequest;

class ContactController extends Controller
{
    public function apiValidateContact(ContactRequest $request)
    {
        return [
            'passed' => true
        ];
    }
}
