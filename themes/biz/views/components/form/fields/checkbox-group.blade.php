<tr>
    <td>
        {{ __($label) }}:
    </td>
    <td>
        @if (!empty($value))
            <ul class="mt-0">
                @foreach ($value as $option)
                    <li>{{ $valueReadable($option) }}</li>
                @endforeach
            </ul>
        @else
            -
        @endif
    </td>
</tr>