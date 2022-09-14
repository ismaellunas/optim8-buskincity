<div @class($uniqueClass)>
    @if (session('failed'))
        <div class="alert alert-danger">
            {{ session('failed') }}
        </div>
    @endif

    <form-builder
        :form-id="{{ Illuminate\Support\Js::from($formId) }}"
        :recaptcha-site-key="{{ Illuminate\Support\Js::from($recaptchaSiteKey) }}"
    ></form-builder>
</div>