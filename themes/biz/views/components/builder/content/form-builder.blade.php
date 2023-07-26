@if ($schema)
<div @class($uniqueClass)>
    @if (session('failed'))
        <div class="alert alert-danger">
            {{ session('failed') }}
        </div>
    @endif

    <form-builder-slotable
        :form-id="{{ Illuminate\Support\Js::from($formId) }}"
        :form-structure="{{ Illuminate\Support\Js::from($form) }}"
        :recaptcha-site-key="{{ Illuminate\Support\Js::from($recaptchaSiteKey) }}"
        v-slot="{ form, getError, formErrors }"
    >
        @foreach ($schema['fieldGroups'] as $group)
        <div class="card mb-5">
            @if ($group['title'])
                <header class="card-header">
                    <h3 class="card-header-title">
                        {{ $group['title'] }}
                    </h3>
                </header>
            @endif
            <div class="card-content">
                <div class="columns is-multiline is-mobile">
                    @foreach ($group['fields'] as $name => $field)
                        <div @class(['column', $getColumnSizeClass($field['column'])]) >
                            <x-dynamic-component
                                component="formbuilder::{{ strtolower($field['type']) }}"
                                :field="$field"
                                :field-name="$name"
                            />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <x-field :class="$schema['button']['position']">
            <button class="button is-medium is-primary">
                <span class="has-text-weight-bold">
                    {{ $schema['button']['text'] }}
                </span>
            </button>
        </x-field>
    </form-builder-slotable>
</div>
@endif
