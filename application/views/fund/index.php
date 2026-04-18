<?php
$role        = $current_user['role'];
$can_adjust  = in_array($role, ['super_admin','treasurer']);
$th_months   = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
?>
<div id="app" v-cloak>

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
      <div class="flex gap-2">
        <button class="btn btn-gray btn-sm" @click="exportExcel">📊 Export</button>
        <?php if ($can_adjust): ?>
          <button class="btn btn-blue btn-sm" @click="showAdjust=true">+ เพิ่มรายการ</button>
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

<!-- Adjust modal -->
<div v-if="showAdjust" class="modal-bg" @click.self="showAdjust=false">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">เพิ่มรายการบัญชี</h2>
        <button @click="showAdjust=false" class="btn-icon">✕</button>
      </div>
    </div>
    <form method="POST" action="<?= base_url('fund/adjust') ?>" class="modal-body space-y-4">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="lbl">ประเภท</label>
          <select name="type" v-model="form.type" class="inp">
            <option value="income">💚 รายรับ</option>
            <option value="expense">🔴 รายจ่าย</option>
          </select>
        </div>
        <div>
          <label class="lbl">วันที่</label>
          <input name="txn_date" v-model="form.txn_date" type="date" class="inp" required/>
        </div>
      </div>
      <div>
        <label class="lbl">ชื่อรายการ <span class="text-red-500">*</span></label>
        <input name="title" v-model="form.title" class="inp" placeholder="เช่น เก็บเงินห้อง มิ.ย. 68" required/>
      </div>
      <div>
        <label class="lbl">จำนวนเงิน (฿) <span class="text-red-500">*</span></label>
        <input name="amount" v-model="form.amount" type="number" step="0.01" min="0.01" class="inp" placeholder="0.00" required/>
      </div>
      <div>
        <label class="lbl">หมายเหตุ</label>
        <input name="note" v-model="form.note" class="inp" placeholder="(ไม่บังคับ)"/>
      </div>
      <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-gray flex-1" @click="showAdjust=false">ยกเลิก</button>
        <button type="submit" class="btn btn-blue flex-1">บันทึก</button>
      </div>
    </form>
  </div>
</div>

</div>

<script>
const ledgerData = <?= json_encode(array_map(fn($e) => [
  'entry_date' => $e->entry_date,
  'title'      => $e->title,
  'income'     => $e->type === 'income' ? $e->income : 0,
  'expense'    => $e->type === 'expense' ? $e->expense : 0,
  'balance'    => $e->balance,
  'note'       => $e->note ?? '',
], $ledger)) ?>;

const { createApp, ref, reactive } = Vue
createApp({
  setup() {
    const showAdjust = ref(false)
    const today = new Date().toISOString().slice(0,10)
    const form = reactive({ type:'income', title:'', amount:'', note:'', txn_date: today })

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
      const wb = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(wb, ws, 'Ledger')
      XLSX.writeFile(wb, 'fund_ledger_' + new Date().toISOString().slice(0,10) + '.xlsx')
    }

    return { showAdjust, form, exportExcel }
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
</script>
