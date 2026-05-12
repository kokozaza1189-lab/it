<?php
// Fix script — creates fix.php in root, visit ?s=itfix to run.
// After success: delete this file from server.
if (($_GET['s'] ?? '') !== 'itfix') {
    echo '<!DOCTYPE html><html><body style="font:14px sans-serif;max-width:400px;margin:60px auto;text-align:center">
    <h2>IT Finance Fix</h2>
    <p>ไปที่: <strong>'.htmlspecialchars($_SERVER['REQUEST_URI']).'?s=itfix</strong></p>
    </body></html>';
    exit;
}

$base = __DIR__ . '/';
$ok = 0; $fail = 0;

// Try GitHub first (works if repo is public)
$gh = 'https://raw.githubusercontent.com/kokozaza1189-lab/it/master/';
$files = [
    'application/views/payment/all.php',
    'application/views/payment/index.php',
    'application/views/pay/index.php',
    'application/controllers/Payment.php',
    'application/models/Student_model.php',
];

$from_gh = true;
$results = [];
foreach ($files as $f) {
    $c = @file_get_contents($gh . $f);
    if ($c === false || strlen($c) < 100) { $from_gh = false; break; }
}

if ($from_gh) {
    foreach ($files as $f) {
        $c = @file_get_contents($gh . $f);
        if ($c && file_put_contents($base . $f, $c) !== false) {
            $results[] = "✅ $f";
            $ok++;
        } else {
            $results[] = "❌ $f";
            $fail++;
        }
    }
} else {
    // Fallback: write payment/all.php directly (most critical)
    $dir = $base . 'application/views/payment/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $written = file_put_contents($dir . 'all.php', PAYMENT_ALL_CONTENT);
    if ($written !== false) { $results[] = "✅ application/views/payment/all.php (embedded)"; $ok++; }
    else { $results[] = "❌ application/views/payment/all.php — write failed"; $fail++; }
    $results[] = "⚠️ GitHub repo is private — other files not updated. Use it_deploy.zip for full deploy.";
}

header('Content-Type: text/plain; charset=utf-8');
echo "IT Finance Fix — " . ($ok > 0 ? "$ok fixed" : "FAILED") . "\n\n";
foreach ($results as $r) echo $r . "\n";
if ($fail === 0 && $ok > 0) echo "\n✅ Delete this fix.php file now.\n";
exit;

// ──────────────────────────────────────────────────────────────────────────────
// Embedded payment/all.php content (fallback if GitHub is private)
// ──────────────────────────────────────────────────────────────────────────────
const PAYMENT_ALL_CONTENT = <<<'PHPEOF'
<?php
$th_months    = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$status_labels = ['paid'=>'ชำระแล้ว','overdue'=>'ค้างชำระ','pending'=>'รอ','none'=>'ไม่เก็บ'];
$status_badge  = ['paid'=>'b-paid','overdue'=>'b-overdue','pending'=>'b-pending','none'=>'b-none'];
$alert_overdue  = 0; $alert_pending  = 0; $alert_penalty  = 0;
foreach ($students as $s) {
    foreach ($active_months as $m) {
        $p = $s->payments[$m] ?? null;
        if (!$p) continue;
        if ($p->status === 'overdue')  $alert_overdue++;
        if ($p->status === 'pending')  $alert_pending++;
        if ($p->penalty > 0)           $alert_penalty++;
    }
}
?>
<div id="app">
<?php if ($alert_overdue > 0 || $alert_penalty > 0 || $alert_pending > 0): ?>
<div class="flex flex-wrap gap-3 mb-4">
  <?php if ($alert_overdue > 0): ?><div class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium" style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b"><span>🔴</span><span>ค้างชำระ <strong><?= $alert_overdue ?></strong> รายการ — ต้องดำเนินการด่วน</span></div><?php endif; ?>
  <?php if ($alert_pending > 0): ?><div class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium" style="background:#fef3c7;border:1px solid #fcd34d;color:#92400e"><span>🟡</span><span>รอตรวจสอบ <strong><?= $alert_pending ?></strong> รายการ</span></div><?php endif; ?>
  <?php if ($alert_penalty > 0): ?><div class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium" style="background:#fef9c3;border:1px solid #fde047;color:#a16207"><span>⚠️</span><span>ค้างค่าปรับ <strong><?= $alert_penalty ?></strong> รายการ</span></div><?php endif; ?>
