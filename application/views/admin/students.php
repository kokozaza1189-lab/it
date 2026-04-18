<?php
$role    = $current_user['role'];
$is_super = $role === 'super_admin';
?>
<div id="app">

<!-- Top bar -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div>
    <p class="text-slate-500 text-sm">นิสิตทั้งหมด <span class="font-bold text-slate-800"><?= $total ?></span> คน</p>
  </div>
  <div class="flex gap-2 flex-wrap">
    <?php if ($is_super): ?>
    <button class="btn btn-gray btn-sm" @click="importModal=true">📂 นำเข้า CSV</button>
    <button class="btn btn-gray btn-sm text-red-500" @click="clearModal=true">🗑 ล้างข้อมูลทั้งหมด</button>
    <?php endif; ?>
    <button class="btn btn-blue" @click="openAdd">+ เพิ่มนิสิต</button>
  </div>
</div>

<!-- Search -->
<form method="GET" action="<?= base_url('admin/students') ?>" class="card mb-5 flex gap-3 items-end">
  <div class="flex-1">
    <label class="lbl">ค้นหา</label>
    <input name="search" value="<?= htmlspecialchars($search) ?>" class="inp" placeholder="ชื่อหรือรหัสนิสิต"/>
  </div>
  <button type="submit" class="btn btn-blue">🔍</button>
  <a href="<?= base_url('admin/students') ?>" class="btn btn-gray">รีเซ็ต</a>
</form>

<!-- Table -->
<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr>
        <th>#</th><th>รหัสนิสิต</th><th>ชื่อ-สกุล</th><th>อีเมล</th><th></th>
      </tr></thead>
      <tbody>
        <?php foreach ($students as $i => $s): ?>
        <tr>
          <td class="text-slate-400 text-xs"><?= $i+1 ?></td>
          <td class="font-mono text-sm font-medium text-slate-700"><?= $s->student_id ?></td>
          <td class="font-medium"><?= htmlspecialchars($s->name) ?></td>
          <td class="text-slate-500 text-sm"><?= htmlspecialchars($s->email ?: '-') ?></td>
          <td class="text-right">
            <div class="flex gap-2 justify-end">
              <button class="btn btn-gray btn-xs"
                      @click="openEdit('<?= $s->student_id ?>','<?= addslashes(htmlspecialchars($s->name)) ?>','<?= addslashes($s->email ?? '') ?>')">
                ✏️ แก้ไข
              </button>
              <button class="btn btn-xs text-red-500 bg-red-50 border border-red-100 rounded-lg px-3 hover:bg-red-100"
                      @click="confirmDelete('<?= $s->student_id ?>','<?= addslashes(htmlspecialchars($s->name)) ?>')">
                🗑
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($students)): ?>
        <tr><td colspan="5" class="text-center text-slate-400 py-10">ไม่พบนิสิต</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add modal -->
<div v-if="addModal" class="modal-bg" @click.self="addModal=false">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">เพิ่มนิสิต</h2>
        <button @click="addModal=false" class="btn-icon">✕</button>
      </div>
    </div>
    <div class="modal-body space-y-4">
      <div>
        <label class="lbl">รหัสนิสิต <span class="text-red-500">*</span></label>
        <input v-model="form.student_id" class="inp" placeholder="เช่น 6600000001" maxlength="20"/>
      </div>
      <div>
        <label class="lbl">ชื่อ-สกุล <span class="text-red-500">*</span></label>
        <input v-model="form.name" class="inp" placeholder="นายสมชาย ใจดี"/>
      </div>
      <div>
        <label class="lbl">อีเมล</label>
        <input v-model="form.email" class="inp" type="email" placeholder="(ไม่บังคับ)"/>
      </div>
      <p v-if="formError" class="text-red-500 text-sm">{{ formError }}</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="addModal=false">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="submitAdd" :disabled="saving">
        <span v-if="saving" class="spin">⏳</span> เพิ่ม
      </button>
    </div>
  </div>
</div>

<!-- Edit modal -->
<div v-if="editModal" class="modal-bg" @click.self="editModal=false">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">แก้ไขข้อมูลนิสิต</h2>
        <button @click="editModal=false" class="btn-icon">✕</button>
      </div>
      <p class="text-slate-500 text-sm font-mono mt-1">{{ editForm.student_id }}</p>
    </div>
    <div class="modal-body space-y-4">
      <div>
        <label class="lbl">ชื่อ-สกุล <span class="text-red-500">*</span></label>
        <input v-model="editForm.name" class="inp"/>
      </div>
      <div>
        <label class="lbl">อีเมล</label>
        <input v-model="editForm.email" class="inp" type="email"/>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="editModal=false">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="submitEdit" :disabled="saving">
        <span v-if="saving" class="spin">⏳</span> บันทึก
      </button>
    </div>
  </div>
