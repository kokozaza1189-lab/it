<?php
$role     = $current_user['role'];
$is_super = $role === 'super_admin';
$role_labels = [
  'student'         => 'นิสิต',
  'activity_staff'  => 'เจ้าหน้าที่กิจกรรม',
  'academic_staff'  => 'เจ้าหน้าที่วิชาการ',
  'treasurer'       => 'เหรัญญิก',
  'head_it'         => 'หัวหน้าสาขา',
  'advisor'         => 'อาจารย์ที่ปรึกษา',
  'auditor'         => 'ผู้ตรวจสอบ',
  'super_admin'     => 'ผู้ดูแลระบบ',
];
$role_colors = [
  'student'         => 'bg-blue-50 text-blue-700',
  'activity_staff'  => 'bg-purple-50 text-purple-700',
  'academic_staff'  => 'bg-indigo-50 text-indigo-700',
  'treasurer'       => 'bg-amber-50 text-amber-700',
  'head_it'         => 'bg-teal-50 text-teal-700',
  'advisor'         => 'bg-cyan-50 text-cyan-700',
  'auditor'         => 'bg-slate-100 text-slate-600',
  'super_admin'     => 'bg-red-50 text-red-700',
];
?>
<div id="app">

<!-- Stats + Search bar -->
<div class="flex flex-col sm:flex-row sm:items-end gap-4 mb-5">
  <div class="flex gap-4 flex-1">
    <div class="kpi flex-1">
      <p class="text-slate-500 text-xs font-semibold uppercase">ผู้ใช้ทั้งหมด</p>
      <p class="text-2xl font-bold text-slate-800 mt-1"><?= $total ?></p>
    </div>
    <div class="kpi flex-1">
      <p class="text-slate-500 text-xs font-semibold uppercase">แสดงผล</p>
      <p class="text-2xl font-bold text-blue-600 mt-1"><?= count($users) ?></p>
    </div>
  </div>
  <form method="GET" action="<?= base_url('admin/users') ?>" class="flex gap-2 flex-1">
    <input name="search" value="<?= htmlspecialchars($search) ?>"
           class="inp flex-1" placeholder="ค้นหา ชื่อ / อีเมล / รหัสนิสิต"/>
    <button type="submit" class="btn btn-blue">🔍</button>
    <?php if ($search): ?>
      <a href="<?= base_url('admin/users') ?>" class="btn btn-gray">รีเซ็ต</a>
    <?php endif; ?>
  </form>
  <button class="btn btn-blue whitespace-nowrap" @click="openAdd">+ เพิ่มผู้ใช้</button>
</div>

<!-- User Table -->
<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr>
        <th>#</th>
        <th>ชื่อ-สกุล</th>
        <th>อีเมล</th>
        <th>รหัสนิสิต/พนักงาน</th>
        <th>สิทธิ์</th>
        <th>สถานะ</th>
        <th></th>
      </tr></thead>
      <tbody>
        <?php foreach ($users as $i => $u): ?>
        <tr class="<?= !$u->is_active ? 'opacity-50' : '' ?>">
          <td class="text-slate-400 text-xs"><?= $i+1 ?></td>
          <td class="font-medium text-slate-800"><?= htmlspecialchars($u->name) ?></td>
          <td class="text-slate-500 text-sm"><?= htmlspecialchars($u->email) ?></td>
          <td class="font-mono text-xs text-slate-500"><?= $u->student_id ?: '-' ?></td>
          <td>
            <span class="badge <?= $role_colors[$u->role] ?? 'bg-slate-100 text-slate-600' ?>">
              <?= $role_labels[$u->role] ?? $u->role ?>
            </span>
          </td>
          <td>
            <?php if ($u->is_active): ?>
              <span class="badge bg-emerald-50 text-emerald-700">ใช้งาน</span>
            <?php else: ?>
              <span class="badge bg-slate-100 text-slate-500">ปิดใช้งาน</span>
            <?php endif; ?>
          </td>
          <td class="flex gap-1">
            <button class="btn btn-gray btn-sm"
              @click="openEdit(<?= htmlspecialchars(json_encode([
                'id'         => (int)$u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'student_id' => $u->student_id ?? '',
                'role'       => $u->role,
                'is_active'  => (bool)$u->is_active,
              ]), ENT_QUOTES) ?>)">แก้ไข</button>
            <?php if ($is_super): ?>
              <button class="btn btn-sm <?= $u->is_active ? 'btn-gray' : 'btn-blue' ?>"
                @click="toggleUser(<?= (int)$u->id ?>, '<?= htmlspecialchars($u->name) ?>', <?= $u->is_active ? 'true' : 'false' ?>)">
                <?= $u->is_active ? 'ปิด' : 'เปิด' ?>
              </button>
              <button class="btn btn-sm text-red-500 border border-red-200 hover:bg-red-50"
                @click="deleteUser(<?= (int)$u->id ?>, '<?= htmlspecialchars($u->name) ?>')">ลบ</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (empty($users)): ?>
    <p class="text-center text-slate-400 py-8">ไม่พบผู้ใช้</p>
  <?php endif; ?>
