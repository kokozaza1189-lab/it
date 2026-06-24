<?php
$th_month_short = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$month_names    = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                   7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
$status_badge   = ['paid'=>'b-paid','overdue'=>'b-overdue','pending'=>'b-pending','none'=>'b-none'];

$total_due      = 0;
$overdue_months = [];
$payable        = [];  // months that can be paid (overdue or pending)
foreach ($payments as $p) {
    if ($p->status === 'overdue') { $total_due += $p->amount + $p->penalty; $overdue_months[] = $p; }
    if (in_array($p->status, ['overdue','pending'])) $payable[] = $p;
}

$bank_account = $settings['bank_account'] ?? '202-3-90895-9';
$bank_name    = $settings['bank_name']    ?? 'นายไพศาล กองมณี';
$monthly_fee  = (float)($settings['monthly_fee'] ?? 50);
$penalty_day  = (float)($settings['penalty_per_day'] ?? 5);
$role         = $current_user['role'];
$has_student  = !empty($current_user['student_id']);
?>
<div id="app">

<!-- ── Year selector bar ──────────────────────────────────────── -->
<?php if (count($years) > 1): ?>
<div class="flex items-center gap-2 mb-4">
  <span class="text-xs text-slate-400 font-medium">ปีการศึกษา:</span>
  <?php foreach ($years as $y): ?>
  <a href="<?= base_url('payment?year='.$y) ?>"
     class="px-3 py-1 rounded-full text-xs font-bold border transition-all"
     style="<?= $y == $year
       ? 'background:#1d4ed8;color:#fff;border-color:#1d4ed8'
       : 'background:#fff;color:#64748b;border-color:#e2e8f0' ?>">
    <?= $y ?>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($has_student): ?>
<!-- ── Notification alerts ──────────────────────────────────── -->
<?php
$my_overdue  = array_filter($payments, fn($p) => $p->status === 'overdue');
$my_pending  = array_filter($payments, fn($p) => $p->status === 'pending');
$my_penalty  = array_filter($payments, fn($p) => $p->penalty > 0);
$month_names_th = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                   7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
?>
<?php if (!empty($my_overdue)): ?>
<div class="mb-4 rounded-xl p-4" style="background:#fee2e2;border:1.5px solid #fca5a5">
  <div class="flex items-start gap-3">
    <span style="font-size:24px;line-height:1">🔴</span>
    <div class="flex-1">
      <p class="font-bold text-red-700 mb-1">มียอดค้างชำระ <?= count($my_overdue) ?> เดือน — กรุณาชำระโดยด่วน</p>
      <?php foreach ($my_overdue as $p): ?>
      <div class="flex items-center justify-between text-sm mt-1">
        <span class="text-red-600">📅 <?= $month_names_th[$p->month] ?> <?= $year ?></span>
        <span class="font-bold text-red-700">฿<?= number_format($p->amount + $p->penalty, 2) ?>
          <?php if ($p->penalty > 0): ?>
            <span class="text-xs font-normal">(รวมค่าปรับ ฿<?= number_format($p->penalty, 2) ?>)</span>
          <?php endif; ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($my_pending)): ?>
<div class="mb-4 rounded-xl p-4" style="background:#fef3c7;border:1.5px solid #fcd34d">
  <div class="flex items-start gap-3">
    <span style="font-size:24px;line-height:1">🟡</span>
    <div class="flex-1">
      <p class="font-bold text-amber-700 mb-1">รอการยืนยัน <?= count($my_pending) ?> เดือน — เจ้าหน้าที่กำลังตรวจสอบสลิป</p>
      <?php foreach ($my_pending as $p): ?>
      <p class="text-sm text-amber-600 mt-0.5">📅 <?= $month_names_th[$p->month] ?> <?= $year ?> — รอตรวจสอบ</p>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php
// Show penalty balance alert (paid but still owes penalty)
$penalty_only = array_filter($payments, fn($p) => $p->status === 'paid' && $p->penalty > 0);
if (!empty($penalty_only)):
?>
<div class="mb-4 rounded-xl p-4" style="background:#fef9c3;border:1.5px solid #fde047">
  <div class="flex items-start gap-3">
    <span style="font-size:24px;line-height:1">⚠️</span>
    <div class="flex-1">
      <p class="font-bold mb-1" style="color:#a16207">มีค่าปรับค้างชำระ <?= count($penalty_only) ?> รายการ</p>
      <?php foreach ($penalty_only as $p): ?>
      <div class="flex items-center justify-between text-sm mt-1">
        <span style="color:#92400e">📅 <?= $month_names_th[$p->month] ?> <?= $year ?> — ชำระแล้วแต่ยังค้างค่าปรับ</span>
        <span class="font-bold" style="color:#b45309">฿<?= number_format($p->penalty, 2) ?></span>
      </div>
      <?php endforeach; ?>
      <p class="text-xs mt-2" style="color:#92400e">กรุณาติดต่อเหรัญญิกเพื่อชำระค่าปรับส่วนที่เหลือ</p>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── Student view ──────────────────────────────────────────── -->

