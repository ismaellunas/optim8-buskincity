<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\HumanReadable;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationPerformerRequest;
use App\Mail\ApplicationPerformer;
use App\Models\PerformerApplication;
use App\Models\Setting;
use App\Services\CountryService;
use App\Traits\FlashNotifiable;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Propaganistas\LaravelPhone\PhoneNumber;

class PerformerApplicationController extends Controller
{
    use FlashNotifiable;

    public function index()
    {
        $user = auth()->user();

        return Inertia::render('ApplicationPerformer', [
            'countryOptions' => app(CountryService::class)->getCountryOptions(),
            'defaultCountry' => $user->getMetas(['country'])->first(),
            'disciplineOptions' => $this->getDisciplineOptions(),
            'email' => $user->email,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'phoneCountryOptions' => app(CountryService::class)->getPhoneCountryOptions(),
            'photoInstructions' => [
                __('Accepted file extensions: :extensions.', [
                    'extensions' => implode(', ', config('constants.extensions.image'))
                ]),
                __('Max file size: :filesize.', [
                    'filesize' => HumanReadable::bytesToHuman(
                        1536 * config('constants.one_megabyte')
                    )
                ]),
            ],
            'title' => __('Become a Performer'),
            'description' => __('Fill in the form below to become a performer on BuskinCity. It will allow you to receive payments, private bookings and have your public page.')
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

        if (isset($data['photos'])) {
            unset($data['photos']);
        }

        $performerApplication = new PerformerApplication();
        $performerApplication->applicant_id = $user->id;
        $performerApplication->data = $data;
        $performerApplication->save();

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
        $mailTo = Setting::where('key', 'performer_application_mail_to')
            ->value('value');

        if (!$mailTo) {
            $mailTo = 'tiago@biz752.com';
        }

        Mail::to($mailTo)->send(new ApplicationPerformer($data));
    }
}
