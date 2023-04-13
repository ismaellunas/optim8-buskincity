<x-layouts.exception>
    <h1
        class="has-text-white has-text-weight-bold"
        style="font-size: 10em"
    >
        {{ $statusCode }}
    </h1>
    <p class="is-size-1 has-text-weight-bold has-text-grey-lighter">
        {{ __("The page your were looking for does not exist") }}
    </p>
    <p class="is-size-5">{{ __("Go back to") }} <a href="{{ route('homepage') }}">{{ __("Home") }}</a></p>
</x-layouts.exception>
