@if ($schema)
<div @class($uniqueClass)>
    @if (session('failed'))
        <div class="alert alert-danger">
            {{ session('failed') }}
        </div>
    @endif

    @include('formbuilder::form-builder-slotable-slot')
</div>
@endif
