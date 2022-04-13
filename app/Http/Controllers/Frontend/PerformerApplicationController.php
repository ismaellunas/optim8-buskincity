<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationPerformerRequest;
use App\Mail\ApplicationPerformer;
use App\Services\CountryService;
use App\Traits\FlashNotifiable;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PerformerApplicationController extends Controller
{
    use FlashNotifiable;

    public function create()
    {
        $user = auth()->user();

        return Inertia::render('ApplicationPerformer', [
            'disciplineOptions' => $this->getDisciplineOptions(),
            'countryOptions' => app(CountryService::class)->getCountryOptions(),
            'defaultCountry' => $user->country_code,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'email' => $user->email,
        ]);
    }

    public function store(ApplicationPerformerRequest $request)
    {
        $this->sendEmail($request->validated());

        $this->generateFlashMessage('Your Application successfully submitted.');

        return redirect()->route('dashboard');
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