<!-- Header card -->
<div class="card mb-4">
  <div class="flex items-start justify-between">
    <div>
      <p class="text-slate-400 text-xs">รหัสนิสิต</p>
      <p class="font-bold text-slate-800 text-xl font-mono"><?= htmlspecialchars($current_user['student_id']) ?></p>
      <p class="text-slate-600 text-sm mt-0.5"><?= htmlspecialchars($current_user['name']) ?></p>
    </div>
    <div class="text-right">
      <p class="text-slate-400 text-xs">ยอดค้างทั้งหมด</p>
      <p class="font-bold text-2xl <?= $total_due > 0 ? 'text-red-500' : 'text-emerald-600' ?>">
        <?= $total_due > 0 ? '฿'.number_format($total_due, 2) : '฿0.00' ?>
      </p>
      <span class="badge <?= $total_due > 0 ? 'b-overdue' : 'b-paid' ?> mt-1">
        <?= $total_due > 0 ? 'มียอดค้าง' : 'ชำระครบ' ?>
      </span>
    </div>
  </div>
</div>

<!-- Month grid -->
<div class="card mb-4">
  <div class="flex items-center justify-between mb-4">
    <p class="font-bold text-slate-800">สถานะการชำระเงิน ปี <?= $year ?></p>
    <div class="flex gap-2 flex-wrap text-xs">
      <span class="badge b-paid">จ่ายแล้ว</span>
      <span class="badge b-overdue">ค้าง</span>
      <span class="badge b-pending">รอ</span>
    </div>
  </div>
  <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
    <?php foreach ($payments as $p):
      $cls = ['paid'=>'mc-paid','overdue'=>'mc-overdue','pending'=>'mc-pending','none'=>'mc-none'][$p->status] ?? 'mc-none';
    ?>
    <div class="month-cell <?= $cls ?>"
         <?php if (in_array($p->status, ['overdue','pending'])): ?>
         style="cursor:pointer" @click="openPayModal(<?= (int)$p->id ?>, <?= (int)$p->month ?>, <?= (float)$p->amount ?>, <?= (float)$p->penalty ?>)"
         <?php endif; ?>>
      <p class="font-bold text-base"><?= $p->month ?></p>
      <p class="text-xs mt-0.5"><?= $th_month_short[$p->month] ?></p>
      <?php if ($p->status !== 'none'): ?>
        <p class="text-xs font-semibold mt-1">฿<?= number_format($p->amount, 0) ?></p>
      <?php else: ?>
        <p class="text-xs mt-1">—</p>
      <?php endif; ?>
      <?php if ($p->penalty > 0): ?>
        <p class="text-xs mt-0.5" style="color:#dc2626">+฿<?= (float)$p->penalty ?>ปรับ</p>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Bills — penalty-style detailed view -->
<?php if (!empty($payable)): ?>
<p class="font-semibold text-slate-700 text-sm px-1 mb-3">📌 บิลที่ต้องชำระ <?= count($payable) ?> รายการ</p>
<?php foreach ($payable as $p):
  $total_bill = (float)$p->amount + (float)$p->penalty;
  $is_overdue = $p->status === 'overdue';
  $days = ($penalty_day > 0 && $p->penalty > 0) ? round($p->penalty / $penalty_day) : 0;
