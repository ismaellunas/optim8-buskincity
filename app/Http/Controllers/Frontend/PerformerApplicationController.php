<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationPerformer;
use Illuminate\Support\Facades\Mail;
//use Inertia\Inertia;

class PerformerApplicationController extends Controller
{
    public function create()
    {

    }

    public function store()
    {

    }

    private function sendEmail(array $data): void
    {
        $adminEmail = 'admin@biz.com';

        Mail::to($adminEmail)->queue(new ApplicationPerformer($data));
    }
    }
}
