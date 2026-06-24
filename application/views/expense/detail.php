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
      <?php if (!empty($expense->bank_name) || !empty($expense->bank_account)): ?>
      <div>
        <p class="text-slate-400 text-xs">🏦 ธนาคาร</p>
        <p class="font-medium"><?= htmlspecialchars($expense->bank_name ?? '-') ?></p>
      </div>
      <div>
        <p class="text-slate-400 text-xs">เลขที่บัญชี</p>
        <p class="font-medium font-mono"><?= htmlspecialchars($expense->bank_account ?? '-') ?></p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Items table -->
  <div class="card">
    <h3 class="font-bold text-slate-800 mb-4">รายการสินค้า</h3>
    <?php
      $hasDiscount = array_sum(array_map(fn($it) => (float)($it->discount ?? 0), $expense->items)) > 0;
    ?>
    <table class="tbl">
      <thead><tr>
        <th>#</th><th>รายการ</th><th>ราคา/ชิ้น</th><th>จำนวน</th>
        <?php if ($hasDiscount): ?><th class="text-amber-600">ส่วนลด</th><?php endif; ?>
        <th>ยอดสุทธิ</th>
      </tr></thead>
      <tbody>
        <?php $grand = 0; $totalDisc = 0;
          foreach ($expense->items as $i => $item):
            $disc = (float)($item->discount ?? 0);
            $sub  = max(0, $item->price * $item->quantity - $disc);
            $grand += $sub; $totalDisc += $disc;
        ?>
        <tr>
          <td class="text-slate-400"><?= $i+1 ?></td>
          <td class="font-medium"><?= htmlspecialchars($item->item_name) ?></td>
          <td>฿<?= number_format($item->price, 2) ?></td>
          <td><?= $item->quantity ?></td>
          <?php if ($hasDiscount): ?>
          <td class="<?= $disc > 0 ? 'text-amber-600 font-semibold' : 'text-slate-300' ?>">
            <?= $disc > 0 ? '-฿'.number_format($disc, 2) : '—' ?>
          </td>
          <?php endif; ?>
          <td class="font-bold <?= $disc > 0 ? 'text-green-600' : '' ?>">฿<?= number_format($sub, 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if ($hasDiscount && $totalDisc > 0): ?>
        <tr style="background:#fffbeb">
          <td colspan="<?= $hasDiscount ? 5 : 4 ?>" class="text-right text-amber-700 font-medium text-sm">รวมส่วนลดทั้งหมด</td>
          <td class="font-bold text-amber-600">-฿<?= number_format($totalDisc, 2) ?></td>
        </tr>
        <?php endif; ?>
        <tr style="background:#f8fafc">
          <td colspan="<?= $hasDiscount ? 5 : 4 ?>" class="text-right font-bold text-slate-700">ยอดรวมทั้งหมด</td>
          <td class="font-bold text-blue-600 text-lg">฿<?= number_format($grand, 2) ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Attachment -->
  <?php if (!empty($expense->attachment)): ?>
  <?php
    $attExt = strtolower(pathinfo($expense->attachment, PATHINFO_EXTENSION));
    $attUrl = base_url('assets/uploads/expense_docs/' . $expense->attachment);
  ?>
  <div class="card">
    <h3 class="font-bold text-slate-800 mb-4">📎 เอกสารแนบ / หลักฐาน</h3>
    <?php if (in_array($attExt, ['jpg','jpeg','png'])): ?>
      <a href="<?= $attUrl ?>" target="_blank" class="block">
        <img src="<?= $attUrl ?>" alt="เอกสารแนบ"
             style="max-width:100%;max-height:420px;border-radius:10px;object-fit:contain;border:1px solid #e2e8f0"/>
      </a>
      <a href="<?= $attUrl ?>" target="_blank" class="btn btn-gray btn-sm mt-3 inline-flex items-center gap-1">
        🔍 เปิดในแท็บใหม่
      </a>
    <?php else: ?>
      <a href="<?= $attUrl ?>" target="_blank" class="btn btn-gray inline-flex items-center gap-2">
        📄 ดาวน์โหลดเอกสาร (<?= strtoupper($attExt) ?>)
      </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

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

