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
.finp{width:100%;padding:12px 14px 12px 42px;background:rgba(255,255,255,.07);border:1.5px solid rgba(255,255,255,.12);border-radius:12px;color:#fff;font-size:14px;font-family:'Sarabun',sans-serif;transition:all .2s;outline:none}
.finp:focus{border-color:rgba(59,130,246,.7);background:rgba(59,130,246,.08);box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.finp.ferr{border-color:rgba(239,68,68,.6)!important;background:rgba(239,68,68,.06)!important}
.finp::placeholder{color:rgba(255,255,255,.28)}
.fwrap{position:relative}
.ficon{position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;line-height:1}
.fpw-toggle{position:absolute;right:13px;top:50%;transform:translateY(-50%);cursor:pointer;color:rgba(255,255,255,.4);font-size:15px;padding:2px;background:none;border:none;line-height:1}
.btn-main{width:100%;padding:13px;background:linear-gradient(135deg,#3b82f6,#6366f1);color:#fff;font-size:15px;font-weight:700;font-family:'Sarabun',sans-serif;border:none;border-radius:12px;cursor:pointer;box-shadow:0 8px 20px rgba(99,102,241,.35);transition:all .2s;display:block;text-align:center;text-decoration:none}
.btn-main:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 12px 28px rgba(99,102,241,.45)}
.btn-main:disabled{opacity:.55;cursor:not-allowed;transform:none}
.ferr-msg{color:#f87171;font-size:12px;margin-top:4px}
.auth-link{color:#60a5fa;font-weight:600;cursor:pointer;background:none;border:none;font-family:'Sarabun',sans-serif;font-size:inherit;padding:0;text-decoration:none;transition:color .2s}
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

    <!-- Flash success (from register/reset redirects) -->
    <?php if (!empty($flash_success)): ?>
    <div class="mb-4 p-3 rounded-xl text-green-300 text-sm lo-slideup"
         style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3)">
      ✅ <?= htmlspecialchars($flash_success) ?>
    </div>
    <?php endif; ?>

    <!-- Flash error -->
    <?php if (!empty($flash_error)): ?>
    <div class="mb-4 p-3 rounded-xl text-red-300 text-sm lo-slideup"
         style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3)">
      ❌ <?= htmlspecialchars($flash_error) ?>
    </div>
    <?php endif; ?>

    <!-- Login card -->
    <div class="login-glass p-7 lo-slideup">

      <!-- Server error (wrong password etc.) -->
      <?php if (!empty($error)): ?>
      <div class="mb-4 p-3 rounded-xl text-red-300 text-sm"
           style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3)">
        ❌ <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <h2 class="text-white text-lg font-bold mb-5 text-center">เข้าสู่ระบบ</h2>

      <div class="space-y-4">

        <!-- Identifier -->
        <div>
          <label class="block text-slate-300 text-sm font-medium mb-1.5">อีเมล / รหัสนิสิต</label>
          <div class="fwrap">
            <span class="ficon">🪪</span>
            <input v-model="lf.identifier" class="finp" :class="{ferr:le.identifier}"
                   type="text" placeholder="example@ku.th หรือ 682165xxxx"
                   autocomplete="username" @keyup.enter="submitLogin"/>
          </div>
          <p v-if="le.identifier" class="ferr-msg">{{ le.identifier }}</p>
        </div>

        <!-- Password -->
        <div>
          <div class="flex justify-between items-center mb-1.5">
            <label class="text-slate-300 text-sm font-medium">รหัสผ่าน</label>
            <a href="<?= base_url('forgot') ?>" class="text-blue-400 text-xs hover:text-blue-300">ลืมรหัสผ่าน?</a>
          </div>
          <div class="fwrap">
            <span class="ficon">🔑</span>
            <input v-model="lf.pass" class="finp" style="padding-right:42px" :class="{ferr:le.pass}"
                   :type="showPass?'text':'password'" placeholder="••••••••"
                   autocomplete="current-password" @keyup.enter="submitLogin"/>
            <button type="button" class="fpw-toggle" @click="showPass=!showPass">{{ showPass?'🙈':'👁️' }}</button>
          </div>
          <p v-if="le.pass" class="ferr-msg">{{ le.pass }}</p>
        </div>

        <!-- Submit -->
        <button class="btn-main" :disabled="loading" @click="submitLogin">
          <span v-if="loading" style="display:inline-flex;align-items:center;justify-content:center;gap:8px">
            <span class="spin" style="font-size:14px">⏳</span> กำลังเข้าสู่ระบบ...
          </span>
          <span v-else>เข้าสู่ระบบ →</span>
        </button>

      </div>

      <hr style="border:none;border-top:1px solid rgba(255,255,255,.08);margin:20px 0"/>

      <p class="text-center text-slate-400 text-sm">
        ยังไม่มีบัญชี?
        <a href="<?= base_url('register') ?>" class="auth-link">สมัครสมาชิก</a>
      </p>

    </div>

    <p class="text-center text-slate-600 text-xs mt-5">
      IT Finance System · สาขาวิชาเทคโนโลยีสารสนเทศ มก. · <?= date('Y')+543 ?>
    </p>
  </div>
</div>
</div>

<script>
const { createApp, ref, reactive } = Vue
createApp({
  setup() {
    const loading  = ref(false)
    const showPass = ref(false)
    const lf = reactive({ identifier: '', pass: '' })
    const le = reactive({ identifier: '', pass: '' })

    function submitLogin() {
      le.identifier = ''; le.pass = ''
      let ok = true
      if (!lf.identifier.trim()) { le.identifier = 'กรุณากรอกอีเมลหรือรหัสนิสิต'; ok = false }
      if (!lf.pass.trim())       { le.pass        = 'กรุณากรอกรหัสผ่าน'; ok = false }
      if (!ok) return
      loading.value = true
      const f = document.createElement('form')
      f.method = 'POST'; f.action = '<?= base_url('login') ?>'
      const add = (n, v) => { const i = document.createElement('input'); i.type='hidden'; i.name=n; i.value=v; f.appendChild(i) }
      add('identifier', lf.identifier)
      add('password',   lf.pass)
      document.body.appendChild(f); f.submit()
    }

    return { loading, showPass, lf, le, submitLogin }
  }
}).mount('#app')
</script>
</body>
</html>
