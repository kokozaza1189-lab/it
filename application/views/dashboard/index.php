<?php
$role = $current_user['role'];
$th_months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$can_view_all = in_array($role, ['treasurer','head_it','advisor','auditor','super_admin']);
$role_labels = ['student'=>'นิสิต','activity_staff'=>'เจ้าหน้าที่กิจกรรม','academic_staff'=>'เจ้าหน้าที่วิชาการ',
  'treasurer'=>'เหรัญญิก','head_it'=>'หัวหน้าสาขา','advisor'=>'อาจารย์ที่ปรึกษา','auditor'=>'ผู้ตรวจสอบ','super_admin'=>'ผู้ดูแลระบบ'];
?>

<!-- Welcome banner -->
<div class="rounded-2xl p-4 sm:p-5 mb-5" style="background:linear-gradient(135deg,#1e40af,#3b82f6)">
  <div class="flex items-center justify-between">
    <div class="min-w-0">
      <p class="text-blue-100 text-xs sm:text-sm">สวัสดี 👋</p>
      <h2 class="text-white text-lg sm:text-xl font-bold mt-0.5 truncate"><?= htmlspecialchars($current_user['name']) ?></h2>
      <p class="text-blue-200 text-xs sm:text-sm mt-1"><?= $role_labels[$role] ?? $role ?></p>
    </div>
    <div class="text-right flex-shrink-0 ml-3">
      <p class="text-blue-200 text-xs">เงินกลางคงเหลือ</p>
      <p class="text-white text-2xl sm:text-3xl font-bold">฿<?= number_format($balance, 0) ?></p>
      <p class="text-blue-200 text-xs mt-0.5">ปีการศึกษา <?= $year ?></p>
    </div>
  </div>
</div>

<!-- KPI row -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
  <div class="kpi">
    <span class="kpi-icon">🏦</span>
    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wide">เงินกลาง</p>
    <p class="text-slate-800 text-2xl font-bold mt-1">฿<?= number_format($balance, 0) ?></p>
    <p class="text-slate-400 text-xs mt-0.5">คงเหลือในระบบ</p>
  </div>
  <div class="kpi">
    <span class="kpi-icon">✅</span>
    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wide">ชำระแล้ว</p>
    <p class="text-emerald-600 text-2xl font-bold mt-1"><?= $stats['paid'] ?></p>
    <p class="text-slate-400 text-xs mt-0.5">รายการ</p>
  </div>
  <div class="kpi">
    <span class="kpi-icon">⚠️</span>
    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wide">ค้างชำระ</p>
    <p class="text-red-500 text-2xl font-bold mt-1"><?= $stats['overdue'] ?></p>
    <p class="text-slate-400 text-xs mt-0.5">รายการ</p>
  </div>
  <div class="kpi">
    <span class="kpi-icon">💸</span>
    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wide">รออนุมัติเบิก</p>
    <p class="text-amber-500 text-2xl font-bold mt-1"><?= $pending_exp ?></p>
    <p class="text-slate-400 text-xs mt-0.5">รายการ</p>
  </div>
</div>

