<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?= isset($title) ? $title : 'สมัครสมาชิก — IT Finance System' ?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3.4.21/dist/vue.global.prod.js"></script>
<style>
*{font-family:'Sarabun',sans-serif;box-sizing:border-box}
body{margin:0}
::-webkit-scrollbar{width:4px}::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.login-bg{min-height:100vh;background:linear-gradient(135deg,#0a0f1e 0%,#0d1f40 35%,#0a2240 60%,#091226 100%);display:flex;align-items:center;justify-content:center;padding:16px;position:relative;overflow:hidden}
.login-orb{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none}
.login-orb-1{width:480px;height:480px;background:rgba(59,130,246,.18);top:-120px;left:-160px;animation:lo-float 7s ease-in-out infinite}
.login-orb-2{width:380px;height:380px;background:rgba(139,92,246,.14);bottom:-80px;right:-120px;animation:lo-float 9s ease-in-out infinite reverse}
.login-orb-3{width:280px;height:280px;background:rgba(6,182,212,.12);top:40%;left:60%;animation:lo-float 11s ease-in-out infinite 1s}
.login-grid{position:absolute;inset:0;pointer-events:none;background-image:radial-gradient(circle,rgba(255,255,255,.04) 1px,transparent 1px);background-size:32px 32px}
@keyframes lo-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-18px)}}
.login-glass{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.10);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,.5),0 0 0 1px rgba(255,255,255,.04) inset}
.finp{width:100%;padding:12px 14px 12px 42px;background:rgba(255,255,255,.07);border:1.5px solid rgba(255,255,255,.12);border-radius:12px;color:#fff;font-size:14px;font-family:'Sarabun',sans-serif;transition:all .2s;outline:none}
.finp:focus{border-color:rgba(59,130,246,.7);background:rgba(59,130,246,.08);box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.finp.ferr{border-color:rgba(239,68,68,.6)!important;background:rgba(239,68,68,.06)!important}
.finp::placeholder{color:rgba(255,255,255,.28)}
.finp option{background:#1e293b;color:#fff}
.fwrap{position:relative}
.ficon{position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;line-height:1}
.fpw-toggle{position:absolute;right:13px;top:50%;transform:translateY(-50%);cursor:pointer;color:rgba(255,255,255,.4);font-size:15px;padding:2px;background:none;border:none;line-height:1}
.btn-main{width:100%;padding:13px;background:linear-gradient(135deg,#3b82f6,#6366f1);color:#fff;font-size:15px;font-weight:700;font-family:'Sarabun',sans-serif;border:none;border-radius:12px;cursor:pointer;box-shadow:0 8px 20px rgba(99,102,241,.35);transition:all .2s;display:block;text-align:center;text-decoration:none}
.btn-main:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 12px 28px rgba(99,102,241,.45)}
.btn-main:disabled{opacity:.55;cursor:not-allowed;transform:none}
.ferr-msg{color:#f87171;font-size:12px;margin-top:4px}
.auth-link{color:#60a5fa;font-weight:600;text-decoration:none;transition:color .2s}
.auth-link:hover{color:#93c5fd}
@keyframes lo-slideup{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.lo-slideup{animation:lo-slideup .35s ease}
@keyframes spin{to{transform:rotate(360deg)}}
.spin{animation:spin .7s linear infinite;display:inline-block}
</style>
</head>
<body>
<div id="app">
<div class="login-bg">
  <div class="login-orb login-orb-1"></div>
  <div class="login-orb login-orb-2"></div>
  <div class="login-orb login-orb-3"></div>
  <div class="login-grid"></div>

  <div style="position:relative;z-index:10;width:100%;max-width:460px;padding:8px 0">

    <!-- Logo -->
    <div class="text-center mb-6 lo-slideup">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-3"
           style="background:linear-gradient(135deg,#3b82f6,#6366f1);box-shadow:0 12px 28px rgba(99,102,241,.45)">
        <span class="text-white font-bold text-xl" style="letter-spacing:-1px">IT</span>
      </div>
      <h1 class="text-white text-xl font-bold">สมัครสมาชิก</h1>
      <p class="text-slate-400 text-sm mt-1">IT Finance System</p>
    </div>

    <!-- Server-side errors banner -->
    <?php if (!empty($errors)): ?>
    <div class="mb-4 p-3 rounded-xl text-red-300 text-sm lo-slideup"
         style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3)">
      ❌ กรุณาตรวจสอบข้อมูลอีกครั้ง
    </div>
    <?php endif; ?>

    <!-- Register card -->
    <div class="login-glass p-6 lo-slideup" style="max-height:84vh;overflow-y:auto">

      <div class="space-y-3.5">

        <!-- Name -->
        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">ชื่อ-นามสกุล <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">👤</span>
            <input v-model="sf.name" class="finp" :class="{ferr:se.name}"
                   type="text" placeholder="ชื่อ นามสกุล (ภาษาไทย)"/>
          </div>
          <p v-if="se.name" class="ferr-msg">{{ se.name }}</p>
          <?php if (!empty($errors['name'])): ?>
          <p class="ferr-msg"><?= htmlspecialchars($errors['name']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Student ID -->
        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">รหัสนิสิต <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">🎓</span>
            <input v-model="sf.studentId" class="finp" :class="{ferr:se.studentId}"
                   type="text" placeholder="682165xxxx" maxlength="10"/>
          </div>
          <p v-if="se.studentId" class="ferr-msg">{{ se.studentId }}</p>
          <?php if (!empty($errors['student_id'])): ?>
          <p class="ferr-msg"><?= htmlspecialchars($errors['student_id']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">อีเมล <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">✉️</span>
            <input v-model="sf.email" class="finp" :class="{ferr:se.email}"
                   type="email" placeholder="example@ku.th"/>
          </div>
          <p v-if="se.email" class="ferr-msg">{{ se.email }}</p>
          <?php if (!empty($errors['email'])): ?>
          <p class="ferr-msg"><?= htmlspecialchars($errors['email']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">รหัสผ่าน <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">🔑</span>
            <input v-model="sf.pass" class="finp" style="padding-right:42px" :class="{ferr:se.pass}"
                   :type="showPass?'text':'password'" placeholder="อย่างน้อย 8 ตัวอักษร"
                   autocomplete="new-password"/>
            <button type="button" class="fpw-toggle" @click="showPass=!showPass">{{ showPass?'🙈':'👁️' }}</button>
          </div>
          <p v-if="se.pass" class="ferr-msg">{{ se.pass }}</p>
          <?php if (!empty($errors['password'])): ?>
          <p class="ferr-msg"><?= htmlspecialchars($errors['password']) ?></p>
          <?php endif; ?>
          <!-- Strength meter -->
          <div v-if="sf.pass" class="mt-2">
            <div class="flex gap-1 mb-1">
              <div v-for="i in 4" :key="i" class="flex-1 rounded-full" style="height:3px;transition:background .3s"
                   :style="{background: i<=pwStrength ? pwStrengthColor : 'rgba(255,255,255,.1)'}"></div>
            </div>
            <p class="text-xs" :style="{color:pwStrengthColor}">{{ pwStrengthLabel }}</p>
          </div>
        </div>

        <!-- Confirm Password -->
        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">ยืนยันรหัสผ่าน <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">🔒</span>
            <input v-model="sf.confirm" class="finp" style="padding-right:42px" :class="{ferr:se.confirm}"
                   :type="showPass2?'text':'password'" placeholder="••••••••"
                   autocomplete="new-password"/>
            <button type="button" class="fpw-toggle" @click="showPass2=!showPass2">{{ showPass2?'🙈':'👁️' }}</button>
          </div>
          <p v-if="se.confirm" class="ferr-msg">{{ se.confirm }}</p>
          <p v-else-if="sf.confirm && sf.pass===sf.confirm" style="color:#34d399;font-size:12px;margin-top:4px">✓ รหัสผ่านตรงกัน</p>
          <?php if (!empty($errors['confirm'])): ?>
          <p class="ferr-msg"><?= htmlspecialchars($errors['confirm']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Role -->
        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">สถานะ <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">🏷️</span>
            <select v-model="sf.role" class="finp" :class="{ferr:se.role}">
              <option value="">เลือกสถานะ</option>
              <option value="student">🎓 นิสิต</option>
              <option value="activity_staff">📋 เจ้าหน้าที่กิจกรรม</option>
              <option value="academic_staff">📚 เจ้าหน้าที่วิชาการ</option>
            </select>
          </div>
          <p v-if="se.role" class="ferr-msg">{{ se.role }}</p>
          <?php if (!empty($errors['role'])): ?>
          <p class="ferr-msg"><?= htmlspecialchars($errors['role']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Submit -->
        <button class="btn-main" :disabled="loading" @click="submitSignup" style="margin-top:4px">
          <span v-if="loading" style="display:inline-flex;align-items:center;justify-content:center;gap:8px">
            <span class="spin" style="font-size:14px">⏳</span> กำลังสมัคร...
          </span>
          <span v-else>สมัครสมาชิก →</span>
        </button>

      </div>

      <hr style="border:none;border-top:1px solid rgba(255,255,255,.08);margin:20px 0"/>

      <p class="text-center text-slate-400 text-sm">
        มีบัญชีแล้ว?
        <a href="<?= base_url('login') ?>" class="auth-link">เข้าสู่ระบบ</a>
      </p>

    </div>

    <p class="text-center text-slate-600 text-xs mt-5">
      IT Finance System · สาขาวิชาเทคโนโลยีสารสนเทศ มก. · <?= date('Y')+543 ?>
    </p>
  </div>
</div>
</div>

<script>
const { createApp, ref, reactive, computed } = Vue
createApp({
  setup() {
    const loading  = ref(false)
    const showPass  = ref(false)
    const showPass2 = ref(false)
    const sf = reactive({ name:'', studentId:'', email:'', pass:'', confirm:'', role:'' })
    const se = reactive({ name:'', studentId:'', email:'', pass:'', confirm:'', role:'' })

    const pwStrength = computed(() => {
      const p = sf.pass; if (!p) return 0
      let s = 0
      if (p.length >= 8) s++; if (/[A-Z]/.test(p)) s++; if (/[0-9]/.test(p)) s++; if (/[^A-Za-z0-9]/.test(p)) s++
      return s
    })
    const pwStrengthColor = computed(() => ['','#ef4444','#f97316','#eab308','#10b981'][pwStrength.value])
    const pwStrengthLabel = computed(() => ['','อ่อนมาก','อ่อน','ปานกลาง','แข็งแกร่ง'][pwStrength.value])

    function submitSignup() {
      Object.assign(se, { name:'', studentId:'', email:'', pass:'', confirm:'', role:'' })
      let ok = true
      if (!sf.name.trim())     { se.name      = 'กรุณากรอกชื่อ-นามสกุล'; ok = false }
      if (!sf.studentId.trim() || sf.studentId.length < 10) { se.studentId = 'รหัสนิสิต 10 หลัก'; ok = false }
      if (!sf.email.trim() || !sf.email.includes('@')) { se.email = 'กรุณากรอกอีเมลที่ถูกต้อง'; ok = false }
      if (!sf.pass || sf.pass.length < 8)  { se.pass    = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร'; ok = false }
      if (sf.pass !== sf.confirm)          { se.confirm = 'รหัสผ่านไม่ตรงกัน'; ok = false }
      if (!sf.role)                        { se.role    = 'กรุณาเลือกสถานะ'; ok = false }
      if (!ok) return
      loading.value = true
      const f = document.createElement('form')
      f.method = 'POST'; f.action = '<?= base_url('register') ?>'
      const add = (n, v) => { const i = document.createElement('input'); i.type='hidden'; i.name=n; i.value=v; f.appendChild(i) }
      add('name',       sf.name)
      add('student_id', sf.studentId)
      add('email',      sf.email)
      add('password',   sf.pass)
      add('confirm',    sf.confirm)
      add('role',       sf.role)
      document.body.appendChild(f); f.submit()
    }

    return { loading, showPass, showPass2, sf, se, pwStrength, pwStrengthColor, pwStrengthLabel, submitSignup }
  }
}).mount('#app')
</script>
</body>
</html>