?>
<div class="card mb-4" style="border:2px solid <?= $is_overdue ? '#fca5a5' : '#93c5fd' ?>">

  <!-- Bill header -->
  <div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
           style="background:<?= $is_overdue ? '#ef4444' : '#3b82f6' ?>">
        <?= $p->month ?>
      </div>
      <div>
        <p class="font-bold text-slate-800 text-base"><?= $month_names[$p->month] ?> <?= $year ?></p>
        <span class="badge <?= $is_overdue ? 'b-overdue' : 'b-pending' ?> text-xs">
          <?= $is_overdue ? 'ค้างชำระ' : 'รอดำเนินการ' ?>
        </span>
      </div>
    </div>
    <p class="font-bold text-xl <?= $is_overdue ? 'text-red-600' : 'text-blue-600' ?>">
      ฿<?= number_format($total_bill, 2) ?>
    </p>
  </div>

  <!-- Breakdown -->
  <div class="rounded-xl p-4 mb-4 space-y-2" style="background:#f8fafc;border:1px solid #e2e8f0">
    <div class="flex justify-between items-center text-sm">
      <span class="text-slate-500">💰 ค่าธรรมเนียมรายเดือน</span>
      <span class="font-semibold text-slate-700">฿<?= number_format($p->amount, 2) ?></span>
    </div>
    <?php if ($p->penalty > 0): ?>
    <div class="flex justify-between items-center text-sm">
      <span class="text-red-500">🚨 ค่าปรับ<?= $days > 0 ? " ({$days} วัน × ฿{$penalty_day})" : '' ?></span>
      <span class="font-bold text-red-500">+฿<?= number_format($p->penalty, 2) ?></span>
    </div>
    <?php else: ?>
    <div class="flex justify-between items-center text-sm">
      <span class="text-slate-400">ค่าปรับ</span>
      <span class="text-slate-400">฿0.00</span>
    </div>
    <?php endif; ?>
    <div class="flex justify-between items-center font-bold text-base pt-2"
         style="border-top:2px dashed #e2e8f0">
      <span class="text-slate-700">รวมที่ต้องชำระ</span>
      <span class="text-red-600">฿<?= number_format($total_bill, 2) ?></span>
    </div>
  </div>

  <!-- Action -->
  <?php if ($is_overdue): ?>
  <button class="btn btn-blue w-full text-base"
          style="padding:12px"
          @click="openPayModal(<?= (int)$p->id ?>, <?= (int)$p->month ?>, <?= (float)$p->amount ?>, <?= (float)$p->penalty ?>)">
    💳 ชำระบิลนี้ &nbsp; ฿<?= number_format($total_bill, 2) ?>
  </button>
  <?php else: ?>
  <div class="rounded-xl py-3 px-4 text-center text-sm font-medium" style="background:#eff6ff;color:#1d4ed8">
    ⏳ รอเจ้าหน้าที่ตรวจสอบสลิปการโอน
    <?php if (!empty($p->slip_file)): ?>
    <br><a href="<?= base_url('assets/uploads/slips/'.$p->slip_file) ?>" target="_blank"
           class="text-xs text-blue-500 underline mt-1 inline-block">ดูสลิปที่แนบ</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>
<?php endforeach; ?>

<!-- Total bar when multiple overdue -->
<?php if (!empty($overdue_months)): ?>
<div class="card mb-4" style="background:#fef2f2;border:2px solid #fca5a5">
  <div class="flex items-center justify-between">
    <div>
      <p class="font-bold text-red-700">💳 ยอดรวมที่ต้องชำระทั้งหมด</p>
      <p class="text-xs text-red-400 mt-0.5">กรุณาชำระแยกตามเดือน 1 สลิป / 1 เดือน</p>
    </div>
    <p class="font-bold text-2xl text-red-600">฿<?= number_format($total_due, 2) ?></p>
  </div>
</div>
<?php endif; ?>

<?php elseif (!empty($payments)): ?>
<!-- All paid -->
<div class="card mb-4" style="border:1.5px solid #6ee7b7;background:#f0fdf4">
  <div class="flex items-center gap-3">
    <span style="font-size:32px">🎉</span>
    <div>
      <p class="font-bold text-emerald-700">ชำระครบทุกเดือนแล้ว!</p>
      <p class="text-sm text-emerald-600">ไม่มียอดค้างชำระในขณะนี้</p>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- History table -->
