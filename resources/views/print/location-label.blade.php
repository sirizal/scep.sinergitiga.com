<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Label Print</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

@page {
            size: 2.25in 1.25in;
            margin: 0;
        }

        .labels-wrapper {
            display: block;
        }

        .label-container {
            width: 2.25in;
            height: 1.25in;
            padding: 2px 4px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .label-container:last-child {
            page-break-after: auto;
        }

        .barcode-wrapper {
            text-align: center;
            margin-bottom: 1px;
        }

        .barcode-wrapper svg,
        .barcode-wrapper img {
            max-width: 100%;
            height: 24px;
        }

        .code-text {
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            color: #000;
        }

        .name-text {
            font-size: 7px;
            text-align: center;
            color: #333;
            max-width: 2in;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media print {
            @page {
                size: 2.25in auto;
                margin: 0;
            }

            html, body {
                width: 2.25in;
                min-height: auto;
            }

            .print-btn {
                display: none;
            }

            .label-container {
                border: none;
                page-break-after: always;
            }
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 9999;
        }

        .print-btn:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="print-btn" onclick="window.print()">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        Print Labels ({{ count($locations) }})
    </div>

    <div class="labels-wrapper">
        @foreach ($locations as $location)
            <div class="label-container">
                <div class="barcode-wrapper">
                    <svg id="barcode-{{ $location->id }}"></svg>
                </div>
                <div class="code-text">{{ $location->code }}</div>
                @if ($location->name)
                    <div class="name-text">{{ $location->name }}</div>
                @endif
            </div>

            <script>
                try {
                    JsBarcode("#barcode-{{ $location->id }}", "{{ $location->code }}", {
                        format: "CODE128",
                        width: 2,
                        height: 24,
                        displayValue: false,
                        margin: 0,
                        background: "#ffffff",
                        lineColor: "#000000"
                    });
                } catch (e) {
                    console.error("Barcode error for {{ $location->code }}:", e);
                }
            </script>
        @endforeach
    </div>
</body>
</html>