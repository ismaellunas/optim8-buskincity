<x-layouts.blank>
    <div id="app-qr-code" class="page">
        <div class="subpage text-centered">
            <biz-qr-code
                :height="500"
                :width="500"
                text="{{ $text }}"
                logo-url="{{ $logoUrl }}"
            ></biz-qr-code>
        </div>
    </div>

    @vite(['themes/biz/js/print-qr-code.js'])

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
            min-height: 29.7cm;
            padding: 2cm;
            margin: 1cm auto;
            border: 1px #D3D3D3 solid;
            border-radius: 5px;
            background: #fff;
        }

        .subpage {
            padding: 1cm;
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
                height: 297mm;
            }

            .page {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }
        }
    </style>
    @endpush

    @push('bottom_scripts')
    <script>
        window.print();

        window.onafterprint = function() {
            window.document.body.onfocus = function() { window.close(); }
        };
    </script>
    @endpush
</x-layouts.blank>
