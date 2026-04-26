<?php
$role       = $current_user['role'];
$can_save   = in_array($role, ['super_admin','treasurer']);
$is_super   = $role === 'super_admin';
$flash      = $this->session->flashdata('success');
$role_labels = [
  'student'        => 'Student',       'activity_staff' => 'Activity Staff',
  'academic_staff' => 'Academic Staff','treasurer'      => 'Treasurer',
  'head_it'        => 'Head of IT',    'advisor'        => 'Advisor',
  'auditor'        => 'Auditor',       'super_admin'    => 'Super Admin',
];
$role_colors = [
  'student'=>'#6366f1','activity_staff'=>'#8b5cf6','academic_staff'=>'#06b6d4',
  'treasurer'=>'#f59e0b','head_it'=>'#10b981','advisor'=>'#14b8a6',
  'auditor'=>'#ef4444','super_admin'=>'#f97316',
];
?>
<div id="app">

<?php if ($flash): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-emerald-700 text-sm font-medium" style="background:#d1fae5;border:1px solid #a7f3d0">
  ✅ <?= htmlspecialchars($flash) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

  <!-- General Settings -->
  <div class="lg:col-span-1">
    <div class="card">
      <h2 class="font-bold text-slate-800 text-base mb-5">ตั้งค่าทั่วไป</h2>
      <form method="POST" action="<?= base_url('settings/save') ?>" class="space-y-4">
        <div>
          <label class="lbl">ปีการศึกษา (พ.ศ.)</label>
          <input name="academic_year" type="number" class="inp <?= !$can_save?'bg-slate-50':'' ?>"
                 value="<?= htmlspecialchars($settings['academic_year'] ?? 2568) ?>"
                 <?= !$can_save?'readonly':'' ?>/>
        </div>
        <div>
          <label class="lbl">ค่าธรรมเนียมรายเดือน (฿)</label>
          <input name="monthly_fee" type="number" step="0.01" class="inp <?= !$can_save?'bg-slate-50':'' ?>"
                 value="<?= htmlspecialchars($settings['monthly_fee'] ?? 50) ?>"
                 <?= !$can_save?'readonly':'' ?>/>
        </div>
        <div>
          <label class="lbl">วันครบกำหนด (วันที่ของเดือน)</label>
          <input name="due_day" type="number" min="1" max="31" class="inp <?= !$can_save?'bg-slate-50':'' ?>"
                 value="<?= htmlspecialchars($settings['due_day'] ?? 8) ?>"
                 <?= !$can_save?'readonly':'' ?>/>
        </div>
        <div>
          <label class="lbl">ค่าปรับต่อวัน (฿)</label>
          <input name="penalty_per_day" type="number" step="0.01" class="inp <?= !$can_save?'bg-slate-50':'' ?>"
                 value="<?= htmlspecialchars($settings['penalty_per_day'] ?? 5) ?>"
                 <?= !$can_save?'readonly':'' ?>/>
        </div>
        <div>
          <label class="lbl">เดือนที่เก็บเงิน (คั่นด้วย ,)</label>
          <input name="active_months" class="inp <?= !$can_save?'bg-slate-50':'' ?>"
                 value="<?= htmlspecialchars($settings['active_months'] ?? '1,3,4,5,6,7,8,9,10,11') ?>"
                 <?= !$can_save?'readonly':'' ?>/>
          <p class="text-slate-400 text-xs mt-1">เช่น 1,2,3,4 หมายถึง ม.ค.–เม.ย.</p>
        </div>
        <?php if ($can_save): ?>
        <button type="submit" class="btn btn-blue w-full">บันทึกการตั้งค่า</button>
        <?php else: ?>
        <p class="text-slate-400 text-xs text-center py-2">เฉพาะ Treasurer / Super Admin แก้ไขได้</p>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <!-- User Management -->
  <div class="lg:col-span-2">
    <?php if ($is_super): ?>
    <div class="card overflow-hidden">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-slate-800">จัดการผู้ใช้</h2>
        <span class="badge b-none"><?= count($users) ?> บัญชี</span>
      </div>
      <div class="overflow-x-auto">
        <table class="tbl">
          <thead><tr><th>ชื่อ</th><th>อีเมล</th><th>Role</th><th>สถานะ</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                       style="background:<?= $role_colors[$u->role] ?? '#6366f1' ?>">
                    <?= mb_substr($u->name,0,1) ?>
                  </div>
                  <div>
                    <p class="font-medium text-slate-800 text-sm"><?= htmlspecialchars($u->name) ?></p>
                    <?php if ($u->student_id): ?>
                    <p class="text-slate-400 text-xs font-mono"><?= $u->student_id ?></p>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td class="text-slate-500 text-sm"><?= htmlspecialchars($u->email) ?></td>
              <td>
                <select style="border:1.5px solid #e2e8f0;border-radius:7px;padding:4px 8px;font-size:12px;font-family:Sarabun,sans-serif;background:white;cursor:pointer"
                        @change="saveRole(<?= $u->id ?>, $event.target.value, <?= (int)$u->is_active ?>)">
                  <?php foreach ($role_labels as $rv => $rl): ?>
                    <option value="<?= $rv ?>" <?= $u->role===$rv?'selected':'' ?>><?= $rl ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <button class="badge <?= $u->is_active?'b-paid':'b-none' ?> cursor-pointer hover:opacity-75"
                        @click="toggleActive(<?= $u->id ?>, '<?= $u->role ?>', <?= (int)$u->is_active ?>)">
                  <?= $u->is_active?'ใช้งาน':'ปิดใช้งาน' ?>
                </button>
              </td>
              <td>
                <button class="btn btn-gray btn-xs" @click="openResetPass(<?= $u->id ?>, '<?= addslashes(htmlspecialchars($u->name)) ?>')">
                  🔑 รีเซ็ต
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php else: ?>
    <div class="card">
      <h2 class="font-bold text-slate-800 mb-4">ข้อมูลบัญชีของฉัน</h2>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><p class="text-slate-400 text-xs">ชื่อ</p><p class="font-medium"><?= htmlspecialchars($current_user['name']) ?></p></div>
        <div><p class="text-slate-400 text-xs">อีเมล</p><p class="font-medium"><?= htmlspecialchars($current_user['email']) ?></p></div>
        <div><p class="text-slate-400 text-xs">บทบาท</p><p class="font-medium"><?= $current_user['roleLabel'] ?></p></div>
        <div><p class="text-slate-400 text-xs">รหัสนิสิต</p><p class="font-medium font-mono"><?= $current_user['student_id'] ?: '-' ?></p></div>
      </div>
    </div>
    <?php endif; ?>
  </div>

