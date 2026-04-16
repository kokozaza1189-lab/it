<?php
$role = $current_user['role'];
$can_create  = in_array($role, ['activity_staff','academic_staff','super_admin']);
$can_approve = in_array($role, ['treasurer','super_admin']);
$status_label = ['draft'=>'ร่าง','submitted'=>'ส่งแล้ว','pending'=>'รอพิจารณา','approved'=>'อนุมัติ','rejected'=>'ปฏิเสธ','completed'=>'เสร็จสิ้น'];
$status_badge = ['draft'=>'b-draft','submitted'=>'b-submitted','pending'=>'b-pending','approved'=>'b-approved','rejected'=>'b-rejected','completed'=>'b-completed'];
?>
<div id="app">

<!-- Top bar -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div class="flex gap-2 flex-wrap">
    <?php foreach ([''=>'ทั้งหมด','pending'=>'รอพิจารณา','approved'=>'อนุมัติ','rejected'=>'ปฏิเสธ','completed'=>'เสร็จสิ้น'] as $v => $l): ?>
      <a href="<?= base_url('expense').'?status='.$v ?>"
         class="badge <?= ($filters['status']==$v?'bg-blue-100 text-blue-700 border border-blue-200':'b-draft') ?> cursor-pointer py-1.5 px-3 text-sm">
        <?= $l ?>
        <?php if (isset($stats[$v])): ?><span class="ml-1 font-bold"><?= $stats[$v] ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
  <?php if ($can_create): ?>
    <a href="<?= base_url('expense/create') ?>" class="btn btn-blue">+ สร้างคำขอเบิก</a>
  <?php endif; ?>
</div>

<!-- Search -->
<form method="GET" action="<?= base_url('expense') ?>" class="card mb-5 flex gap-3 items-end">
  <div class="flex-1">
    <label class="lbl">ค้นหา</label>
    <input name="search" value="<?= htmlspecialchars($filters['search']) ?>" class="inp" placeholder="ชื่อรายการ / ผู้เบิก"/>
  </div>
  <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status']) ?>"/>
  <button type="submit" class="btn btn-blue">🔍</button>
  <a href="<?= base_url('expense') ?>" class="btn btn-gray">รีเซ็ต</a>
</form>

<!-- Expense list -->
<div class="card overflow-hidden">
  <?php if (empty($expenses)): ?>
    <div class="text-center py-16">
      <p class="text-5xl mb-3">📋</p>
      <p class="text-slate-500 text-lg font-medium">ไม่มีรายการ</p>
      <?php if ($can_create): ?>
        <a href="<?= base_url('expense/create') ?>" class="btn btn-blue mt-4">+ สร้างคำขอแรก</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr>
        <th>รหัส</th><th>ชื่อรายการ</th><th>แผนก</th><th>ผู้เบิก</th>
        <th>จำนวนเงิน</th><th>วันที่</th><th>สถานะ</th><th></th>
      </tr></thead>
      <tbody>
        <?php foreach ($expenses as $e): ?>
        <tr>
          <td class="font-mono text-xs text-slate-500"><?= $e->id ?></td>
          <td class="font-semibold text-slate-800 max-w-xs truncate"><?= htmlspecialchars($e->title) ?></td>
          <td class="text-slate-500 text-sm"><?= htmlspecialchars($e->department) ?></td>
          <td class="text-slate-600 text-sm"><?= htmlspecialchars($e->requester_name) ?></td>
          <td class="font-bold text-slate-800">฿<?= number_format($e->amount, 2) ?></td>
          <td class="text-slate-400 text-xs"><?= $e->expense_date ?></td>
          <td><span class="badge <?= $status_badge[$e->status] ?? '' ?>"><?= $status_label[$e->status] ?? $e->status ?></span></td>
          <td>
            <a href="<?= base_url('expense/'.$e->id) ?>" class="btn btn-gray btn-xs">ดูรายละเอียด</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="text-slate-400 text-xs p-4">แสดง <?= count($expenses) ?> รายการ</p>
  <?php endif; ?>
</div>

</div>