</div>

<!-- Add User Modal -->
<div v-if="addModal" class="modal-bg" @click.self="addModal=false">
  <div class="modal-box" style="max-width:480px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">เพิ่มผู้ใช้งาน</h2>
        <button @click="addModal=false" class="btn-icon">✕</button>
      </div>
    </div>
    <div class="modal-body space-y-4">
      <div class="grid grid-cols-2 gap-3">
        <div class="col-span-2">
          <label class="lbl">ชื่อ-สกุล <span class="text-red-500">*</span></label>
          <input v-model="addForm.name" class="inp" placeholder="เช่น นายสมชาย ใจดี" required/>
        </div>
        <div class="col-span-2 sm:col-span-1">
          <label class="lbl">อีเมล <span class="text-red-500">*</span></label>
          <input v-model="addForm.email" type="email" class="inp" placeholder="example@ku.th" required/>
        </div>
        <div>
          <label class="lbl">รหัสนิสิต/พนักงาน</label>
          <input v-model="addForm.student_id" class="inp" placeholder="เช่น 6821652xxx"/>
        </div>
        <div>
          <label class="lbl">สิทธิ์ <span class="text-red-500">*</span></label>
          <select v-model="addForm.role" class="inp">
            <option value="student">นิสิต</option>
            <option value="activity_staff">เจ้าหน้าที่กิจกรรม</option>
            <option value="academic_staff">เจ้าหน้าที่วิชาการ</option>
            <option value="treasurer">เหรัญญิก</option>
            <option value="head_it">หัวหน้าสาขา</option>
            <option value="advisor">อาจารย์ที่ปรึกษา</option>
            <option value="auditor">ผู้ตรวจสอบ</option>
            <option value="super_admin">ผู้ดูแลระบบ</option>
          </select>
        </div>
        <div>
          <label class="lbl">รหัสผ่าน <span class="text-red-500">*</span></label>
          <input v-model="addForm.password" type="password" class="inp" placeholder="อย่างน้อย 6 ตัวอักษร" required/>
        </div>
      </div>
      <p v-if="addError" class="text-red-500 text-sm">{{ addError }}</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="addModal=false">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="submitAdd" :disabled="saving">
        <span v-if="saving">⏳</span> บันทึก
      </button>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div v-if="editModal" class="modal-bg" @click.self="editModal=false">
  <div class="modal-box" style="max-width:480px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">แก้ไขผู้ใช้งาน</h2>
        <button @click="editModal=false" class="btn-icon">✕</button>
      </div>
    </div>
    <div class="modal-body space-y-4">
      <div class="grid grid-cols-2 gap-3">
        <div class="col-span-2">
          <label class="lbl">ชื่อ-สกุล <span class="text-red-500">*</span></label>
          <input v-model="editForm.name" class="inp" required/>
        </div>
        <div class="col-span-2 sm:col-span-1">
          <label class="lbl">อีเมล <span class="text-red-500">*</span></label>
          <input v-model="editForm.email" type="email" class="inp" required/>
        </div>
        <div>
          <label class="lbl">รหัสนิสิต/พนักงาน</label>
          <input v-model="editForm.student_id" class="inp"/>
        </div>
        <div>
          <label class="lbl">สิทธิ์ <span class="text-red-500">*</span></label>
          <select v-model="editForm.role" class="inp">
            <option value="student">นิสิต</option>
            <option value="activity_staff">เจ้าหน้าที่กิจกรรม</option>
            <option value="academic_staff">เจ้าหน้าที่วิชาการ</option>
            <option value="treasurer">เหรัญญิก</option>
            <option value="head_it">หัวหน้าสาขา</option>
            <option value="advisor">อาจารย์ที่ปรึกษา</option>
            <option value="auditor">ผู้ตรวจสอบ</option>
            <option value="super_admin">ผู้ดูแลระบบ</option>
          </select>
        </div>
        <div class="col-span-2">
          <label class="lbl">รหัสผ่านใหม่ <span class="text-slate-400 text-xs">(เว้นว่างถ้าไม่ต้องการเปลี่ยน)</span></label>
          <input v-model="editForm.password" type="password" class="inp" placeholder="เว้นว่างถ้าไม่ต้องการเปลี่ยน"/>
        </div>
      </div>
      <p v-if="editError" class="text-red-500 text-sm">{{ editError }}</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="editModal=false">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="submitEdit" :disabled="saving">
        <span v-if="saving">⏳</span> บันทึก
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
    const addModal  = ref(false)
    const editModal = ref(false)
    const saving    = ref(false)
    const addError  = ref('')
    const editError = ref('')

    const addForm  = reactive({ name:'', email:'', student_id:'', role:'student', password:'' })
    const editForm = reactive({ id:0, name:'', email:'', student_id:'', role:'student', password:'' })

    function openAdd() {
      Object.assign(addForm, { name:'', email:'', student_id:'', role:'student', password:'' })
      addError.value = ''
      addModal.value = true
    }

    function openEdit(u) {
      Object.assign(editForm, { ...u, password:'' })
      editError.value = ''
      editModal.value = true
    }

    function toFD(obj) {
      const fd = new FormData()
      Object.entries(obj).forEach(([k,v]) => fd.append(k, v ?? ''))
      return fd
    }

    async function submitAdd() {
      addError.value = ''
      if (!addForm.name || !addForm.email || !addForm.password) {
        addError.value = 'กรุณากรอกชื่อ อีเมล และรหัสผ่าน'; return
      }
      saving.value = true
      try {
        const r = await axios.post('<?= base_url('admin/add_user') ?>', toFD(addForm))
        showToast('เพิ่มผู้ใช้เรียบร้อย')
        addModal.value = false
        setTimeout(() => location.reload(), 700)
      } catch(e) {
        addError.value = e.response?.data?.error || 'เกิดข้อผิดพลาด'
      }
      saving.value = false
    }

    async function submitEdit() {
      editError.value = ''
      if (!editForm.name || !editForm.email) {
        editError.value = 'กรุณากรอกชื่อและอีเมล'; return
      }
      saving.value = true
      try {
        await axios.post('<?= base_url('admin/edit_user') ?>', toFD(editForm))
        showToast('แก้ไขข้อมูลเรียบร้อย')
        editModal.value = false
        setTimeout(() => location.reload(), 700)
      } catch(e) {
        editError.value = e.response?.data?.error || 'เกิดข้อผิดพลาด'
      }
      saving.value = false
    }

    async function toggleUser(id, name, isActive) {
      const action = isActive ? 'ปิดการใช้งาน' : 'เปิดการใช้งาน'
      if (!confirm(`${action} "${name}"?`)) return
      try {
        const fd = new FormData(); fd.append('id', id)
        await axios.post('<?= base_url('admin/toggle_user') ?>', fd)
        showToast(`${action}แล้ว`)
        setTimeout(() => location.reload(), 700)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
    }

    async function deleteUser(id, name) {
      if (!confirm(`ลบผู้ใช้ "${name}" ออกจากระบบ?\nการกระทำนี้ไม่สามารถเรียกคืนได้`)) return
      try {
        const fd = new FormData(); fd.append('id', id)
        await axios.post('<?= base_url('admin/delete_user') ?>', fd)
        showToast('ลบผู้ใช้แล้ว')
        setTimeout(() => location.reload(), 700)
      } catch(e) {
        showToast(e.response?.data?.error || 'เกิดข้อผิดพลาด', false)
      }
    }

    return { addModal, editModal, saving, addError, editError, addForm, editForm,
             openAdd, openEdit, submitAdd, submitEdit, toggleUser, deleteUser }
  }
}).mount('#app')
})
</script>
