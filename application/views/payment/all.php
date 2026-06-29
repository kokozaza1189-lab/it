<?php
$th_months    = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$status_labels = ['paid'=>'ชำระแล้ว','overdue'=>'ค้างชำระ','pending'=>'รอ','none'=>'ไม่เก็บ'];
$status_badge  = ['paid'=>'b-paid','overdue'=>'b-overdue','pending'=>'b-pending','none'=>'b-none'];

// Pre-compute overdue and penalty counts for alert bar
$alert_overdue  = 0;
$alert_pending  = 0;
$alert_penalty  = 0;
foreach ($students as $s) {
    foreach ($active_months as $m) {
        $p = $s->payments[$m] ?? null;
        if (!$p) continue;
        if ($p->status === 'overdue')  $alert_overdue++;
        if ($p->status === 'pending')  $alert_pending++;
        if ($p->penalty > 0)           $alert_penalty++;
    }
}
?>
<div id="app">

<!-- ══ Notification alert bar ══ -->
<?php if ($alert_overdue > 0 || $alert_penalty > 0 || $alert_pending > 0): ?>
<div class="flex flex-wrap gap-3 mb-4">
  <?php if ($alert_overdue > 0): ?>
  <div class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium" style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b">
    <span>🔴</span>
    <span>ค้างชำระ <strong><?= $alert_overdue ?></strong> รายการ — ต้องดำเนินการด่วน</span>
  </div>
  <?php endif; ?>
  <?php if ($alert_pending > 0): ?>
  <div class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium" style="background:#fef3c7;border:1px solid #fcd34d;color:#92400e">
    <span>🟡</span>
    <span>รอตรวจสอบ <strong><?= $alert_pending ?></strong> รายการ — มีสลิปรอการยืนยัน</span>
  </div>
  <?php endif; ?>
  <?php if ($alert_penalty > 0): ?>
  <div class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium" style="background:#fef9c3;border:1px solid #fde047;color:#a16207">
    <span>⚠️</span>
    <span>ค้างค่าปรับ <strong><?= $alert_penalty ?></strong> รายการ — ยังไม่ได้ชำระค่าปรับครบ</span>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Stats row -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">นิสิตทั้งหมด</p><p class="text-2xl font-bold text-slate-800 mt-1"><?= $stats['total'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">ชำระแล้ว</p><p class="text-2xl font-bold text-emerald-600 mt-1"><?= $stats['paid'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">ค้างชำระ</p><p class="text-2xl font-bold text-red-500 mt-1"><?= $stats['overdue'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">รอดำเนินการ</p><p class="text-2xl font-bold text-amber-500 mt-1"><?= $stats['pending'] ?></p></div>
</div>

<!-- Year pill selector -->
<?php if (count($years) > 1): ?>
<div class="flex items-center gap-2 mb-4">
  <span class="text-xs text-slate-400 font-medium">ปีการศึกษา:</span>
  <?php foreach ($years as $y): ?>
  <a href="<?= base_url('payment/all?year='.$y.'&search='.urlencode($search)) ?>"
     class="px-3 py-1 rounded-full text-xs font-bold border transition-all"
     style="<?= $y == $year
       ? 'background:#1d4ed8;color:#fff;border-color:#1d4ed8'
       : 'background:#fff;color:#64748b;border-color:#e2e8f0' ?>">
    <?= $y ?>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Search + filter -->
<div class="card mb-5">
  <form method="GET" action="<?= base_url('payment/all') ?>" class="flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
      <label class="lbl">ค้นหา</label>
      <input name="search" value="<?= htmlspecialchars($search) ?>" class="inp" placeholder="ชื่อหรือรหัสนิสิต"/>
    </div>
    <input type="hidden" name="year" value="<?= $year ?>"/>
    <button type="submit" class="btn btn-blue">🔍 ค้นหา</button>
    <a href="<?= base_url('payment/all?year='.$year) ?>" class="btn btn-gray">รีเซ็ต</a>
    <button type="button" class="btn btn-gray" @click="exportExcel">📊 Export Excel</button>
    <a href="<?= base_url('admin/payments') ?>" class="btn btn-gray">⚙️ จัดการ</a>
  </form>
</div>

<!-- Tab selector -->
<div class="flex gap-2 mb-4">
  <button @click="activeTab='payment'"
          :class="activeTab==='payment' ? 'btn btn-blue' : 'btn btn-gray'">
    💳 การชำระเงิน
  </button>
  <button @click="activeTab='penalty'"
          :class="activeTab==='penalty' ? 'btn btn-blue' : 'btn btn-gray'">
    🚨 ค่าปรับ
    <?php if ($alert_overdue > 0): ?>
    <span style="background:#ef4444;color:#fff;border-radius:9999px;padding:1px 7px;font-size:11px;margin-left:4px"><?= $alert_overdue ?></span>
    <?php endif; ?>
  </button>
</div>

<!-- Students table -->
<div v-show="activeTab==='payment'" class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr>
        <th>#</th>
        <th>ชื่อ-สกุล</th>
        <th>รหัส</th>
        <?php foreach ($active_months as $m): ?>
          <th><?= $th_months[$m] ?></th>
        <?php endforeach; ?>
        <th>ค้างรวม</th>
      </tr></thead>
      <tbody>
        <?php foreach ($students as $i => $s):
          $total = 0;
          foreach ($active_months as $m) {
            $p = $s->payments[$m] ?? null;
            if ($p && $p->status === 'overdue') $total += $p->amount + $p->penalty;
          }
        ?>
        <tr>
          <td class="text-slate-400 text-xs"><?= $i+1 ?></td>
          <td class="font-medium text-slate-800"><?= htmlspecialchars($s->name) ?></td>
          <td class="font-mono text-xs text-slate-500"><?= $s->student_id ?></td>
          <?php foreach ($active_months as $m):
            $p   = $s->payments[$m] ?? (object)['id'=>null,'status'=>'none','amount'=>0,'penalty'=>0];
            $cls = ['paid'=>'b-paid','overdue'=>'b-overdue','pending'=>'b-pending','none'=>'b-none'][$p->status] ?? 'b-none';
            $lbl = ['paid'=>'จ่าย','overdue'=>'ค้าง','pending'=>'รอ','none'=>'-'][$p->status] ?? '-';
          ?>
          <td>
            <button class="badge <?= $cls ?> cursor-pointer hover:opacity-80"
                    @click="openStatus(<?= htmlspecialchars(json_encode([
                      'id'        => $p->id ?? null,
                      'month'     => $m,
                      'student'   => $s->name,
                      'status'    => $p->status,
                      'amount'    => isset($p->amount)  ? (float)$p->amount  : 35,
                      'penalty'   => isset($p->penalty) ? (float)$p->penalty : 0,
                      'slip_file' => isset($p->slip_file) ? $p->slip_file : null,
                    ]), ENT_QUOTES) ?>)">
              <?= $lbl ?>
              <?php
                $m_amt = (float)($p->amount ?? 0);
                $m_pen = (float)($p->penalty ?? 0);
                $m_total = $m_amt + $m_pen;
                if ($p->status === 'overdue' && $m_total > 0):
              ?>฿<?= number_format($m_total, 0) ?><?php endif; ?>
              <?php if ($p->status === 'overdue' && $m_pen > 0 && $m_amt > 0): ?>
                <span style="font-size:9px;opacity:.75">(+<?= number_format($m_pen,0) ?>)</span>
              <?php endif; ?>
              <?php if (!empty($p->slip_file)): ?><span style="font-size:9px">📎</span><?php endif; ?>
            </button>
          </td>
          <?php endforeach; ?>
          <td class="font-bold <?= $total > 0 ? 'text-red-500' : 'text-slate-400' ?>">
            <?= $total > 0 ? '฿'.number_format($total,2) : '-' ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="text-slate-400 text-xs p-4">แสดง <?= count($students) ?> รายการ</p>