</div>

<!-- CSV / Excel import modal -->
<div v-if="importModal" class="modal-bg" @click.self="importModal=false">
  <div class="modal-box" style="max-width:460px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">นำเข้านิสิตจาก CSV / Excel</h2>
        <button @click="importModal=false" class="btn-icon">✕</button>
      </div>
    </div>
    <div class="modal-body space-y-4">
      <div class="p-4 rounded-xl text-sm" style="background:#f8fafc;border:1px solid #e2e8f0">
        <p class="font-semibold text-slate-700 mb-2">รูปแบบคอลัมน์ (CSV หรือ Excel)</p>
        <code class="text-xs text-blue-700">รหัสนิสิต | ชื่อ-สกุล | อีเมล (ไม่บังคับ)</code>
        <p class="text-slate-400 text-xs mt-2">* แถวแรกต้องเป็น header | ข้ามรายการที่รหัสซ้ำ</p>
      </div>
      <div>
        <label class="lbl">เลือกไฟล์ CSV หรือ Excel (.xlsx, .xls)</label>
        <input type="file" ref="csvInput" accept=".csv,.xlsx,.xls" class="inp" @change="onCsvChange"/>
        <p v-if="csvName" class="text-slate-500 text-xs mt-1">{{ csvName }}</p>
        <p v-if="importRows.length > 0" class="text-blue-600 text-xs mt-1">พบข้อมูล {{ importRows.length }} แถว พร้อมนำเข้า</p>
      </div>
      <div v-if="importResult" class="p-3 rounded-xl" :style="importResult.added>0?'background:#f0fdf4;border:1px solid #bbf7d0':'background:#fff7ed;border:1px solid #fed7aa'">
        <p class="font-semibold text-sm">✅ เพิ่มแล้ว {{ importResult.added }} คน
          <span v-if="importResult.skipped" class="text-slate-500"> | ข้าม {{ importResult.skipped }} คน (ซ้ำ/ข้อมูลไม่ครบ)</span>
        </p>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="importModal=false">ปิด</button>
      <button class="btn btn-blue flex-1" @click="submitImport" :disabled="saving||(!csvFile&&importRows.length===0)">
        <span v-if="saving" class="spin">⏳</span> นำเข้า
      </button>
    </div>
  </div>
</div>

<!-- Clear data modal -->
<?php if ($is_super): ?>
<div v-if="clearModal" class="modal-bg" @click.self="clearModal=false">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-header">
      <h2 class="font-bold text-red-600">⚠️ ล้างข้อมูลนิสิต</h2>
    </div>
    <div class="modal-body">
      <p class="text-slate-700 mb-3">การดำเนินการนี้จะ<strong class="text-red-600">ลบนิสิตทั้งหมด</strong>และประวัติการชำระเงินทั้งหมดอย่างถาวร</p>
      <p class="text-slate-500 text-sm">พิมพ์ <strong>DELETE</strong> เพื่อยืนยัน</p>
      <input v-model="clearConfirm" class="inp mt-2" placeholder="DELETE"/>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="clearModal=false">ยกเลิก</button>
      <button class="btn btn-red flex-1" @click="doClearStudents" :disabled="clearConfirm!=='DELETE'||saving">
        ลบทั้งหมด
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Delete confirm modal -->
<div v-if="deleteModal" class="modal-bg" @click.self="deleteModal=false">
  <div class="modal-box" style="max-width:380px">
    <div class="modal-header">
      <h2 class="font-bold text-slate-800">ยืนยันการลบ</h2>
    </div>
    <div class="modal-body">
      <p class="text-slate-700">ลบนิสิต <strong>{{ deleteTarget.name }}</strong> ({{ deleteTarget.id }})?</p>
      <p class="text-red-500 text-sm mt-2">ประวัติการชำระเงินของนิสิตคนนี้จะถูกลบด้วย</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="deleteModal=false">ยกเลิก</button>
      <button class="btn btn-red flex-1" @click="submitDelete" :disabled="saving">ลบ</button>
    </div>
  </div>
</div>

</div>