</div>

<!-- Reset password modal -->
<div v-show="resetModal" id="resetModal" class="modal-bg" @click.self="resetModal=false" style="display:none">
  <div class="modal-box" style="max-width:380px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">รีเซ็ตรหัสผ่าน</h2>
        <button @click="resetModal=false" class="btn-icon" data-modal-close="resetModal">✕</button>
      </div>
      <p class="text-slate-500 text-sm mt-1">{{ resetTarget.name }}</p>
    </div>
    <div class="modal-body space-y-4">
      <div>
        <label class="lbl">รหัสผ่านใหม่ (ว่าง = สุ่มอัตโนมัติ)</label>
        <input v-model="newPass" class="inp" placeholder="ปล่อยว่างเพื่อสุ่ม"/>
      </div>
      <div v-if="generatedPass" class="p-3 rounded-xl" style="background:#f0fdf4;border:1px solid #bbf7d0">
        <p class="text-xs text-slate-500 mb-1">รหัสผ่านใหม่ — แจ้งผู้ใช้</p>
        <p class="font-bold font-mono text-emerald-700 text-xl select-all">{{ generatedPass }}</p>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="resetModal=false" data-modal-close="resetModal">ปิด</button>
      <button class="btn btn-blue flex-1" @click="doResetPass" :disabled="saving">
        <span v-if="saving" class="spin">⏳</span> ยืนยัน
      </button>
    </div>
  </div>
</div>

</div>

<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const { createApp, ref, reactive } = Vue
createApp({
  setup() {
    const saving        = ref(false)
    const resetModal    = ref(false)
    const newPass       = ref('')
    const generatedPass = ref('')
    const resetTarget   = reactive({ id: 0, name: '' })

    function openResetPass(id, name) {
      resetTarget.id = id; resetTarget.name = name
      newPass.value = ''; generatedPass.value = ''
      resetModal.value = true
    }

    async function saveRole(id, role, isActive) {
      try {
        const fd = new FormData()
        fd.append('id', id); fd.append('role', role); fd.append('is_active', isActive)
        await axios.post('<?= base_url('settings/save_user') ?>', fd)
        showToast('บันทึก Role แล้ว')
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
    }

    async function toggleActive(id, role, isActive) {
      try {
        const fd = new FormData()
        fd.append('id', id); fd.append('role', role); fd.append('is_active', isActive ? 0 : 1)
        await axios.post('<?= base_url('settings/save_user') ?>', fd)
        showToast('อัปเดตสถานะแล้ว')
        setTimeout(() => location.reload(), 800)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
    }

    async function doResetPass() {
      saving.value = true
      try {
        const fd = new FormData()
        fd.append('id', resetTarget.id)
        if (newPass.value) fd.append('password', newPass.value)
        const res = await axios.post('<?= base_url('settings/reset_pass') ?>', fd)
        generatedPass.value = res.data.new_password
        showToast('รีเซ็ตรหัสผ่านแล้ว')
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      saving.value = false
    }

    return { saving, resetModal, newPass, generatedPass, resetTarget,
             openResetPass, saveRole, toggleActive, doResetPass }
  }
}).mount('#app')
})
</script>