</div>

<!-- Penalty tab -->
<?php
$penalty_rows = [];
foreach ($students as $s) {
    $total_fee = 0; $total_pen = 0; $months = [];
    foreach ($active_months as $m) {
        $p = $s->payments[$m] ?? null;
        if ($p && $p->status === 'overdue' && (float)$p->penalty > 0) {
            $total_fee += (float)$p->amount;
            $total_pen += (float)$p->penalty;
            $months[]   = $m;
        }
    }
    if ($total_pen > 0) {
        $penalty_rows[] = ['name'=>$s->name,'id'=>$s->student_id,'fee'=>$total_fee,'pen'=>$total_pen,'months'=>$months];
    }
}
?>
<div v-show="activeTab==='penalty'" style="display:none">
  <?php if (empty($penalty_rows)): ?>
  <div class="card text-center py-12">
    <p class="text-4xl mb-2">✅</p>
    <p class="text-slate-500 font-medium">ไม่มีนิสิตที่มีค่าปรับค้างชำระ</p>
  </div>
  <?php else: ?>
  <div class="card overflow-hidden mb-4">
    <div class="flex items-center justify-between p-4" style="border-bottom:1px solid #f1f5f9">
      <h2 class="font-bold text-slate-800">นิสิตที่มีค่าปรับค้าง (<?= count($penalty_rows) ?> คน)</h2>
      <p class="font-bold text-red-600">รวมค่าปรับ ฿<?= number_format(array_sum(array_column($penalty_rows,'pen')), 2) ?></p>
    </div>
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr>
          <th>#</th><th>ชื่อ-สกุล</th><th>รหัส</th><th>เดือนที่ค้าง</th>
          <th class="text-right">ค่าธรรมเนียม</th>
          <th class="text-right">ค่าปรับ</th>
          <th class="text-right">รวม</th>
        </tr></thead>
        <tbody>
          <?php foreach ($penalty_rows as $i => $r): ?>
          <tr>
            <td class="text-slate-400 text-xs"><?= $i+1 ?></td>
            <td class="font-medium text-slate-800"><?= htmlspecialchars($r['name']) ?></td>
            <td class="font-mono text-xs text-slate-500"><?= $r['id'] ?></td>
            <td class="text-xs text-slate-500">
              <?= implode(', ', array_map(fn($m) => $th_months[$m], $r['months'])) ?>
            </td>
            <td class="text-right text-sm">฿<?= number_format($r['fee'], 2) ?></td>
            <td class="text-right font-bold text-red-500">฿<?= number_format($r['pen'], 2) ?></td>
            <td class="text-right font-bold text-red-600">฿<?= number_format($r['fee']+$r['pen'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Update status modal -->
<div v-show="statusModal" id="statusModal" class="modal-bg" @click.self="statusModal=false" style="display:none">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">อัปเดตสถานะการชำระ</h2>
        <button @click="statusModal=false" class="btn-icon" data-modal-close="statusModal">✕</button>
      </div>
      <p class="text-slate-500 text-sm mt-1"><span v-text="editData.student"></span> — เดือน <span v-text="monthNames[editData.month]"></span></p>
    </div>
    <div class="modal-body space-y-4">
      <!-- Slip preview -->
      <div v-show="editData.slip_file" style="display:none">
        <label class="lbl mb-2">📎 สลิปที่แนบมา</label>
        <div class="rounded-xl overflow-hidden border border-slate-200" style="background:#f8fafc">
          <img :src="slipUrl" alt="slip"
               style="width:100%;max-height:220px;object-fit:contain;display:block;background:#f8fafc"/>
          <div class="px-3 py-2 text-center" style="border-top:1px solid #e2e8f0">
            <a :href="slipUrl" target="_blank" class="text-xs font-medium" style="color:#3b82f6">
              เปิดไฟล์ต้นฉบับ ↗
            </a>
          </div>
        </div>
      </div>
      <div v-show="!editData.slip_file" style="display:none;background:#f8fafc;border:1px dashed #e2e8f0" class="rounded-xl p-3 text-center text-xs text-slate-400">
        ยังไม่มีสลิปที่แนบมา
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="lbl">สถานะ</label>
          <select v-model="editData.status" class="inp">
            <option value="paid">ชำระแล้ว</option>
            <option value="overdue">ค้างชำระ</option>
            <option value="pending">รอดำเนินการ</option>
            <option value="none">ไม่เก็บ</option>
          </select>
        </div>
        <div>
          <label class="lbl">ค่าธรรมเนียม (฿)</label>
          <input type="number" step="0.01" min="0" v-model.number="editData.amount" class="inp"/>
        </div>
      </div>
      <div v-if="editData.status==='paid'">
        <label class="lbl">วันที่ชำระ</label>
        <input type="date" v-model="editData.paid_date" class="inp"/>
      </div>
      <div>
        <label class="lbl">
          ค่าปรับคงค้าง (฿)
          <span v-show="editData.penalty > 0" style="display:none;background:#fef3c7;color:#b45309;font-size:11px;padding:2px 6px;border-radius:4px;margin-left:4px">⚠️ ยังค้างอยู่</span>
        </label>
        <input type="number" step="0.01" min="0" v-model.number="editData.penalty" class="inp"
               :style="editData.penalty > 0 ? 'border-color:#fcd34d;background:#fffbeb' : ''"/>
        <p v-show="editData.penalty > 0" style="display:none;color:#b45309" class="text-xs mt-1">
          รวมที่ต้องชำระ ฿<span v-text="(Number(editData.amount||0) + Number(editData.penalty||0)).toFixed(2)"></span>
        </p>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="statusModal=false" data-modal-close="statusModal">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="saveStatus" :disabled="saving">
        <span v-if="saving" class="spin">⏳</span> บันทึก
      </button>
    </div>
  </div>
</div>

</div>

<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const paymentAllData = <?= json_encode(
  array_map(fn($s) => [
    'name'       => mb_convert_encoding($s->name, 'UTF-8', 'UTF-8'),
    'student_id' => $s->student_id,
    'payments'   => array_map(fn($p) => [
      'month'   => isset($p->month)  ? (int)$p->month   : 0,
      'status'  => $p->status  ?? 'none',
      'amount'  => (float)($p->amount  ?? 0),
      'penalty' => (float)($p->penalty ?? 0),
    ], (array)$s->payments),
  ], $students ?: []),
  JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
) ?: '[]' ?>;
const activeMonthsAll = <?= json_encode($active_months) ?>;
const thMonths = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];

const { createApp, ref, reactive, computed } = Vue
const monthNames = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม']
const SLIP_BASE_URL = '<?= base_url('assets/uploads/slips/') ?>'
createApp({
  setup() {
    const _tabParam   = new URLSearchParams(window.location.search).get('tab')
    const activeTab   = ref(_tabParam === 'penalty' ? 'penalty' : 'payment')
    const statusModal = ref(false)
    const saving      = ref(false)
    const editData    = reactive({ id:null, month:0, student:'', status:'', paid_date:'', penalty:0, amount:0, slip_file:null })
    const slipUrl     = computed(() => editData.slip_file ? SLIP_BASE_URL + editData.slip_file : '')

    function exportExcel() {
      if (!window.XLSX) return alert('โหลด SheetJS ไม่สำเร็จ')
      const statusTH = { paid:'ชำระแล้ว', overdue:'ค้างชำระ', pending:'รอดำเนินการ', none:'-' }
      const rows = paymentAllData.map(s => {
        const row = { 'ชื่อ-สกุล': s.name, 'รหัสนิสิต': s.student_id }
        activeMonthsAll.forEach(m => {
          const p = s.payments[m]
          let val = p ? statusTH[p.status] || p.status : '-'
          if (p && p.penalty > 0) val += ' (+฿' + p.penalty + ')'
          row[thMonths[m]] = val
        })
        return row
      })
      const ws = XLSX.utils.json_to_sheet(rows)
      const wb = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(wb, ws, 'Payments')
      XLSX.writeFile(wb, 'payments_' + new Date().toISOString().slice(0,10) + '.xlsx')
    }

    function openStatus(data) {
      Object.assign(editData, data, { paid_date:'' })
      statusModal.value = true
    }

    async function saveStatus() {
      if (!editData.id) { showToast('ไม่พบ ID รายการ', false); return }
      saving.value = true
      try {
        const fd = new FormData()
        fd.append('id',        editData.id)
        fd.append('status',    editData.status)
        fd.append('amount',    editData.amount || 0)
        fd.append('penalty',   editData.penalty || 0)
        fd.append('paid_date', editData.paid_date || '')
        await axios.post('<?= base_url('payment/update_status') ?>', fd)
        showToast('บันทึกสถานะแล้ว')
        statusModal.value = false
        setTimeout(() => location.reload(), 800)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      saving.value = false
    }

    return { activeTab, statusModal, saving, editData, monthNames, slipUrl, openStatus, saveStatus, exportExcel }
  }
}).mount('#app')
})
</script>
