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
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3.4.21/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<style>
* { font-family: 'Sarabun', sans-serif; box-sizing: border-box; }
body { background: #f0f2f5; margin: 0; }

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
.drop-zone:hover, .drop-zone.dragging {
  border-color: #673ab7;
  background: #f3e5f5;
}
.btn-primary {
  background: #673ab7;
  color: white;
  font-weight: 600;
  font-size: 14px;
  padding: 10px 28px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: background .2s;
  font-family: 'Sarabun', sans-serif;
}
.btn-primary:hover { background: #5e35b1; }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.qr-bg { background: linear-gradient(145deg, #e8f5e9, #e3f2fd, #ede7f6); }
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; display: inline-block; }
</style>
</head>
<body class="min-h-screen py-8 px-3">

<div id="loading" style="text-align:center;padding:60px;color:#9aa0a6">กำลังโหลด...</div>

<div id="app" style="display:none">

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

    <!-- Header card -->
    <div class="gf-card" style="border-top:10px solid #673ab7">
      <h1 class="text-2xl font-semibold text-gray-800">
        ฟอร์มชำระเงินสาขา IT<br/>
        ประจำเดือน <span style="color:#673ab7"><?= $month_names[$month] ?? '' ?> <?= $year ?></span>
      </h1>

      <!-- Bank info -->
      <div class="mt-4 rounded-xl p-4" style="background:#f8f9fa">
        <div class="flex items-center gap-3 mb-3">
          <span style="font-size:28px">🏦</span>
          <div>
            <p class="font-semibold text-gray-800">ธนาคารกสิกรไทย</p>
            <p class="text-gray-500 text-xs">฿<?= number_format($monthly_fee, 0) ?> / เดือน</p>
          </div>
        </div>
        <div class="flex gap-8 pl-1 flex-wrap">
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
      <div class="mt-3 flex items-center gap-3 rounded-r-xl px-4 py-3"
           style="background:#fff3e0;border-left:4px solid #ff6d00">
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
      <div class="relative">
        <input v-model="studentId" type="text"
               :class="['gf-input', errSid ? 'error' : '']"
               placeholder="กรอกรหัสนิสิต 10 หลัก"
               maxlength="10"
               @input="onSidInput"
               @blur="validateSid" />
        <span v-if="lookupState==='found'" class="absolute right-0 top-1 text-sm font-medium" style="color:#2e7d32">✓ {{ foundName }}</span>
        <span v-if="lookupState==='miss'" class="absolute right-0 top-1 text-xs" style="color:#c62828">ไม่พบรหัสนี้</span>
        <span v-if="lookupState==='loading'" class="absolute right-0 top-1 text-xs text-gray-400">ค้นหา...</span>
      </div>
      <p v-if="errSid" class="text-xs mt-1" style="color:#d93025">{{ errSid }}</p>
    </div>

    <!-- Amount summary -->
    <div class="gf-card" style="background:#ede7f6;border:1px solid #d1c4e9">
      <p class="text-sm font-medium text-gray-700 mb-3">สรุปยอดที่ต้องชำระ</p>
      <div style="display:flex;flex-direction:column;gap:8px">
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">ค่าธรรมเนียมเดือน <?= $month_names[$month] ?? '' ?></span>
          <span class="font-medium text-gray-800">฿<?= number_format($monthly_fee, 2) ?></span>
        </div>
        <?php if ($penalty > 0): ?>
        <div class="flex justify-between text-sm">
          <span style="color:#c62828">ค่าปรับ (<?= $days_overdue ?> วัน × ฿<?= $penalty_per_day ?>)</span>
          <span class="font-medium" style="color:#c62828">฿<?= number_format($penalty, 2) ?></span>
        </div>
        <?php endif; ?>
        <div class="flex justify-between pt-2" style="border-top:1px solid #d1c4e9">
          <span class="font-semibold text-gray-800">รวมทั้งสิ้น</span>
          <span class="text-xl font-bold" style="color:#673ab7">
            ฿<?= number_format($total, 2) ?>
          </span>
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
        <div class="rounded-xl px-4 py-2 flex items-center justify-between mb-3"
             style="background:linear-gradient(135deg,#1a3a2a,#1b5e20)">
          <div>
            <p class="font-bold text-lg text-white leading-none">make</p>
            <p class="text-xs" style="color:#a5d6a7">by KBank</p>
          </div>
          <div class="w-7 h-7 rounded-full flex items-center justify-center"
               style="background:rgba(255,255,255,.15)">
            <span class="text-white text-xs font-bold">K</span>
          </div>
        </div>

        <!-- QR frame -->
        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 12px rgba(0,0,0,.12)">
          <div class="flex items-center gap-3 px-4 py-3" style="background:#1a237e">
            <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background:#00bcd4">
              <span class="text-white text-sm font-bold">+</span>
            </div>
            <div>
              <p class="text-white font-bold text-sm tracking-widest">THAI QR</p>
              <p class="text-xs tracking-widest" style="color:#90caf9">PAYMENT</p>
            </div>
          </div>

          <div class="px-5 py-5 text-center">
            <div class="inline-block text-white text-xs font-bold px-5 py-1 rounded mb-4 tracking-wider"
                 style="background:#1a237e">PromptPay</div>

            <div class="flex justify-center mb-4">
              <div class="rounded-xl border-2 border-gray-100 p-3 bg-white inline-block">
                <svg width="150" height="150" viewBox="0 0 150 150">
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
                  <rect x="105" y="65" width="7" height="7" rx="1" fill="#111" opacity=".6"/>
                  <rect x="105" y="85" width="7" height="7" rx="1" fill="#111" opacity=".3"/>
                  <rect x="38" y="65" width="7" height="7" rx="1" fill="#111" opacity=".7"/>
                  <rect x="38" y="85" width="7" height="7" rx="1" fill="#111" opacity=".5"/>
                </svg>
              </div>
            </div>

            <p class="text-sm font-medium mb-1" style="color:#00897b">สแกน QR เพื่อโอนเข้าบัญชี</p>
            <p class="font-bold text-gray-800"><?= htmlspecialchars($settings['bank_name'] ?? 'นายไพศาล กองมณี') ?></p>
            <p class="text-sm text-gray-500 mt-0.5"><?= htmlspecialchars($settings['bank_account'] ?? '202-3-90895-9') ?></p>

            <div class="mt-3 rounded-xl px-4 py-2 text-xs text-gray-500" style="background:#f5f5f5">
              💬 บัญชีออมทรัพย์ IT สาขาวิชาเทคโนโลยีสารสนเทศ
            </div>

            <div class="flex items-center justify-between mt-3 pt-3" style="border-top:1px solid #f0f0f0">
              <p class="text-left text-gray-400" style="font-size:10px;line-height:1.4">
                Accepts all banks<br/>รับเงินได้จากทุกธนาคาร
              </p>
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                   style="background:#00897b">m</div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-3 flex items-center gap-2 rounded-lg px-3 py-2 text-xs text-gray-500" style="background:#f8f9fa">
        💡 กรอกจำนวน <strong style="color:#673ab7">฿<?= number_format($total, 2) ?></strong> ตอนโอน
      </div>
    </div>

    <!-- Slip upload -->
    <div class="gf-card">
      <label class="block text-sm font-medium text-gray-700 mb-3">
        อัปโหลดสลิปการโอนเงิน <span style="color:#d93025">*</span>
      </label>

      <!-- Preview -->
      <div v-if="slipPreview" class="mb-3 relative">
        <img :src="slipPreview"
             class="w-full rounded-xl border border-gray-200"
             style="max-height:260px;object-fit:contain"/>
        <button @click="clearSlip"
                class="absolute top-2 right-2 rounded-full w-7 h-7 text-white text-xs flex items-center justify-center"
                style="background:#e53935">✕</button>
        <p class="text-xs text-center mt-1" style="color:#2e7d32">✅ เลือกสลิปแล้ว</p>
      </div>

      <!-- Drop zone -->
      <label v-if="!slipPreview" for="slipInput"
             :class="['drop-zone flex flex-col items-center justify-center py-8 gap-2 text-center', dragging ? 'dragging' : '']"
             @dragover.prevent="dragging=true"
             @dragleave="dragging=false"
             @drop.prevent="onDrop">
        <span style="font-size:36px">📎</span>
        <p class="text-sm font-medium text-gray-600">แตะหรือลากไฟล์มาวางที่นี่</p>
        <p class="text-xs text-gray-400">PNG, JPG, PDF ไม่เกิน 5MB</p>
        <input id="slipInput" type="file" accept="image/*,.pdf" class="hidden" @change="onFile"/>
      </label>

      <p v-if="errSlip" class="text-xs mt-1.5" style="color:#d93025">{{ errSlip }}</p>
    </div>

    <!-- Submit -->
    <div class="gf-card flex items-center justify-between">
      <button class="btn-primary flex items-center gap-2"
              :disabled="submitting" @click="submit">
        <span v-if="submitting" class="spin">⏳</span>
        {{ submitting ? 'กำลังส่ง...' : 'ส่งแบบฟอร์ม' }}
      </button>
      <button @click="reset" class="text-sm" style="color:#673ab7">ล้างแบบฟอร์ม</button>
    </div>

    <p class="text-center text-xs text-gray-400 pb-6">
      ระบบการเงิน · สาขาวิชาเทคโนโลยีสารสนเทศ · <?= $year ?>
    </p>

  </div><!-- /v-else -->

  <!-- Toast -->
  <div v-if="toastShow"
       :style="toastOk ? 'background:#2e7d32' : 'background:#c62828'"
       class="fixed text-white text-sm font-medium rounded-xl px-5 py-3 flex items-center gap-2"
       style="bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;white-space:nowrap;box-shadow:0 4px 20px rgba(0,0,0,.2)">
    {{ toastOk ? '✅' : '❌' }} {{ toastMsg }}
  </div>

</div><!-- #app -->

<script>
const { createApp, ref } = Vue

createApp({
  setup() {
    document.getElementById('loading').style.display = 'none'
    document.getElementById('app').style.display = 'block'

    const LOOKUP_URL = '<?= base_url('pay/lookup') ?>'
    const SUBMIT_URL = '<?= base_url('pay/submit') ?>'
    const MONTH      = <?= (int)$month ?>

    const YEAR       = <?= (int)$year ?>

    const studentId   = ref('')
    const foundName   = ref('')
    const lookupState = ref('') // '', 'loading', 'found', 'miss'
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
      document.getElementById('slipInput') && (document.getElementById('slipInput').value = '')
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
