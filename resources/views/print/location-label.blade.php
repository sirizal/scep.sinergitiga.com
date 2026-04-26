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

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .print-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .print-btn:hover {
            background: #2563eb;
        }

        .labels-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            max-width: 800px;
        }

        .label-container {
            width: 2in;
            height: 1in;
            padding: 4px;
            background: #fff;
            border: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            page-break-inside: avoid;
        }

        .barcode-wrapper {
            text-align: center;
            margin-bottom: 2px;
        }

        .barcode-wrapper svg,
        .barcode-wrapper img {
            max-width: 100%;
            height: 32px;
        }

        .code-text {
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            color: #000;
            margin-top: 1px;
        }

        .name-text {
            font-size: 7px;
            text-align: center;
            color: #333;
            max-width: 1.8in;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .print-header {
                display: none;
            }

            .label-container {
                border: none;
            }

            .labels-wrapper {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h2>Location Labels</h2>
        <p>{{ count($locations) }} label(s) ready to print</p>
        <br>
        <button type="button" class="print-btn" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Print Labels
        </button>
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
                        width: 1,
                        height: 28,
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