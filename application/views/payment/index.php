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
$role         = $current_user['role'];
$has_student  = !empty($current_user['student_id']);
?>
<div id="app">

<?php if ($has_student): ?>
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

<!-- Overdue bills -->
<?php if (!empty($payable)): ?>
<p class="font-semibold text-slate-700 text-sm px-1 mb-2">📌 บิลที่ต้องชำระ</p>
<?php foreach ($payable as $p): ?>
<div class="card mb-3" style="border:2px solid <?= $p->status==='overdue' ? '#fca5a5' : '#93c5fd' ?>">
  <div class="flex items-center justify-between mb-3">
    <div>
      <p class="font-bold text-slate-800">บิลเดือน <?= $th_month_short[$p->month] ?> <?= $year ?></p>
      <p class="text-xs text-slate-400 mt-0.5">รหัสนิสิต: <?= htmlspecialchars($current_user['student_id']) ?></p>
    </div>
    <span class="badge <?= $p->status==='overdue'?'b-overdue':'b-pending' ?>">
      <?= $p->status==='overdue' ? 'ค้างชำระ' : 'รอดำเนินการ' ?>
    </span>
  </div>
  <div class="space-y-1 mb-3">
    <div class="flex justify-between text-sm">
      <span class="text-slate-500">ค่าธรรมเนียมรายเดือน</span>
      <span class="font-medium">฿<?= number_format($p->amount, 2) ?></span>
    </div>
    <?php if ($p->penalty > 0): ?>
    <div class="flex justify-between text-sm">
      <span class="text-red-500">ค่าปรับ (<?= round($p->penalty / 5) ?> วัน × 5฿)</span>
      <span class="font-medium text-red-500">฿<?= number_format($p->penalty, 2) ?></span>
    </div>
    <?php endif; ?>
    <div class="flex justify-between font-bold pt-2 mt-1" style="border-top:1px solid #fee2e2">
      <span>รวมบิลนี้</span>
      <span class="text-red-500">฿<?= number_format($p->amount + $p->penalty, 2) ?></span>
    </div>
  </div>
  <button class="btn btn-blue w-full"
          @click="openPayModal(<?= (int)$p->id ?>, <?= (int)$p->month ?>, <?= (float)$p->amount ?>, <?= (float)$p->penalty ?>)">
    📱 ชำระบิลนี้ ฿<?= number_format($p->amount + $p->penalty, 2) ?>
  </button>
</div>
<?php endforeach; ?>
<?php elseif (!empty($payments)): ?>
<!-- All paid → show summary -->
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
        <th>เดือน</th><th>ค่าธรรมเนียม</th><th>ค่าปรับ</th><th>รวม</th><th>สถานะ</th><th>วันที่ชำระ</th>
      </tr></thead>
      <tbody>
        <?php $has_history = false; foreach ($payments as $p): if ($p->status === 'none') continue; $has_history = true; ?>
        <tr>
          <td class="font-medium"><?= $month_names[$p->month] ?? $p->month ?> <?= $year ?></td>
          <td>฿<?= number_format($p->amount, 2) ?></td>
          <td><?= $p->penalty > 0 ? '฿'.number_format($p->penalty, 2) : '—' ?></td>
          <td class="font-bold">฿<?= number_format($p->amount + $p->penalty, 2) ?></td>
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

