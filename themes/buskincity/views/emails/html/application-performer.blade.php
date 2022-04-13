@component('mail::message')
# {{ __('Hello') }}

{{ __('New performer application.') }}

@component('mail::table')
| Label                                     |       | Description                           |
| :---------------------------------------- | :---: | :------------------------------------ |
| {{ __('First Name') }}                    | :     | {{ $first_name }}                     |
| {{ __('Last Name') }}                     | :     | {{ $last_name }}                      |
| {{ __('Company') }}                       | :     | {{ $company }}                        |
| {{ __('Email') }}                         | :     | {{ $email }}                          |
| {{ __('Phone') }}                         | :     | {{ $phone }}                          |
| {{ __('Stage Name') }}                    | :     | {{ $stage_name }}                     |
| {{ __('Discipline') }}                    | :     | {{ $discipline }}                     |

## {{__('Address')}}

| Label                                     |       | Description                           |
| :---------------------------------------- | :---: | :------------------------------------ |
| {{ __('Street Address') }}                | :     | {{ $address }}                        |
| {{ __('City') }}                          | :     | {{ $city }}                           |
| {{ __('Postal Code') }}                   | :     | {{ $postal_code }}                    |
| {{ __('Country') }}                       | :     | {{ $country }}                        |

## {{__('Performance')}}

| Label                                     |       | Description                           |
| :---------------------------------------- | :---: | :------------------------------------ |
| {{ __('Tell us about you') }}             | :     | {{ $about_you }}                      |
| {{ __('Describe your performance') }}     | :     | {{ $performance_description }}        |

## {{__('Fees Per Day')}}

| Label                                     |       | Description                           |
| :---------------------------------------- | :---: | :------------------------------------ |
| {{ __('Corporate Gigs') }}                | :     | {{ $fees_per_day_corporate_gigs }}    |
| {{ __('Private Gigs') }}                  | :     | {{ $fees_per_day_private_gigs }}      |
| {{ __('Festival Gigs') }}                 | :     | {{ $fees_per_day_festival_gigs }}     |

## {{__('Social Media and Video')}}

| Label                                     |       | Description                           |
| :---------------------------------------- | :---: | :------------------------------------ |
| {{ __('Facebook') }}                      | :     | {{ $facebook }}                       |
| {{ __('Twitter') }}                       | :     | {{ $twitter }}                        |
| {{ __('Instagram') }}                     | :     | {{ $instagram }}                      |
| {{ __('Youtube') }}                       | :     | {{ $youtube }}                        |
| {{ __('Other(s)') }}                      | :     | {{ $other_links }}                    |
| {{ __('Promotional Video') }}             | :     | {{ $promotional_video }}              |
@endcomponent

{{ __('Thanks')}},<br>
{{ config('app.name') }}
@endcomponent