</div>
<?php endif; ?>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">นิสิตทั้งหมด</p><p class="text-2xl font-bold text-slate-800 mt-1"><?= $stats['total'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">ชำระแล้ว</p><p class="text-2xl font-bold text-emerald-600 mt-1"><?= $stats['paid'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">ค้างชำระ</p><p class="text-2xl font-bold text-red-500 mt-1"><?= $stats['overdue'] ?></p></div>
  <div class="kpi"><p class="text-slate-500 text-xs font-semibold uppercase">รอดำเนินการ</p><p class="text-2xl font-bold text-amber-500 mt-1"><?= $stats['pending'] ?></p></div>
</div>
<div class="card mb-5">
  <form method="GET" action="<?= base_url('payment/all') ?>" class="flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48"><label class="lbl">ค้นหา</label><input name="search" value="<?= htmlspecialchars($search) ?>" class="inp" placeholder="ชื่อหรือรหัสนิสิต"/></div>
    <div><label class="lbl">ปีการศึกษา</label><input name="year" type="number" value="<?= $year ?>" class="inp" style="width:100px"/></div>
    <button type="submit" class="btn btn-blue">🔍 ค้นหา</button>
    <a href="<?= base_url('payment/all') ?>" class="btn btn-gray">รีเซ็ต</a>
    <button type="button" class="btn btn-gray" @click="exportExcel">📊 Export Excel</button>
    <a href="<?= base_url('admin/payments') ?>" class="btn btn-gray">⚙️ จัดการ</a>
  </form>
</div>
<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>#</th><th>ชื่อ-สกุล</th><th>รหัส</th>
        <?php foreach ($active_months as $m): ?><th><?= $th_months[$m] ?></th><?php endforeach; ?>
        <th>ค้างรวม</th>
      </tr></thead>
      <tbody>
        <?php foreach ($students as $i => $s):
          $total = 0;
          foreach ($active_months as $m) { $p = $s->payments[$m] ?? null; if ($p && $p->status === 'overdue') $total += $p->amount + $p->penalty; }
        ?>
        <tr>
          <td class="text-slate-400 text-xs"><?= $i+1 ?></td>
          <td class="font-medium text-slate-800"><?= htmlspecialchars($s->name) ?></td>
          <td class="font-mono text-xs text-slate-500"><?= $s->student_id ?></td>
          <?php foreach ($active_months as $m):
            $p   = $s->payments[$m] ?? (object)['id'=>null,'status'=>'none','amount'=>0,'penalty'=>0];
            $cls = ['paid'=>'b-paid','overdue'=>'b-overdue','pending'=>'b-pending','none'=>'b-none'][$p->status] ?? 'b-none';
            $lbl = ['paid'=>'จ่าย','overdue'=>'ค้าง','pending'=>'รอ','none'=>'-'][$p->status] ?? '-';
          ?>
          <td><button class="badge <?= $cls ?> cursor-pointer hover:opacity-80"
              @click="openStatus(<?= htmlspecialchars(json_encode(['id'=>$p->id??null,'month'=>$m,'student'=>$s->name,'status'=>$p->status,'amount'=>isset($p->amount)?(float)$p->amount:35,'penalty'=>isset($p->penalty)?(float)$p->penalty:0,'slip_file'=>isset($p->slip_file)?$p->slip_file:null]),ENT_QUOTES) ?>)">
            <?= $lbl ?>
            <?php $m_amt=(float)($p->amount??0);$m_pen=(float)($p->penalty??0);$m_tot=$m_amt+$m_pen;if($p->status==='overdue'&&$m_tot>0):?>฿<?=number_format($m_tot,0)?><?php endif;?>
            <?php if($p->status==='overdue'&&$m_pen>0&&$m_amt>0):?><span style="font-size:9px;opacity:.75">(+<?=number_format($m_pen,0)?>)</span><?php endif;?>
            <?php if(!empty($p->slip_file)):?><span style="font-size:9px">📎</span><?php endif;?>
          </button></td>
          <?php endforeach; ?>
          <td class="font-bold <?= $total>0?'text-red-500':'text-slate-400' ?>"><?= $total>0?'฿'.number_format($total,2):'-' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="text-slate-400 text-xs p-4">แสดง <?= count($students) ?> รายการ</p>
</div>
<div v-show="statusModal" id="statusModal" class="modal-bg" @click.self="statusModal=false" style="display:none">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-header">
      <div class="flex items-center justify-between"><h2 class="font-bold text-slate-800">อัปเดตสถานะการชำระ</h2><button @click="statusModal=false" class="btn-icon">✕</button></div>
      <p class="text-slate-500 text-sm mt-1"><span v-text="editData.student"></span> — เดือน <span v-text="monthNames[editData.month]"></span></p>
    </div>
    <div class="modal-body space-y-4">
      <div v-show="editData.slip_file" style="display:none">
        <label class="lbl mb-2">📎 สลิปที่แนบมา</label>
        <div class="rounded-xl overflow-hidden border border-slate-200" style="background:#f8fafc">
          <img :src="slipUrl" alt="slip" style="width:100%;max-height:220px;object-fit:contain;display:block"/>
          <div class="px-3 py-2 text-center" style="border-top:1px solid #e2e8f0"><a :href="slipUrl" target="_blank" class="text-xs font-medium" style="color:#3b82f6">เปิดไฟล์ต้นฉบับ ↗</a></div>
        </div>
      </div>
      <div v-show="!editData.slip_file" style="display:none;background:#f8fafc;border:1px dashed #e2e8f0" class="rounded-xl p-3 text-center text-xs text-slate-400">ยังไม่มีสลิปที่แนบมา</div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl">สถานะ</label>
          <select v-model="editData.status" class="inp"><option value="paid">ชำระแล้ว</option><option value="overdue">ค้างชำระ</option><option value="pending">รอดำเนินการ</option><option value="none">ไม่เก็บ</option></select></div>
        <div><label class="lbl">ค่าธรรมเนียม (฿)</label><input type="number" step="0.01" min="0" v-model.number="editData.amount" class="inp"/></div>
      </div>
      <div v-if="editData.status==='paid'"><label class="lbl">วันที่ชำระ</label><input type="date" v-model="editData.paid_date" class="inp"/></div>
      <div>
        <label class="lbl">ค่าปรับคงค้าง (฿)<span v-show="editData.penalty>0" style="display:none;background:#fef3c7;color:#b45309;font-size:11px;padding:2px 6px;border-radius:4px;margin-left:4px">⚠️ ยังค้างอยู่</span></label>
        <input type="number" step="0.01" min="0" v-model.number="editData.penalty" class="inp" :style="editData.penalty>0?'border-color:#fcd34d;background:#fffbeb':''"/>
        <p v-show="editData.penalty>0" style="display:none;color:#b45309" class="text-xs mt-1">รวมที่ต้องชำระ ฿<span v-text="(editData.amount+editData.penalty).toFixed(2)"></span></p>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-gray flex-1" @click="statusModal=false">ยกเลิก</button>
      <button class="btn btn-blue flex-1" @click="saveStatus" :disabled="saving"><span v-if="saving" class="spin">⏳</span> บันทึก</button>
    </div>
  </div>
</div>
</div>
<script>
(window.__vue_inits = window.__vue_inits || []).push(function() {
const paymentAllData = <?= json_encode(array_map(function($s){return['name'=>mb_convert_encoding($s->name,'UTF-8','UTF-8'),'student_id'=>$s->student_id,'payments'=>array_map(function($p){return['month'=>isset($p->month)?(int)$p->month:0,'status'=>$p->status??'none','amount'=>(float)($p->amount??0),'penalty'=>(float)($p->penalty??0)];},(array)$s->payments)];}, $students?:[]), JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE)?:'[]' ?>;
const activeMonthsAll = <?= json_encode($active_months) ?>;
const thMonths = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
const { createApp, ref, reactive, computed } = Vue
const monthNames = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม']
const SLIP_BASE_URL = '<?= base_url('assets/uploads/slips/') ?>'
createApp({setup(){
  const statusModal=ref(false), saving=ref(false)
  const editData=reactive({id:null,month:0,student:'',status:'',paid_date:'',penalty:0,amount:0,slip_file:null})
  const slipUrl=computed(()=>editData.slip_file?SLIP_BASE_URL+editData.slip_file:'')
  function exportExcel(){
    if(!window.XLSX)return alert('โหลด SheetJS ไม่สำเร็จ')
    const statusTH={paid:'ชำระแล้ว',overdue:'ค้างชำระ',pending:'รอดำเนินการ',none:'-'}
    const rows=paymentAllData.map(s=>{const row={'ชื่อ-สกุล':s.name,'รหัสนิสิต':s.student_id};activeMonthsAll.forEach(m=>{const p=s.payments[m];let val=p?statusTH[p.status]||p.status:'-';if(p&&p.penalty>0)val+=' (+฿'+p.penalty+')';row[thMonths[m]]=val});return row})
    const ws=XLSX.utils.json_to_sheet(rows);const wb=XLSX.utils.book_new();XLSX.utils.book_append_sheet(wb,ws,'Payments');XLSX.writeFile(wb,'payments_'+new Date().toISOString().slice(0,10)+'.xlsx')
  }
  function openStatus(data){Object.assign(editData,data,{paid_date:''});statusModal.value=true}
  async function saveStatus(){
    if(!editData.id){showToast('ไม่พบ ID รายการ',false);return}
    saving.value=true
    try{const fd=new FormData();fd.append('id',editData.id);fd.append('status',editData.status);fd.append('amount',editData.amount);fd.append('penalty',editData.penalty||0);fd.append('paid_date',editData.paid_date||'');await axios.post('<?= base_url('payment/update_status') ?>',fd);showToast('บันทึกสถานะแล้ว');statusModal.value=false;setTimeout(()=>location.reload(),800)}catch(e){showToast('เกิดข้อผิดพลาด',false)}
    saving.value=false
  }
  return{statusModal,saving,editData,monthNames,slipUrl,openStatus,saveStatus,exportExcel}
}}).mount('#app')
})
</script>
PHPEOF;