<!-- ── QR Payment Form (ทุก role เห็น) ──────────────────────── -->
<div class="card mt-4">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-bold text-slate-800 text-base">💳 ฟอร์มชำระค่าธรรมเนียม</h2>
    <span class="badge b-pending text-xs">PromptPay / QR</span>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 items-start">

    <!-- QR + bank info -->
    <div class="rounded-2xl p-4" style="background:linear-gradient(145deg,#e8f5e9,#e3f2fd,#ede7f6)">
      <div class="rounded-xl px-4 py-2 flex items-center justify-between mb-3"
           style="background:linear-gradient(135deg,#1a3a2a,#1b5e20)">
        <div>
          <p class="font-bold text-base text-white leading-none">make</p>
          <p class="text-xs" style="color:#a5d6a7">by KBank</p>
        </div>
        <div class="w-6 h-6 rounded-full flex items-center justify-center"
             style="background:rgba(255,255,255,.15)">
          <span class="text-white text-xs font-bold">K</span>
        </div>
      </div>
      <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 12px rgba(0,0,0,.12)">
        <div class="flex items-center gap-2 px-3 py-2" style="background:#1a237e">
          <div class="w-7 h-7 rounded-full flex items-center justify-center" style="background:#00bcd4">
            <span class="text-white text-xs font-bold">+</span>
          </div>
          <div>
            <p class="text-white font-bold text-xs tracking-widest">THAI QR</p>
            <p class="text-xs tracking-widest" style="color:#90caf9;font-size:9px">PAYMENT</p>
          </div>
        </div>
        <div class="px-4 py-4 text-center">
          <div class="inline-block text-white text-xs font-bold px-4 py-0.5 rounded mb-3 tracking-wider"
               style="background:#1a237e;font-size:11px">PromptPay</div>
          <div class="flex justify-center mb-3">
            <div class="rounded-xl border border-gray-100 p-2 bg-white inline-block">
              <svg width="130" height="130" viewBox="0 0 150 150">
                <rect x="5" y="5" width="40" height="40" rx="5" fill="#111"/>
                <rect x="10" y="10" width="30" height="30" rx="3" fill="white"/>
                <rect x="15" y="15" width="20" height="20" rx="2" fill="#111"/>
                <rect x="105" y="5" width="40" height="40" rx="5" fill="#111"/>
                <rect x="110" y="10" width="30" height="30" rx="3" fill="white"/>
                <rect x="115" y="15" width="20" height="20" rx="2" fill="#111"/>
                <rect x="5" y="105" width="40" height="40" rx="5" fill="#111"/>
                <rect x="10" y="110" width="30" height="30" rx="3" fill="white"/>
                <rect x="15" y="115" width="20" height="20" rx="2" fill="#111"/>
                <circle cx="75" cy="75" r="18" fill="white" stroke="#e0e0e0" stroke-width="1.5"/>
                <text x="75" y="80" text-anchor="middle" font-size="13" fill="#673ab7" font-weight="bold">QR</text>
                <rect x="55" y="7" width="7" height="7" rx="1" fill="#111" opacity=".8"/>
                <rect x="65" y="7" width="7" height="7" rx="1" fill="#111" opacity=".5"/>
                <rect x="75" y="7" width="7" height="7" rx="1" fill="#111" opacity=".9"/>
                <rect x="85" y="7" width="7" height="7" rx="1" fill="#111" opacity=".3"/>
                <rect x="95" y="7" width="7" height="7" rx="1" fill="#111" opacity=".7"/>
                <rect x="55" y="17" width="7" height="7" rx="1" fill="#111" opacity=".6"/>
                <rect x="75" y="17" width="7" height="7" rx="1" fill="#111" opacity=".9"/>
                <rect x="95" y="17" width="7" height="7" rx="1" fill="#111" opacity=".4"/>
                <rect x="55" y="27" width="7" height="7" rx="1" fill="#111" opacity=".7"/>
                <rect x="65" y="27" width="7" height="7" rx="1" fill="#111" opacity=".5"/>
                <rect x="85" y="27" width="7" height="7" rx="1" fill="#111" opacity=".8"/>
                <rect x="7" y="55" width="7" height="7" rx="1" fill="#111" opacity=".7"/>
                <rect x="7" y="65" width="7" height="7" rx="1" fill="#111" opacity=".5"/>
                <rect x="7" y="85" width="7" height="7" rx="1" fill="#111" opacity=".9"/>
                <rect x="7" y="95" width="7" height="7" rx="1" fill="#111" opacity=".4"/>
                <rect x="17" y="55" width="7" height="7" rx="1" fill="#111" opacity=".6"/>
                <rect x="27" y="65" width="7" height="7" rx="1" fill="#111" opacity=".8"/>
                <rect x="17" y="85" width="7" height="7" rx="1" fill="#111" opacity=".5"/>
                <rect x="27" y="95" width="7" height="7" rx="1" fill="#111" opacity=".7"/>
                <rect x="136" y="55" width="7" height="7" rx="1" fill="#111" opacity=".8"/>
                <rect x="136" y="65" width="7" height="7" rx="1" fill="#111" opacity=".4"/>
                <rect x="136" y="75" width="7" height="7" rx="1" fill="#111" opacity=".9"/>
                <rect x="136" y="85" width="7" height="7" rx="1" fill="#111" opacity=".6"/>
                <rect x="126" y="55" width="7" height="7" rx="1" fill="#111" opacity=".5"/>
                <rect x="116" y="65" width="7" height="7" rx="1" fill="#111" opacity=".7"/>
                <rect x="126" y="85" width="7" height="7" rx="1" fill="#111" opacity=".8"/>
                <rect x="55" y="136" width="7" height="7" rx="1" fill="#111" opacity=".6"/>
                <rect x="65" y="136" width="7" height="7" rx="1" fill="#111" opacity=".9"/>
                <rect x="75" y="136" width="7" height="7" rx="1" fill="#111" opacity=".4"/>
                <rect x="85" y="136" width="7" height="7" rx="1" fill="#111" opacity=".8"/>
                <rect x="55" y="126" width="7" height="7" rx="1" fill="#111" opacity=".5"/>
                <rect x="75" y="126" width="7" height="7" rx="1" fill="#111" opacity=".7"/>
                <rect x="95" y="126" width="7" height="7" rx="1" fill="#111" opacity=".6"/>
                <rect x="55" y="55" width="7" height="7" rx="1" fill="#111" opacity=".4"/>
                <rect x="95" y="55" width="7" height="7" rx="1" fill="#111" opacity=".7"/>
                <rect x="55" y="95" width="7" height="7" rx="1" fill="#111" opacity=".8"/>
                <rect x="95" y="95" width="7" height="7" rx="1" fill="#111" opacity=".5"/>
              </svg>
            </div>
          </div>
          <p class="text-xs font-medium mb-0.5" style="color:#00897b">สแกน QR เพื่อโอนเข้าบัญชี</p>
          <p class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($bank_name) ?></p>
          <p class="text-xs text-slate-500 tracking-wider"><?= htmlspecialchars($bank_account) ?></p>
          <div class="mt-2 rounded-xl px-3 py-1.5 text-xs text-slate-500" style="background:#f5f5f5">
            💬 บัญชีออมทรัพย์ IT สาขาวิชาเทคโนโลยีสารสนเทศ
          </div>
        </div>
      </div>
      <div class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs text-slate-500 mt-2" style="background:rgba(255,255,255,.7)">
        💡 กรอกจำนวน <strong style="color:#673ab7">฿<?= number_format($monthly_fee, 2) ?></strong> ตอนโอน (บวกค่าปรับถ้ามี)
      </div>
    </div>

    <!-- Slip upload form -->
    <div class="space-y-4">
      <?php if ($has_student && !empty($payable)): ?>
      <div>
        <label class="lbl">เลือกเดือนที่ชำระ</label>
        <div class="space-y-2">
          <?php foreach ($payable as $p): ?>
          <button type="button"
                  class="w-full flex items-center justify-between px-4 py-3 rounded-xl font-medium text-sm transition-colors"
                  style="background:#eff6ff;border:1.5px solid #bfdbfe;color:#1d4ed8"
                  @click="openPayModal(<?= (int)$p->id ?>, <?= (int)$p->month ?>, <?= (float)$p->amount ?>, <?= (float)$p->penalty ?>)">
            <span>📅 <?= $th_month_short[$p->month] ?> <?= $year ?></span>
            <span class="font-bold">฿<?= number_format($p->amount + $p->penalty, 2) ?></span>
          </button>
          <?php endforeach; ?>
        </div>
        <p class="text-xs text-slate-400 mt-2">กดเลือกเดือนเพื่อเปิดฟอร์มชำระพร้อมอัปโหลดสลิป</p>
      </div>
      <?php elseif ($has_student): ?>
      <div class="rounded-xl p-4 text-center" style="background:#f0fdf4;border:1.5px solid #bbf7d0">
        <span style="font-size:32px">✅</span>
        <p class="font-bold text-emerald-700 mt-2">ชำระครบทุกเดือนแล้ว</p>
        <p class="text-sm text-emerald-600">ไม่มียอดที่ต้องชำระในขณะนี้</p>
      </div>
      <?php else: ?>
      <!-- Staff: link to public pay form -->
      <div class="space-y-3">
        <p class="text-sm text-slate-600 font-medium">สำหรับช่วยนิสิตชำระเงิน ใช้ฟอร์มสาธารณะ:</p>
        <a href="<?= base_url('pay') ?>" target="_blank"
           class="btn btn-blue w-full text-center"
           style="display:flex;align-items:center;justify-content:center;gap:8px">
          📱 เปิดฟอร์มชำระเงินสาธารณะ
        </a>
        <p class="text-xs text-slate-400">ฟอร์มนี้ไม่ต้องล็อกอิน นิสิตสามารถเปิดได้โดยตรง</p>
      </div>
      <?php endif; ?>

      <!-- Bank details box -->
      <div class="rounded-xl p-4 space-y-2" style="background:#f8fafc;border:1px solid #e2e8f0">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">ข้อมูลการโอนเงิน</p>
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">ชื่อบัญชี</span>
          <span class="font-medium text-slate-800"><?= htmlspecialchars($bank_name) ?></span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">เลขบัญชี</span>
          <span class="font-mono font-bold text-slate-800"><?= htmlspecialchars($bank_account) ?></span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">ค่าธรรมเนียม/เดือน</span>
          <span class="font-bold text-blue-700">฿<?= number_format($monthly_fee, 2) ?></span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">ครบกำหนด</span>
          <span class="font-medium text-slate-700">วันที่ <?= $settings['due_day'] ?? 8 ?> ของทุกเดือน</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- QR Payment modal (for payable months) -->
