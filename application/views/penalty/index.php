<?php
$month_names = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
$th_short    = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$penalty_day = (float)($settings['penalty_per_day'] ?? 5);
$has_overdue = !empty(array_filter($penalties, fn($p) => $p->status === 'overdue'));
?>
<div id="app">

<!-- Header card -->
<div class="card mb-4" style="border:2px solid <?= $total_due > 0 ? '#fca5a5' : '#6ee7b7' ?>">
  <div class="flex items-start justify-between">
    <div>
      <p class="text-slate-400 text-xs">รหัสนิสิต</p>
      <p class="font-bold text-slate-800 text-xl font-mono"><?= htmlspecialchars($current_user['student_id'] ?? '—') ?></p>
      <p class="text-slate-600 text-sm mt-0.5"><?= htmlspecialchars($current_user['name']) ?></p>
    </div>
    <div class="text-right">
      <p class="text-slate-400 text-xs">ยอดค้างรวม</p>
      <p class="font-bold text-2xl <?= $total_due > 0 ? 'text-red-500' : 'text-emerald-600' ?>">
        ฿<?= number_format($total_due, 2) ?>
      </p>
      <span class="badge <?= $total_due > 0 ? 'b-overdue' : 'b-paid' ?> mt-1 inline-block">
        <?= $total_due > 0 ? 'มียอดค้าง' : 'ไม่มีค่าปรับ' ?>
      </span>
    </div>
  </div>
</div>

<?php if (empty($penalties)): ?>
<!-- All clear -->
<div class="card" style="border:1.5px solid #6ee7b7;background:#f0fdf4">
  <div class="flex items-center gap-4 py-2">
    <span style="font-size:48px">🎉</span>
    <div>
      <p class="font-bold text-emerald-700 text-lg">ไม่มีค่าปรับค้าง</p>
      <p class="text-sm text-emerald-600">คุณชำระครบทุกเดือนแล้ว ปีการศึกษา <?= $year ?></p>
    </div>
  </div>
</div>

<?php else: ?>

<p class="text-sm font-semibold text-slate-600 mb-3">
  🔴 พบ <?= count($penalties) ?> เดือนที่ยังค้างชำระ — ปีการศึกษา <?= $year ?>
</p>

<?php foreach ($penalties as $p):
  $total_bill = (float)$p->amount + (float)$p->penalty;
  $is_overdue = $p->status === 'overdue';
  $days = $penalty_day > 0 && $p->penalty > 0 ? round($p->penalty / $penalty_day) : 0;
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
      <span class="text-red-500">
        🚨 ค่าปรับ<?= $days > 0 ? " ({$days} วัน × ฿{$penalty_day})" : '' ?>
      </span>
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
  <button class="btn btn-blue w-full text-base py-3"
          @click="openPayModal(<?= (int)$p->id ?>, <?= (int)$p->month ?>, <?= (float)$p->amount ?>, <?= (float)$p->penalty ?>)">
    💳 ชำระบิลนี้ &nbsp; ฿<?= number_format($total_bill, 2) ?>
  </button>
  <?php else: ?>
  <div class="rounded-xl py-3 px-4 text-center text-sm font-medium" style="background:#eff6ff;color:#1d4ed8">
    ⏳ รอเจ้าหน้าที่ตรวจสอบสลิปการโอน
    <?php if ($p->slip_file): ?>
    <br><a href="<?= base_url('assets/uploads/slips/'.$p->slip_file) ?>" target="_blank"
           class="text-xs text-blue-500 underline mt-1 inline-block">ดูสลิปที่แนบ</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php endforeach; ?>

<!-- Total summary bar -->
<?php if ($has_overdue): ?>
<div class="card" style="background:#fef2f2;border:2px solid #fca5a5">
  <div class="flex items-center justify-between">
    <div>
      <p class="font-bold text-red-700">💳 ยอดรวมที่ต้องชำระทั้งหมด</p>
      <p class="text-xs text-red-400 mt-0.5">กรุณาชำระแยกตามเดือน 1 สลิป / 1 เดือน</p>
    </div>
    <p class="font-bold text-2xl text-red-600">฿<?= number_format($total_due, 2) ?></p>
  </div>
</div>
<?php endif; ?>

<?php endif; ?>

