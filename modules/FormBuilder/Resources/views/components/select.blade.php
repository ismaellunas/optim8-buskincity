@props([
    'field' => [],
    'fieldName',
])

<x-form.select
    v-model="form.{{ $fieldName }}"
    is-fullwidth
    :label="$field['label']"
    :name="$field['name']"
    :placeholder="$field['placeholder']"
    :disabled="$field['is_disabled']"
    :readonly="$field['is_readonly']"
    :required="$field['is_required']"
>
    <option :value="null">- {{ __("Select") }} -</option>

    @foreach ($field['options'] as $key => $option)
        @if (is_array($option))
            <option
                value="{{ $option['id'] }}"
                @if(!is_null($field['value']) && $option['id'] == $field['value']) selected @endif
            >
                {{ $option['value'] }}
            </option>
        @else
            <option
                value="{{ $key }}"
                @if(!is_null($field['value']) && $key == $field['value']) selected @endif
            >
                {{ $option }}
            </option>
        @endif
    @endforeach

    <x-slot:notes>
        <div>
            <ul class="help ml-0 is-info" style="list-style-type: none">
            @foreach ($field['notes'] as $note)
                <li>{{ $note }}</li>
            @endforeach
            </ul>
        </div>

        <div v-show="getError('{{ $fieldName }}', null, formErrors)">
            <p
                v-for="(msg, index) in getError('{{ $fieldName }}', null, formErrors)"
                :key="index"
                class="help is-danger"
            >
                @{{ msg }}
            </p>
        </div>
    </x-slot>
</x-form.input>
