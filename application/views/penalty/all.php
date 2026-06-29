<?php
$month_names = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
$th_short    = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$total_outstanding = $summary['total_fee'] + $summary['total_penalty'];
?>
<div id="app">

<!-- QR Payment Info -->
<?php if (!empty($settings['qr_image'])): ?>
<div class="card mb-5" style="padding:0;overflow:hidden;border:2px solid #1565c0">
  <div style="background:#0d47a1;padding:10px 20px;display:flex;align-items:center;justify-content:space-between">
    <div style="display:flex;align-items:center;gap:10px">
      <span style="color:white;font-weight:700;font-size:14px;letter-spacing:.5px">🔲 THAI QR PAYMENT</span>
    </div>
    <span style="background:white;border-radius:6px;padding:3px 10px;font-size:11px;font-weight:700;color:#0d47a1">PromptPay</span>
  </div>
  <div style="padding:16px 20px;display:flex;align-items:center;gap:20px;background:linear-gradient(135deg,#e8f5e9,#e3f2fd)">
    <div style="background:white;border-radius:12px;padding:10px;box-shadow:0 2px 10px rgba(0,0,0,.12);flex-shrink:0">
      <img src="<?= base_url('assets/uploads/qr/'.$settings['qr_image']) ?>"
           alt="QR PromptPay" style="width:110px;height:110px;object-fit:contain;display:block"/>
    </div>
    <div>
      <p style="color:#1565c0;font-weight:600;font-size:12px;margin-bottom:4px">สแกน QR เพื่อรับชำระค่าปรับ</p>
      <?php if (!empty($settings['bank_name'])): ?>
      <p style="font-weight:700;color:#0d47a1;font-size:16px;margin:0"><?= htmlspecialchars($settings['bank_name']) ?></p>
      <?php endif; ?>
      <?php if (!empty($settings['bank_account'])): ?>
      <p style="color:#546e7a;font-size:13px;margin:2px 0 0;font-family:monospace"><?= htmlspecialchars($settings['bank_account']) ?></p>
      <?php endif; ?>
      <p style="color:#90a4ae;font-size:11px;margin-top:6px">รับเงินได้จากทุกธนาคาร · Accepts all banks</p>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Stats row -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
  <div class="card text-center py-4">
    <p class="text-2xl font-bold text-red-600">฿<?= number_format($total_outstanding, 0) ?></p>
    <p class="text-xs text-slate-400 mt-1">ยอดค้างรวม</p>
  </div>
  <div class="card text-center py-4">
    <p class="text-2xl font-bold text-amber-600">฿<?= number_format($summary['total_penalty'], 0) ?></p>
    <p class="text-xs text-slate-400 mt-1">ค่าปรับสะสม</p>
  </div>
  <div class="card text-center py-4">
    <p class="text-2xl font-bold text-slate-700"><?= $summary['students'] ?></p>
    <p class="text-xs text-slate-400 mt-1">นิสิตมียอดค้าง</p>
  </div>
  <div class="card text-center py-4">
    <p class="text-2xl font-bold text-blue-600"><?= $summary['pending_count'] ?></p>
    <p class="text-xs text-slate-400 mt-1">รอตรวจสลิป</p>
  </div>
</div>

<!-- Year pill selector -->
<?php if (count($years) > 1): ?>
<div class="flex items-center gap-2 mb-4">
  <span class="text-xs text-slate-400 font-medium">ปีการศึกษา:</span>
  <?php foreach ($years as $y): ?>
  <a href="<?= base_url('penalty/overview?year='.$y.'&status='.urlencode($status)) ?>"
     class="px-3 py-1 rounded-full text-xs font-bold border transition-all"
     style="<?= $y == $year
       ? 'background:#1d4ed8;color:#fff;border-color:#1d4ed8'
       : 'background:#fff;color:#64748b;border-color:#e2e8f0' ?>">
    <?= $y ?>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Filter bar -->
<div class="card mb-5">
  <form method="GET" action="<?= base_url('penalty/overview') ?>" class="flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-0">
      <label class="lbl">ค้นหานิสิต</label>
      <input name="search" value="<?= htmlspecialchars($search) ?>" class="inp"
             placeholder="ชื่อ หรือ รหัสนิสิต"/>
    </div>
    <div style="width:160px">
      <label class="lbl">สถานะ</label>
      <select name="status" class="inp">
        <option value="overdue"  <?= $status==='overdue'  ? 'selected':'' ?>>ค้างชำระ (overdue)</option>
        <option value="pending"  <?= $status==='pending'  ? 'selected':'' ?>>รอดำเนินการ (pending)</option>
      </select>
    </div>
    <input type="hidden" name="year" value="<?= $year ?>"/>
    <button type="submit" class="btn btn-blue">🔍 ค้นหา</button>
    <a href="<?= base_url('penalty?year='.$year) ?>" class="btn btn-gray">รีเซ็ต</a>
  </form>