<div class="card hide-on-mobile">
  <h2 class="font-bold text-slate-800 mb-4">ประวัติการชำระ</h2>
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr>
        <th>เดือน</th><th>ค่าธรรมเนียม</th><th>ค่าปรับค้าง</th><th>รวม</th><th>สถานะ</th><th>วันที่ชำระ</th>
      </tr></thead>
      <tbody>
        <?php $has_history = false; foreach ($payments as $p): if ($p->status === 'none') continue; $has_history = true; ?>
        <tr <?= ($p->status === 'overdue') ? 'style="background:#fff5f5"' : (($p->status === 'paid' && $p->penalty > 0) ? 'style="background:#fffbeb"' : '') ?>>
          <td class="font-medium"><?= $month_names[$p->month] ?? $p->month ?> <?= $year ?></td>
          <td>฿<?= number_format($p->amount, 2) ?></td>
          <td>
            <?php if ($p->penalty > 0): ?>
              <span class="font-bold" style="color:#b45309">฿<?= number_format($p->penalty, 2) ?></span>
              <?php if ($p->status === 'paid'): ?>
                <span class="badge" style="background:#fef3c7;color:#b45309;font-size:10px;margin-left:2px">⚠️ค้าง</span>
              <?php endif; ?>
            <?php else: ?>—
            <?php endif; ?>
          </td>
          <td class="font-bold <?= $p->status === 'overdue' ? 'text-red-600' : '' ?>">฿<?= number_format($p->amount + $p->penalty, 2) ?></td>
          <td><span class="badge <?= $status_badge[$p->status] ?? '' ?>">
            <?= ['paid'=>'ชำระแล้ว','overdue'=>'ค้างชำระ','pending'=>'รอดำเนินการ'][$p->status] ?? $p->status ?>
          </span></td>
          <td class="text-slate-400 text-xs"><?= $p->paid_date ?: '—' ?></td>
        </tr>
        <?php endforeach; if (!$has_history): ?>
        <tr><td colspan="6" class="text-center text-slate-400 py-8">ยังไม่มีประวัติการชำระ</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php else: ?>
<!-- ── Non-student / Staff view ─────────────────────────────── -->
<div class="card mb-5" style="border:1.5px solid #dbeafe;background:#eff6ff">
  <div class="flex items-start gap-3">
    <span style="font-size:28px">ℹ️</span>
    <div>
      <p class="font-bold text-blue-800">หน้านี้สำหรับนิสิตเพื่อดูสถานะและชำระเงิน</p>
      <p class="text-sm text-blue-700 mt-1">บัญชีของคุณไม่ใช่บัญชีนิสิต หากต้องการช่วยนิสิตชำระเงินให้ใช้ฟอร์มชำระเงินสาธารณะด้านล่าง</p>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── Bank info card (ทุก role เห็น) ───────────────────────── -->
<div class="card mt-4">
  <h2 class="font-bold text-slate-800 text-base mb-4">🏦 ข้อมูลการโอนเงิน</h2>
  <div class="space-y-2.5">
    <div class="flex justify-between text-sm">
      <span class="text-slate-500">ธนาคาร</span>
      <span class="font-medium text-slate-800">ธนาคารกสิกรไทย (KBank)</span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-slate-500">ชื่อบัญชี</span>
      <span class="font-medium text-slate-800"><?= htmlspecialchars($bank_name) ?></span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-slate-500">เลขบัญชี</span>
      <span class="font-mono font-bold text-slate-800 tracking-wider"><?= htmlspecialchars($bank_account) ?></span>
    </div>
    <div class="flex justify-between text-sm" style="border-top:1px solid #f1f5f9;padding-top:10px;margin-top:4px">
      <span class="text-slate-500">ค่าธรรมเนียม/เดือน</span>
      <span class="font-bold text-blue-700">
        <?php
          $fee_jan = (float)($settings['fee_january'] ?? 35);
          $fee_std = (float)($settings['monthly_fee'] ?? 50);
          echo $fee_jan !== $fee_std
            ? "฿{$fee_jan} (ม.ค.) / ฿{$fee_std} (เดือนอื่น)"
            : "฿" . number_format($fee_std, 2);
        ?>
      </span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-slate-500">ครบกำหนด</span>
      <span class="font-medium text-slate-700">วันที่ <?= $settings['due_day'] ?? 8 ?> ของทุกเดือน</span>
    </div>
  </div>
  <div class="mt-4 rounded-lg px-4 py-3 text-xs text-slate-500" style="background:#f0f9ff;border:1px solid #bae6fd">
    💡 กรอกยอดตามที่แสดงในแต่ละบิล (รวมค่าปรับถ้ามี) แล้วกดปุ่ม <strong>ชำระบิล</strong> เพื่ออัปโหลดสลิป
  </div>
  <?php if (!$has_student): ?>
  <a href="<?= base_url('pay') ?>" target="_blank"
     class="btn btn-blue w-full text-center mt-4"
     style="display:flex;align-items:center;justify-content:center;gap:8px">
    📱 เปิดฟอร์มชำระเงินสาธารณะ
  </a>
  <p class="text-xs text-slate-400 text-center mt-1">ฟอร์มนี้ไม่ต้องล็อกอิน นิสิตสามารถเปิดได้โดยตรง</p>
  <?php endif; ?>
