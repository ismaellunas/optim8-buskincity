@component('mail::message')
# {{ __('Hello') }}

{{ __('New performer application.') }}

@component('mail::table')
| Label                                     | Description                           |
| :---------------------------------------- | :------------------------------------ |
| {{ __('First Name') }}                    | {{ $first_name }}                     |
| {{ __('Last Name') }}                     | {{ $last_name }}                      |
| {{ __('Company') }}                       | {{ $company }}                        |
| {{ __('Email') }}                         | {{ $email }}                          |
| {{ __('Phone') }}                         | {{ $phone }}                          |
| {{ __('Stage Name') }}                    | {{ $stage_name }}                     |
| {{ __('Discipline') }}                    | {{ $discipline }}                     |
| {{ __('Address') }}                       | {{ $address }}                        |
| {{ __('City') }}                          | {{ $city }}                           |
| {{ __('Postal Code') }}                   | {{ $postal_code }}                    |
| {{ __('Country') }}                       | {{ $country }}                        |
| {{ __('About You') }}                     | {{ $about_you }}                      |
| {{ __('Performer Description') }}         | {{ $performer_description }}          |
| {{ __('Fees Per Day Corporate Gigs') }}   | {{ $fees_per_day_corporate_gigs }}    |
| {{ __('Fees Per Day Private Gigs') }}     | {{ $fees_per_day_private_gigs }}      |
| {{ __('Fees Per Day Festival Gigs') }}    | {{ $fees_per_day_festival_gigs }}     |
| {{ __('Facebook') }}                      | {{ $facebook }}                       |
| {{ __('Twitter') }}                       | {{ $twitter }}                        |
| {{ __('Instagram') }}                     | {{ $instagram }}                      |
| {{ __('Youtube') }}                       | {{ $youtube }}                        |
| {{ __('Other') }}                         | {{ $other }}                          |
| {{ __('Promotional Video') }}             | {{ $promotional_video }}              |
@endcomponent

{{ __('Thanks')}},<br>
{{ config('app.name') }}
@endcomponent