<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const { createApp, ref, reactive } = Vue
createApp({
  setup() {
    const saving       = ref(false)
    const formError    = ref('')
    const addModal     = ref(false)
    const editModal    = ref(false)
    const importModal  = ref(false)
    const clearModal   = ref(false)
    const deleteModal  = ref(false)
    const clearConfirm = ref('')
    const importResult = ref(null)
    const csvFile      = ref(null)
    const csvName      = ref('')
    const importRows   = ref([])
    const form         = reactive({ student_id:'', name:'', email:'' })
    const editForm     = reactive({ student_id:'', name:'', email:'' })
    const deleteTarget = reactive({ id:'', name:'' })

    function openAdd()  { Object.assign(form,{student_id:'',name:'',email:''}); formError.value=''; addModal.value=true }
    function openEdit(id,name,email) { Object.assign(editForm,{student_id:id,name,email}); editModal.value=true }
    function confirmDelete(id,name)  { Object.assign(deleteTarget,{id,name}); deleteModal.value=true }

    async function submitAdd() {
      formError.value = ''
      if (!form.student_id || !form.name) { formError.value='กรุณากรอกข้อมูลให้ครบ'; return }
      saving.value = true
      try {
        const fd = new FormData()
        fd.append('student_id',form.student_id); fd.append('name',form.name); fd.append('email',form.email)
        const res = await axios.post('<?= base_url('admin/add_student') ?>', fd)
        showToast('เพิ่มนิสิตแล้ว')
        addModal.value = false
        setTimeout(() => location.reload(), 800)
      } catch(e) {
        formError.value = e.response?.data?.error || 'เกิดข้อผิดพลาด'
      }
      saving.value = false
    }

    async function submitEdit() {
      saving.value = true
      try {
        const fd = new FormData()
        fd.append('student_id',editForm.student_id); fd.append('name',editForm.name); fd.append('email',editForm.email)
        await axios.post('<?= base_url('admin/edit_student') ?>', fd)
        showToast('บันทึกแล้ว')
        editModal.value = false
        setTimeout(() => location.reload(), 800)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      saving.value = false
    }

    async function submitDelete() {
      saving.value = true
      try {
        const fd = new FormData(); fd.append('student_id', deleteTarget.id)
        await axios.post('<?= base_url('admin/delete_student') ?>', fd)
        showToast('ลบนิสิตแล้ว')
        deleteModal.value = false
        setTimeout(() => location.reload(), 800)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      saving.value = false
    }

    function onCsvChange(e) {
      const f = e.target.files[0]
      if (!f) return
      csvFile.value = f; csvName.value = f.name; importResult.value = null; importRows.value = []
      const isExcel = f.name.match(/\.(xlsx|xls)$/i)
      if (isExcel) {
        // Parse Excel client-side with SheetJS
        const reader = new FileReader()
        reader.onload = ev => {
          const wb  = XLSX.read(ev.target.result, { type:'array' })
          const ws  = wb.Sheets[wb.SheetNames[0]]
          const arr = XLSX.utils.sheet_to_json(ws, { header:1, defval:'' })
          // skip header row, map to [student_id, name, email]
          importRows.value = arr.slice(1).filter(r => r[0]).map(r => [
            String(r[0]).trim(), String(r[1] || '').trim(), String(r[2] || '').trim()
          ])
        }
        reader.readAsArrayBuffer(f)
      }
    }

    async function submitImport() {
      saving.value = true
      try {
        let res
        if (importRows.value.length > 0) {
          // Excel: send parsed rows as JSON
          res = await axios.post('<?= base_url('admin/import_students_json') ?>', { rows: importRows.value })
        } else if (csvFile.value) {
          // CSV: send file directly
          const fd = new FormData(); fd.append('csv', csvFile.value)
          res = await axios.post('<?= base_url('admin/import_students') ?>', fd)
        } else { saving.value = false; return }
        importResult.value = res.data
        showToast(`นำเข้าสำเร็จ เพิ่ม ${res.data.added} คน`)
        setTimeout(() => location.reload(), 2000)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      saving.value = false
    }

    async function doClearStudents() {
      saving.value = true
      try {
        const fd = new FormData()
        await axios.post('<?= base_url('admin/clear_students') ?>', fd)
        showToast('ลบข้อมูลนิสิตทั้งหมดแล้ว')
        clearModal.value = false
        setTimeout(() => location.reload(), 1000)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      saving.value = false
    }

    return { saving, formError, addModal, editModal, importModal, clearModal, deleteModal,
             clearConfirm, importResult, csvFile, csvName, importRows, form, editForm, deleteTarget,
             openAdd, openEdit, confirmDelete, submitAdd, submitEdit, submitDelete,
             onCsvChange, submitImport, doClearStudents }
  }
}).mount('#app')
})
</script>
