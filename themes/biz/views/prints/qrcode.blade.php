<x-layouts.blank>
    <div id="app-qr-code" class="page">
        <div class="subpage text-centered">
            <biz-qr-code
                :options="{{ Illuminate\Support\Js::from($qrCodeOptions) }}"
                logo-url="{{ $logoUrl }}"
                @on-rendered="print"
            ></biz-qr-code>
        </div>
    </div>

    @push('scripts')
        @vite(['themes/biz/js/print-qr-code.js'])
    @endpush

    @push('styles')
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        * {
            box-sizing: border-box;
            -moz-box-sizing: border-box;
        }
        .page {
            width: 21cm;
            min-height: 296mm;
            padding: 1cm 0.5cm;
            margin: 1cm auto;
            border: 1px #D3D3D3 solid;
            border-radius: 5px;
            background: #fff;
        }
        .subpage {
            height: 256mm;
        }
        .text-centered {
            text-align: center;
        }

        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            html, body {
                width: 210mm;
                height: 276mm;
            }
            .page {
                margin: 0;
                width: auto;
                border: initial;
                border-radius: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
            }
        }
    </style>
    @endpush
</x-layouts.blank>
