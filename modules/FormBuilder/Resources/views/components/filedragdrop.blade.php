@props([
    'field' => [],
    'fieldName' => 'name',
])

<form-file-drag-drop
    v-model="form.{{ $fieldName }}"
    :errors="formErrors"
    :schema="{{ Illuminate\Support\Js::from($field) }}"
>
    <template #note>
        <div>
            <ul class="help ml-0 is-info" style="list-style-type: none">
            @foreach ($field['notes'] as $note)
                <li>{{ $note }}</li>
            @endforeach
            </ul>
        </div>
    </template>
</form-file-drag-drop>
