<tr>
    <td>
        {{ $label }}:
    </td>
    <td>
        @if (!empty($value))
            <ul class="mt-0">
                @foreach ($value as $option)
                    <li>{{ ucfirst($option) }}</li>
                @endforeach
            </ul>
        @else
            -
        @endif
    </td>
</tr>