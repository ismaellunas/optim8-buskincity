@props([
    'field' => [],
    'fieldName' => 'name',
])

<x-form.input
    v-model="form.{{ $fieldName }}"
    :label="$field['label']"
    :name="$field['name']"
    :maxlength="$field['maxlength']"
    :placeholder="$field['placeholder']"
    :disabled="$field['is_disabled']"
    :readonly="$field['is_readonly']"
    :required="$field['is_required']"
>
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
</x-form.input>
