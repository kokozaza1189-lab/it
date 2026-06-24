<?php
$role        = $current_user['role'];
$can_adjust  = in_array($role, ['super_admin','treasurer']);
$th_months   = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
?>
<div id="app">

<!-- Balance KPI -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
  <div class="kpi sm:col-span-1" style="background:linear-gradient(135deg,#1e40af,#3b82f6)">
    <p class="text-blue-200 text-xs font-semibold uppercase">ยอดเงินคงเหลือ</p>
    <p class="text-white text-3xl font-bold mt-2">฿<?= number_format($balance, 2) ?></p>
    <p class="text-blue-200 text-xs mt-1">อัปเดตล่าสุด: <?= date('d/m/Y') ?></p>
    <span class="text-5xl" style="position:absolute;right:16px;top:14px;opacity:.15">🏦</span>
  </div>
  <?php
  $total_income  = array_sum(array_column($monthly, 'income'));
  $total_expense = array_sum(array_column($monthly, 'expense'));
  ?>
  <div class="kpi">
    <p class="text-slate-500 text-xs font-semibold uppercase">รายรับรวม (ปีนี้)</p>
    <p class="text-emerald-600 text-2xl font-bold mt-1">฿<?= number_format($total_income, 2) ?></p>
    <span class="text-3xl" style="position:absolute;right:16px;top:14px;opacity:.12">📈</span>
  </div>
  <div class="kpi">
    <p class="text-slate-500 text-xs font-semibold uppercase">รายจ่ายรวม (ปีนี้)</p>
    <p class="text-red-500 text-2xl font-bold mt-1">฿<?= number_format($total_expense, 2) ?></p>
    <span class="text-3xl" style="position:absolute;right:16px;top:14px;opacity:.12">📉</span>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

  <!-- Ledger table -->
  <div class="card lg:col-span-2 overflow-hidden">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-slate-800">รายการบัญชี</h2>
      <div class="flex gap-2 flex-wrap">
        <button class="btn btn-gray btn-sm" @click="exportExcel">📊 Excel</button>
        <button class="btn btn-gray btn-sm" @click="exportPDF">📄 PDF</button>
        <?php if ($can_adjust): ?>
          <button class="btn btn-blue btn-sm" onclick="document.getElementById('adjustModal').style.display='flex'">+ เพิ่มรายการ</button>
        <?php endif; ?>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr>
          <th>วันที่</th><th>รายการ</th><th>รายรับ</th><th>รายจ่าย</th><th>คงเหลือ</th><th>หมายเหตุ</th>
          <?php if ($can_adjust): ?><th></th><?php endif; ?>
        </tr></thead>
        <tbody>
          <?php foreach ($ledger as $entry): ?>
          <tr>
            <td class="text-slate-400 text-xs whitespace-nowrap"><?= htmlspecialchars($entry->entry_date) ?></td>
            <td class="font-medium text-slate-700"><?= htmlspecialchars($entry->title) ?></td>
            <td class="text-emerald-600 font-semibold">
              <?= $entry->type==='income' ? '฿'.number_format($entry->income,2) : '-' ?>
            </td>
            <td class="text-red-500 font-semibold">
              <?= $entry->type==='expense' ? '฿'.number_format($entry->expense,2) : '-' ?>
            </td>
            <td class="font-bold text-slate-700">฿<?= number_format($entry->balance,2) ?></td>
            <td class="text-slate-400 text-xs"><?= htmlspecialchars($entry->note ?? '') ?></td>
            <?php if ($can_adjust): ?>
            <td>
              <a href="<?= base_url('fund/delete/'.$entry->id) ?>"
                 onclick="return confirm('ลบรายการนี้?')"
                 class="btn-icon btn-xs text-red-400 hover:text-red-600" style="font-size:13px">🗑</a>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (empty($ledger)): ?>
      <p class="text-center text-slate-400 py-8">ยังไม่มีรายการ</p>
    <?php endif; ?>
  </div>

  <!-- Monthly chart -->
  <div class="card">
    <h2 class="font-bold text-slate-800 mb-4">สรุปรายเดือน <?= date('Y')+543 ?></h2>
    <canvas id="fundChart" height="280"></canvas>
  </div>

