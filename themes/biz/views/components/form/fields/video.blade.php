<tr>
    <td>
        {{ __($label) }}:
    </td>
    <td>
        @if ($value)
            <iframe width="100%" height="300"
                src="{{ $value }}"
                frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
            </iframe>
        @else
            -
        @endif
    </td>
</tr>