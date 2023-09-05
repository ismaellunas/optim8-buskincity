@inject('loginService', 'App\Services\LoginService')

@if ($loginService->isSocialiteDriverExists())
    @foreach ($loginService->getAvailableSocialiteDrivers() as $driver)
        <a href="{{ route('oauth.redirect', $driver) }}" @class([
            "button is-medium is-light is-fullwidth",
            "mt-6" => $loop->first,
            "mt-4" => !$loop->first,
        ])>
            <x-icon icon="fa-brands fa-{{ $driver == 'twitter' ? 'x-'.$driver : $driver }}" is-small/>
            <span>
                {!! __('Continue with :driver', ['driver' => '<span class="has-text-weight-bold">'.Str::title($driver).'</span>']) !!}
            </span>
        </a>
    @endforeach

    <div class="is-divider mt-6 mb-6 ml-5 mr-6" data-content="OR"></div>
@endif

<a class="button is-medium is-light is-fullwidth mt-4" onclick="showForm()">
    <x-icon icon="fa-envelope is-small" />
    <span>
        {!! __('Continue with :driver', ['driver' => '<span class="has-text-weight-bold">Email</span>']) !!}
    </span>
</a>