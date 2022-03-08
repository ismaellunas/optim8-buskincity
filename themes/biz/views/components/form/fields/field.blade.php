<table class="table-profile">
    <tbody>
        <tr>
            <th colspan="1" style="width: 30%"></th>
            <th colspan="2"></th>
        </tr>

        @if ($title)
        <tr>
            <th>
                {{ $title }}
            </th>
            <th></th>
        </tr>
        @endif

        @foreach ($fields as $field)
            <x-dynamic-component
                :component="$componentName($field['type'])"
                :field="$field"
                :user-locale="$userLocale"
            />
        @endforeach
    </tbody>
</table>