</div>

<!-- Results -->
<?php if (empty($cases)): ?>
<div class="card text-center py-16">
  <p class="text-5xl mb-3">✅</p>
  <p class="text-slate-500 text-lg font-medium">ไม่มีรายการ<?= $status==='overdue'?'ค้างชำระ':'รอดำเนินการ' ?></p>
  <?php if ($search): ?>
  <p class="text-slate-400 text-sm mt-1">ลองเปลี่ยนคำค้นหา</p>
  <?php endif; ?>
</div>
<?php else: ?>

<p class="text-sm text-slate-500 mb-4 font-medium">แสดง <?= count($cases) ?> นิสิต ·
  <?= array_sum(array_map(fn($c) => count($c->records), $cases)) ?> รายการ</p>

<?php foreach ($cases as $student):
  $grand = $student->total_fee + $student->total_pen;
  $months_list = implode(', ', array_map(fn($r) => $th_short[$r->month], $student->records));
?>
<div class="card mb-4" style="border:1.5px solid <?= $status==='overdue'?'#fca5a5':'#93c5fd' ?>">

  <!-- Student header -->
  <div class="flex items-center justify-between mb-4 pb-3" style="border-bottom:1px solid #f1f5f9">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
           style="background:<?= $status==='overdue'?'#ef4444':'#3b82f6' ?>">
        <?= mb_substr(preg_replace('/นาย|นางสาว|น\.ส\./u', '', $student->name), 0, 1) ?>
      </div>
      <div>
        <p class="font-bold text-slate-800"><?= htmlspecialchars($student->name) ?></p>
        <p class="text-xs text-slate-400 font-mono"><?= $student->student_id ?></p>
      </div>
    </div>
    <div class="text-right">
      <p class="font-bold text-lg <?= $status==='overdue'?'text-red-600':'text-blue-600' ?>">
        ฿<?= number_format($grand, 2) ?>
      </p>
      <p class="text-xs text-slate-400">เดือน: <?= $months_list ?></p>
    </div>
  </div>

  <!-- Monthly records -->
  <div class="space-y-2">
    <?php foreach ($student->records as $r):
      $bill = (float)$r->amount + (float)$r->penalty;
    ?>
    <div class="flex items-center justify-between rounded-xl px-4 py-3"
         style="background:#f8fafc;border:1px solid #e2e8f0">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0"
             style="background:<?= $r->penalty>0?'#fee2e2':'#f0fdf4' ?>;color:<?= $r->penalty>0?'#dc2626':'#16a34a' ?>">
          <?= $r->month ?>
        </div>
        <div>
          <p class="font-semibold text-slate-700 text-sm"><?= $month_names[$r->month] ?> <?= $year ?></p>
          <div class="flex gap-3 text-xs text-slate-400 mt-0.5">
            <span>ค่าธรรมเนียม ฿<?= number_format($r->amount, 2) ?></span>
            <?php if ($r->penalty > 0): ?>
            <span class="text-red-400 font-medium">+ ค่าปรับ ฿<?= number_format($r->penalty, 2) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-shrink-0">
        <p class="font-bold text-slate-800">฿<?= number_format($bill, 2) ?></p>
        <?php if ($r->slip_file): ?>
        <a href="<?= base_url('assets/uploads/slips/'.$r->slip_file) ?>" target="_blank"
           class="btn btn-gray btn-xs" title="ดูสลิป">🧾</a>
        <?php endif; ?>
        <span class="badge <?= $r->status==='overdue'?'b-overdue':'b-pending' ?> text-xs">
          <?= $r->status==='overdue'?'ค้าง':'รอ' ?>
        </span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Subtotal row -->
  <div class="flex items-center justify-between mt-3 pt-3 font-semibold"
       style="border-top:1px dashed #e2e8f0">
    <span class="text-sm text-slate-500">รวม <?= count($student->records) ?> เดือน</span>
    <div class="flex gap-4 text-sm">
      <?php if ($student->total_pen > 0): ?>
      <span class="text-amber-600">ค่าปรับ ฿<?= number_format($student->total_pen, 2) ?></span>
      <?php endif; ?>
      <span class="text-red-600 font-bold">รวม ฿<?= number_format($grand, 2) ?></span>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php endif; ?>
</div>
