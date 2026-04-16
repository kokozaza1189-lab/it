<?php
$th_months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$status_labels = ['paid'=>'ชำระแล้ว','overdue'=>'ค้างชำระ','pending'=>'รอ','none'=>'ไม่เก็บ'];
$status_badge  = ['paid'=>'b-paid','overdue'=>'b-overdue','pending'=>'b-pending','none'=>'b-none'];
?>
<div id="app">

<!-- Stats row -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">นิสิตทั้งหมด</p><p class="text-2xl font-bold text-slate-800 mt-1"><?= $stats['total'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">ชำระแล้ว</p><p class="text-2xl font-bold text-emerald-600 mt-1"><?= $stats['paid'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">ค้างชำระ</p><p class="text-2xl font-bold text-red-500 mt-1"><?= $stats['overdue'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">รอดำเนินการ</p><p class="text-2xl font-bold text-amber-500 mt-1"><?= $stats['pending'] ?></p></div>
</div>

<!-- Search + filter bar -->
<div class="card mb-5">
  <form method="GET" action="<?= base_url('payment/all') ?>" class="flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
      <label class="lbl">ค้นหา</label>
      <input name="search" value="<?= htmlspecialchars($search) ?>" class="inp" placeholder="ชื่อหรือรหัสนิสิต"/>
    </div>
    <div>
      <label class="lbl">ปีการศึกษา</label>
      <select name="year" class="inp" style="min-width:120px">
        <option value="2568" <?= $year==2568?'selected':'' ?>>2568</option>
        <option value="2567" <?= $year==2567?'selected':'' ?>>2567</option>
      </select>
    </div>
    <button type="submit" class="btn btn-blue">🔍 ค้นหา</button>
    <a href="<?= base_url('payment/all') ?>" class="btn btn-gray">รีเซ็ต</a>
  </form>
</div>

<!-- Students table -->
<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr>
        <th>#</th>
        <th>ชื่อ-สกุล</th>
        <th>รหัส</th>
        <?php foreach ([1,2,3,4] as $m): ?>
          <th><?= $th_months[$m] ?></th>
        <?php endforeach; ?>
        <th>ค้างรวม</th>
        <th></th>
      </tr></thead>
      <tbody>
        <?php foreach ($students as $i => $s):
          $total = 0;
          foreach ([1,2,3,4] as $m) {
            $p = $s->payments[$m];
            if ($p->status === 'overdue') $total += $p->amount + $p->penalty;
          }
        ?>
        <tr>
          <td class="text-slate-400 text-xs"><?= $i+1 ?></td>
          <td class="font-medium text-slate-800"><?= htmlspecialchars($s->name) ?></td>
          <td class="font-mono text-xs text-slate-500"><?= $s->student_id ?></td>
          <?php foreach ([1,2,3,4] as $m):
            $p = $s->payments[$m];
            $cls = ['paid'=>'b-paid','overdue'=>'b-overdue','pending'=>'b-pending','none'=>'b-none'][$p->status] ?? 'b-none';
            $lbl = ['paid'=>'จ่าย','overdue'=>'ค้าง','pending'=>'รอ','none'=>'-'][$p->status] ?? '-';
          ?>
          <td>
            <button class="badge <?= $cls ?> cursor-pointer hover:opacity-80"
                    @click="openStatus(<?= json_encode(['id'=>$p->id??null,'month'=>$m,'student'=>$s->name,'status'=>$p->status,'penalty'=>$p->penalty]) ?>)">
              <?= $lbl ?>
              <?php if ($p->penalty > 0): ?>+<?= $p->penalty ?>฿<?php endif; ?>
            </button>
          </td>
          <?php endforeach; ?>
          <td class="font-bold <?= $total > 0 ? 'text-red-500' : 'text-slate-400' ?>">
            <?= $total > 0 ? '฿'.number_format($total,2) : '-' ?>
          </td>
          <td>
            <a href="#" class="btn btn-gray btn-xs" @click.prevent="openStudentDetail(<?= json_encode($s) ?>)">ดู</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="text-slate-400 text-xs p-4">แสดง <?= count($students) ?> รายการ</p>
</div>

<!-- Update status modal -->
<div v-if="statusModal" class="modal-bg" @click.self="statusModal=false">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">อัปเดตสถานะการชำระ</h2>
        <button @click="statusModal=false" class="btn-icon">✕</button>
      </div>
      <p class="text-slate-500 text-sm mt-1">{{ editData.student }} — เดือน {{ monthNames[editData.month] }}</p>
    </div>
    <div class="modal-body space-y-4">
      <div>
        <label class="lbl">สถานะ</label>
        <select v-model="editData.status" class="inp">
          <option value="paid">ชำระแล้ว</option>
          <option value="overdue">ค้างชำระ</option>
          <option value="pending">รอดำเนินการ</option>
          <option value="none">ไม่เก็บ</option>
        </select>
      </div>
      <div v-if="editData.status==='paid'">
        <label class="lbl">วันที่ชำระ</label>
        <input type="date" v-model="editData.paid_date" class="inp"/>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="statusModal=false">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="saveStatus" :disabled="saving">
        <span v-if="saving" class="spin">⏳</span> บันทึก
      </button>
    </div>
  </div>
</div>

</div>

<script>
const { createApp, ref, reactive } = Vue
const monthNames = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม']
createApp({
  setup() {
    const statusModal = ref(false)
    const saving      = ref(false)
    const editData    = reactive({ id:null, month:0, student:'', status:'', paid_date:'', penalty:0 })

    function openStatus(data) {
      Object.assign(editData, data, { paid_date: '' })
      statusModal.value = true
    }

    async function saveStatus() {
      if (!editData.id) return
      saving.value = true
      try {
        await axios.post('<?= base_url('api/payment_status') ?>', {
          id: editData.id, status: editData.status, paid_date: editData.paid_date
        })
        showToast('บันทึกสถานะแล้ว')
        statusModal.value = false
        setTimeout(() => location.reload(), 800)
      } catch(e) { showToast('เกิดข้อผิดพลาด', false) }
      saving.value = false
    }

    function openStudentDetail(s) {
      // Future: open detail modal
    }

    return { statusModal, saving, editData, monthNames, openStatus, saveStatus, openStudentDetail }
  }
}).mount('#app')
</script>
