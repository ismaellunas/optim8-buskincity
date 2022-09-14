<div @class($uniqueClass)>
    @if (session('failed'))
        <div class="alert alert-danger">
            {{ session('failed') }}
        </div>
    @endif

    <form-builder
        :form-id="{{ Illuminate\Support\Js::from($formId) }}"
    ></form-builder>
</div>