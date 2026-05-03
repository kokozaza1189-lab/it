<?php
$th_months_full = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
$role     = $current_user['role'];
$is_super = $role === 'super_admin';
?>
<div id="app">

<!-- Top bar -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div>
    <p class="text-slate-500 text-sm">ปีการศึกษา <span class="font-bold text-slate-800"><?= $year ?></span>
       &nbsp;|&nbsp; นิสิตทั้งหมด <span class="font-bold text-slate-800"><?= $total_students ?></span> คน</p>
  </div>
  <div class="flex gap-2 flex-wrap">
    <form method="GET" action="<?= base_url('admin/payments') ?>" class="flex gap-2 items-end">
      <input name="year" type="number" value="<?= $year ?>" class="inp" style="width:100px"/>
      <button type="submit" class="btn btn-blue btn-sm">ค้นหา</button>
    </form>
    <button class="btn btn-sm" style="background:#fef3c7;border:1px solid #fcd34d;color:#92400e;border-radius:.5rem;padding:.3rem .8rem"
            @click="doSeedJanuary" :disabled="loading">
      📥 นำเข้าข้อมูล ม.ค.2569
    </button>
    <?php if ($is_super): ?>
    <button class="btn btn-gray btn-sm text-red-500" data-modal-open="clearTxnModal">🗑 ล้างธุรกรรม</button>
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

    <?php
      $total = max($s['total'], 1);
      $pct = $total > 0 ? round($s['paid'] / $total * 100) : 0;
    ?>
    <div class="w-full bg-slate-100 rounded-full h-2 mb-3">
      <div class="bg-emerald-500 h-2 rounded-full" style="width:<?= $pct ?>%"></div>
    </div>
    <p class="text-slate-400 text-xs mb-3"><?= $s['total'] ?> รายการ | <?= $pct ?>% ชำระแล้ว</p>

    <div class="flex flex-wrap gap-2">
      <button class="btn btn-blue btn-xs" style="flex:1;min-width:80px"
              @click="openMonthDetail(<?= $m ?>, <?= $year ?>, '<?= $th_months_full[$m] ?>')"
              :disabled="loading">
        📋 ดูรายชื่อ
      </button>
      <button class="btn btn-gray btn-xs" style="flex:1;min-width:80px"
              @click="doGenerate(<?= $m ?>, <?= $year ?>)"
              :disabled="loading">
        ➕ สร้างรายการ
      </button>
      <button class="btn btn-xs" style="flex:1;min-width:80px;background:#fff7ed;border:1px solid #fed7aa;color:#92400e;border-radius:.5rem;padding:.25rem .5rem"
              @click="doOverdue(<?= $m ?>, <?= $year ?>)"
              :disabled="loading">
        ⏰ ค้างชำระ
      </button>
      <button class="btn btn-xs" style="flex:1;min-width:80px;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:.5rem;padding:.25rem .5rem"
              @click="doPenalty(<?= $m ?>, <?= $year ?>)"
              :disabled="loading">
        💰 ค่าปรับ
      </button>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ═══ Month Detail Modal ═══ -->