</div>

<!-- Adjust modal — vanilla JS only, no Vue dependency -->
<div id="adjustModal" class="modal-bg" style="display:none" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">เพิ่มรายการบัญชี</h2>
        <button type="button" class="btn-icon" onclick="document.getElementById('adjustModal').style.display='none'">✕</button>
      </div>
    </div>
    <form method="POST" action="<?= base_url('fund/adjust') ?>" class="modal-body space-y-4">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="lbl">ประเภท</label>
          <select name="type" class="inp">
            <option value="income">💚 รายรับ</option>
            <option value="expense">🔴 รายจ่าย</option>
          </select>
        </div>
        <div>
          <label class="lbl">วันที่</label>
          <input name="txn_date" type="date" class="inp" value="<?= date('Y-m-d') ?>" required/>
        </div>
      </div>
      <div>
        <label class="lbl">ชื่อรายการ <span class="text-red-500">*</span></label>
        <input name="title" class="inp" placeholder="เช่น เก็บเงินห้อง มิ.ย. 68" required/>
      </div>
      <div>
        <label class="lbl">จำนวนเงิน (฿) <span class="text-red-500">*</span></label>
        <input name="amount" type="number" step="0.01" min="0.01" class="inp" placeholder="0.00" required/>
      </div>
      <div>
        <label class="lbl">หมายเหตุ</label>
        <input name="note" class="inp" placeholder="(ไม่บังคับ)"/>
      </div>
      <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-gray flex-1" onclick="document.getElementById('adjustModal').style.display='none'">ยกเลิก</button>
        <button type="submit" class="btn btn-blue flex-1">บันทึก</button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const ledgerData = <?= json_encode(array_map(fn($e) => [
  'entry_date' => $e->entry_date,
  'title'      => $e->title,
  'income'     => $e->type === 'income' ? $e->income : 0,
  'expense'    => $e->type === 'expense' ? $e->expense : 0,
  'balance'    => $e->balance,
  'note'       => $e->note ?? '',
], $ledger)) ?>;

