<?php
$role        = $current_user['role'];
$can_approve = in_array($role, ['treasurer','super_admin']);
$status_label = ['draft'=>'ร่าง','submitted'=>'ส่งแล้ว','pending'=>'รอพิจารณา','approved'=>'อนุมัติ','rejected'=>'ปฏิเสธ','completed'=>'เสร็จสิ้น'];
$status_badge = ['draft'=>'b-draft','submitted'=>'b-submitted','pending'=>'b-pending','approved'=>'b-approved','rejected'=>'b-rejected','completed'=>'b-completed'];
$flow     = ['draft','submitted','pending','approved','completed'];
$cur_idx  = array_search($expense->status, $flow);
?>
<div id="app">
<div class="max-w-2xl mx-auto space-y-5">

  <!-- Header card -->
  <div class="card">
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-slate-500 font-mono text-sm"><?= $expense->id ?></p>
        <h2 class="text-xl font-bold text-slate-800 mt-1"><?= htmlspecialchars($expense->title) ?></h2>
        <p class="text-slate-500 text-sm mt-1"><?= htmlspecialchars($expense->department) ?> · <?= htmlspecialchars($expense->category) ?></p>
      </div>
      <span class="badge <?= $status_badge[$expense->status] ?? '' ?> text-sm py-1.5 px-3 flex-shrink-0">
        <?= $status_label[$expense->status] ?? $expense->status ?>
      </span>
    </div>

    <!-- Flow steps -->
    <div class="flex items-center mt-5 overflow-x-auto pb-1">
      <?php foreach ($flow as $i => $step):
        $done    = $cur_idx !== false && $i <= $cur_idx && $expense->status !== 'rejected';
        $current = $expense->status === $step;
        $labels  = ['draft'=>'ร่าง','submitted'=>'ส่งแล้ว','pending'=>'รอ','approved'=>'อนุมัติ','completed'=>'เสร็จ'];
      ?>
        <div class="flex items-center <?= $i > 0 ? 'flex-1' : '' ?>">
          <?php if ($i > 0): ?>
            <div class="flex-1 h-0.5 <?= $done ? 'bg-blue-400' : 'bg-slate-200' ?>"></div>
          <?php endif; ?>
          <div class="flex flex-col items-center flex-shrink-0">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                        <?= $current ? 'bg-blue-500 text-white ring-4 ring-blue-100' : ($done ? 'bg-blue-400 text-white' : 'bg-slate-200 text-slate-400') ?>">
              <?= $done && !$current ? '✓' : ($i+1) ?>
            </div>
            <span class="text-xs mt-1 text-slate-500 whitespace-nowrap"><?= $labels[$step] ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($expense->status === 'rejected' && $expense->reject_note): ?>
    <div class="mt-4 p-3 rounded-xl text-red-700 text-sm" style="background:#fef2f2;border:1px solid #fecaca">
      ❌ เหตุผลที่ปฏิเสธ: <?= htmlspecialchars($expense->reject_note) ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Details -->
  <div class="card">
    <h3 class="font-bold text-slate-800 mb-4">รายละเอียด</h3>
    <div class="grid grid-cols-2 gap-3 text-sm">
      <div><p class="text-slate-400 text-xs">ผู้เบิก</p><p class="font-medium"><?= htmlspecialchars($expense->requester_name) ?></p></div>
      <div><p class="text-slate-400 text-xs">วันที่</p><p class="font-medium"><?= $expense->expense_date ?></p></div>
      <div class="col-span-2"><p class="text-slate-400 text-xs">เหตุผล</p><p class="font-medium"><?= htmlspecialchars($expense->reason ?? '-') ?></p></div>
    </div>
  </div>

  <!-- Items table -->
  <div class="card">
    <h3 class="font-bold text-slate-800 mb-4">รายการสินค้า</h3>
    <table class="tbl">
      <thead><tr><th>#</th><th>รายการ</th><th>ราคา</th><th>จำนวน</th><th>รวม</th></tr></thead>
      <tbody>
        <?php $grand = 0; foreach ($expense->items as $i => $item):
          $sub = $item->price * $item->quantity; $grand += $sub; ?>
        <tr>
          <td class="text-slate-400"><?= $i+1 ?></td>
          <td class="font-medium"><?= htmlspecialchars($item->item_name) ?></td>
          <td>฿<?= number_format($item->price, 2) ?></td>
          <td><?= $item->quantity ?></td>
          <td class="font-bold">฿<?= number_format($sub, 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="background:#f8fafc">
          <td colspan="4" class="text-right font-bold text-slate-700">ยอดรวมทั้งหมด</td>
          <td class="font-bold text-blue-600 text-lg">฿<?= number_format($grand, 2) ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Actions — plain form POSTs, no Vue needed -->
  <div class="flex flex-wrap gap-3">
    <a href="<?= base_url('expense') ?>" class="btn btn-gray">← กลับ</a>

    <?php if ($can_approve && $expense->status === 'submitted'): ?>
      <form method="POST" action="<?= base_url('expense/pending/'.$expense->id) ?>">
        <button type="submit" class="btn btn-blue">📥 รับเรื่อง</button>
      </form>
    <?php endif; ?>

    <?php if ($can_approve && $expense->status === 'pending'): ?>
      <form method="POST" action="<?= base_url('expense/approve/'.$expense->id) ?>">
        <button type="submit" class="btn btn-green" onclick="return confirm('ยืนยันอนุมัติ?')">✓ อนุมัติ</button>
      </form>
      <button class="btn btn-red" data-modal-open="rejectModal">✕ ปฏิเสธ</button>
    <?php endif; ?>

    <?php if ($can_approve && $expense->status === 'approved'): ?>
      <form method="POST" action="<?= base_url('expense/complete/'.$expense->id) ?>">
        <button type="submit" class="btn btn-violet" onclick="return confirm('ยืนยันทำเครื่องหมายเสร็จสิ้น?')">✓ เสร็จสิ้น</button>
      </form>
    <?php endif; ?>

    <?php if ($expense->status === 'draft' && ($expense->requester_id === $current_user['student_id'] || $current_user['role'] === 'super_admin')): ?>
      <a href="<?= base_url('expense/edit/'.$expense->id) ?>" class="btn btn-blue">✏️ แก้ไขคำร้อง</a>
    <?php endif; ?>

    <?php if (in_array($expense->status,['draft','submitted']) && ($expense->requester_id === $current_user['student_id'] || $current_user['role'] === 'super_admin')): ?>
      <form method="POST" action="<?= base_url('expense/reject/'.$expense->id) ?>">
        <input type="hidden" name="note" value="ยกเลิกโดยผู้เบิก"/>
        <button type="submit" class="btn btn-red" onclick="return confirm('ยืนยันยกเลิกคำขอ?')">ยกเลิกคำขอ</button>
      </form>
    <?php endif; ?>
  </div>

  <!-- Reject modal — vanilla JS, plain form POST -->
  <div id="rejectModal" class="modal-bg" style="display:none">
    <div class="modal-box" style="max-width:400px">
      <div class="modal-header">
        <div class="flex items-center justify-between">
          <h2 class="font-bold text-slate-800">ระบุเหตุผลที่ปฏิเสธ</h2>
          <button type="button" class="btn-icon" data-modal-close="rejectModal">✕</button>
        </div>
      </div>
      <form method="POST" action="<?= base_url('expense/reject/'.$expense->id) ?>">
        <div class="modal-body">
          <label class="lbl">เหตุผล <span class="text-red-500">*</span></label>
          <textarea name="note" class="inp" rows="3" placeholder="กรอกเหตุผล..." required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-gray flex-1" data-modal-close="rejectModal">ยกเลิก</button>
          <button type="submit" class="btn btn-red flex-1">ปฏิเสธ</button>
        </div>
      </form>
    </div>
  </div>

</div>
</div>

