<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationPerformerRequest;
use App\Mail\ApplicationPerformer;
use App\Services\CountryService;
use App\Traits\FlashNotifiable;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Propaganistas\LaravelPhone\PhoneNumber;

class PerformerApplicationController extends Controller
{
    use FlashNotifiable;

    public function create()
    {
        $user = auth()->user();

        return Inertia::render('ApplicationPerformer', [
            'countryOptions' => app(CountryService::class)->getCountryOptions(),
            'defaultCountry' => $user->country_code,
            'disciplineOptions' => $this->getDisciplineOptions(),
            'email' => $user->email,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'phoneCountryOptions' => app(CountryService::class)->getPhoneCountryOptions(),
        ]);
    }

    public function store(ApplicationPerformerRequest $request)
    {
        $data = $request->validated();

        $user = auth()->user();

        $phone = $data['phone'];
        $data['phone'] = PhoneNumber::make($phone['number'], $phone['country'])
            ->formatE164();

        $this->sendEmail($data);

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
