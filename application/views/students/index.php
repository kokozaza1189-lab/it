<?php
$role      = $current_user['role'];
$can_edit  = in_array($role, ['treasurer','super_admin']);
$th_months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
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
          <td class="font-medium text-slate-800"><?= htmlspecialchars($s->name) ?></td>
          <td class="font-mono text-xs text-slate-500"><?= $s->student_id ?></td>
          <?php foreach ($active_months as $m):
            $p   = $s->payments[$m] ?? (object)['status'=>'none','amount'=>0,'penalty'=>0];
            $cls = ['paid'=>'b-paid','overdue'=>'b-overdue','pending'=>'b-pending','none'=>'b-none'][$p->status] ?? 'b-none';
            $lbl = ['paid'=>'จ่าย','overdue'=>'ค้าง','pending'=>'รอ','none'=>'-'][$p->status] ?? '-';
          ?>
          <td>
            <span class="badge <?= $cls ?>">
              <?= $lbl ?><?php if (isset($p->penalty) && $p->penalty > 0): ?> +<?= $p->penalty ?>฿<?php endif; ?>
            </span>
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
    'penalty' => $p->penalty ?? 0,
  ], $s->payments),
], $students), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]' ?>;
const activeMonths = <?= json_encode($active_months) ?>;
const monthLabel   = <?= json_encode(array_combine(range(1,12),['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'])) ?>;

const { createApp } = Vue
createApp({
  setup() {
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
    return { exportStudentsExcel }
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
