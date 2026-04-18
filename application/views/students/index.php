<?php
$role      = $current_user['role'];
$can_edit  = in_array($role, ['treasurer','super_admin']);
$th_months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
// stats: count across active months
$paid_count = $overdue_count = 0;
foreach ($students as $s) {
  foreach ($active_months as $m) {
    $p = $s->payments[$m] ?? null;
    if (!$p) continue;
    if ($p->status === 'paid')    $paid_count++;
    if ($p->status === 'overdue') $overdue_count++;
  }
}
?>
<div id="app">

<!-- Stats -->
<div class="grid grid-cols-3 gap-4 mb-5">
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">นิสิตทั้งหมด</p>
    <p class="text-2xl font-bold text-slate-800 mt-1"><?= count($students) ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">ชำระแล้ว</p>
    <p class="text-2xl font-bold text-emerald-600 mt-1"><?= $paid_count ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">ค้างชำระ</p>
    <p class="text-2xl font-bold text-red-500 mt-1"><?= $overdue_count ?></p></div>
</div>

<!-- Search -->
<form method="GET" action="<?= base_url('students') ?>" class="card mb-5 flex flex-wrap gap-3 items-end">
  <div class="flex-1 min-w-0" style="min-width:180px">
    <label class="lbl">ค้นหา</label>
    <input name="search" value="<?= htmlspecialchars($search) ?>" class="inp" placeholder="ชื่อหรือรหัสนิสิต"/>
  </div>
  <button type="submit" class="btn btn-blue">🔍 ค้นหา</button>
  <a href="<?= base_url('students') ?>" class="btn btn-gray">รีเซ็ต</a>
  <button type="button" class="btn btn-gray" @click="exportStudentsExcel">📊 Export Excel</button>
  <?php if ($can_edit): ?>
  <a href="<?= base_url('admin/students') ?>" class="btn btn-gray">⚙️ จัดการนิสิต</a>
  <?php endif; ?>
</form>

<!-- Table -->
<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr>
        <th>#</th>
        <th>ชื่อ-สกุล</th>
        <th>รหัสนิสิต</th>
        <?php foreach ($active_months as $m): ?>
          <th><?= $th_months[$m] ?></th>
        <?php endforeach; ?>
        <th>ค้างรวม</th>
      </tr></thead>
      <tbody>
        <?php foreach ($students as $i => $s):
          $total_due = 0;
          foreach ($active_months as $m) {
            $p = $s->payments[$m] ?? null;
            if ($p && $p->status === 'overdue') $total_due += $p->amount + $p->penalty;
          }
        ?>
        <tr>
          <td class="text-slate-400 text-xs"><?= $i+1 ?></td>
          <td>
            <button class="text-left hover:text-blue-600 font-medium text-slate-800 transition-colors"
                    @click="openDetail(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)">
              <?= htmlspecialchars($s->name) ?>
            </button>
          </td>
          <td class="font-mono text-xs text-slate-500"><?= $s->student_id ?></td>
          <?php foreach ($active_months as $m):
            $p   = $s->payments[$m] ?? (object)['id'=>null,'status'=>'none','amount'=>0,'penalty'=>0];
            $cls = ['paid'=>'b-paid','overdue'=>'b-overdue','pending'=>'b-pending','none'=>'b-none'][$p->status] ?? 'b-none';
            $lbl = ['paid'=>'จ่าย','overdue'=>'ค้าง','pending'=>'รอ','none'=>'-'][$p->status] ?? '-';
          ?>
          <td>
            <?php if ($can_edit && $p->status !== 'none'): ?>
              <button class="badge <?= $cls ?> cursor-pointer hover:opacity-75"
                      @click="openEdit(<?= htmlspecialchars(json_encode(['id'=>$p->id??null,'month'=>$m,'name'=>$s->name,'status'=>$p->status]), ENT_QUOTES) ?>)">
                <?= $lbl ?><?php if (isset($p->penalty) && $p->penalty > 0): ?> +<?= $p->penalty ?>฿<?php endif; ?>
              </button>
            <?php else: ?>
              <span class="badge <?= $cls ?>"><?= $lbl ?></span>
            <?php endif; ?>
          </td>
          <?php endforeach; ?>
          <td class="font-bold <?= $total_due > 0 ? 'text-red-500' : 'text-slate-300' ?>">
            <?= $total_due > 0 ? '฿'.number_format($total_due,2) : '-' ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="text-slate-400 text-xs p-4">แสดง <?= count($students) ?> นิสิต</p>
</div>

