<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label — {{ $asset['asset_code'] ?? '' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { size: 85mm 54mm; margin: 0; }
        body { margin: 0; background: #fff; font-family: 'Segoe UI', sans-serif; }

        .label-card {
            width: 85mm;
            height: 54mm;
            padding: 4mm;
            box-sizing: border-box;
            display: flex;
            gap: 4mm;
            align-items: stretch;
            border: 1px solid #e2e8f0;
            border-radius: 3mm;
            page-break-inside: avoid;
        }

        /* Screen preview wrapper */
        @media screen {
            body { background: #f1f5f9; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; padding: 40px 20px; flex-direction: column; gap: 20px; }
            .preview-wrapper { background: white; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border-radius: 8px; padding: 20px; }
            .print-controls { display: flex; gap: 12px; }
        }
        @media print {
            body { background: white; padding: 0; display: block; }
            .print-controls { display: none !important; }
            .preview-wrapper { box-shadow: none; padding: 0; border-radius: 0; }
            .label-card { border: 1px solid #ccc; }
        }

        .qr-section {
            width: 42mm;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-section img {
            width: 40mm;
            height: 40mm;
            object-fit: contain;
        }
        .qr-placeholder {
            width: 40mm;
            height: 40mm;
            border: 1.5px dashed #cbd5e1;
            border-radius: 2mm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7pt;
            color: #94a3b8;
            text-align: center;
            flex-direction: column;
            gap: 2mm;
        }

        .info-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .org-name {
            font-size: 6pt;
            font-weight: 700;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            border-bottom: 0.5pt solid #e2e8f0;
            padding-bottom: 1.5mm;
            margin-bottom: 1.5mm;
        }
        .item-name {
            font-size: 8pt;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .label-number {
            font-size: 10pt;
            font-weight: 800;
            color: #1e293b;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.3pt;
        }
        .asset-code {
            font-size: 6.5pt;
            color: #64748b;
            font-family: 'Courier New', monospace;
        }
        .meta-row {
            display: flex;
            gap: 2mm;
            flex-wrap: wrap;
        }
        .meta-chip {
            font-size: 5.5pt;
            color: #475569;
            background: #f1f5f9;
            padding: 0.5mm 1.5mm;
            border-radius: 1mm;
            font-weight: 600;
        }
        .divider { height: 0.5pt; background: #e2e8f0; margin: 1.5mm 0; }
    </style>
</head>
<body>

{{-- Screen controls --}}
<div class="print-controls">
    <button onclick="window.print()"
            style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:#4f46e5;color:white;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;">
        🖨️ Cetak Label
    </button>
    <button onclick="window.close()"
            style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;">
        ✕ Tutup
    </button>
</div>

<div class="preview-wrapper">
    <div class="label-card">

        {{-- QR Code --}}
        <div class="qr-section">
            @if($asset['qr_code'] ?? ($asset['photo_url'] ?? null))
                <img src="{{ $asset['qr_code'] ?? $asset['photo_url'] }}"
                     alt="QR Code {{ $asset['label_number'] ?? $asset['asset_code'] }}">
            @else
                <div class="qr-placeholder" id="qr-gen">
                    <div id="qr-container"></div>
                </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="info-section">
            <div class="org-name">Labventory · Lab Inventory System</div>

            <div class="item-name">{{ $asset['item_name'] ?? '—' }}</div>

            <div class="divider"></div>

            <div>
                <div class="label-number">{{ $asset['label_number'] ?? 'BELUM BERLABEL' }}</div>
                <div class="asset-code">{{ $asset['asset_code'] ?? '—' }}</div>
            </div>

            <div class="divider"></div>

            <div class="meta-row">
                @if($asset['lab_name'] ?? null)
                    <span class="meta-chip">{{ $asset['lab_name'] }}</span>
                @endif
                @if($asset['received_date'] ?? null)
                    <span class="meta-chip">{{ date('d/m/Y', strtotime($asset['received_date'])) }}</span>
                @endif
                @if($asset['category_name'] ?? null)
                    <span class="meta-chip">{{ $asset['category_name'] }}</span>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Generate QR client-side jika belum ada --}}
@if(!($asset['qr_code'] ?? ($asset['photo_url'] ?? null)))
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('qr-container');
    const label = '{{ $asset['label_number'] ?? $asset['asset_code'] ?? '' }}';
    if (container && label) {
        document.getElementById('qr-gen').innerHTML = '';
        document.getElementById('qr-gen').id = '';
        new QRCode(container, {
            text: label,
            width: 151,
            height: 151,
            colorDark: '#1e293b',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }
});
</script>
@endif

</body>
</html>
