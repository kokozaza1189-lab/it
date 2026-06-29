<?php
$month_names = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
$is_pending = ($status === 'pending');
// QR url (qr_image may be a bare filename or a full path)
$qr_url = $qr_alt = '';
if (!empty($settings['qr_image'])) {
    $qr_img = $settings['qr_image'];
    $qr_in_uploads = base_url('assets/uploads/qr/'.$qr_img);
    $qr_as_path    = base_url($qr_img);
    $qr_url = (strpos($qr_img, '/') !== false) ? $qr_as_path : $qr_in_uploads;
    $qr_alt = (strpos($qr_img, '/') !== false) ? $qr_in_uploads : $qr_as_path;
}
?>
<div id="app" style="max-width:560px;margin:0 auto;display:flex;flex-direction:column;gap:14px">

  <a href="<?= base_url('penalty') ?>" style="color:#64748b;font-size:13px;text-decoration:none">← กลับไปหน้าค่าปรับ</a>

  <!-- Header card -->
  <div class="card" style="border-top:8px solid #673ab7">
    <h1 class="font-bold text-slate-800" style="font-size:22px;line-height:1.3">
      ชำระค่าปรับ<br/>
      ประจำเดือน <span style="color:#673ab7"><?= $month_names[$month] ?? '' ?> <?= $year ?></span>
    </h1>
    <p class="text-slate-500 text-sm mt-1">
      <?= htmlspecialchars($current_user['name']) ?> · <?= htmlspecialchars($current_user['student_id'] ?? '') ?>
    </p>

    <!-- Bank info -->
    <div class="mt-4 rounded-xl p-4" style="background:#f8fafc">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
        <span style="font-size:28px">🏦</span>
        <div>
          <p class="font-semibold text-slate-800"><?= htmlspecialchars($settings['bank_name'] ?? 'บัญชีสาขา IT') ?></p>
          <p class="text-slate-500 text-xs">ค่าปรับเดือน <?= $month_names[$month] ?? '' ?></p>
        </div>
      </div>
      <?php if (!empty($settings['bank_account'])): ?>
      <div style="padding-left:4px">
        <p class="text-xs text-slate-400">เลขบัญชี</p>
        <p class="font-semibold text-slate-800" style="letter-spacing:1px;font-family:monospace"><?= htmlspecialchars($settings['bank_account']) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Deadline -->
    <div class="mt-3" style="display:flex;align-items:center;gap:12px;background:<?= $is_past_due ? '#fef2f2' : '#fff3e0' ?>;border-left:4px solid <?= $is_past_due ? '#dc2626' : '#ff6d00' ?>;border-radius:0 12px 12px 0;padding:12px 16px">
      <span style="font-size:20px"><?= $is_past_due ? '⚠️' : '⏰' ?></span>
      <div style="flex:1">
        <p class="text-xs font-semibold" style="color:<?= $is_past_due ? '#b91c1c' : '#e65100' ?>">กำหนดชำระ</p>
        <p class="text-sm" style="color:<?= $is_past_due ? '#7f1d1d' : '#bf360c' ?>">
          <strong>วันที่ <?= $due_day ?> <?= $month_names[$month] ?? '' ?> <?= $year ?></strong>
        </p>
      </div>
      <div class="text-right">
        <p class="text-xs" style="color:<?= $is_past_due ? '#dc2626' : '#ff6d00' ?>"><?= $is_past_due ? 'เกินกำหนด' : 'เหลืออีก' ?></p>
        <p class="font-bold" style="font-size:20px;color:<?= $is_past_due ? '#b91c1c' : '#e65100' ?>">
          <?= $is_past_due ? $days_overdue : $days_left ?> <span class="text-xs" style="font-weight:400">วัน</span>
        </p>
      </div>
    </div>
  </div>

  <!-- Amount summary -->
  <div class="card" style="background:#ede7f6;border:1px solid #d1c4e9">
    <p class="text-sm font-medium text-slate-700 mb-3">สรุปยอดที่ต้องชำระ</p>
    <div style="display:flex;flex-direction:column;gap:8px">
      <div style="display:flex;justify-content:space-between" class="text-sm">
        <span class="text-slate-600">ค่าธรรมเนียมเดือน <?= $month_names[$month] ?? '' ?></span>
        <span class="font-medium text-slate-800">฿<?= number_format($fee, 2) ?></span>
      </div>
      <?php if ($penalty > 0): ?>
      <div style="display:flex;justify-content:space-between" class="text-sm">
        <span style="color:#dc2626">🚨 ค่าปรับค้างชำระ</span>
        <span class="font-bold" style="color:#dc2626">+฿<?= number_format($penalty, 2) ?></span>
      </div>
      <?php endif; ?>
      <div style="display:flex;justify-content:space-between;border-top:1px solid #d1c4e9;padding-top:8px">
        <span class="font-semibold text-slate-800">รวมทั้งสิ้น</span>
        <span class="font-bold" style="font-size:20px;color:#673ab7">฿<?= number_format($total, 2) ?></span>
      </div>
    </div>
    <p class="text-xs text-slate-500 rounded-lg px-3 py-2 mt-3" style="background:rgba(255,255,255,.6)">
      💡 โอนยอด <strong style="color:#673ab7">฿<?= number_format($total, 2) ?></strong> แล้วแนบสลิปด้านล่าง
    </p>
  </div>

  <!-- QR -->
  <?php if (!empty($settings['qr_image'])): ?>
  <div class="card" style="padding:0;overflow:hidden;border:2px solid #1565c0">
    <div style="background:#0d47a1;padding:10px 20px;display:flex;align-items:center;justify-content:space-between">
      <span style="color:white;font-weight:700;font-size:14px;letter-spacing:.5px">🔲 THAI QR PAYMENT</span>
      <span style="background:white;border-radius:6px;padding:3px 10px;font-size:11px;font-weight:700;color:#0d47a1">PromptPay</span>
    </div>
    <div style="padding:18px 16px;background:linear-gradient(145deg,#e8f5e9,#e3f2fd,#ede7f6);text-align:center">
      <div style="background:white;border-radius:14px;padding:14px;display:inline-block;box-shadow:0 2px 12px rgba(0,0,0,.12)">
        <img src="<?= htmlspecialchars($qr_url) ?>"
             onerror="this.onerror=null;this.src='<?= htmlspecialchars($qr_alt) ?>'"
             alt="QR PromptPay" style="width:200px;height:200px;object-fit:contain;display:block"/>
      </div>
      <p style="color:#1565c0;font-weight:600;font-size:12px;margin-top:12px">สแกน QR เพื่อโอนชำระค่าปรับ</p>
      <?php if (!empty($settings['bank_name'])): ?>
      <p style="font-weight:700;font-size:16px;color:#1a237e;margin-top:4px"><?= htmlspecialchars($settings['bank_name']) ?></p>
      <?php endif; ?>
      <p style="color:#90a4ae;font-size:11px;margin-top:6px">รับเงินได้จากทุกธนาคาร · Accepts all banks</p>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($is_pending): ?>
  <!-- Already submitted -->
  <div class="card text-center" style="background:#eff6ff;border:1.5px solid #93c5fd">
    <p style="font-size:40px">⏳</p>
    <p class="font-bold text-blue-700">ส่งสลิปแล้ว รอเจ้าหน้าที่ตรวจสอบ</p>
    <?php if ($slip_file): ?>
    <a href="<?= base_url('assets/uploads/slips/'.$slip_file) ?>" target="_blank" class="text-xs text-blue-500 underline mt-1 inline-block">ดูสลิปที่แนบ</a>
    <?php endif; ?>
  </div>
  <?php else: ?>
  <!-- Slip upload + submit -->
  <div class="card">
    <label class="lbl mb-2">แนบสลิปการโอนเงิน <span style="color:#dc2626">*</span></label>
    <label :class="['block border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-colors',
                    slipFile ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:border-blue-400 hover:bg-blue-50']">
      <input type="file" class="hidden" accept="image/*,.pdf" @change="onSlip"/>
      <span v-show="!slipFile">
        <span class="text-3xl block mb-2">📎</span>
        <p class="text-sm text-slate-600 font-medium">แตะเพื่อเลือกไฟล์สลิป</p>
        <p class="text-xs text-slate-400 mt-1">PNG, JPG, PDF ไม่เกิน 5MB</p>
      </span>
      <span v-show="slipFile" class="text-emerald-700" style="display:none">
        <span class="text-3xl block mb-1">✅</span>
        <p class="text-sm font-medium" v-text="slipName"></p>
        <p class="text-xs text-slate-400 mt-1">แตะเพื่อเปลี่ยนไฟล์</p>
      </span>
    </label>
    <button class="btn btn-blue w-full mt-4" style="padding:12px;font-size:15px" @click="submitPayment" :disabled="submitting || !slipFile">
      <span v-show="!submitting">ยืนยันชำระ ฿<?= number_format($total, 2) ?></span>
      <span v-show="submitting" style="display:none">กำลังส่ง...</span>
    </button>
  </div>
  <?php endif; ?>

  <p class="text-center text-xs text-slate-400" style="padding-bottom:8px">
    ระบบการเงิน · สาขาวิชา IT · <?= $year ?>
  </p>
</div>

<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const { createApp, ref } = Vue
createApp({
  setup() {
    const slipFile   = ref(null)
    const slipName   = ref('')
    const submitting = ref(false)
    function onSlip(e) {
      const f = e.target.files[0]
      if (!f) return
      if (f.size > 5 * 1024 * 1024) { showToast('ไฟล์ใหญ่เกิน 5MB', false); return }
      slipFile.value = f; slipName.value = f.name
    }
    async function submitPayment() {
      if (!slipFile.value) return
      submitting.value = true
      try {
        const fd = new FormData()
        fd.append('month', <?= (int)$month ?>)
        fd.append('year',  <?= (int)$year ?>)
        fd.append('slip',  slipFile.value)
        await axios.post('<?= base_url('payment/submit') ?>', fd)
        showToast('ส่งหลักฐานการชำระแล้ว รอการตรวจสอบ')
        setTimeout(() => location.href = '<?= base_url('penalty') ?>', 1200)
      } catch (e) { showToast('เกิดข้อผิดพลาด', false); submitting.value = false }
    }
    return { slipFile, slipName, submitting, onSlip, submitPayment }
  }
}).mount('#app')
})
</script>
