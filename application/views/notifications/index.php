<?php
$role = $current_user['role'];
$th_months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$treasurer_roles = ['treasurer','head_it','advisor','auditor','super_admin'];
$is_treasurer = in_array($role, $treasurer_roles);
$total_alerts = count($my_overdue) + count($submitted_exp) + count($overdue_payments) + count($pending_exp);
?>

<?php if ($total_alerts === 0): ?>
<div class="card text-center py-16">
  <p class="text-5xl mb-3">🎉</p>
  <p class="text-slate-700 text-lg font-bold">ไม่มีการแจ้งเตือน</p>
  <p class="text-slate-400 text-sm mt-1">ทุกอย่างเรียบร้อยดี</p>
</div>
<?php else: ?>

<!-- My overdue (personal) -->
<?php if (!empty($my_overdue)): ?>
<div class="card mb-5">
  <div class="flex items-center gap-3 mb-4">
    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
      <span class="text-xl">⚠️</span>
    </div>
    <div>
      <h2 class="font-bold text-slate-800">ค้างชำระของคุณ</h2>
      <p class="text-red-500 text-sm font-medium"><?= count($my_overdue) ?> เดือนที่ยังค้างชำระ</p>
    </div>
  </div>
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <?php foreach ($my_overdue as $p): ?>
    <div class="rounded-xl p-3 text-center" style="background:#fef2f2;border:1px solid #fecaca">
      <p class="font-semibold text-sm text-slate-700"><?= $th_months[$p->month] ?? 'เดือน '.$p->month ?></p>
      <p class="font-bold text-red-600 mt-1">฿<?= number_format($p->amount + $p->penalty, 2) ?></p>
      <?php if ($p->penalty > 0): ?>
      <p class="text-red-400 text-xs">ค่าปรับ +฿<?= number_format($p->penalty, 2) ?></p>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="mt-4">
    <a href="<?= base_url('payment') ?>" class="btn btn-red">ชำระเงินเลย</a>
  </div>
</div>
<?php endif; ?>

<!-- Submitted expenses waiting to be picked up (treasurer+) -->
<?php if ($is_treasurer && !empty($submitted_exp)): ?>
<div class="card mb-5">
  <div class="flex items-center gap-3 mb-4">
    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
      <span class="text-xl">📥</span>
    </div>
    <div>
      <h2 class="font-bold text-slate-800">คำขอเบิกที่ยังไม่รับเรื่อง</h2>
      <p class="text-blue-600 text-sm font-medium"><?= count($submitted_exp) ?> รายการรอดำเนินการ</p>
    </div>
    <a href="<?= base_url('expense?status=submitted') ?>" class="ml-auto text-blue-500 text-xs font-medium hover:underline">ดูทั้งหมด →</a>
  </div>
  <div class="space-y-2">
    <?php foreach (array_slice($submitted_exp, 0, 5) as $e): ?>
    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50" style="border:1px solid #f1f5f9">
      <div class="min-w-0">
        <p class="font-medium text-slate-800 text-sm truncate"><?= htmlspecialchars($e->title) ?></p>
        <p class="text-slate-400 text-xs"><?= htmlspecialchars($e->requester_name) ?> · <?= $e->expense_date ?></p>
      </div>
      <div class="flex items-center gap-2 ml-3 flex-shrink-0">
        <span class="font-bold text-slate-700 text-sm">฿<?= number_format($e->amount, 2) ?></span>
        <a href="<?= base_url('expense/'.$e->id) ?>" class="btn btn-blue btn-xs">รับเรื่อง</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Pending expenses awaiting approval (treasurer+) -->
<?php if ($is_treasurer && !empty($pending_exp)): ?>
<div class="card mb-5">
  <div class="flex items-center gap-3 mb-4">
    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
      <span class="text-xl">⏳</span>
    </div>
    <div>
      <h2 class="font-bold text-slate-800">คำขอเบิกรอพิจารณา</h2>
      <p class="text-amber-600 text-sm font-medium"><?= count($pending_exp) ?> รายการ</p>
    </div>
    <a href="<?= base_url('expense?status=pending') ?>" class="ml-auto text-blue-500 text-xs font-medium hover:underline">ดูทั้งหมด →</a>
  </div>
  <div class="space-y-2">
    <?php foreach (array_slice($pending_exp, 0, 5) as $e): ?>
    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50" style="border:1px solid #f1f5f9">
      <div class="min-w-0">
        <p class="font-medium text-slate-800 text-sm truncate"><?= htmlspecialchars($e->title) ?></p>
        <p class="text-slate-400 text-xs"><?= htmlspecialchars($e->requester_name) ?> · <?= $e->expense_date ?></p>
      </div>
      <div class="flex items-center gap-2 ml-3 flex-shrink-0">
        <span class="font-bold text-slate-700 text-sm">฿<?= number_format($e->amount, 2) ?></span>
        <a href="<?= base_url('expense/'.$e->id) ?>" class="btn btn-gray btn-xs">พิจารณา</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Overdue payments overview (treasurer+) -->
<?php if ($is_treasurer && !empty($overdue_payments)): ?>
<div class="card">
  <div class="flex items-center gap-3 mb-4">
    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
      <span class="text-xl">🔴</span>
    </div>
    <div>
      <h2 class="font-bold text-slate-800">นิสิตค้างชำระ</h2>
      <p class="text-red-500 text-sm font-medium"><?= count($overdue_payments) ?> รายการค้าง</p>
    </div>
    <a href="<?= base_url('students') ?>" class="ml-auto text-blue-500 text-xs font-medium hover:underline">ดูรายชื่อ →</a>
  </div>
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>นิสิต</th><th>รหัส</th><th>เดือน</th><th>ยอดค้าง</th><th>ค่าปรับ</th></tr></thead>
      <tbody>
        <?php foreach (array_slice($overdue_payments, 0, 10) as $p): ?>
        <tr>
          <td class="font-medium"><?= htmlspecialchars($p->student_name) ?></td>
          <td class="font-mono text-xs text-slate-500"><?= $p->student_id ?></td>
          <td><?= $th_months[$p->month] ?? $p->month ?></td>
          <td class="font-bold text-red-600">฿<?= number_format($p->amount, 2) ?></td>
          <td class="text-red-400"><?= $p->penalty > 0 ? '+฿'.number_format($p->penalty,2) : '-' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (count($overdue_payments) > 10): ?>
  <p class="text-slate-400 text-xs p-3">แสดง 10 จาก <?= count($overdue_payments) ?> รายการ</p>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>
