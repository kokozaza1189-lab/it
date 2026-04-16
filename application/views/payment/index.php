<?php
$month_names = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',
                5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',
                9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
$status_labels = ['paid'=>'ชำระแล้ว','overdue'=>'ค้างชำระ','pending'=>'รอดำเนินการ','none'=>'ไม่เก็บ'];
$status_badge  = ['paid'=>'b-paid','overdue'=>'b-overdue','pending'=>'b-pending','none'=>'b-none'];
$total_due = 0;
foreach ($payments as $p) {
  if ($p->status === 'overdue') $total_due += $p->amount + $p->penalty;
  elseif ($p->status === 'paid' && $p->penalty > 0) $total_due += $p->penalty;
}
?>
<div id="app">

<!-- Summary bar -->
<?php if ($total_due > 0): ?>
<div class="mb-5 p-4 rounded-2xl flex items-center justify-between gap-4" style="background:#fff7ed;border:1.5px solid #fed7aa">
  <div class="flex items-center gap-3">
    <span class="text-2xl">⚠️</span>
    <div>
      <p class="font-bold text-orange-700">มียอดค้างชำระ</p>
      <p class="text-orange-600 text-sm">กรุณาชำระภายในกำหนด เพื่อหลีกเลี่ยงค่าปรับ</p>
    </div>
  </div>
  <div class="text-right flex-shrink-0">
    <p class="text-orange-700 font-bold text-xl">฿<?= number_format($total_due, 2) ?></p>
    <p class="text-orange-500 text-xs">ยอดรวมที่ต้องชำระ</p>
  </div>
</div>
<?php endif; ?>

<!-- Month grid -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
  <?php foreach ($payments as $p):
    $cls_map = ['paid'=>'mc-paid','overdue'=>'mc-overdue','pending'=>'mc-pending','none'=>'mc-none'];
    $cls = $cls_map[$p->status] ?? 'mc-none';
  ?>
  <div class="<?= $cls ?> rounded-2xl p-4 text-center" style="border:2px solid transparent">
    <p class="font-bold text-lg"><?= $month_names[$p->month] ?? 'เดือน '.$p->month ?></p>
    <p class="text-sm mt-1 font-semibold"><?= $status_labels[$p->status] ?? $p->status ?></p>
    <?php if ($p->status !== 'none'): ?>
      <p class="font-bold mt-1">฿<?= number_format($p->amount, 2) ?></p>
    <?php endif; ?>
    <?php if ($p->penalty > 0): ?>
      <p class="text-red-500 text-xs mt-0.5">ค่าปรับ +฿<?= $p->penalty ?></p>
    <?php endif; ?>
    <?php if ($p->paid_date): ?>
      <p class="text-xs mt-1 opacity-70">ชำระ <?= $p->paid_date ?></p>
    <?php endif; ?>
    <?php if ($p->status === 'overdue' || $p->status === 'pending'): ?>
      <button class="mt-2 btn btn-blue btn-xs w-full"
              @click="openPayModal(<?= $p->id ?>, <?= $p->month ?>, <?= $p->amount + $p->penalty ?>)">
        ชำระเงิน
      </button>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<!-- Payment history table -->
<div class="card">
  <h2 class="font-bold text-slate-800 mb-4">ประวัติการชำระ</h2>
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr>
        <th>เดือน</th><th>จำนวน</th><th>ค่าปรับ</th><th>รวม</th><th>สถานะ</th><th>วันที่ชำระ</th>
      </tr></thead>
      <tbody>
        <?php foreach ($payments as $p): if ($p->status === 'none') continue; ?>
        <tr>
          <td class="font-medium"><?= $month_names[$p->month] ?> <?= $year ?></td>
          <td>฿<?= number_format($p->amount, 2) ?></td>
          <td><?= $p->penalty > 0 ? '฿'.$p->penalty : '-' ?></td>
          <td class="font-bold">฿<?= number_format($p->amount + $p->penalty, 2) ?></td>
          <td><span class="badge <?= $status_badge[$p->status] ?? '' ?>"><?= $status_labels[$p->status] ?></span></td>
          <td class="text-slate-400 text-xs"><?= $p->paid_date ?: '-' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Payment Modal -->
<div v-if="payModal" class="modal-bg" @click.self="payModal=false">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-header">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-slate-800">ชำระค่าห้องเดือน {{ monthName }}</h2>
        <button @click="payModal=false" class="btn-icon">✕</button>
      </div>
    </div>
    <div class="modal-body space-y-4">
      <div class="p-4 rounded-xl text-center" style="background:#eff6ff">
        <p class="text-blue-700 text-sm">ยอดที่ต้องชำระ</p>
        <p class="text-blue-800 text-3xl font-bold">฿{{ totalDue.toLocaleString() }}</p>
      </div>
      <div>
        <label class="lbl">แนบสลิปการโอนเงิน</label>
        <input type="file" ref="slipInput" accept="image/*,.pdf" class="inp" @change="onSlip"/>
        <p v-if="slipName" class="text-green-600 text-xs mt-1">✓ {{ slipName }}</p>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="payModal=false">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="submitPayment" :disabled="submitting">
        <span v-if="submitting" class="spin">⏳</span>
        {{ submitting ? 'กำลังส่ง...' : 'ยืนยันชำระ' }}
      </button>
    </div>
  </div>
</div>

</div><!-- #app -->

<style>
.mc-paid{background:#d1fae5;color:#065f46;border-color:#6ee7b7!important}
.mc-overdue{background:#fee2e2;color:#b91c1c;border-color:#fca5a5!important}
.mc-pending{background:#fef3c7;color:#92400e;border-color:#fcd34d!important}
.mc-none{background:#f8fafc;color:#94a3b8;border-color:#e2e8f0!important}
</style>

<script>
const { createApp, ref, computed } = Vue
const monthNames = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม']
createApp({
  setup() {
    const payModal  = ref(false)
    const payId     = ref(null)
    const payMonth  = ref(0)
    const totalDue  = ref(0)
    const submitting = ref(false)
    const slipFile  = ref(null)
    const slipName  = ref('')
    const monthName = computed(() => monthNames[payMonth.value] || '')

    function openPayModal(id, month, due) {
      payId.value = id; payMonth.value = month; totalDue.value = due
      slipFile.value = null; slipName.value = ''
      payModal.value = true
    }

    function onSlip(e) {
      const f = e.target.files[0]
      if (f) { slipFile.value = f; slipName.value = f.name }
    }

    async function submitPayment() {
      submitting.value = true
      const fd = new FormData()
      fd.append('id',    payId.value)
      fd.append('month', payMonth.value)
      fd.append('year',  <?= $year ?>)
      if (slipFile.value) fd.append('slip', slipFile.value)
      try {
        await axios.post('<?= base_url('payment/submit') ?>', fd)
        showToast('ส่งหลักฐานการชำระแล้ว รอการตรวจสอบ')
        payModal.value = false
        setTimeout(() => location.reload(), 1000)
      } catch(e) {
        showToast('เกิดข้อผิดพลาด', false)
      }
      submitting.value = false
    }

    return { payModal, payMonth, totalDue, submitting, slipName, monthName, openPayModal, onSlip, submitPayment }
  }
}).mount('#app')
</script>
