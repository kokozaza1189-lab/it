<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?= isset($title) ? $title : 'ตั้งรหัสผ่านใหม่ — IT Finance System' ?></title>
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
.finp::placeholder{color:rgba(255,255,255,.28)}
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
</style>
</head>
<body>
<div id="app">
<div class="login-bg">
  <div class="login-orb login-orb-1"></div>
  <div class="login-orb login-orb-2"></div>
  <div class="login-orb login-orb-3"></div>
  <div class="login-grid"></div>

  <div style="position:relative;z-index:10;width:100%;max-width:420px">

    <!-- Logo -->
    <div class="text-center mb-7 lo-slideup">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4"
           style="background:linear-gradient(135deg,#3b82f6,#6366f1);box-shadow:0 12px 28px rgba(99,102,241,.45)">
        <span class="text-white font-bold text-2xl" style="letter-spacing:-1px">IT</span>
      </div>
      <h1 class="text-white text-2xl font-bold">IT Finance System</h1>
      <p class="text-slate-400 text-sm mt-1">ระบบการเงินสาขาวิชาเทคโนโลยีสารสนเทศ</p>
    </div>

    <!-- Reset card -->
    <div class="login-glass p-7 lo-slideup">

      <div class="text-center mb-5">
        <div style="font-size:44px;margin-bottom:8px">🔑</div>
        <h2 class="text-white text-lg font-bold">ตั้งรหัสผ่านใหม่</h2>
        <p class="text-slate-400 text-sm mt-1">กรอกรหัสผ่านใหม่ของคุณ (อย่างน้อย 8 ตัวอักษร)</p>
      </div>

      <!-- Server-side error -->
      <?php if (!empty($reset_error)): ?>
      <div class="mb-4 p-3 rounded-xl text-red-300 text-sm"
           style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3)">
        ❌ <?= htmlspecialchars($reset_error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="<?= base_url('reset/'.htmlspecialchars($token)) ?>" class="space-y-4">

        <!-- New Password -->
        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">รหัสผ่านใหม่ <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">🔒</span>
            <input name="new_password" v-model="pass" class="finp" style="padding-right:42px"
                   :type="showPass?'text':'password'" placeholder="อย่างน้อย 8 ตัวอักษร"
                   autocomplete="new-password"/>
            <button type="button" class="fpw-toggle" @click="showPass=!showPass">{{ showPass?'🙈':'👁️' }}</button>
          </div>
          <!-- Strength meter -->
          <div v-if="pass" class="mt-2">
            <div class="flex gap-1 mb-1">
              <div v-for="i in 4" :key="i" class="flex-1 rounded-full" style="height:3px;transition:background .3s"
                   :style="{background: i<=strength ? strengthColor : 'rgba(255,255,255,.1)'}"></div>
            </div>
            <p class="text-xs" :style="{color:strengthColor}">{{ strengthLabel }}</p>
          </div>
        </div>

        <!-- Confirm Password -->
        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">ยืนยันรหัสผ่าน <span class="text-red-400">*</span></label>
          <div class="fwrap">
            <span class="ficon">🔑</span>
            <input name="confirm_password" v-model="confirm" class="finp" style="padding-right:42px"
                   :type="showPass2?'text':'password'" placeholder="••••••••"
                   autocomplete="new-password"/>
            <button type="button" class="fpw-toggle" @click="showPass2=!showPass2">{{ showPass2?'🙈':'👁️' }}</button>
          </div>
          <p v-if="confirm && pass!==confirm" class="ferr-msg">รหัสผ่านไม่ตรงกัน</p>
          <p v-else-if="confirm && pass===confirm" style="color:#34d399;font-size:12px;margin-top:4px">✓ รหัสผ่านตรงกัน</p>
        </div>

        <button type="submit" class="btn-main" :disabled="pass.length < 8 || pass !== confirm">
          ยืนยันตั้งรหัสผ่านใหม่ →
        </button>

      </form>

      <hr style="border:none;border-top:1px solid rgba(255,255,255,.08);margin:20px 0"/>

      <p class="text-center text-slate-400 text-sm">
        <a href="<?= base_url('forgot') ?>" class="auth-link">ขอลิงก์ใหม่</a>
        &nbsp;·&nbsp;
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
const { createApp, ref, computed } = Vue
createApp({
  setup() {
    const pass     = ref('')
    const confirm  = ref('')
    const showPass  = ref(false)
    const showPass2 = ref(false)

    const strength = computed(() => {
      const p = pass.value; if (!p) return 0
      let s = 0
      if (p.length >= 8) s++; if (/[A-Z]/.test(p)) s++; if (/[0-9]/.test(p)) s++; if (/[^A-Za-z0-9]/.test(p)) s++
      return s
    })
    const strengthColor = computed(() => ['','#ef4444','#f97316','#eab308','#10b981'][strength.value])
    const strengthLabel = computed(() => ['','อ่อนมาก','อ่อน','ปานกลาง','แข็งแกร่ง'][strength.value])

    return { pass, confirm, showPass, showPass2, strength, strengthColor, strengthLabel }
  }
}).mount('#app')
</script>
</body>
</html>
