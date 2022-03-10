<tr>
    <td>
        {{ __($label) }}:
    </td>
    <td>
        @if (!empty($media))
            @foreach ($media as $medium)
                <div class="field has-addons">
                    <div class="control is-expanded">
                        <input
                            class="input"
                            disabled
                            readonly
                            type="text"
                            value="{{ $medium->display_file_name }}"
                        >
                    </div>
                    <div class="control">
                        <a
                            href="{{ $medium->file_url }}"
                            class="button is-link"
                            download
                        >
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        @else
            -
        @endif
    </td>
</tr>