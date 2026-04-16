<?php
$role = $current_user['role'];
$th_months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$can_view_all = in_array($role, ['treasurer','head_it','advisor','auditor','super_admin']);
?>

<!-- KPI Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="kpi">
    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wide">เงินกลางคงเหลือ</p>
    <p class="text-slate-800 text-2xl font-bold mt-1">฿<?= number_format($balance, 2) ?></p>
    <span class="text-3xl" style="position:absolute;right:16px;top:14px;opacity:.12">🏦</span>
  </div>
  <div class="kpi">
    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wide">ชำระแล้ว</p>
    <p class="text-emerald-600 text-2xl font-bold mt-1"><?= $stats['paid'] ?></p>
    <span class="text-3xl" style="position:absolute;right:16px;top:14px;opacity:.12">✅</span>
  </div>
  <div class="kpi">
    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wide">ค้างชำระ</p>
    <p class="text-red-500 text-2xl font-bold mt-1"><?= $stats['overdue'] ?></p>
    <span class="text-3xl" style="position:absolute;right:16px;top:14px;opacity:.12">⚠️</span>
  </div>
  <div class="kpi">
    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wide">รออนุมัติเบิก</p>
    <p class="text-amber-500 text-2xl font-bold mt-1"><?= $pending_exp ?></p>
    <span class="text-3xl" style="position:absolute;right:16px;top:14px;opacity:.12">💸</span>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

  <!-- Cash Flow Chart -->
  <div class="card lg:col-span-2">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-slate-800">Cash Flow <?= $year ?></h2>
    </div>
    <canvas id="cashflowChart" height="200"></canvas>
  </div>

  <!-- Fund Ledger (recent) -->
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-slate-800">รายการล่าสุด</h2>
      <?php if ($can_view_all): ?>
        <a href="<?= base_url('fund') ?>" class="text-blue-500 text-xs font-medium hover:underline">ดูทั้งหมด →</a>
      <?php endif; ?>
    </div>
    <div class="space-y-2">
      <?php foreach ($ledger as $entry): ?>
      <div class="flex items-center justify-between py-2" style="border-bottom:1px solid #f1f5f9">
        <div class="min-w-0">
          <p class="text-sm font-medium text-slate-700 truncate"><?= htmlspecialchars($entry->title) ?></p>
          <p class="text-xs text-slate-400"><?= $entry->entry_date ?></p>
        </div>
        <div class="ml-3 text-right flex-shrink-0">
          <?php if ($entry->type === 'income'): ?>
            <span class="text-emerald-600 font-semibold text-sm">+฿<?= number_format($entry->income, 2) ?></span>
          <?php else: ?>
            <span class="text-red-500 font-semibold text-sm">-฿<?= number_format($entry->expense, 2) ?></span>
          <?php endif; ?>
          <p class="text-xs text-slate-400">฿<?= number_format($entry->balance, 2) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($ledger)): ?>
        <p class="text-slate-400 text-sm text-center py-4">ยังไม่มีรายการ</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- My Payment Status (for student/staff) -->
  <?php if (!empty($my_payments)): ?>
  <div class="card">
    <h2 class="font-bold text-slate-800 mb-4">สถานะการชำระของฉัน</h2>
    <div class="grid grid-cols-2 gap-2">
      <?php
      $month_names = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน'];
      foreach ($my_payments as $p):
        $color_map = ['paid'=>'mc-paid','overdue'=>'mc-overdue','pending'=>'mc-pending','none'=>'mc-none'];
        $cls = $color_map[$p->status] ?? 'mc-none';
        $label_map = ['paid'=>'ชำระแล้ว','overdue'=>'ค้างชำระ','pending'=>'รอดำเนินการ','none'=>'ไม่เก็บ'];
      ?>
      <div class="rounded-xl p-3 text-center text-xs <?= $cls ?>" style="border:1.5px solid transparent">
        <p class="font-semibold"><?= $month_names[$p->month] ?? 'เดือน '.$p->month ?></p>
        <p class="mt-1 font-bold"><?= $label_map[$p->status] ?? $p->status ?></p>
        <?php if ($p->penalty > 0): ?>
          <p class="text-red-500 mt-0.5">ปรับ ฿<?= $p->penalty ?></p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <a href="<?= base_url('payment') ?>" class="btn btn-blue btn-sm w-full mt-4 block text-center">จัดการการชำระเงิน</a>
  </div>
  <?php endif; ?>

  <!-- Overdue list (treasurer+) -->
  <?php if ($can_view_all && !empty($overdue)): ?>
  <div class="card lg:col-span-2">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-slate-800">นิสิตค้างชำระ</h2>
      <a href="<?= base_url('payment/all') ?>" class="text-blue-500 text-xs font-medium hover:underline">ดูทั้งหมด →</a>
    </div>
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr>
          <th>ชื่อ</th><th>รหัสนิสิต</th><th>เดือน</th><th>สถานะ</th>
        </tr></thead>
        <tbody>
          <?php foreach (array_slice($overdue, 0, 8) as $o): ?>
          <tr>
            <td class="font-medium"><?= htmlspecialchars($o->student_name) ?></td>
            <td class="font-mono text-xs"><?= $o->student_id ?></td>
            <td><?= $th_months[$o->month] ?? $o->month ?></td>
            <td><span class="badge b-overdue">ค้างชำระ</span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Pending expenses (treasurer+) -->
  <?php if ($can_view_all && !empty($expenses)): ?>
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-slate-800">รออนุมัติเบิก</h2>
      <a href="<?= base_url('expense') ?>" class="text-blue-500 text-xs font-medium hover:underline">ดูทั้งหมด →</a>
    </div>
    <div class="space-y-2">
      <?php foreach ($expenses as $e): ?>
      <a href="<?= base_url('expense/'.$e->id) ?>" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors" style="border:1px solid #f1f5f9">
        <div class="min-w-0">
          <p class="text-sm font-semibold text-slate-700 truncate"><?= htmlspecialchars($e->title) ?></p>
          <p class="text-xs text-slate-400"><?= htmlspecialchars($e->requester_name) ?></p>
        </div>
        <span class="text-slate-700 font-bold text-sm ml-3 flex-shrink-0">฿<?= number_format($e->amount, 2) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<script>
// Cash Flow Chart
const monthLabels = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.']
const monthlyData = <?= json_encode(array_values($monthly)) ?>
const ctx = document.getElementById('cashflowChart')
if (ctx) {
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: monthLabels,
      datasets: [{
        label: 'รายรับ (฿)',
        data: monthlyData,
        backgroundColor: 'rgba(59,130,246,.7)',
        borderRadius: 6,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { ticks: { callback: v => '฿' + v.toLocaleString() }, grid: { color: '#f1f5f9' } },
        x: { grid: { display: false } }
      }
    }
  })
}
</script>

<style>
.mc-paid{background:#d1fae5;color:#065f46}
.mc-overdue{background:#fee2e2;color:#b91c1c}
.mc-pending{background:#fef3c7;color:#92400e}
.mc-none{background:#f8fafc;color:#94a3b8}
</style>