<div v-show="detailModal" id="detailModal" class="modal-bg" @click.self="detailModal=false" style="display:none">
  <div class="modal-box" style="max-width:920px;width:96vw">
    <div class="modal-header">
      <div class="flex items-start justify-between flex-wrap gap-2">
        <div>
          <h2 class="font-bold text-slate-800 text-base">
            รายชื่อนิสิต — เดือน <span v-text="detailLabel"></span>
            <span class="text-slate-400 font-normal text-sm" v-text="' ('+detailYear+')'"></span>
          </h2>
          <div class="flex gap-3 mt-1 flex-wrap">
            <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background:#d1fae5;color:#065f46">
              ✅ ชำระแล้ว <span v-text="detailStats.paid"></span> คน
            </span>
            <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background:#fee2e2;color:#991b1b">
              ❌ ค้างชำระ <span v-text="detailStats.overdue"></span> คน
            </span>
            <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background:#fef3c7;color:#92400e">
              ⏳ รอ <span v-text="detailStats.pending"></span> คน
            </span>
            <span v-show="detailStats.penalty_due > 0" style="display:none"
                  class="text-xs font-medium px-2 py-0.5 rounded-full" style="background:#fef9c3;color:#a16207">
              ⚠️ ค้างค่าปรับ <span v-text="detailStats.penalty_due"></span> คน
            </span>
          </div>
        </div>
        <div class="flex gap-2 items-center">
          <input v-model="detailSearch" class="inp" style="width:190px" placeholder="🔍 ค้นหาชื่อ / รหัส"/>
          <button @click="detailModal=false" class="btn-icon" data-modal-close="detailModal">✕</button>
        </div>
      </div>
    </div>
    <div class="modal-body" style="padding:0;max-height:58vh;overflow-y:auto">
      <div v-if="detailLoading" class="text-center py-10 text-slate-400 text-sm">กำลังโหลด...</div>
      <table v-else class="tbl w-full" style="font-size:13px">
        <thead style="position:sticky;top:0;z-index:10;background:#f8fafc">
          <tr>
            <th style="width:32px">#</th>
            <th>รหัสนิสิต</th>
            <th>ชื่อ-สกุล</th>
            <th>สถานะ</th>
            <th style="width:90px">ค่าธรรมเนียม</th>
            <th style="width:90px">ค่าปรับ (฿)</th>
            <th style="width:80px">รวม (฿)</th>
            <th style="width:60px">สลิป</th>
            <th style="width:64px"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(r, i) in filteredRecords" :key="r.id"
              :style="r.status==='overdue' ? 'background:#fff5f5' : (r.penalty>0 && r.status==='paid') ? 'background:#fffbeb' : ''">
            <td class="text-slate-400 text-xs" v-text="i+1"></td>
            <td class="font-mono text-xs text-slate-500" v-text="r.student_id"></td>
            <td>
              <p class="font-medium text-slate-800" v-text="r.name"></p>
              <p v-show="r.status==='overdue'" style="display:none" class="text-xs text-red-500">❌ ค้างชำระ</p>
              <p v-show="r.status==='paid' && r.penalty>0" style="display:none" class="text-xs" style="color:#b45309">⚠️ ค้างค่าปรับ</p>
            </td>
            <td>
              <select v-model="r.status" style="border:1.5px solid #e2e8f0;border-radius:6px;padding:3px 6px;font-size:12px;font-family:inherit;background:white;cursor:pointer"
                      :style="r.status==='overdue'?'border-color:#fca5a5;background:#fff5f5':r.status==='paid'?'border-color:#86efac;background:#f0fdf4':''">
                <option value="paid">ชำระแล้ว</option>
                <option value="overdue">ค้างชำระ</option>
                <option value="pending">รอดำเนินการ</option>
                <option value="none">ไม่เก็บ</option>
              </select>
            </td>
            <td>
              <input v-model.number="r.amount" type="number" step="0.01" min="0"
                     style="width:80px;border:1.5px solid #e2e8f0;border-radius:6px;padding:3px 6px;font-size:12px;font-family:inherit"/>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:4px">
                <input v-model.number="r.penalty" type="number" step="0.01" min="0"
                       style="width:70px;border:1.5px solid;border-radius:6px;padding:3px 6px;font-size:12px;font-family:inherit"
                       :style="r.penalty>0 ? 'border-color:#fcd34d;background:#fffbeb;color:#b45309;font-weight:600' : 'border-color:#e2e8f0'"/>
                <span v-show="r.penalty>0" style="display:none;font-size:10px;color:#d97706">⚠️</span>
              </div>
            </td>
            <td class="font-bold text-sm" :style="r.status==='overdue'?'color:#dc2626':''" style="white-space:nowrap">
              ฿<span v-text="(r.amount + r.penalty).toFixed(2)"></span>
            </td>
            <td>
              <a v-if="r.slip_file" :href="'<?= base_url('assets/uploads/slips/') ?>'+r.slip_file"
                 target="_blank" style="font-size:11px;color:#3b82f6;white-space:nowrap">📎 ดู</a>
              <span v-else style="color:#cbd5e1;font-size:11px">—</span>
            </td>
            <td>
              <button @click="saveRecord(r)" :disabled="saving"
                      style="background:#3b82f6;color:white;border:none;border-radius:6px;padding:3px 10px;font-size:12px;cursor:pointer;font-family:inherit"
                      :style="saving?'opacity:.6;cursor:not-allowed':''">บันทึก</button>
            </td>
          </tr>
          <tr v-if="filteredRecords.length===0">
            <td colspan="9" class="text-center text-slate-400 py-8">ไม่พบข้อมูล</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer" style="justify-content:space-between">
      <span class="text-xs text-slate-400">แสดง <span v-text="filteredRecords.length"></span> รายการ</span>
      <button class="btn btn-gray" @click="detailModal=false" data-modal-close="detailModal">ปิด</button>
    </div>
  </div>
</div>

