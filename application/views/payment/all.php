<?php
$th_months    = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$status_labels = ['paid'=>'ชำระแล้ว','overdue'=>'ค้างชำระ','pending'=>'รอ','none'=>'ไม่เก็บ'];
$status_badge  = ['paid'=>'b-paid','overdue'=>'b-overdue','pending'=>'b-pending','none'=>'b-none'];
?>
<div id="app">

<!-- Stats row -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">นิสิตทั้งหมด</p><p class="text-2xl font-bold text-slate-800 mt-1"><?= $stats['total'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">ชำระแล้ว</p><p class="text-2xl font-bold text-emerald-600 mt-1"><?= $stats['paid'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">ค้างชำระ</p><p class="text-2xl font-bold text-red-500 mt-1"><?= $stats['overdue'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">รอดำเนินการ</p><p class="text-2xl font-bold text-amber-500 mt-1"><?= $stats['pending'] ?></p></div>
</div>

<!-- Search + filter -->
<div class="card mb-5">
  <form method="GET" action="<?= base_url('payment/all') ?>" class="flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
      <label class="lbl">ค้นหา</label>
      <input name="search" value="<?= htmlspecialchars($search) ?>" class="inp" placeholder="ชื่อหรือรหัสนิสิต"/>
    </div>
    <div>
      <label class="lbl">ปีการศึกษา</label>
      <input name="year" type="number" value="<?= $year ?>" class="inp" style="width:100px"/>
    </div>
    <button type="submit" class="btn btn-blue">🔍 ค้นหา</button>
    <a href="<?= base_url('payment/all') ?>" class="btn btn-gray">รีเซ็ต</a>
    <button type="button" class="btn btn-gray" @click="exportExcel">📊 Export Excel</button>
    <a href="<?= base_url('admin/payments') ?>" class="btn btn-gray">⚙️ จัดการ</a>
  </form>
</div>

<!-- Students table -->
<div class="card overflow-hidden">
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
                    @click="openStatus(<?= htmlspecialchars(json_encode(['id'=>$p->id??null,'month'=>$m,'student'=>$s->name,'status'=>$p->status,'penalty'=>isset($p->penalty)?(float)$p->penalty:0]), ENT_QUOTES) ?>)">
              <?= $lbl ?>
              <?php if (isset($p->penalty) && $p->penalty > 0): ?>+<?= $p->penalty ?>฿<?php endif; ?>
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

<!-- Update status modal -->
<div v-if="statusModal" class="modal-bg" @click.self="statusModal=false">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">อัปเดตสถานะการชำระ</h2>
        <button @click="statusModal=false" class="btn-icon">✕</button>
      </div>
      <p class="text-slate-500 text-sm mt-1">{{ editData.student }} — เดือน {{ monthNames[editData.month] }}</p>
    </div>
    <div class="modal-body space-y-4">
      <div>
        <label class="lbl">สถานะ</label>
        <select v-model="editData.status" class="inp">
          <option value="paid">ชำระแล้ว</option>
          <option value="overdue">ค้างชำระ</option>
          <option value="pending">รอดำเนินการ</option>
          <option value="none">ไม่เก็บ</option>
        </select>
      </div>
      <div v-if="editData.status==='paid'">
        <label class="lbl">วันที่ชำระ</label>
        <input type="date" v-model="editData.paid_date" class="inp"/>
      </div>
      <div v-if="editData.status==='overdue' || editData.status==='paid'">
        <label class="lbl">ค่าปรับ (฿)</label>
        <input type="number" step="0.01" v-model.number="editData.penalty" class="inp"/>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="statusModal=false">ยกเลิก</button>
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

const { createApp, ref, reactive } = Vue
const monthNames = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม']
createApp({
  setup() {
    const statusModal = ref(false)
    const saving      = ref(false)
    const editData    = reactive({ id:null, month:0, student:'', status:'', paid_date:'', penalty:0 })

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
        fd.append('paid_date', editData.paid_date || '')
        fd.append('penalty',   editData.penalty || 0)
        await axios.post('<?= base_url('payment/update_status') ?>', fd)
        showToast('บันทึกสถานะแล้ว')
        statusModal.value = false
        setTimeout(() => location.reload(), 800)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      saving.value = false
    }

    return { statusModal, saving, editData, monthNames, openStatus, saveStatus, exportExcel }
  }
}).mount('#app')
})
</script>
