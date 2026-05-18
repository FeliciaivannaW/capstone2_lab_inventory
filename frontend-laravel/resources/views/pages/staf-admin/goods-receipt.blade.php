@extends('layouts.app')
@section('title', 'Penerimaan Barang')
@section('content')
<style>
    .badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; }
    .badge-success { background:#dcfce7; color:#166534; }
    .badge-warning { background:#fef3c7; color:#92400e; }
    .badge-info { background:#dbeafe; color:#1e40af; }
    .btn-sm { padding:6px 12px; border:none; border-radius:6px; font-size:13px; cursor:pointer; font-weight:500; }
    .btn-green { background:#22c55e; color:white; }
    .btn-green:hover { background:#16a34a; }
    .btn-gray { background:#6b7280; color:white; }
    .progress-bar { background:#e5e7eb; border-radius:999px; height:8px; overflow:hidden; margin-top:4px; }
    .progress-fill { background:#22c55e; height:100%; border-radius:999px; transition:width 0.3s; }
    .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; }
    .modal-box { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:28px; border-radius:10px; width:90%; max-width:480px; box-shadow:0 4px 20px rgba(0,0,0,0.15); }
    .form-group { margin-bottom:14px; }
    .form-group label { display:block; font-weight:600; margin-bottom:5px; font-size:14px; }
    .form-group input, .form-group textarea { width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:6px; font-size:14px; box-sizing:border-box; }
</style>

<div class="header">
    <h1>📦 Penerimaan Barang</h1>
    <p>Draf: <strong>{{ $draft['title'] }}</strong> | Lab: <strong>{{ $draft['lab_name'] }}</strong> | Tahun: <strong>{{ $draft['budget_year'] }}</strong></p>
</div>

<div class="section">
    <h3>Item yang Disetujui — Input Tanggal Terima</h3>
    @if(empty($approvedItems))
        <div class="empty">Tidak ada item yang disetujui untuk diterima.</div>
    @else
        <table>
            <thead><tr><th>No</th><th>Nama Barang</th><th>Tipe</th><th>Dipesan</th><th>Diterima</th><th>Progress</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($approvedItems as $i => $item)
                    @php
                        $receipts = $receiptMap[$item['id']] ?? [];
                        $totalReceived = collect($receipts)->sum('quantity_received');
                        $ordered = $item['quantity'];
                        $pct = $ordered > 0 ? round(($totalReceived / $ordered) * 100) : 0;
                        $isDone = $totalReceived >= $ordered;
                    @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><strong>{{ $item['item_name'] }}</strong></td>
                        <td><span class="badge badge-info">{{ ucfirst($item['item_type']) }}</span></td>
                        <td>{{ $ordered }}</td>
                        <td>{{ $totalReceived }}</td>
                        <td style="min-width:120px;">
                            <div class="progress-bar"><div class="progress-fill" style="width:{{ $pct }}%"></div></div>
                            <small style="color:#64748b;">{{ $pct }}%</small>
                        </td>
                        <td>
                            @if($isDone)
                                <span class="badge badge-success">✓ Lengkap</span>
                            @else
                                <span class="badge badge-warning">⏳ Belum Lengkap</span>
                            @endif
                        </td>
                        <td>
                            @if(!$isDone)
                                <button class="btn-sm btn-green" onclick="showReceiptModal({{ $item['id'] }}, '{{ addslashes($item['item_name']) }}', {{ $ordered }}, {{ $totalReceived }})">
                                    + Terima
                                </button>
                            @endif
                            @if(!empty($receipts))
                                <button class="btn-sm btn-gray" onclick="toggleHistory({{ $item['id'] }})" style="margin-left:4px;">📋</button>
                            @endif
                        </td>
                    </tr>
                    @if(!empty($receipts))
                        <tr id="history-{{ $item['id'] }}" style="display:none;">
                            <td colspan="8" style="background:#f8fafc; padding:12px 20px;">
                                <strong style="font-size:13px;">Riwayat Penerimaan:</strong>
                                <table style="margin-top:8px; font-size:13px;">
                                    <thead><tr><th>Tanggal</th><th>Qty</th><th>Penerima</th><th>Catatan</th></tr></thead>
                                    <tbody>
                                        @foreach($receipts as $r)
                                            <tr>
                                                <td>{{ date('d/m/Y', strtotime($r['received_date'])) }}</td>
                                                <td>{{ $r['quantity_received'] }}</td>
                                                <td>{{ $r['received_by_name'] ?? '-' }}</td>
                                                <td>{{ $r['note'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="section">
    <a href="{{ route('staf-admin.procurement-approved.detail', $draft['id']) }}" style="color:#3b82f6; text-decoration:none;">← Kembali ke Detail Draf</a>
</div>

<!-- Modal Input Penerimaan -->
<div id="receiptModal" class="modal-overlay">
    <div class="modal-box">
        <h3>📦 Input Penerimaan Barang</h3>
        <p id="modalItemName" style="color:#64748b;"></p>
        <form id="receiptForm" onsubmit="handleReceipt(event)">
            <input type="hidden" id="modalItemId">
            <div class="form-group">
                <label>Tanggal Terima *</label>
                <input type="date" id="receivedDate" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Jumlah Diterima * <small id="remainingInfo" style="color:#64748b;"></small></label>
                <input type="number" id="qtyReceived" min="1" required>
            </div>
            <div class="form-group">
                <label>Catatan</label>
                <textarea id="receiptNote" rows="2" placeholder="Opsional..."></textarea>
            </div>
            <div style="text-align:right; margin-top:16px;">
                <button type="button" onclick="closeReceiptModal()" class="btn-sm btn-gray" style="margin-right:8px;">Batal</button>
                <button type="submit" class="btn-sm btn-green">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function showReceiptModal(itemId, itemName, ordered, received) {
    document.getElementById('modalItemId').value = itemId;
    document.getElementById('modalItemName').textContent = 'Barang: ' + itemName;
    document.getElementById('remainingInfo').textContent = '(sisa: ' + (ordered - received) + ')';
    document.getElementById('qtyReceived').max = ordered - received;
    document.getElementById('qtyReceived').value = '';
    document.getElementById('receiptNote').value = '';
    document.getElementById('receiptModal').style.display = 'block';
}
function closeReceiptModal() { document.getElementById('receiptModal').style.display = 'none'; }
function toggleHistory(id) {
    var el = document.getElementById('history-' + id);
    el.style.display = el.style.display === 'none' ? 'table-row' : 'none';
}
function handleReceipt(e) {
    e.preventDefault();
    var data = {
        procurement_item_id: parseInt(document.getElementById('modalItemId').value),
        received_date: document.getElementById('receivedDate').value,
        quantity_received: parseInt(document.getElementById('qtyReceived').value),
        note: document.getElementById('receiptNote').value || null
    };
    fetch('{{ route("staf-admin.goods-receipt.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') { alert('Penerimaan berhasil dicatat!'); location.reload(); }
        else { alert('Error: ' + (d.message || 'Gagal')); }
    })
    .catch(err => { console.error(err); alert('Terjadi kesalahan'); });
    closeReceiptModal();
}
document.getElementById('receiptModal').addEventListener('click', function(e) { if (e.target === this) closeReceiptModal(); });
</script>
@endsection
