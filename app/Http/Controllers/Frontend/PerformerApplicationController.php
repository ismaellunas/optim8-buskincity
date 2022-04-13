<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationPerformer;
use App\Services\CountryService;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PerformerApplicationController extends Controller
{
    public function create()
    {
        $user = auth()->user();

        $userMetas = $user->getMetas(['country']);

        return Inertia::render('ApplicationPerformer', [
            'disciplineOptions' => $this->getDisciplineOptions(),
            'countryOptions' => app(CountryService::class)->getCountryOptions(),
            'defaultCountry' => $userMetas->get('country'),
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'email' => $user->email,
        ]);
    }

    public function store()
    {

    }

    private function getDisciplineOptions(): array
    {
        return collect(config('buskincity.disciplines'))
            ->values()
            ->map(function ($discipline) {
                return [
                    'id' => $discipline,
                    'value' => $discipline,
                ];
            })
            ->all();
    }

    private function sendEmail(array $data): void
    {
        $adminEmail = 'admin@biz.com';

        Mail::to($adminEmail)->queue(new ApplicationPerformer($data));
    }
}
