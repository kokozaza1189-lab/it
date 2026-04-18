<?php
$th_months_full = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
$role     = $current_user['role'];
$is_super = $role === 'super_admin';
?>
<div id="app" v-cloak>

<!-- Top bar -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div>
    <p class="text-slate-500 text-sm">ปีการศึกษา <span class="font-bold text-slate-800"><?= $year ?></span>
       &nbsp;|&nbsp; นิสิตทั้งหมด <span class="font-bold text-slate-800"><?= $total_students ?></span> คน</p>
  </div>
  <div class="flex gap-2">
    <form method="GET" action="<?= base_url('admin/payments') ?>" class="flex gap-2 items-end">
      <input name="year" type="number" value="<?= $year ?>" class="inp" style="width:100px"/>
      <button type="submit" class="btn btn-blue btn-sm">ค้นหา</button>
    </form>
    <?php if ($is_super): ?>
    <button class="btn btn-gray btn-sm text-red-500" @click="clearTxnModal=true">🗑 ล้างธุรกรรม</button>
    <?php endif; ?>
  </div>
</div>

<!-- Month cards grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
  <?php foreach ($summaries as $m => $s): ?>
  <div class="card">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h3 class="font-bold text-slate-800"><?= $s['label'] ?></h3>
        <p class="text-slate-400 text-xs"><?= $th_months_full[$m] ?></p>
      </div>
      <div class="text-right">
        <p class="text-emerald-600 font-bold text-sm">฿<?= number_format($s['income'],2) ?></p>
        <p class="text-slate-400 text-xs">รายได้</p>
      </div>
    </div>

    <!-- Status bars -->
    <div class="grid grid-cols-3 gap-2 mb-3">
      <div class="text-center p-2 rounded-lg" style="background:#d1fae5">
        <p class="text-emerald-700 font-bold text-lg"><?= $s['paid'] ?></p>
        <p class="text-emerald-600 text-xs">ชำระแล้ว</p>
      </div>
      <div class="text-center p-2 rounded-lg" style="background:#fee2e2">
        <p class="text-red-600 font-bold text-lg"><?= $s['overdue'] ?></p>
        <p class="text-red-500 text-xs">ค้างชำระ</p>
      </div>
      <div class="text-center p-2 rounded-lg" style="background:#fef3c7">
        <p class="text-amber-700 font-bold text-lg"><?= $s['pending'] ?></p>
        <p class="text-amber-600 text-xs">รอดำเนินการ</p>
      </div>
    </div>

    <!-- Progress bar -->
    <?php
      $total = max($s['total'], 1);
      $pct = $total > 0 ? round($s['paid'] / $total * 100) : 0;
    ?>
    <div class="w-full bg-slate-100 rounded-full h-2 mb-3">
      <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width:<?= $pct ?>%"></div>
    </div>
    <p class="text-slate-400 text-xs mb-3"><?= $s['total'] ?> รายการ | <?= $pct ?>% ชำระแล้ว</p>

    <!-- Action buttons -->
    <div class="flex flex-wrap gap-2">
      <button class="btn btn-gray btn-xs flex-1"
              @click="doGenerate(<?= $m ?>, <?= $year ?>)"
              :disabled="loading">
        📋 สร้างรายการ
      </button>
      <button class="btn btn-xs flex-1"
              style="background:#fff7ed;border:1px solid #fed7aa;color:#92400e;border-radius:.5rem;padding:.25rem .75rem"
              @click="doOverdue(<?= $m ?>, <?= $year ?>)"
              :disabled="loading">
        ⏰ ตั้งค้างชำระ
      </button>
      <button class="btn btn-xs flex-1"
              style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:.5rem;padding:.25rem .75rem"
              @click="doPenalty(<?= $m ?>, <?= $year ?>)"
              :disabled="loading">
        💰 คำนวณค่าปรับ
      </button>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Clear transactions modal -->
<?php if ($is_super): ?>
<div v-if="clearTxnModal" class="modal-bg" @click.self="clearTxnModal=false">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-header">
      <h2 class="font-bold text-red-600">⚠️ ล้างข้อมูลธุรกรรม</h2>
    </div>
    <div class="modal-body">
      <p class="text-slate-700 mb-3">การดำเนินการนี้จะลบ<strong class="text-red-600">ข้อมูลการชำระเงิน การเบิกเงิน และเงินกลางทั้งหมด</strong>อย่างถาวร (ข้อมูลนิสิตและผู้ใช้จะยังคงอยู่)</p>
      <p class="text-slate-500 text-sm">พิมพ์ <strong>DELETE</strong> เพื่อยืนยัน</p>
      <input v-model="clearConfirm" class="inp mt-2" placeholder="DELETE"/>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="clearTxnModal=false">ยกเลิก</button>
      <button class="btn btn-red flex-1" @click="doClearTxn" :disabled="clearConfirm!=='DELETE'||loading">
        ลบทั้งหมด
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

</div>

<script>
const { createApp, ref } = Vue
createApp({
  setup() {
    const loading      = ref(false)
    const clearTxnModal = ref(false)
    const clearConfirm  = ref('')

    async function doGenerate(month, year) {
      if (!confirm(`สร้างรายการชำระเงินเดือน ${month}/${year} สำหรับนิสิตที่ยังไม่มีรายการ?`)) return
      loading.value = true
      try {
        const fd = new FormData()
        fd.append('month', month); fd.append('year', year)
        const res = await axios.post('<?= base_url('admin/generate_month') ?>', fd)
        showToast(`สร้างรายการเพิ่ม ${res.data.created} รายการ`)
        setTimeout(() => location.reload(), 1000)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      loading.value = false
    }

    async function doOverdue(month, year) {
      if (!confirm(`ตั้งสถานะค้างชำระทุก "รอดำเนินการ" ในเดือน ${month}/${year}?`)) return
      loading.value = true
      try {
        const fd = new FormData()
        fd.append('month', month); fd.append('year', year)
        const res = await axios.post('<?= base_url('admin/mark_overdue') ?>', fd)
        showToast(`อัปเดต ${res.data.updated} รายการเป็นค้างชำระ`)
        setTimeout(() => location.reload(), 1000)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      loading.value = false
    }

    async function doPenalty(month, year) {
      loading.value = true
      try {
        const fd = new FormData()
        fd.append('month', month); fd.append('year', year)
        const res = await axios.post('<?= base_url('admin/recalc_penalties') ?>', fd)
        showToast(`คำนวณค่าปรับ (${res.data.days_overdue} วันที่ค้าง)`)
        setTimeout(() => location.reload(), 1000)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      loading.value = false
    }

    async function doClearTxn() {
      loading.value = true
      try {
        const fd = new FormData()
        await axios.post('<?= base_url('admin/clear_transactions') ?>', fd)
        showToast('ลบข้อมูลธุรกรรมทั้งหมดแล้ว')
        clearTxnModal.value = false
        setTimeout(() => location.reload(), 1000)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      loading.value = false
    }

    return { loading, clearTxnModal, clearConfirm, doGenerate, doOverdue, doPenalty, doClearTxn }
  }
}).mount('#app')
</script>
