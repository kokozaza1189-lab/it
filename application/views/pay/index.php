<?php
$month_names = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>ฟอร์มชำระเงินสาขา IT</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<style>
* { font-family: 'Sarabun', sans-serif; box-sizing: border-box; }
body { background: #f0f2f5; margin: 0; padding: 32px 12px; min-height: 100vh; }

.gf-card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,.12);
  padding: 24px;
}
.gf-input {
  border: none;
  border-bottom: 1.5px solid #dadce0;
  outline: none;
  padding: 4px 0 8px;
  width: 100%;
  font-size: 15px;
  font-family: 'Sarabun', sans-serif;
  background: transparent;
  color: #202124;
  transition: border-color .2s;
}
.gf-input:focus { border-bottom: 2px solid #673ab7; }
.gf-input.error { border-bottom: 2px solid #d93025; }
.gf-input::placeholder { color: #9aa0a6; font-size: 13px; }
.drop-zone {
  border: 2px dashed #c7c7c7;
  border-radius: 12px;
  cursor: pointer;
  transition: all .2s;
}
.drop-zone:hover, .drop-zone.dragging { border-color: #673ab7; background: #f3e5f5; }
.btn-primary {
  background: #673ab7; color: white; font-weight: 600; font-size: 14px;
  padding: 10px 28px; border-radius: 8px; border: none; cursor: pointer;
  transition: background .2s; font-family: 'Sarabun', sans-serif;
  display: inline-flex; align-items: center; gap: 8px;
}
.btn-primary:hover { background: #5e35b1; }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.qr-bg { background: linear-gradient(145deg, #e8f5e9, #e3f2fd, #ede7f6); }
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; display: inline-block; }

/* Layout utilities — no Tailwind CDN needed */
.wrap { max-width: 560px; margin: 0 auto; display: flex; flex-direction: column; gap: 12px; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-xs  { font-size: 12px; }
.text-sm  { font-size: 13px; }
.text-lg  { font-size: 18px; }
.text-xl  { font-size: 20px; }
.text-2xl { font-size: 24px; }
.font-medium   { font-weight: 500; }
.font-semibold { font-weight: 600; }
.font-bold     { font-weight: 700; }
.block { display: block; }
.inline-block { display: inline-block; }
.relative { position: relative; }
.w-full { width: 100%; }
.overflow-hidden { overflow: hidden; }
.rounded-xl  { border-radius: 12px; }
.rounded-2xl { border-radius: 16px; }
.rounded-full { border-radius: 9999px; }
.bg-white { background: #fff; }
.text-white { color: #fff; }
.text-gray-400 { color: #9ca3af; }
.text-gray-500 { color: #6b7280; }
.text-gray-600 { color: #4b5563; }
.text-gray-700 { color: #374151; }
.text-gray-800 { color: #1f2937; }
.text-purple-700 { color: #7c3aed; }
.mt-0\.5 { margin-top: 2px; }
.mt-1 { margin-top: 4px; }
.mt-1\.5 { margin-top: 6px; }
.mt-3 { margin-top: 12px; }
.mt-4 { margin-top: 16px; }
.mb-1 { margin-bottom: 4px; }
.mb-2 { margin-bottom: 8px; }
.mb-3 { margin-bottom: 12px; }
.mb-4 { margin-bottom: 16px; }
.mb-6 { margin-bottom: 24px; }
.px-3 { padding-left: 12px; padding-right: 12px; }
.px-4 { padding-left: 16px; padding-right: 16px; }
.px-5 { padding-left: 20px; padding-right: 20px; }
.py-1 { padding-top: 4px; padding-bottom: 4px; }
.py-2 { padding-top: 8px; padding-bottom: 8px; }
.py-5 { padding-top: 20px; padding-bottom: 20px; }
.py-20 { padding-top: 80px; padding-bottom: 80px; }
.p-4 { padding: 16px; }
.pb-6 { padding-bottom: 24px; }
.border { border: 1px solid #e5e7eb; }
.border-gray-200 { border-color: #e5e7eb; }
.flex { display: flex; }
.flex-col { flex-direction: column; }
.flex-1 { flex: 1; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.justify-center { justify-content: center; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.gap-4 { gap: 16px; }
.flex-wrap { flex-wrap: wrap; }
.max-w-xl { max-width: 560px; }
.mx-auto { margin-left: auto; margin-right: auto; }
</style>
</head>
<body>

<div id="app">

  <!-- SUCCESS -->
  <div v-if="submitted" class="max-w-xl mx-auto text-center py-20">
    <div class="gf-card">
      <div style="font-size:64px">✅</div>
      <h2 class="text-2xl font-semibold text-gray-800 mt-3 mb-2">ส่งแบบฟอร์มเรียบร้อย</h2>
      <p class="text-gray-500 text-sm">ขอบคุณ <span class="font-semibold text-purple-700">{{ foundName }}</span> ที่ชำระเงิน</p>
      <p class="text-gray-400 text-xs mt-1 mb-6">ระบบจะตรวจสอบสลิปภายใน 24 ชั่วโมง</p>
      <button class="btn-primary" @click="reset">ส่งอีกครั้ง</button>
    </div>
  </div>

  <!-- FORM -->
  <div v-else class="max-w-xl mx-auto" style="display:flex;flex-direction:column;gap:12px">

    <!-- Month selector -->
    <?php if (count($active_months) > 1): ?>
    <div class="gf-card">
      <p class="text-xs font-medium text-gray-500 mb-2">เลือกเดือนที่ต้องการชำระ</p>
      <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach ($active_months as $m): ?>
          <a href="<?= base_url('pay') ?>?month=<?= $m ?>&year=<?= $year ?>"
             style="padding:8px 16px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;border:1.5px solid;transition:all .2s;
                    <?= $m == $month
                      ? 'background:#673ab7;color:#fff;border-color:#673ab7'
                      : 'background:#f8f9fa;color:#5f6368;border-color:#dadce0' ?>">
            <?= $month_names[$m] ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Header card -->
    <div class="gf-card" style="border-top:10px solid #673ab7">
      <h1 class="text-2xl font-semibold text-gray-800">
        ฟอร์มชำระเงินสาขา IT<br/>
        ประจำเดือน <span style="color:#673ab7"><?= $month_names[$month] ?? '' ?> <?= $year ?></span>
      </h1>

      <!-- Bank info -->
      <div class="mt-4 rounded-xl p-4" style="background:#f8f9fa">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
          <span style="font-size:28px">🏦</span>
          <div>
            <p class="font-semibold text-gray-800">ธนาคารกสิกรไทย</p>
            <p class="text-gray-500 text-xs">฿<?= number_format($monthly_fee, 0) ?> / เดือน</p>
          </div>
        </div>
        <div style="display:flex;gap:32px;flex-wrap:wrap;padding-left:4px">
          <div>
            <p class="text-xs text-gray-400">เลขบัญชี</p>
            <p class="font-semibold text-gray-800" style="letter-spacing:1px"><?= htmlspecialchars($settings['bank_account'] ?? '202-3-90895-9') ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-400">ชื่อบัญชี</p>
            <p class="font-semibold text-gray-800"><?= htmlspecialchars($settings['bank_name'] ?? 'นายไพศาล กองมณี') ?></p>
          </div>
        </div>
      </div>

      <!-- Deadline -->
      <div class="mt-3" style="display:flex;align-items:center;gap:12px;background:#fff3e0;border-left:4px solid #ff6d00;border-radius:0 12px 12px 0;padding:12px 16px">
        <span style="font-size:20px">⏰</span>
        <div class="flex-1">
          <p class="text-xs font-semibold" style="color:#e65100">กำหนดชำระ</p>
          <p class="text-sm" style="color:#bf360c">
            <strong>วันที่ <?= $due_day ?> <?= $month_names[$month] ?? '' ?> <?= $year ?></strong>
          </p>
        </div>
        <?php
          $due_date_ts = mktime(0, 0, 0, $month, $due_day, $year - 543);
          $days_left = max(0, (int)(($due_date_ts - time()) / 86400));
        ?>
        <div class="text-right">
          <p class="text-xs" style="color:#ff6d00"><?= time() > $due_date_ts ? 'เกินกำหนด' : 'เหลืออีก' ?></p>
          <p class="text-xl font-bold" style="color:#e65100">
            <?= $days_overdue > 0 ? $days_overdue : $days_left ?>
            <span class="text-xs font-normal">วัน</span>
          </p>
        </div>
      </div>

      <p class="text-xs mt-4" style="color:#d93025">* ระบุว่าเป็นคำถามที่จำเป็น</p>
    </div>

    <!-- Student ID -->
    <div class="gf-card">
      <label class="block text-sm font-medium text-gray-700 mb-3">
        รหัสนิสิต <span style="color:#d93025">*</span>
      </label>
      <div style="position:relative">
        <input v-model="studentId" type="text"
               :class="['gf-input', errSid ? 'error' : '']"
               placeholder="กรอกรหัสนิสิต 10 หลัก"
               maxlength="10"
               @input="onSidInput"
               @blur="validateSid" />
        <span v-if="lookupState==='found'" style="position:absolute;right:0;top:4px;font-size:14px;font-weight:600;color:#2e7d32">✓ {{ foundName }}</span>
        <span v-if="lookupState==='miss'"  style="position:absolute;right:0;top:4px;font-size:12px;color:#c62828">ไม่พบรหัสนี้</span>
        <span v-if="lookupState==='loading'" style="position:absolute;right:0;top:4px;font-size:12px;color:#9aa0a6">ค้นหา...</span>
      </div>
      <p v-if="errSid" class="text-xs mt-1" style="color:#d93025">{{ errSid }}</p>
    </div>

    <!-- Amount summary -->
    <div class="gf-card" style="background:#ede7f6;border:1px solid #d1c4e9">
      <p class="text-sm font-medium text-gray-700 mb-3">สรุปยอดที่ต้องชำระ</p>
      <div style="display:flex;flex-direction:column;gap:8px">
        <div style="display:flex;justify-content:space-between" class="text-sm">
          <span class="text-gray-600">ค่าธรรมเนียมเดือน <?= $month_names[$month] ?? '' ?></span>
          <span class="font-medium text-gray-800">฿<?= number_format($monthly_fee, 2) ?></span>
        </div>
        <?php if ($penalty > 0): ?>
        <div style="display:flex;justify-content:space-between" class="text-sm">
          <span style="color:#c62828">ค่าปรับ (<?= $days_overdue ?> วัน × ฿<?= $penalty_per_day ?>)</span>
          <span class="font-medium" style="color:#c62828">฿<?= number_format($penalty, 2) ?></span>
        </div>
        <?php endif; ?>
        <div style="display:flex;justify-content:space-between;border-top:1px solid #d1c4e9;padding-top:8px">
          <span class="font-semibold text-gray-800">รวมทั้งสิ้น</span>
          <span class="text-xl font-bold" style="color:#673ab7">฿<?= number_format($total, 2) ?></span>
        </div>
      </div>
      <span class="inline-block mt-3 text-xs font-medium px-3 py-1 rounded-full"
            style="<?= $penalty > 0 ? 'background:#ffebee;color:#c62828' : 'background:#e8f5e9;color:#2e7d32' ?>">
        <?= $penalty > 0 ? '🔴 เกินกำหนดชำระแล้ว' : '🟢 ยังไม่เกินกำหนด' ?>
      </span>
    </div>

    <!-- QR Code -->
    <div class="gf-card">
      <p class="text-sm font-medium text-gray-700 mb-4">สแกน QR เพื่อชำระเงิน</p>

      <div class="qr-bg rounded-2xl p-4">
        <!-- KBank Make Header -->
        <div style="border-radius:12px;padding:8px 16px;display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;background:linear-gradient(135deg,#1a3a2a,#1b5e20)">
          <div>
            <p class="font-bold text-lg text-white" style="line-height:1">make</p>
            <p class="text-xs" style="color:#a5d6a7">by KBank</p>
          </div>
          <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.15)">
            <span class="text-white text-xs font-bold">K</span>
          </div>
        </div>

        <!-- QR frame -->
        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 12px rgba(0,0,0,.12)">
          <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#1a237e">
            <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#00bcd4">
              <span class="text-white text-sm font-bold">+</span>
            </div>
            <div>
              <p class="text-white font-bold text-sm" style="letter-spacing:3px">THAI QR</p>
              <p class="text-xs" style="color:#90caf9;letter-spacing:3px">PAYMENT</p>
            </div>
          </div>

          <div class="px-5 py-5 text-center">
            <div class="inline-block text-white text-xs font-bold px-5 py-1 rounded mb-4" style="background:#1a237e;letter-spacing:2px">PromptPay</div>

            <div style="display:flex;justify-content:center;margin-bottom:16px">
              <div style="border-radius:12px;border:2px solid #f3f4f6;padding:12px;background:white;display:inline-block">
                <?php
                $qr_path = FCPATH . ($settings['qr_image'] ?? 'assets/img/qr_payment.jpg');
                $qr_url  = base_url($settings['qr_image'] ?? 'assets/img/qr_payment.jpg');
                ?>
                <?php if (file_exists($qr_path)): ?>
                  <img src="<?= $qr_url ?>" alt="QR PromptPay" width="180" height="180"
                       style="display:block;border-radius:8px"/>
                <?php else: ?>
                  <div style="width:180px;height:180px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f8fafc;border-radius:8px;border:1.5px dashed #cbd5e1">
                    <span style="font-size:40px">🔲</span>
                    <p style="font-size:11px;color:#94a3b8;margin-top:8px;text-align:center">วาง qr_payment.jpg<br>ในโฟลเดอร์<br>assets/img/</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <p class="text-sm font-medium mb-1" style="color:#00897b">สแกน QR เพื่อโอนเข้าบัญชี</p>
            <p class="font-bold text-gray-800"><?= htmlspecialchars($settings['bank_name'] ?? 'นายไพศาล กองมณี') ?></p>
            <p class="text-sm text-gray-500 mt-0.5"><?= htmlspecialchars($settings['bank_account'] ?? '202-3-90895-9') ?></p>

            <div class="mt-3 rounded-xl px-4 py-2 text-xs text-gray-500" style="background:#f5f5f5">
              💬 บัญชีออมทรัพย์ IT สาขาวิชาเทคโนโลยีสารสนเทศ
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding-top:12px;border-top:1px solid #f0f0f0">
              <p class="text-left text-gray-400" style="font-size:10px;line-height:1.4">
                Accepts all banks<br/>รับเงินได้จากทุกธนาคาร
              </p>
              <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#00897b;color:white;font-size:12px;font-weight:700">m</div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-3" style="display:flex;align-items:center;gap:8px;background:#f8f9fa;border-radius:8px;padding:8px 12px;font-size:12px;color:#5f6368">
        💡 กรอกจำนวน <strong style="color:#673ab7">฿<?= number_format($total, 2) ?></strong> ตอนโอน
      </div>
    </div>

    <!-- Slip upload -->
    <div class="gf-card">
      <label class="block text-sm font-medium text-gray-700 mb-3">
        อัปโหลดสลิปการโอนเงิน <span style="color:#d93025">*</span>
      </label>

      <!-- Preview -->
      <div v-if="slipPreview" class="mb-3" style="position:relative">
        <img :src="slipPreview"
             class="w-full rounded-xl border border-gray-200"
             style="max-height:260px;object-fit:contain"/>
        <button @click="clearSlip"
                style="position:absolute;top:8px;right:8px;border-radius:50%;width:28px;height:28px;background:#e53935;color:white;font-size:12px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer">✕</button>
        <p class="text-xs text-center mt-1" style="color:#2e7d32">✅ เลือกสลิปแล้ว</p>
      </div>

      <!-- Drop zone -->
      <label v-if="!slipPreview" for="slipInput"
             :class="['drop-zone', dragging ? 'dragging' : '']"
             style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 16px;gap:8px;text-align:center"
             @dragover.prevent="dragging=true"
             @dragleave="dragging=false"
             @drop.prevent="onDrop">
        <span style="font-size:36px">📎</span>
        <p class="text-sm font-medium text-gray-600">แตะหรือลากไฟล์มาวางที่นี่</p>
        <p class="text-xs text-gray-400">PNG, JPG, PDF ไม่เกิน 5MB</p>
        <input id="slipInput" type="file" accept="image/*,.pdf" style="display:none" @change="onFile"/>
      </label>

      <p v-if="errSlip" class="text-xs mt-1.5" style="color:#d93025">{{ errSlip }}</p>
    </div>

    <!-- Submit -->
    <div class="gf-card" style="display:flex;align-items:center;justify-content:space-between">
      <button class="btn-primary" style="display:flex;align-items:center;gap:8px"
              :disabled="submitting" @click="submit">
        <span v-if="submitting" class="spin">⏳</span>
        {{ submitting ? 'กำลังส่ง...' : 'ส่งแบบฟอร์ม' }}
      </button>
      <button @click="reset" class="text-sm" style="color:#673ab7;background:none;border:none;cursor:pointer">ล้างแบบฟอร์ม</button>
    </div>

    <p class="text-center text-xs text-gray-400 pb-6">
      ระบบการเงิน · สาขาวิชาเทคโนโลยีสารสนเทศ · <?= $year ?>
    </p>

  </div><!-- /v-else -->

  <!-- Toast -->
  <div v-if="toastShow"
       :style="toastOk ? 'background:#2e7d32' : 'background:#c62828'"
       style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;white-space:nowrap;box-shadow:0 4px 20px rgba(0,0,0,.2);color:white;font-size:14px;font-weight:600;border-radius:12px;padding:12px 20px;display:flex;align-items:center;gap:8px">
    {{ toastOk ? '✅' : '❌' }} {{ toastMsg }}
  </div>

</div><!-- #app -->

<!-- Vue + Axios only — no Tailwind CDN (all styles are inline above) -->
<script src="https://cdn.jsdelivr.net/npm/vue@3.4.21/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
const { createApp, ref } = Vue

createApp({
  setup() {
    const LOOKUP_URL = '<?= base_url('pay/lookup') ?>'
    const SUBMIT_URL = '<?= base_url('pay/submit') ?>'
    const MONTH      = <?= (int)$month ?>
    const YEAR       = <?= (int)$year ?>

    const studentId   = ref('')
    const foundName   = ref('')
    const lookupState = ref('')
    const errSid      = ref('')

    const slipFile    = ref(null)
    const slipPreview = ref(null)
    const dragging    = ref(false)
    const errSlip     = ref('')

    const submitting  = ref(false)
    const submitted   = ref(false)
    const toastShow   = ref(false)
    const toastMsg    = ref('')
    const toastOk     = ref(true)

    let lookupTimer = null

    function showToast(msg, ok = true) {
      toastMsg.value = msg; toastOk.value = ok; toastShow.value = true
      setTimeout(() => toastShow.value = false, 3500)
    }

    function onSidInput() {
      lookupState.value = ''
      foundName.value = ''
      errSid.value = ''
      clearTimeout(lookupTimer)
      if (studentId.value.length < 10) return
      lookupState.value = 'loading'
      lookupTimer = setTimeout(async () => {
        try {
          const r = await axios.get(LOOKUP_URL, { params: { id: studentId.value } })
          if (r.data.found) {
            foundName.value = r.data.name
            lookupState.value = 'found'
          } else {
            lookupState.value = 'miss'
          }
        } catch(e) {
          lookupState.value = ''
        }
      }, 400)
    }

    function validateSid() {
      if (!studentId.value) { errSid.value = 'กรุณากรอกรหัสนิสิต'; return false }
      if (studentId.value.length !== 10) { errSid.value = 'รหัสนิสิตต้องมี 10 หลัก'; return false }
      if (lookupState.value === 'miss') { errSid.value = 'ไม่พบรหัสนิสิตนี้ในระบบ'; return false }
      errSid.value = ''
      return true
    }

    function onFile(e) { loadFile(e.target.files[0]) }
    function onDrop(e) { dragging.value = false; loadFile(e.dataTransfer.files[0]) }
    function loadFile(file) {
      if (!file) return
      if (file.size > 5 * 1024 * 1024) { showToast('ไฟล์ใหญ่เกิน 5MB', false); return }
      slipFile.value = file
      errSlip.value = ''
      if (file.type.startsWith('image/')) {
        const r = new FileReader()
        r.onload = e => slipPreview.value = e.target.result
        r.readAsDataURL(file)
      } else {
        slipPreview.value = null
      }
    }
    function clearSlip() {
      slipFile.value = null; slipPreview.value = null
      const inp = document.getElementById('slipInput')
      if (inp) inp.value = ''
    }

    async function submit() {
      const sidOk = validateSid()
      if (!slipFile.value) errSlip.value = 'กรุณาอัปโหลดสลิปการโอนเงิน'
      if (!sidOk || errSlip.value) {
        showToast('กรุณากรอกข้อมูลให้ครบถ้วน', false); return
      }
      if (lookupState.value !== 'found') {
        showToast('กรุณาตรวจสอบรหัสนิสิตก่อนส่ง', false); return
      }
      submitting.value = true
      try {
        const fd = new FormData()
        fd.append('student_id', studentId.value)
        fd.append('month', MONTH)
        fd.append('year', YEAR)
        fd.append('slip', slipFile.value)
        const res = await axios.post(SUBMIT_URL, fd)
        if (res.data.success) {
          submitted.value = true
        } else {
          showToast(res.data.error || 'เกิดข้อผิดพลาด', false)
        }
      } catch(e) {
        const msg = e.response?.data?.error || 'เกิดข้อผิดพลาด กรุณาลองใหม่'
        showToast(msg, false)
      }
      submitting.value = false
    }

    function reset() {
      studentId.value = ''; foundName.value = ''; lookupState.value = ''
      errSid.value = ''; errSlip.value = ''
      clearSlip(); submitted.value = false
    }

    return {
      studentId, foundName, lookupState, errSid,
      slipFile, slipPreview, dragging, errSlip,
      submitting, submitted, toastShow, toastMsg, toastOk,
      onSidInput, validateSid, onFile, onDrop, clearSlip, submit, reset
    }
  }
}).mount('#app')
</script>

</body>
</html>