<!-- Quick actions (mobile-friendly) -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
  <a href="<?= base_url('payment') ?>"
     class="card text-center hover:shadow-md transition-shadow cursor-pointer" style="padding:16px">
    <span class="text-3xl block mb-2">💳</span>
    <span class="text-xs font-semibold text-slate-700">การชำระเงินของฉัน</span>
  </a>
  <?php if (in_array($role, ['activity_staff','academic_staff','treasurer','super_admin','head_it','advisor'])): ?>
  <a href="<?= base_url('expense/create') ?>"
     class="card text-center hover:shadow-md transition-shadow cursor-pointer" style="padding:16px">
    <span class="text-3xl block mb-2">💸</span>
    <span class="text-xs font-semibold text-slate-700">ยื่นเบิกเงิน</span>
  </a>
  <?php else: ?>
  <a href="<?= base_url('pay') ?>"
     class="card text-center hover:shadow-md transition-shadow cursor-pointer" style="padding:16px">
    <span class="text-3xl block mb-2">📱</span>
    <span class="text-xs font-semibold text-slate-700">ชำระเงิน</span>
  </a>
  <?php endif; ?>
  <?php if ($can_view_all): ?>
  <a href="<?= base_url('payment/all') ?>"
     class="card text-center hover:shadow-md transition-shadow cursor-pointer" style="padding:16px">
    <span class="text-3xl block mb-2">📋</span>
    <span class="text-xs font-semibold text-slate-700">ภาพรวมการชำระ</span>
  </a>
  <a href="<?= base_url('fund') ?>"
     class="card text-center hover:shadow-md transition-shadow cursor-pointer" style="padding:16px">
    <span class="text-3xl block mb-2">🏦</span>
    <span class="text-xs font-semibold text-slate-700">เงินกลาง</span>
  </a>
  <?php else: ?>
  <a href="<?= base_url('students') ?>"
     class="card text-center hover:shadow-md transition-shadow cursor-pointer" style="padding:16px">
    <span class="text-3xl block mb-2">👥</span>
    <span class="text-xs font-semibold text-slate-700">รายชื่อนิสิต</span>
  </a>
  <a href="<?= base_url('notifications') ?>"
     class="card text-center hover:shadow-md transition-shadow cursor-pointer" style="padding:16px">
    <span class="text-3xl block mb-2">🔔</span>
    <span class="text-xs font-semibold text-slate-700">การแจ้งเตือน</span>
  </a>
  <?php endif; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

  <!-- Cash Flow Chart -->
  <div class="card lg:col-span-2">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-bold text-slate-800">กระแสเงิน <?= $year ?></h2>
        <p class="text-xs text-slate-400 mt-0.5">รายรับรายเดือน (บาท)</p>
      </div>
      <div class="flex items-center gap-3 text-xs">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full inline-block" style="background:#3b82f6"></span>รายรับ</span>
      </div>
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
    <?php if (empty($ledger)): ?>
      <p class="text-slate-400 text-sm text-center py-8">ยังไม่มีรายการ</p>
    <?php else: ?>
    <div class="space-y-1">
      <?php foreach ($ledger as $entry): ?>
      <div class="flex items-center justify-between py-2.5" style="border-bottom:1px solid #f8fafc">
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-slate-700 truncate"><?= htmlspecialchars($entry->title) ?></p>
          <p class="text-xs text-slate-400"><?= $entry->entry_date ?></p>
        </div>
        <div class="ml-3 text-right flex-shrink-0">
          <?php if ($entry->type === 'income'): ?>
            <p class="text-emerald-600 font-semibold text-sm">+฿<?= number_format($entry->income, 0) ?></p>
          <?php else: ?>
            <p class="text-red-500 font-semibold text-sm">-฿<?= number_format($entry->expense, 0) ?></p>
          <?php endif; ?>
          <p class="text-xs text-slate-400">฿<?= number_format($entry->balance, 0) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- My Payment Status -->
  <?php if (!empty($my_payments)): ?>
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-slate-800">สถานะการชำระของฉัน</h2>
      <a href="<?= base_url('payment') ?>" class="text-blue-500 text-xs font-medium hover:underline">ดูทั้งหมด →</a>
    </div>
    <div class="grid grid-cols-2 gap-2">
      <?php
      $month_names = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
        7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
      foreach ($my_payments as $p):
        $cls = ['paid'=>'mc-paid','overdue'=>'mc-overdue','pending'=>'mc-pending','none'=>'mc-none'][$p->status] ?? 'mc-none';
        $lbl = ['paid'=>'ชำระแล้ว','overdue'=>'ค้างชำระ','pending'=>'รอดำเนินการ','none'=>'ไม่เก็บ'][$p->status] ?? $p->status;
      ?>
      <div class="month-cell <?= $cls ?>">
        <p class="font-semibold text-xs"><?= $th_months[$p->month] ?></p>
        <p class="font-bold mt-1"><?= $lbl ?></p>
        <?php if ($p->penalty > 0): ?>
          <p class="text-xs mt-0.5" style="color:#dc2626">+฿<?= $p->penalty ?></p>
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
          <th>ชื่อ-สกุล</th><th>รหัสนิสิต</th><th>เดือน</th><th>สถานะ</th>
        </tr></thead>
        <tbody>
          <?php foreach (array_slice($overdue, 0, 8) as $o): ?>
          <tr>
            <td class="font-medium"><?= htmlspecialchars($o->student_name) ?></td>
            <td class="font-mono text-xs text-slate-500"><?= $o->student_id ?></td>
            <td><?= $th_months[$o->month] ?? $o->month ?></td>
            <td><span class="badge b-overdue">ค้างชำระ</span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Pending expenses -->
  <?php if ($can_view_all && !empty($expenses)): ?>
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-slate-800">รออนุมัติเบิก</h2>
      <a href="<?= base_url('expense') ?>" class="text-blue-500 text-xs font-medium hover:underline">ดูทั้งหมด →</a>
    </div>
    <div class="space-y-2">
      <?php foreach ($expenses as $e): ?>
      <a href="<?= base_url('expense/'.$e->id) ?>"
         class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors"
         style="border:1px solid #f1f5f9">
        <div class="min-w-0 flex-1">
          <p class="text-sm font-semibold text-slate-700 truncate"><?= htmlspecialchars($e->title) ?></p>
          <p class="text-xs text-slate-400"><?= htmlspecialchars($e->requester_name) ?></p>
        </div>
        <span class="text-slate-700 font-bold text-sm ml-3 flex-shrink-0">฿<?= number_format($e->amount, 0) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<script>
const monthLabels = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.']
const monthlyData = <?= json_encode(array_values($monthly)) ?>;
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
        hoverBackgroundColor: '#3b82f6',
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: { label: ctx => '฿' + ctx.raw.toLocaleString() }
        }
      },
      scales: {
        y: {
          ticks: { callback: v => '฿' + v.toLocaleString(), font:{size:11} },
          grid: { color: '#f1f5f9' }
        },
        x: { grid: { display: false }, ticks: { font:{size:11} } }
      }
    }
  })
}
</script>