<!-- ──────────── Pay Modal ──────────────── -->
<div v-show="payModal" class="modal-bg" @click.self="payModal=false" style="display:none">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">ชำระค่าปรับ — <span v-text="monthLabel"></span></h2>
        <button @click="payModal=false" class="btn-icon">✕</button>
      </div>
    </div>
    <div class="modal-body space-y-4">
      <!-- Amount breakdown -->
      <div class="rounded-xl p-4" style="background:#ede7f6;border:1px solid #d1c4e9">
        <div class="space-y-2 mb-3">
          <div class="flex justify-between text-sm">
            <span class="text-slate-600">ค่าธรรมเนียม</span>
            <span class="font-medium">฿<span v-text="amount.toFixed(2)"></span></span>
          </div>
          <div v-if="penalty > 0" class="flex justify-between text-sm">
            <span class="text-red-500">ค่าปรับ</span>
            <span class="font-bold text-red-500">+฿<span v-text="penalty.toFixed(2)"></span></span>
          </div>
          <div class="flex justify-between font-bold pt-2 text-lg" style="border-top:1px solid #d1c4e9">
            <span>รวม</span>
            <span style="color:#673ab7">฿<span v-text="total.toFixed(2)"></span></span>
          </div>
        </div>
        <div class="text-xs text-slate-500 rounded-lg px-3 py-2" style="background:rgba(255,255,255,.6)">
          💡 กรอกยอด <strong style="color:#673ab7">฿<span v-text="total.toFixed(2)"></span></strong> ตอนโอน
        </div>
      </div>

      <!-- QR Code Payment -->
      <?php if (!empty($settings['qr_image'])): ?>
      <div style="border:2px solid #1565c0;border-radius:14px;overflow:hidden">
        <div style="background:#0d47a1;padding:10px 16px;display:flex;align-items:center;justify-content:space-between">
          <div class="flex items-center gap-2">
            <span style="background:#fff;border-radius:6px;padding:3px 6px;font-size:11px;font-weight:900;color:#0d47a1;letter-spacing:.5px">🔲 THAI QR PAYMENT</span>
          </div>
          <span style="background:#fff;border-radius:20px;padding:2px 10px;font-size:11px;font-weight:800;color:#003087">PromptPay</span>
        </div>
        <div style="padding:14px 12px 10px;background:linear-gradient(135deg,#e8f5e9,#e3f2fd);text-align:center">
          <div style="background:#fff;border-radius:12px;padding:10px;display:inline-block;box-shadow:0 2px 10px rgba(0,0,0,.12)">
            <img src="<?= base_url('assets/uploads/qr/'.$settings['qr_image']) ?>"
                 alt="QR PromptPay" style="width:170px;height:170px;object-fit:contain;display:block"/>
          </div>
          <p style="color:#1565c0;font-size:12px;margin:8px 0 2px;font-weight:600">สแกน QR เพื่อโอนชำระค่าปรับ</p>
          <?php if (!empty($settings['bank_name'])): ?>
          <p style="font-weight:700;color:#0d47a1;font-size:14px;margin:0"><?= htmlspecialchars($settings['bank_name']) ?></p>
          <?php endif; ?>
          <?php if (!empty($settings['bank_account'])): ?>
          <p style="color:#546e7a;font-size:12px;margin:2px 0 0;font-family:monospace"><?= htmlspecialchars($settings['bank_account']) ?></p>
          <?php endif; ?>
          <div style="margin-top:6px;padding:5px 10px;background:rgba(255,255,255,.6);border-radius:8px;display:inline-block">
            <p style="font-size:11px;color:#e65100;font-weight:700;margin:0">
              ⚠️ กรอกยอด <strong>฿<span class="qr-total-amount">0.00</span></strong> ตอนโอน
            </p>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Slip upload -->
      <div>
        <label class="lbl">แนบสลิปการโอนเงิน <span class="text-red-500">*</span></label>
        <label :class="['block border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-colors',
                        slipFile ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:border-blue-400 hover:bg-blue-50']">
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
      <button class="btn btn-gray flex-1" @click="payModal=false">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="submitPayment" :disabled="submitting || !slipFile">
        <span v-show="!submitting">ยืนยันชำระ</span>
        <span v-show="submitting" style="display:none">กำลังส่ง...</span>
      </button>
    </div>
  </div>
</div>
</div>

<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const { createApp, ref, computed } = Vue;
const monthNames = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                    'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
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
    const monthLabel = computed(() => monthNames[payMonth.value] + ' <?= $year ?>')

    function openPayModal(id, month, amt, pen) {
      payId.value = id; payMonth.value = month
      amount.value = amt; penalty.value = pen
      slipFile.value = null; slipName.value = ''
      payModal.value = true
      // Update QR amount display
      const el = document.querySelector('.qr-total-amount')
      if (el) el.textContent = (amt + pen).toFixed(2)
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
        setTimeout(() => location.reload(), 1200)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      submitting.value = false
    }
    return { payModal, payMonth, amount, penalty, total, submitting,
             slipFile, slipName, monthLabel, openPayModal, onSlip, submitPayment }
  }
}).mount('#app')
})
</script>
