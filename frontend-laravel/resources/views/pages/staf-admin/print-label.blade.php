<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label — {{ $asset['asset_code'] ?? '' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style id="page-css">
        @page { size: 85mm 54mm; margin: 0; }
    </style>
    <style>
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
            body { background: #f8fafc; display: flex; justify-content: flex-start; align-items: center; min-height: 100vh; padding: 40px 20px; flex-direction: column; gap: 24px; font-family: 'Inter', 'Segoe UI', sans-serif; }
            .preview-wrapper { background: white; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01); border: 1px solid #e2e8f0; border-radius: 24px; padding: 40px; display: flex; justify-content: center; overflow: auto; max-width: 100%; }
            .print-controls { display: flex; gap: 16px; align-items: center; justify-content: space-between; flex-wrap: wrap; background: white; padding: 20px 24px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02); width: 100%; max-width: 800px; }
            .a4-mode { box-shadow: 0 10px 30px rgba(0,0,0,0.05); } /* Give the A4 paper a shadow when in screen preview */
        }
        @media print {
            body { background: white; padding: 0; display: block; }
            .print-controls { display: none !important; }
            .preview-wrapper { box-shadow: none; padding: 0; border-radius: 0; border: none; }
            .label-card { border: 1px solid #ccc; }
            .a4-mode { border: none !important; } /* If printing A4, hide the outer border or keep it based on preference */
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
            line-height: 1.4;
            padding-bottom: 1px;
            flex-shrink: 0;
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

        /* =========================================
           A4 MODE STYLES (Kertas Besar)
           ========================================= */
        .a4-mode {
            width: 277mm; /* A4 landscape width minus margins */
            height: 190mm; /* A4 landscape height minus margins */
            padding: 15mm;
            gap: 15mm;
            border-radius: 5mm;
            border: 2px solid #e2e8f0;
            background: white;
            margin: 0 auto;
        }
        .a4-mode .qr-section { width: 90mm; }
        .a4-mode .qr-section img, .a4-mode .qr-placeholder { width: 90mm; height: 90mm; border-width: 3px; font-size: 16pt; border-radius: 5mm; }
        .a4-mode .info-section { justify-content: space-between; gap: 0; overflow: visible; }
        .a4-mode .org-name { font-size: 18pt; padding-bottom: 5mm; margin-bottom: 0; border-bottom-width: 2px; }
        .a4-mode .item-name { font-size: 32pt; -webkit-line-clamp: 3; }
        .a4-mode .label-number { font-size: 42pt; }
        .a4-mode .asset-code { font-size: 20pt; margin-top: 2mm; }
        .a4-mode .meta-chip { font-size: 16pt; padding: 4mm 8mm; border-radius: 3mm; border: 1px solid #cbd5e1; }
        .a4-mode .meta-row { gap: 6mm; margin-top: 4mm; }
        .a4-mode .divider { height: 3px; margin: 0; }
    </style>
</head>
<body>

{{-- Screen controls --}}
<div class="print-controls">
    <div style="flex: 1; min-width: 250px;">
        <h2 style="margin:0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #4f46e5;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Pratinjau Cetak Label
        </h2>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Pilih pengaturan kertas yang sesuai dengan printer Anda.</p>
    </div>
    
    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <select id="paper-size" onchange="changePaperSize(this.value)" 
                style="padding: 10px 16px; border-radius: 12px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 14px; font-weight: 600; color: #334155; cursor: pointer; outline: none; transition: 0.2s;">
            <option value="label">🖨️ Printer Label (85x54mm)</option>
            <option value="a4">📄 Kertas A4 (Besar)</option>
        </select>

        <button onclick="window.print()"
                style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:#4f46e5;color:white;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;transition:0.2s;box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);"
                onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            Cetak
        </button>
        <button onclick="window.close()"
                style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:#fff;color:#475569;border:1px solid #cbd5e1;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;transition:0.2s;"
                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
            Tutup
        </button>
    </div>
</div>

<div class="preview-wrapper">
    <div class="label-card" id="label-card">

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

            <div class="item-name" title="{{ $asset['item_name'] ?? '—' }}">{{ $asset['item_name'] ?? '—' }}</div>

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

<script>
    function changePaperSize(size) {
        const pageCss = document.getElementById('page-css');
        const card = document.getElementById('label-card');
        
        if (size === 'a4') {
            // Ubah @page rule untuk A4 Landscape
            pageCss.innerHTML = '@page { size: A4 landscape; margin: 10mm; }';
            card.classList.add('a4-mode');
        } else {
            // Kembalikan ke ukuran standar printer label
            pageCss.innerHTML = '@page { size: 85mm 54mm; margin: 0; }';
            card.classList.remove('a4-mode');
        }
    }
</script>

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
            width: 300, // Cukup besar agar tajam saat dicetak di A4
            height: 300,
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
