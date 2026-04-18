<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?= isset($title) ? $title : 'เข้าสู่ระบบ — IT Finance System' ?></title>
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
.login-tab-pill{background:rgba(255,255,255,.06);border-radius:12px;padding:4px;display:flex}
.login-tab-btn{flex:1;padding:8px 16px;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;transition:all .25s;border:none;outline:none;color:rgba(255,255,255,.45);background:transparent;font-family:'Sarabun',sans-serif}
.login-tab-btn.active{background:rgba(59,130,246,.85);color:#fff;box-shadow:0 4px 12px rgba(59,130,246,.4)}
.finp{width:100%;padding:12px 14px 12px 42px;background:rgba(255,255,255,.07);border:1.5px solid rgba(255,255,255,.12);border-radius:12px;color:#fff;font-size:14px;font-family:'Sarabun',sans-serif;transition:all .2s;outline:none}
.finp:focus{border-color:rgba(59,130,246,.7);background:rgba(59,130,246,.08);box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.finp.ferr{border-color:rgba(239,68,68,.6)!important;background:rgba(239,68,68,.06)!important}
.finp::placeholder{color:rgba(255,255,255,.28)}
.finp option{background:#1e293b;color:#fff}
.fwrap{position:relative}
.ficon{position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;line-height:1}
.fpw-toggle{position:absolute;right:13px;top:50%;transform:translateY(-50%);cursor:pointer;color:rgba(255,255,255,.4);font-size:15px;padding:2px;background:none;border:none;line-height:1}
.btn-login{width:100%;padding:13px;background:linear-gradient(135deg,#3b82f6,#6366f1);color:#fff;font-size:15px;font-weight:700;font-family:'Sarabun',sans-serif;border:none;border-radius:12px;cursor:pointer;box-shadow:0 8px 20px rgba(99,102,241,.35);transition:all .2s}
.btn-login:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 12px 28px rgba(99,102,241,.45)}
.btn-login:disabled{opacity:.55;cursor:not-allowed;transform:none}
.btn-social{flex:1;padding:10px 8px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:12px;cursor:pointer;color:rgba(255,255,255,.7);font-size:13px;font-family:'Sarabun',sans-serif;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:6px}
.btn-social:hover{background:rgba(255,255,255,.1)}
.login-divider{display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.25);font-size:12px}
.login-divider::before,.login-divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.1)}
.ferr-msg{color:#f87171;font-size:12px;margin-top:4px}
.login-success-ring{width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;margin:0 auto;box-shadow:0 0 0 12px rgba(16,185,129,.15),0 8px 24px rgba(16,185,129,.4)}
@keyframes lo-slideup{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.lo-slideup{animation:lo-slideup .35s ease}
.slide-enter-active,.slide-leave-active{transition:all .28s ease}
.slide-enter-from{opacity:0;transform:translateX(18px)}
.slide-leave-to{opacity:0;transform:translateX(-18px)}
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

  <div style="position:relative;z-index:10;width:100%;max-width:440px">

    <!-- Logo -->
    <div class="text-center mb-7 lo-slideup">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4"
           style="background:linear-gradient(135deg,#3b82f6,#6366f1);box-shadow:0 12px 28px rgba(99,102,241,.45)">
        <span class="text-white font-bold text-2xl" style="letter-spacing:-1px">IT</span>
      </div>
      <h1 class="text-white text-2xl font-bold">IT Finance System</h1>
      <p class="text-slate-400 text-sm mt-1">ระบบการเงินสาขาวิชาเทคโนโลยีสารสนเทศ</p>
    </div>

    <!-- Success message -->
    <div v-if="successMsg" class="login-glass p-8 text-center lo-slideup">
      <div class="login-success-ring mb-5">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <h2 class="text-white text-xl font-bold mb-2">{{ successMsg }}</h2>
      <button class="btn-login mt-2" @click="successMsg=null;tab='login'">กลับสู่หน้า Login</button>
    </div>

    <!-- Main card -->
    <div v-else class="login-glass p-7 lo-slideup">

      <!-- Server errors -->
      <?php if (!empty($error)): ?>
      <div class="mb-4 p-3 rounded-xl text-red-300 text-sm" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3)">
        ❌ <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <!-- Tab pill -->
      <div class="login-tab-pill mb-7">
        <button class="login-tab-btn" :class="{active:tab==='login'}" @click="tab='login'">เข้าสู่ระบบ</button>
        <button class="login-tab-btn" :class="{active:tab==='signup'}" @click="tab='signup'">สมัครสมาชิก</button>
      </div>

      <!-- LOGIN FORM -->
      <transition name="slide" mode="out-in">
      <form v-if="tab==='login'" key="login" method="POST" action="<?= base_url('login') ?>" @submit="loading=true" class="space-y-4" novalidate>

        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">อีเมล / รหัสนิสิต</label>
          <div class="fwrap">
            <span class="ficon">🪪</span>
            <input name="identifier" v-model="lf.identifier" class="finp" :class="{ferr:le.identifier}"
                   type="text" placeholder="example@ku.th หรือ 682165xxxx" autocomplete="username"/>
          </div>
          <p v-if="le.identifier" class="ferr-msg">{{ le.identifier }}</p>
        </div>

        <div>
          <div class="flex justify-between items-center mb-1.5">
            <label class="text-slate-300 text-sm font-medium">รหัสผ่าน</label>
            <span class="text-slate-500 text-xs">ลืมรหัสผ่าน? ติดต่อผู้ดูแลระบบ</span>
          </div>
          <div class="fwrap">
            <span class="ficon">🔑</span>
            <input name="password" v-model="lf.pass" class="finp" style="padding-right:42px" :class="{ferr:le.pass}"
                   :type="showPass?'text':'password'" placeholder="••••••••" autocomplete="current-password"/>
            <button type="button" class="fpw-toggle" @click="showPass=!showPass">{{ showPass?'🙈':'👁️' }}</button>
          </div>
          <p v-if="le.pass" class="ferr-msg">{{ le.pass }}</p>
        </div>

        <button type="submit" class="btn-login" :disabled="loading" @click.prevent="submitLogin">
          <span v-if="loading" class="inline-flex items-center gap-2"><span class="spin" style="font-size:14px">⏳</span> กำลังเข้าสู่ระบบ...</span>
          <span v-else>เข้าสู่ระบบ →</span>
        </button>

        <p class="text-center text-slate-500 text-xs">
          ยังไม่มีบัญชี?
          <button type="button" class="text-blue-400 font-medium" @click="tab='signup'">สมัครสมาชิก</button>
        </p>
      </form>
      </transition>

      <!-- SIGNUP FORM -->
      <transition name="slide" mode="out-in">
      <form v-if="tab==='signup'" key="signup" method="POST" action="<?= base_url('register') ?>" @submit="loading=true" class="space-y-3.5" novalidate>

        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">ชื่อ-นามสกุล <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">👤</span>
            <input name="name" v-model="sf.name" class="finp" :class="{ferr:se.name}" type="text" placeholder="ชื่อ นามสกุล (ภาษาไทย)"/>
          </div>
          <p v-if="se.name" class="ferr-msg">{{ se.name }}</p>
        </div>

        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">รหัสนิสิต <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">🎓</span>
            <input name="student_id" v-model="sf.studentId" class="finp" :class="{ferr:se.studentId}" type="text" placeholder="682165xxxx" maxlength="10"/>
          </div>
          <p v-if="se.studentId" class="ferr-msg">{{ se.studentId }}</p>
        </div>

        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">อีเมล <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">✉️</span>
            <input name="email" v-model="sf.email" class="finp" :class="{ferr:se.email}" type="email" placeholder="example@ku.th"/>
          </div>
          <p v-if="se.email" class="ferr-msg">{{ se.email }}</p>
        </div>

        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">รหัสผ่าน <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">🔑</span>
            <input name="password" v-model="sf.pass" class="finp" style="padding-right:42px" :class="{ferr:se.pass}"
                   :type="showPass2?'text':'password'" placeholder="อย่างน้อย 8 ตัวอักษร" autocomplete="new-password"/>
            <button type="button" class="fpw-toggle" @click="showPass2=!showPass2">{{ showPass2?'🙈':'👁️' }}</button>
          </div>
          <p v-if="se.pass" class="ferr-msg">{{ se.pass }}</p>
          <div v-if="sf.pass" class="mt-2">
            <div class="flex gap-1 mb-1">
              <div v-for="i in 4" :key="i" class="flex-1 rounded-full" style="height:3px;transition:background .3s"
                   :style="{background: i<=pwStrength ? pwStrengthColor : 'rgba(255,255,255,.1)'}"></div>
            </div>
            <p class="text-xs" :style="{color:pwStrengthColor}">{{ pwStrengthLabel }}</p>
          </div>
        </div>

        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">ยืนยันรหัสผ่าน <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">🔒</span>
            <input name="confirm" v-model="sf.confirm" class="finp" style="padding-right:42px" :class="{ferr:se.confirm}"
                   :type="showPass3?'text':'password'" placeholder="••••••••" autocomplete="new-password"/>
            <button type="button" class="fpw-toggle" @click="showPass3=!showPass3">{{ showPass3?'🙈':'👁️' }}</button>
          </div>
          <p v-if="se.confirm" class="ferr-msg">{{ se.confirm }}</p>
          <p v-else-if="sf.confirm && sf.pass===sf.confirm" style="color:#34d399;font-size:12px;margin-top:4px">✓ รหัสผ่านตรงกัน</p>
        </div>

        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">สถานะ <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">🏷️</span>
            <select name="role" v-model="sf.role" class="finp" :class="{ferr:se.role}">
              <option value="">เลือกสถานะ</option>
              <option value="student">🎓 นิสิต</option>
              <option value="activity_staff">📋 เจ้าหน้าที่กิจกรรม</option>
              <option value="academic_staff">📚 เจ้าหน้าที่วิชาการ</option>
            </select>
          </div>
          <p v-if="se.role" class="ferr-msg">{{ se.role }}</p>
        </div>

        <button type="submit" class="btn-login mt-1" :disabled="loading" @click.prevent="submitSignup">
          <span v-if="loading" class="inline-flex items-center gap-2"><span class="spin" style="font-size:14px">⏳</span> กำลังสมัคร...</span>
          <span v-else>สมัครสมาชิก →</span>
        </button>

        <p class="text-center text-slate-500 text-xs">
          มีบัญชีแล้ว?
          <button type="button" class="text-blue-400 font-medium" @click="tab='login'">เข้าสู่ระบบ</button>
        </p>
      </form>
      </transition>
    </div>

    <p class="text-center text-slate-600 text-xs mt-5">IT Finance System · สาขาวิชาเทคโนโลยีสารสนเทศ มก. · <?= date('Y')+543 ?></p>
  </div>
</div>

</div>

<script>
const { createApp, ref, reactive, computed } = Vue
createApp({
  setup() {
    const tab        = ref('<?= isset($tab) ? $tab : 'login' ?>')
    const loading    = ref(false)
    const showPass   = ref(false)
    const showPass2  = ref(false)
    const showPass3  = ref(false)
    const successMsg  = ref(<?= isset($success) ? json_encode($success) : 'null' ?>)

    const lf = reactive({ identifier: '', pass: '' })
    const le = reactive({ identifier: '', pass: '' })
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

    function validateLogin() {
      le.identifier = ''; le.pass = ''
      let ok = true
      if (!lf.identifier.trim()) { le.identifier = 'กรุณากรอกอีเมลหรือรหัสนิสิต'; ok = false }
      if (!lf.pass.trim())       { le.pass        = 'กรุณากรอกรหัสผ่าน'; ok = false }
      return ok
    }

    function submitLogin() {
      if (!validateLogin()) return
      loading.value = true
      const f = document.createElement('form')
      f.method = 'POST'
      f.action = '<?= base_url('login') ?>'
      const addField = (n, v) => { const i = document.createElement('input'); i.type='hidden'; i.name=n; i.value=v; f.appendChild(i) }
      addField('identifier', lf.identifier)
      addField('password',   lf.pass)
      document.body.appendChild(f)
      f.submit()
    }

    function validateSignup() {
      Object.assign(se, { name:'', studentId:'', email:'', pass:'', confirm:'', role:'' })
      let ok = true
      if (!sf.name.trim())     { se.name      = 'กรุณากรอกชื่อ-นามสกุล'; ok = false }
      if (!sf.studentId.trim() || sf.studentId.length < 10) { se.studentId = 'รหัสนิสิต 10 หลัก'; ok = false }
      if (!sf.email.trim() || !sf.email.includes('@')) { se.email = 'กรุณากรอกอีเมลที่ถูกต้อง'; ok = false }
      if (!sf.pass || sf.pass.length < 8) { se.pass = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร'; ok = false }
      if (sf.pass !== sf.confirm) { se.confirm = 'รหัสผ่านไม่ตรงกัน'; ok = false }
      if (!sf.role) { se.role = 'กรุณาเลือกสถานะ'; ok = false }
      return ok
    }

    function submitSignup() {
      if (!validateSignup()) return
      loading.value = true
      const f = document.createElement('form')
      f.method = 'POST'
      f.action = '<?= base_url('register') ?>'
      const addField = (n, v) => { const i = document.createElement('input'); i.type='hidden'; i.name=n; i.value=v; f.appendChild(i) }
      addField('name',       sf.name)
      addField('student_id', sf.studentId)
      addField('email',      sf.email)
      addField('password',   sf.pass)
      addField('confirm',    sf.confirm)
      addField('role',       sf.role)
      document.body.appendChild(f)
      f.submit()
    }

    return { tab, loading, showPass, showPass2, showPass3, successMsg,
             lf, le, sf, se, pwStrength, pwStrengthColor, pwStrengthLabel,
             submitLogin, submitSignup }
  }
}).mount('#app')
</script>
</body>
</html>
