<?php
$th_months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
?>
<div class="no-print mb-4 flex flex-wrap gap-3 items-center justify-between">
  <form method="GET" action="<?= base_url('reports') ?>" class="flex gap-2 items-end">
    <div>
      <label class="lbl">ปีการศึกษา</label>
      <input name="year" type="number" value="<?= $year ?>" class="inp" style="width:100px"/>
    </div>
    <button type="submit" class="btn btn-blue btn-sm">ค้นหา</button>
    <a href="<?= base_url('reports') ?>" class="btn btn-gray btn-sm">ปีปัจจุบัน</a>
  </form>
  <button onclick="window.print()" class="btn btn-gray btn-sm">🖨️ พิมพ์รายงาน</button>
</div>

<!-- Print header (hidden on screen) -->
<div class="print-only mb-5">
  <h1 class="text-xl font-bold text-center">รายงานสรุปการเงิน — ปีการศึกษา <?= $year ?></h1>
  <p class="text-center text-sm text-slate-500">พิมพ์เมื่อ <?= date('d/m/') . (date('Y')+543) . ' ' . date('H:i') ?></p>
</div>

<!-- KPI row -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
  <div class="kpi">
    <p class="text-slate-500 text-xs font-semibold uppercase">นิสิตทั้งหมด</p>
    <p class="text-2xl font-bold text-slate-800 mt-1"><?= $total_students ?></p>
  </div>
  <div class="kpi">
    <p class="text-slate-500 text-xs font-semibold uppercase">รายรับรวม</p>
    <p class="text-2xl font-bold text-emerald-600 mt-1">฿<?= number_format($total_income, 2) ?></p>
  </div>
  <div class="kpi">
    <p class="text-slate-500 text-xs font-semibold uppercase">ค้างชำระ</p>
    <p class="text-2xl font-bold text-red-500 mt-1"><?= $total_overdue ?> ราย</p>
  </div>
  <div class="kpi">
    <p class="text-slate-500 text-xs font-semibold uppercase">ยอดเงินกลาง</p>
    <p class="text-2xl font-bold <?= $balance >= 0 ? 'text-emerald-600' : 'text-red-500' ?> mt-1">
      ฿<?= number_format($balance, 2) ?>
    </p>
  </div>
</div>

