<?php
$month_names = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
$slip_base = base_url('assets/uploads/slips/');
$show_all  = !empty($show_all);
$status_badge = [
    'paid'    => ['จ่ายแล้ว',   '#dcfce7', '#15803d'],
    'pending' => ['รอตรวจ',    '#fff3e0', '#e65100'],
    'overdue' => ['ค้างชำระ',  '#fee2e2', '#b91c1c'],
    'none'    => ['ยังไม่จ่าย', '#f1f5f9', '#64748b'],
];
?>
<div id="app">

  <div class="flex items-center justify-between mb-4" style="gap:12px;flex-wrap:wrap">
    <p class="text-sm font-semibold text-slate-600">
      <?php if ($show_all): ?>
        🗂️ สลิปทั้งหมดที่ส่งเข้ามา <strong style="color:#1565c0"><?= count($rows) ?></strong> ใบ
      <?php else: ?>
        🧾 มีสลิปรอตรวจสอบ <strong style="color:#e65100"><?= count($rows) ?></strong> รายการ
      <?php endif; ?>
    </p>
    <a href="<?= base_url('payment/all') ?>" class="btn btn-gray" style="text-decoration:none">← กลับภาพรวมการชำระ</a>
  </div>

  <!-- Toggle: review queue vs full history -->
  <div style="display:inline-flex;background:#f1f5f9;border-radius:10px;padding:4px;margin-bottom:16px;gap:4px">
    <a href="<?= base_url('payment/pending') ?>" style="text-decoration:none;font-size:13px;font-weight:600;padding:6px 14px;border-radius:7px;<?= !$show_all ? 'background:#fff;color:#e65100;box-shadow:0 1px 3px rgba(0,0,0,.08)' : 'color:#64748b' ?>">
      🧾 รอตรวจ<?= $pending_count > 0 ? ' ('.$pending_count.')' : '' ?>
    </a>
    <a href="<?= base_url('payment/pending/all') ?>" style="text-decoration:none;font-size:13px;font-weight:600;padding:6px 14px;border-radius:7px;<?= $show_all ? 'background:#fff;color:#1565c0;box-shadow:0 1px 3px rgba(0,0,0,.08)' : 'color:#64748b' ?>">
      🗂️ ประวัติทั้งหมด
    </a>
  </div>

  <?php if (empty($rows)): ?>
  <div class="card text-center py-12">
    <p class="text-4xl mb-2"><?= $show_all ? '🗂️' : '✅' ?></p>
    <p class="text-slate-500 font-medium"><?= $show_all ? 'ยังไม่มีสลิปส่งเข้ามาในระบบ' : 'ไม่มีสลิปรอตรวจสอบ' ?></p>
  </div>
  <?php else: ?>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($rows as $r):
      $total = (float)$r->amount + (float)$r->penalty;
    ?>
    <div class="card" style="padding:0;overflow:hidden" :data-id="<?= (int)$r->id ?>" v-show="!done.includes(<?= (int)$r->id ?>)">
      <!-- Slip image -->
      <?php if (!empty($r->slip_file)): ?>
      <a href="<?= $slip_base . htmlspecialchars($r->slip_file) ?>" target="_blank" style="display:block;background:#0f172a">
        <img src="<?= $slip_base . htmlspecialchars($r->slip_file) ?>"
             alt="slip" style="width:100%;max-height:260px;object-fit:contain;display:block"/>
      </a>
      <?php else: ?>
      <div style="background:#f8fafc;border-bottom:1px dashed #e2e8f0" class="py-10 text-center text-xs text-slate-400">ไม่มีไฟล์สลิป</div>
      <?php endif; ?>

      <div class="p-4">
        <div class="flex items-center justify-between mb-2">
          <div>
            <p class="font-semibold text-slate-800 text-sm"><?= htmlspecialchars($r->name) ?></p>
            <p class="font-mono text-xs text-slate-400"><?= $r->student_id ?></p>
          </div>
          <div class="text-right" style="display:flex;flex-direction:column;gap:3px;align-items:flex-end">
            <span style="background:#eef2ff;color:#4338ca;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px"><?= $month_names[$r->month] ?? '' ?> <?= $r->year ?></span>
            <?php $sb = $status_badge[$r->status] ?? $status_badge['none']; ?>
            <span style="background:<?= $sb[1] ?>;color:<?= $sb[2] ?>;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px"><?= $sb[0] ?></span>
          </div>
        </div>
        <div class="flex justify-between text-sm mb-3" style="border-top:1px solid #f1f5f9;padding-top:8px">
          <span class="text-slate-500">ค่าธรรมเนียม ฿<?= number_format($r->amount, 0) ?><?= $r->penalty > 0 ? ' + ค่าปรับ ฿'.number_format($r->penalty,0) : '' ?></span>
          <span class="font-bold" style="color:#dc2626">฿<?= number_format($total, 2) ?></span>
        </div>
        <?php if ($r->status === 'pending'): ?>
        <div class="flex gap-2">
          <button class="btn btn-blue flex-1" style="padding:8px" @click="review(<?= (int)$r->id ?>, 'paid')" :disabled="busy===<?= (int)$r->id ?>">✅ อนุมัติ</button>
          <button class="btn btn-gray flex-1" style="padding:8px" @click="review(<?= (int)$r->id ?>, 'overdue')" :disabled="busy===<?= (int)$r->id ?>">❌ ปฏิเสธ</button>
        </div>
        <?php elseif ($r->status === 'paid'): ?>
        <div class="flex items-center justify-between" style="background:#f0fdf4;border-radius:8px;padding:7px 10px">
          <span class="text-xs" style="color:#15803d">✅ อนุมัติแล้ว<?= $r->paid_date ? ' · '.$r->paid_date : '' ?></span>
          <button class="btn btn-gray" style="padding:4px 10px;font-size:12px" @click="review(<?= (int)$r->id ?>, 'overdue')" :disabled="busy===<?= (int)$r->id ?>">ยกเลิก</button>
        </div>
        <?php else: ?>
        <div class="flex gap-2">
          <button class="btn btn-blue flex-1" style="padding:8px" @click="review(<?= (int)$r->id ?>, 'paid')" :disabled="busy===<?= (int)$r->id ?>">✅ อนุมัติ</button>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const { createApp, ref } = Vue
createApp({
  setup() {
    const done = ref([])   // ids already reviewed (hidden)
    const busy = ref(null)
    async function review(id, status) {
      busy.value = id
      try {
        const fd = new FormData()
        fd.append('id', id)
        fd.append('status', status)
        if (status === 'paid') fd.append('paid_date', new Date().toISOString().slice(0,10))
        const res = await axios.post('<?= base_url('payment/update_status') ?>', fd)
        if (!res.data || res.data.success !== true) {
          showToast('บันทึกไม่สำเร็จ — กรุณาล็อกอินใหม่', false); busy.value = null; return
        }
        showToast(status === 'paid' ? 'อนุมัติแล้ว ✅' : 'ปฏิเสธแล้ว (ตั้งเป็นค้างชำระ)')
        done.value.push(id)
      } catch (e) { showToast('เกิดข้อผิดพลาด', false) }
      busy.value = null
    }
    return { done, busy, review }
  }
}).mount('#app')
})
</script>