<!-- Detail modal -->
<div v-if="detailModal && selectedStudent" class="modal-bg" @click.self="detailModal=false">
  <div class="modal-box">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="font-bold text-slate-800">{{ selectedStudent.name }}</h2>
          <p class="text-slate-500 text-sm font-mono">{{ selectedStudent.student_id }}</p>
        </div>
        <button @click="detailModal=false" class="btn-icon">✕</button>
      </div>
    </div>
    <div class="modal-body">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div v-for="(pay, m) in selectedStudent.payments" :key="m"
             class="rounded-xl p-3 text-center text-sm"
             :class="statusClass(pay.status)">
          <p class="font-semibold text-xs">{{ monthLabel[m] }}</p>
          <p class="font-bold mt-1">{{ statusLabel(pay.status) }}</p>
          <p v-if="pay.penalty > 0" class="text-red-500 text-xs">+฿{{ pay.penalty }}</p>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="detailModal=false">ปิด</button>
    </div>
  </div>
</div>

<!-- Edit status modal -->
<div v-if="editModal" class="modal-bg" @click.self="editModal=false">
  <div class="modal-box" style="max-width:380px">
    <div class="modal-header">
      <h2 class="font-bold text-slate-800">แก้ไขสถานะ</h2>
      <p class="text-slate-500 text-sm">{{ editData.name }} — เดือน {{ monthLabel[editData.month] }}</p>
    </div>
    <div class="modal-body space-y-4">
      <div>
        <label class="lbl">สถานะ</label>
        <select v-model="editData.status" class="inp">
          <option value="paid">ชำระแล้ว</option>
          <option value="overdue">ค้างชำระ</option>
          <option value="pending">รอดำเนินการ</option>
        </select>
      </div>
      <div v-if="editData.status==='paid'">
        <label class="lbl">วันที่ชำระ</label>
        <input type="date" v-model="editData.paid_date" class="inp"/>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="editModal=false">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="saveEdit" :disabled="saving">
        <span v-if="saving" class="spin">⏳</span> บันทึก
      </button>
    </div>
  </div>
</div>

</div>

<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const studentsData = <?= json_encode(array_map(fn($s) => [
  'name'       => $s->name,
  'student_id' => $s->student_id,
  'payments'   => array_map(fn($p) => [
    'month'   => $p->month,
    'status'  => $p->status,
    'amount'  => $p->amount,
    'penalty' => $p->penalty,
  ], $s->payments),
], $students)) ?>;
const activeMonths = <?= json_encode($active_months) ?>;

const { createApp, ref, reactive } = Vue
const monthLabel = <?= json_encode(array_combine(range(1,12),['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'])) ?>;
createApp({
  setup() {
    const detailModal     = ref(false)
    const editModal       = ref(false)
    const saving          = ref(false)
    const selectedStudent = ref(null)
    const editData        = reactive({ id:null, month:0, name:'', status:'', paid_date:'' })

    function exportStudentsExcel() {
      if (!window.XLSX) return alert('โหลด SheetJS ไม่สำเร็จ')
      const statusTH = { paid:'ชำระแล้ว', overdue:'ค้างชำระ', pending:'รอดำเนินการ', none:'-' }
      const rows = studentsData.map(s => {
        const row = { 'ชื่อ-สกุล': s.name, 'รหัสนิสิต': s.student_id }
        activeMonths.forEach(m => {
          const p = s.payments[m]
          row[monthLabel[m]] = p ? statusTH[p.status] || p.status : '-'
          if (p && p.penalty > 0) row[monthLabel[m] + ' (ค่าปรับ)'] = p.penalty
        })
        return row
      })
      const ws = XLSX.utils.json_to_sheet(rows)
      const wb = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(wb, ws, 'Students')
      XLSX.writeFile(wb, 'students_' + new Date().toISOString().slice(0,10) + '.xlsx')
    }

    function statusClass(s) {
      return { paid:'mc-paid', overdue:'mc-overdue', pending:'mc-pending', none:'mc-none' }[s] || 'mc-none'
    }
    function statusLabel(s) {
      return { paid:'ชำระแล้ว', overdue:'ค้างชำระ', pending:'รอ', none:'ไม่เก็บ' }[s] || s
    }
    function openDetail(s) { selectedStudent.value = s; detailModal.value = true }
    function openEdit(d)   { Object.assign(editData, d, {paid_date:''}); editModal.value = true }

    async function saveEdit() {
      if (!editData.id) return
      saving.value = true
      try {
        await axios.post('<?= base_url('students/update_payment') ?>', {
          id: editData.id, status: editData.status, paid_date: editData.paid_date
        })
        showToast('บันทึกแล้ว')
        editModal.value = false
        setTimeout(() => location.reload(), 800)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      saving.value = false
    }

    return { detailModal, editModal, saving, selectedStudent, editData, monthLabel,
             statusClass, statusLabel, openDetail, openEdit, saveEdit, exportStudentsExcel }
  }
}).mount('#app')
})
</script>
<style>
.mc-paid{background:#d1fae5;color:#065f46}
.mc-overdue{background:#fee2e2;color:#b91c1c}
.mc-pending{background:#fef3c7;color:#92400e}
.mc-none{background:#f8fafc;color:#94a3b8}
</style>