</div>

<!-- QR Payment modal (for payable months) -->
<div v-show="payModal" id="payModal" class="modal-bg" @click.self="payModal=false" style="display:none">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">ชำระเงิน — <span v-text="monthLabel"></span></h2>
        <button @click="payModal=false" class="btn-icon" data-modal-close="payModal">✕</button>
      </div>
    </div>
    <div class="modal-body space-y-4">
      <div class="rounded-xl p-4" style="background:#ede7f6;border:1px solid #d1c4e9">
        <div class="space-y-1.5 mb-2">
          <div class="flex justify-between text-sm">
            <span class="text-slate-600">ค่าธรรมเนียม</span>
            <span class="font-medium">฿<span v-text="amount.toFixed(2)"></span></span>
          </div>
          <div v-if="penalty > 0" class="flex justify-between text-sm">
            <span class="text-red-500">ค่าปรับ</span>
            <span class="font-medium text-red-500">+฿<span v-text="penalty.toFixed(2)"></span></span>
          </div>
          <div class="flex justify-between font-bold pt-2" style="border-top:1px solid #d1c4e9">
            <span>รวม</span>
            <span style="color:#673ab7;font-size:1.2rem">฿<span v-text="total.toFixed(2)"></span></span>
          </div>
        </div>
        <div class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs text-slate-500 mt-2" style="background:rgba(255,255,255,.6)">
          💡 กรอกยอด <strong style="color:#673ab7">฿<span v-text="total.toFixed(2)"></span></strong> เมื่อโอนเงิน
        </div>
      </div>
      <div>
        <label class="lbl">แนบสลิปการโอนเงิน <span class="text-red-500">*</span></label>
        <label :class="['block border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-colors', slipFile ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:border-blue-400 hover:bg-blue-50']">
          <input type="file" class="hidden" accept="image/*,.pdf" @change="onSlip"/>
          <span v-show="!slipFile">
            <span class="text-3xl block mb-2">📎</span>
            <p class="text-sm text-slate-600 font-medium">แตะเพื่อเลือกไฟล์</p>
            <p class="text-xs text-slate-400 mt-1">PNG, JPG, PDF ไม่เกิน 5MB</p>
          </span>
          <span v-show="slipFile" class="text-emerald-700" style="display:none">
            <span class="text-3xl block mb-1">✅</span>
            <p class="text-sm font-medium" v-text="slipName"></p>
            <p class="text-xs text-slate-400 mt-1">แตะเพื่อเปลี่ยนไฟล์</p>
          </span>
        </label>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="payModal=false" data-modal-close="payModal">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="submitPayment" :disabled="submitting || !slipFile">
        <span v-show="submitting" style="display:none" class="spin">⏳</span>
        <span v-show="submitting" style="display:none">กำลังส่ง...</span>
        <span v-show="!submitting">ยืนยันชำระ</span>
      </button>
    </div>
  </div>
</div>

</div>

<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const { createApp, ref, computed } = Vue;
const monthNames = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
createApp({
  setup() {
    const payModal   = ref(false)
    const payId      = ref(null)
    const payMonth   = ref(0)
    const amount     = ref(0)
    const penalty    = ref(0)
    const total      = computed(() => amount.value + penalty.value)
    const submitting = ref(false)
    const slipFile   = ref(null)
    const slipName   = ref('')
    const monthLabel = computed(() => monthNames[payMonth.value] || '')

    function openPayModal(id, month, amt, pen) {
      payId.value = id; payMonth.value = month
      amount.value = amt; penalty.value = pen
      slipFile.value = null; slipName.value = ''
      payModal.value = true
    }

    function onSlip(e) {
      const f = e.target.files[0]
      if (f) { slipFile.value = f; slipName.value = f.name }
    }

    async function submitPayment() {
      if (!slipFile.value) return
      submitting.value = true
      const fd = new FormData()
      fd.append('month', payMonth.value)
      fd.append('year',  <?= (int)$year ?>)
      fd.append('slip',  slipFile.value)
      try {
        await axios.post('<?= base_url('payment/submit') ?>', fd)
        showToast('ส่งหลักฐานการชำระแล้ว รอการตรวจสอบ')
        payModal.value = false
        setTimeout(() => location.reload(), 1000)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      submitting.value = false
    }

    return { payModal, payMonth, amount, penalty, total, submitting, slipFile, slipName,
             monthLabel, openPayModal, onSlip, submitPayment }
  }
}).mount('#app')
})
</script>
