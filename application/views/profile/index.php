<?php
$role_labels = [
  'student'        => 'นิสิต',
  'activity_staff' => 'เจ้าหน้าที่กิจกรรม',
  'academic_staff' => 'เจ้าหน้าที่วิชาการ',
  'treasurer'      => 'เหรัญญิก',
  'head_it'        => 'หัวหน้าสาขา',
  'advisor'        => 'อาจารย์ที่ปรึกษา',
  'auditor'        => 'ผู้ตรวจสอบ',
  'super_admin'    => 'ผู้ดูแลระบบ',
];
?>
<div id="app">

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

  <!-- Profile card -->
  <div class="col-span-1">
    <div class="card text-center">
      <!-- Avatar -->
      <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl font-bold text-white"
           style="background:<?= $current_user['color'] ?>">
        <?= mb_substr(preg_replace('/นาย|นางสาว|หัวหน้า|อาจารย์/u', '', $db_user->name), 0, 1) ?>
      </div>
      <h2 class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($db_user->name) ?></h2>
      <p class="text-slate-500 text-sm mt-1"><?= $role_labels[$db_user->role] ?? $db_user->role ?></p>
      <?php if (!empty($db_user->student_id)): ?>
      <p class="text-slate-400 text-xs font-mono mt-1"><?= htmlspecialchars($db_user->student_id) ?></p>
      <?php endif; ?>
      <?php if (!empty($db_user->email)): ?>
      <p class="text-slate-400 text-xs mt-1"><?= htmlspecialchars($db_user->email) ?></p>
      <?php endif; ?>
      <div class="mt-4 pt-4" style="border-top:1px solid #f1f5f9">
        <span class="badge <?= $db_user->is_active ? 'b-paid' : 'b-overdue' ?>">
          <?= $db_user->is_active ? 'ใช้งานได้' : 'ปิดใช้งาน' ?>
        </span>
      </div>
    </div>
  </div>

  <!-- Change password -->
  <div class="col-span-1 lg:col-span-2">
    <div class="card">
      <h3 class="font-bold text-slate-800 mb-5">เปลี่ยนรหัสผ่าน</h3>
      <div class="space-y-4 max-w-md">
        <div>
          <label class="lbl">รหัสผ่านปัจจุบัน <span class="text-red-500">*</span></label>
          <input v-model="form.current" type="password" class="inp" placeholder="รหัสผ่านปัจจุบัน"/>
        </div>
        <div>
          <label class="lbl">รหัสผ่านใหม่ <span class="text-red-500">*</span></label>
          <input v-model="form.new_pass" type="password" class="inp" placeholder="อย่างน้อย 8 ตัวอักษร"/>
        </div>
        <div>
          <label class="lbl">ยืนยันรหัสผ่านใหม่ <span class="text-red-500">*</span></label>
          <input v-model="form.confirm" type="password" class="inp" placeholder="พิมพ์รหัสผ่านใหม่อีกครั้ง"/>
        </div>

        <!-- Strength indicator -->
        <div v-if="form.new_pass" class="flex gap-1">
          <div v-for="i in 4" :key="i" class="h-1.5 flex-1 rounded-full transition-all"
               :class="strength >= i ? strengthColor : 'bg-slate-200'"></div>
        </div>
        <p v-if="form.new_pass" class="text-xs" :class="strength < 2 ? 'text-red-500' : strength < 3 ? 'text-amber-500' : 'text-emerald-600'">
          {{ strengthLabel }}
        </p>

        <p v-if="error" class="text-red-500 text-sm bg-red-50 border border-red-100 rounded-lg p-3">{{ error }}</p>
        <p v-if="success" class="text-emerald-600 text-sm bg-emerald-50 border border-emerald-100 rounded-lg p-3">{{ success }}</p>

        <button class="btn btn-blue" @click="submit" :disabled="saving">
          <span v-if="saving" class="spin">⏳</span>
          🔐 เปลี่ยนรหัสผ่าน
        </button>
      </div>
    </div>
  </div>
</div>

</div>

<script>
const { createApp, ref, reactive, computed } = Vue
createApp({
  setup() {
    const saving  = ref(false)
    const error   = ref('')
    const success = ref('')
    const form    = reactive({ current:'', new_pass:'', confirm:'' })

    const strength = computed(() => {
      const p = form.new_pass
      if (!p) return 0
      let s = 0
      if (p.length >= 8)  s++
      if (p.length >= 12) s++
      if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++
      if (/[0-9]/.test(p) || /[^A-Za-z0-9]/.test(p)) s++
      return s
    })

    const strengthColor = computed(() => {
      return ['','bg-red-400','bg-amber-400','bg-emerald-400','bg-emerald-500'][strength.value] || 'bg-slate-200'
    })

    const strengthLabel = computed(() => {
      return ['','อ่อนมาก','อ่อน','ปานกลาง','แข็งแกร่ง'][strength.value] || ''
    })

    async function submit() {
      error.value = ''; success.value = ''
      if (!form.current || !form.new_pass || !form.confirm) {
        error.value = 'กรุณากรอกข้อมูลให้ครบ'; return
      }
      if (form.new_pass !== form.confirm) {
        error.value = 'รหัสผ่านใหม่ไม่ตรงกัน'; return
      }
      if (form.new_pass.length < 8) {
        error.value = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร'; return
      }
      saving.value = true
      try {
        await axios.post('<?= base_url('profile/change_password') ?>', {
          current_password: form.current,
          new_password:     form.new_pass,
          confirm_password: form.confirm
        })
        success.value = 'เปลี่ยนรหัสผ่านสำเร็จ'
        form.current = form.new_pass = form.confirm = ''
        showToast('เปลี่ยนรหัสผ่านสำเร็จ')
      } catch(e) {
        error.value = e.response?.data?.error || 'เกิดข้อผิดพลาด'
      }
      saving.value = false
    }

    return { saving, error, success, form, strength, strengthColor, strengthLabel, submit }
  }
}).mount('#app')
</script>