const { createApp } = Vue
createApp({
  setup() {
    function exportExcel() {
      if (!window.XLSX) return alert('โหลด SheetJS ไม่สำเร็จ')
      const rows = ledgerData.map(r => ({
        'วันที่': r.entry_date,
        'รายการ': r.title,
        'รายรับ (฿)': r.income || '',
        'รายจ่าย (฿)': r.expense || '',
        'คงเหลือ (฿)': r.balance,
        'หมายเหตุ': r.note,
      }))
      const ws = XLSX.utils.json_to_sheet(rows)
      // Column widths
      ws['!cols'] = [14,36,16,16,16,28].map(w => ({ wch: w }))
      const wb = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(wb, ws, 'Ledger')
      XLSX.writeFile(wb, 'fund_ledger_' + new Date().toISOString().slice(0,10) + '.xlsx')
    }

    function exportPDF() {
      const date  = new Date().toLocaleDateString('th-TH',{year:'numeric',month:'long',day:'numeric'})
      const bal   = ledgerData.length ? ledgerData[0].balance : 0
      const fmt   = n => n ? '฿' + Number(n).toLocaleString('th-TH',{minimumFractionDigits:2}) : '-'

      const rows  = ledgerData.map(r => `
        <tr>
          <td>${r.entry_date}</td>
          <td>${r.title}</td>
          <td class="num income">${r.income  ? fmt(r.income)  : '-'}</td>
          <td class="num expense">${r.expense ? fmt(r.expense) : '-'}</td>
          <td class="num bal">${fmt(r.balance)}</td>
          <td class="note">${r.note || ''}</td>
        </tr>`).join('')

      const html = `<!DOCTYPE html><html lang="th"><head>
<meta charset="UTF-8"/>
<title>บัญชีเงินกลาง สาขา IT</title>
<style>
  @page { size: A4 landscape; margin: 15mm 12mm; }
  * { font-family: 'Sarabun', 'Tahoma', sans-serif; box-sizing: border-box; }
  body { margin: 0; color: #0f172a; font-size: 12pt; }
  .header { text-align: center; margin-bottom: 14px; }
  .header h1 { font-size: 16pt; font-weight: 700; margin: 0 0 4px; }
  .header p  { font-size: 10pt; color: #475569; margin: 0; }
  .summary { display: flex; gap: 24px; margin-bottom: 12px; justify-content: flex-end; }
  .summary span { font-size: 11pt; }
  .summary .lbl { color: #64748b; }
  .summary .val { font-weight: 700; color: #1e40af; }
  table { width: 100%; border-collapse: collapse; font-size: 11pt; }
  thead tr { background: #1e293b; color: white; }
  thead th { padding: 8px 10px; text-align: left; font-weight: 600; white-space: nowrap; }
  tbody tr:nth-child(even) { background: #f8fafc; }
  tbody tr:hover { background: #eff6ff; }
  td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
  .num   { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
  .income  { color: #059669; font-weight: 600; }
  .expense { color: #dc2626; font-weight: 600; }
  .bal     { color: #1e40af; font-weight: 700; }
  .note    { color: #64748b; font-size: 10pt; }
  tfoot td { font-weight: 700; background: #f1f5f9; border-top: 2px solid #cbd5e1; padding: 8px 10px; }
  .footer { margin-top: 14px; text-align: right; font-size: 9pt; color: #94a3b8; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet"/>
</head><body>
<div class="header">
  <h1>📒 บัญชีเงินกลาง — สาขาวิชาเทคโนโลยีสารสนเทศ</h1>
  <p>พิมพ์วันที่ ${date}</p>
</div>
<div class="summary">
  <span><span class="lbl">ยอดคงเหลือปัจจุบัน:</span> <span class="val">${fmt(bal)}</span></span>
  <span><span class="lbl">จำนวนรายการ:</span> <span class="val">${ledgerData.length} รายการ</span></span>
</div>
<table>
  <thead><tr>
    <th>วันที่</th><th>รายการ</th>
    <th style="text-align:right">รายรับ (฿)</th>
    <th style="text-align:right">รายจ่าย (฿)</th>
    <th style="text-align:right">คงเหลือ (฿)</th>
    <th>หมายเหตุ</th>
  </tr></thead>
  <tbody>${rows}</tbody>
</table>
<div class="footer">ระบบการเงิน สาขา IT · พิมพ์ด้วยระบบ IT Finance</div>
</body></html>`

      const w = window.open('','_blank','width=1000,height=700')
      w.document.write(html)
      w.document.close()
      w.onload = () => { w.focus(); w.print() }
    }

    return { exportExcel, exportPDF }
  }
}).mount('#app')

// Monthly chart
const monthly = <?= json_encode(array_values($monthly)) ?>;
const labels  = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.']
const inc = monthly.map(m => m.income)
const exp = monthly.map(m => m.expense)
new Chart(document.getElementById('fundChart'), {
  type:'bar',
  data: {
    labels,
    datasets: [
      { label:'รายรับ', data:inc, backgroundColor:'rgba(16,185,129,.7)', borderRadius:4 },
      { label:'รายจ่าย', data:exp, backgroundColor:'rgba(239,68,68,.7)', borderRadius:4 },
    ]
  },
  options: {
    responsive:true,
    plugins:{ legend:{ position:'bottom', labels:{ font:{ family:'Sarabun' }, padding:12 } } },
    scales:{
      y:{ ticks:{ callback: v => '฿'+v }, grid:{ color:'#f1f5f9' } },
      x:{ grid:{ display:false } }
    }
  }
})
})
</script>