<div v-if="payModal" class="modal-bg" @click.self="payModal=false">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">ชำระเงิน — {{ monthLabel }}</h2>
        <button @click="payModal=false" class="btn-icon">✕</button>
      </div>
    </div>
    <div class="modal-body space-y-4">
      <div class="rounded-xl p-4" style="background:#ede7f6;border:1px solid #d1c4e9">
        <div class="space-y-1.5 mb-2">
          <div class="flex justify-between text-sm">
            <span class="text-slate-600">ค่าธรรมเนียม</span>
            <span class="font-medium">฿{{ amount.toFixed(2) }}</span>
          </div>
          <div v-if="penalty > 0" class="flex justify-between text-sm">
            <span class="text-red-500">ค่าปรับ</span>
            <span class="font-medium text-red-500">+฿{{ penalty.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between font-bold pt-2" style="border-top:1px solid #d1c4e9">
            <span>รวม</span>
            <span style="color:#673ab7;font-size:1.2rem">฿{{ total.toFixed(2) }}</span>
          </div>
        </div>
        <div class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs text-slate-500 mt-2" style="background:rgba(255,255,255,.6)">
          💡 กรอกยอด <strong style="color:#673ab7">฿{{ total.toFixed(2) }}</strong> เมื่อโอนเงิน
        </div>
      </div>
      <div>
        <label class="lbl">แนบสลิปการโอนเงิน <span class="text-red-500">*</span></label>
        <label :class="['block border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-colors', slipFile ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:border-blue-400 hover:bg-blue-50']">
          <input type="file" class="hidden" accept="image/*,.pdf" @change="onSlip"/>
          <span v-if="!slipFile">
            <span class="text-3xl block mb-2">📎</span>
            <p class="text-sm text-slate-600 font-medium">แตะเพื่อเลือกไฟล์</p>
            <p class="text-xs text-slate-400 mt-1">PNG, JPG, PDF ไม่เกิน 5MB</p>
          </span>
          <span v-else class="text-emerald-700">
            <span class="text-3xl block mb-1">✅</span>
            <p class="text-sm font-medium">{{ slipName }}</p>
            <p class="text-xs text-slate-400 mt-1">แตะเพื่อเปลี่ยนไฟล์</p>
          </span>
        </label>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="payModal=false">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="submitPayment" :disabled="submitting || !slipFile">
        <span v-if="submitting" class="spin">⏳</span>
        {{ submitting ? 'กำลังส่ง...' : 'ยืนยันชำระ' }}
      </button>
    </div>
  </div>
</div>

</div>

<script>
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
</script>