<!-- Clear transactions modal -->
<?php if ($is_super): ?>
<div id="clearTxnModal" class="modal-bg" style="display:none">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-header">
      <h2 class="font-bold text-red-600">⚠️ ล้างข้อมูลธุรกรรม</h2>
    </div>
    <div class="modal-body">
      <p class="text-slate-700 mb-3">จะลบ<strong class="text-red-600">ข้อมูลการชำระเงิน การเบิกเงิน และเงินกลางทั้งหมด</strong>อย่างถาวร</p>
      <p class="text-slate-500 text-sm">พิมพ์ <strong>DELETE</strong> เพื่อยืนยัน</p>
      <input id="clearConfirmInput" class="inp mt-2" placeholder="DELETE" oninput="document.getElementById('clearTxnBtn').disabled=this.value!=='DELETE'"/>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" data-modal-close="clearTxnModal">ยกเลิก</button>
      <button id="clearTxnBtn" class="btn btn-red flex-1" disabled onclick="doClearTxnVanilla()">ลบทั้งหมด</button>
    </div>
  </div>
</div>
<script>
function doClearTxnVanilla() {
  fetch('<?= base_url('admin/clear_transactions') ?>', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:new FormData() })
    .then(r => r.json())
    .then(d => {
      document.getElementById('clearTxnModal').style.display='none';
      document.getElementById('clearConfirmInput').value='';
      document.getElementById('clearTxnBtn').disabled=true;
      alert(d.success ? '✅ ล้างข้อมูลแล้ว' : '❌ ' + (d.error||'เกิดข้อผิดพลาด'));
      if (d.success) setTimeout(() => location.reload(), 400);
    }).catch(() => alert('❌ เกิดข้อผิดพลาด'));
}
</script>
<?php endif; ?>

</div>

<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const YEAR = <?= (int)$year ?>;
const { createApp, ref, computed } = Vue
createApp({
  setup() {
    const loading       = ref(false)
    const saving        = ref(false)
    const detailModal   = ref(false)
    const detailLoading = ref(false)
    const detailLabel   = ref('')
    const detailYear    = ref(YEAR)
    const detailRecords = ref([])
    const detailSearch  = ref('')

    const filteredRecords = computed(() => {
      const q = detailSearch.value.trim().toLowerCase()
      if (!q) return detailRecords.value
      return detailRecords.value.filter(r =>
        r.name.toLowerCase().includes(q) || r.student_id.includes(q)
      )
    })

    const detailStats = computed(() => ({
      paid:        detailRecords.value.filter(r => r.status==='paid').length,
      overdue:     detailRecords.value.filter(r => r.status==='overdue').length,
      pending:     detailRecords.value.filter(r => r.status==='pending').length,
      penalty_due: detailRecords.value.filter(r => r.penalty > 0).length,
    }))

    async function openMonthDetail(month, year, label) {
      detailLabel.value = label; detailYear.value = year
      detailSearch.value = ''; detailRecords.value = []
      detailModal.value = true; detailLoading.value = true
      try {
        const res = await axios.get('<?= base_url('admin/get_month_detail') ?>', { params: { year, month } })
        detailRecords.value = res.data.records
      } catch(e) { showToast('โหลดข้อมูลล้มเหลว', false) }
      detailLoading.value = false
    }

    async function saveRecord(r) {
      saving.value = true
      try {
        const fd = new FormData()
        fd.append('id', r.id); fd.append('status', r.status)
        fd.append('amount', r.amount); fd.append('penalty', r.penalty)
        if (r.status === 'paid' && r.paid_date) fd.append('paid_date', r.paid_date)
        await axios.post('<?= base_url('admin/update_payment_record') ?>', fd)
        showToast('บันทึก ' + r.name + ' แล้ว ✓')
      } catch(e) { showToast('บันทึกล้มเหลว', false) }
      saving.value = false
    }

    async function doSeedJanuary() {
      if (!confirm('นำเข้าข้อมูลมกราคม 2569 (95 รายการ) จากเอกสาร PDF?\nจะอัปเดตรายการที่มีอยู่แล้วและสร้างรายการใหม่')) return
      loading.value = true
      try {
        const res = await axios.post('<?= base_url('admin/seed_january') ?>', new FormData())
        showToast(`นำเข้าสำเร็จ — อัปเดต ${res.data.updated} รายการ | สร้างใหม่ ${res.data.inserted} รายการ`)
        setTimeout(() => location.reload(), 1400)
      } catch(e) { showToast('นำเข้าล้มเหลว', false) }
      loading.value = false
    }

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

    return {
      loading, saving, detailModal, detailLoading, detailLabel, detailYear,
      detailRecords, detailSearch, filteredRecords, detailStats,
      openMonthDetail, saveRecord, doSeedJanuary, doGenerate, doOverdue, doPenalty
    }
  }
}).mount('#app')
})
</script>