<!-- Monthly payment summary -->
<div class="card mb-5">
  <h2 class="font-bold text-slate-800 mb-4">สรุปการชำระเงินรายเดือน — ปีการศึกษา <?= $year ?></h2>
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr>
        <th>เดือน</th>
        <th class="text-right">ชำระแล้ว</th>
        <th class="text-right">ค้างชำระ</th>
        <th class="text-right">รอ</th>
        <th class="text-right">รวมรายการ</th>
        <th class="text-right">รายรับ (฿)</th>
      </tr></thead>
      <tbody>
        <?php
          $grand_paid = $grand_overdue = $grand_pending = $grand_total = $grand_income = 0;
          foreach ($monthly as $m => $s):
            $grand_paid    += $s['paid'];
            $grand_overdue += $s['overdue'];
            $grand_pending += $s['pending'];
            $grand_total   += $s['total'];
            $grand_income  += $s['income'];
        ?>
        <tr>
          <td class="font-medium"><?= $s['label'] ?></td>
          <td class="text-right">
            <span class="badge b-paid"><?= $s['paid'] ?></span>
          </td>
          <td class="text-right">
            <?php if ($s['overdue'] > 0): ?>
            <span class="badge b-overdue"><?= $s['overdue'] ?></span>
            <?php else: ?>
            <span class="text-slate-300">—</span>
            <?php endif; ?>
          </td>
          <td class="text-right">
            <?php if ($s['pending'] > 0): ?>
            <span class="badge b-pending"><?= $s['pending'] ?></span>
            <?php else: ?>
            <span class="text-slate-300">—</span>
            <?php endif; ?>
          </td>
          <td class="text-right text-slate-500 text-sm"><?= $s['total'] ?></td>
          <td class="text-right font-medium text-emerald-700">
            <?= $s['income'] > 0 ? '฿'.number_format($s['income'],2) : '—' ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="font-bold bg-slate-50">
          <td>รวม</td>
          <td class="text-right text-emerald-700"><?= $grand_paid ?></td>
          <td class="text-right text-red-500"><?= $grand_overdue ?></td>
          <td class="text-right text-amber-600"><?= $grand_pending ?></td>
          <td class="text-right text-slate-600"><?= $grand_total ?></td>
          <td class="text-right text-emerald-700">฿<?= number_format($grand_income,2) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- Expense summary + Fund balance side by side -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

  <!-- Expense stats -->
  <div class="card">
    <h2 class="font-bold text-slate-800 mb-4">สรุปใบเบิกเงิน</h2>
    <div class="space-y-3">
      <?php
        $exp_labels = ['draft'=>'แบบร่าง','submitted'=>'ส่งแล้ว','pending'=>'รับเรื่อง','approved'=>'อนุมัติ','rejected'=>'ปฏิเสธ','completed'=>'เสร็จสิ้น'];
        $exp_colors = ['draft'=>'text-slate-400','submitted'=>'text-blue-500','pending'=>'text-amber-500','approved'=>'text-emerald-600','rejected'=>'text-red-500','completed'=>'text-slate-700'];
        foreach ($exp_stats as $status => $count):
      ?>
      <div class="flex items-center justify-between py-2" style="border-bottom:1px solid #f1f5f9">
        <span class="text-sm text-slate-600"><?= $exp_labels[$status] ?? $status ?></span>
        <span class="font-bold <?= $exp_colors[$status] ?? '' ?>"><?= $count ?></span>
      </div>
      <?php endforeach; ?>
      <div class="flex items-center justify-between py-2 font-bold">
        <span class="text-slate-800">รวมทั้งหมด</span>
        <span class="text-slate-800"><?= array_sum($exp_stats) ?></span>
      </div>
    </div>
  </div>

  <!-- Fund balance -->
  <div class="card">
    <h2 class="font-bold text-slate-800 mb-4">ยอดเงินกลาง</h2>
    <div class="text-center py-4">
      <p class="text-4xl font-bold <?= $balance >= 0 ? 'text-emerald-600' : 'text-red-500' ?>">
        ฿<?= number_format($balance, 2) ?>
      </p>
      <p class="text-slate-400 text-sm mt-2">ยอดคงเหลือล่าสุด</p>
    </div>
    <?php if (!empty($fund_ledger)): ?>
    <div class="mt-4" style="border-top:1px solid #f1f5f9;padding-top:1rem">
      <p class="text-xs font-semibold text-slate-500 mb-2">รายการล่าสุด</p>
      <?php foreach (array_slice($fund_ledger, 0, 5) as $e): ?>
      <div class="flex items-center justify-between text-sm py-1.5" style="border-bottom:1px solid #f8fafc">
        <span class="text-slate-600 truncate flex-1 mr-2"><?= htmlspecialchars($e->description) ?></span>
        <span class="font-medium <?= $e->type === 'income' ? 'text-emerald-600' : 'text-red-500' ?> flex-shrink-0">
          <?= $e->type === 'income' ? '+' : '-' ?>฿<?= number_format($e->income ?: $e->expense, 0) ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Overdue list -->
<?php if (!empty($overdue_list)): ?>
<div class="card mb-5 page-break-before">
  <h2 class="font-bold text-slate-800 mb-4">รายชื่อนิสิตค้างชำระ (<?= count($overdue_list) ?> ราย)</h2>
  <div class="overflow-x-auto">
    <table class="tbl">
      <thead><tr>
        <th>#</th><th>ชื่อ-สกุล</th><th>รหัสนิสิต</th><th>เดือน</th><th class="text-right">ค่าธรรมเนียม</th><th class="text-right">ค่าปรับ</th><th class="text-right">รวม</th>
      </tr></thead>
      <tbody>
        <?php foreach ($overdue_list as $i => $r): ?>
        <tr>
          <td class="text-slate-400 text-xs"><?= $i+1 ?></td>
          <td class="font-medium"><?= htmlspecialchars($r->student_name) ?></td>
          <td class="font-mono text-xs text-slate-500"><?= $r->student_id ?></td>
          <td><?= $th_months[$r->month] ?></td>
          <td class="text-right">฿<?= number_format($r->amount, 2) ?></td>
          <td class="text-right text-red-500"><?= $r->penalty > 0 ? '฿'.number_format($r->penalty,2) : '—' ?></td>
          <td class="text-right font-bold text-red-600">฿<?= number_format($r->amount + $r->penalty, 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<style>
@media print {
  .no-print { display:none !important; }
  .print-only { display:block !important; }
  .sidebar, .main > div:first-child { display:none !important; }
  .page-wrap { padding:0 !important; }
  .card { box-shadow:none !important; border:1px solid #e2e8f0 !important; }
  .page-break-before { page-break-before:always; }
}
@media screen {
  .print-only { display:none; }
}
</style>
