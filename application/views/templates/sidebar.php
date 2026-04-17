<?php
$role  = $current_user['role'];
$menus = [
  // ─── ภาพรวม ───
  ['section'=>'ภาพรวม'],
  ['key'=>'dashboard',   'label'=>'Dashboard',         'icon'=>'📊',
   'roles'=>['student','treasurer','head_it','advisor','auditor','super_admin','activity_staff','academic_staff'],
   'url'=>base_url('dashboard')],
  ['key'=>'notifications','label'=>'การแจ้งเตือน',   'icon'=>'🔔',
   'roles'=>['student','activity_staff','academic_staff','treasurer','head_it','advisor','auditor','super_admin'],
   'url'=>base_url('notifications')],
  // ─── การเงิน ───
  ['section'=>'การเงิน'],
  ['key'=>'payment',     'label'=>'การชำระเงินของฉัน', 'icon'=>'💳',
   'roles'=>['student','activity_staff','academic_staff','treasurer','super_admin'],
   'url'=>base_url('payment')],
  ['key'=>'payment/all', 'label'=>'ภาพรวมการชำระ',    'icon'=>'📋',
   'roles'=>['treasurer','head_it','advisor','auditor','super_admin'],
   'url'=>base_url('payment/all')],
  ['key'=>'expense',     'label'=>'เบิกเงิน',           'icon'=>'💸',
   'roles'=>['activity_staff','academic_staff','treasurer','super_admin','head_it','advisor'],
   'url'=>base_url('expense')],
  ['key'=>'fund',        'label'=>'เงินกลาง',            'icon'=>'🏦',
   'roles'=>['treasurer','head_it','advisor','auditor','super_admin'],
   'url'=>base_url('fund')],
  // ─── นิสิต ───
  ['section'=>'นิสิต'],
  ['key'=>'students',    'label'=>'รายชื่อนิสิต',     'icon'=>'👥',
   'roles'=>['treasurer','head_it','advisor','auditor','super_admin'],
   'url'=>base_url('students')],
  // ─── รายงาน & จัดการ ───
  ['section'=>'รายงาน & จัดการ'],
  ['key'=>'reports',     'label'=>'รายงานสรุป',        'icon'=>'📈',
   'roles'=>['treasurer','head_it','advisor','auditor','super_admin'],
   'url'=>base_url('reports')],
  ['key'=>'admin/students','label'=>'จัดการนิสิต',    'icon'=>'🎓',
   'roles'=>['treasurer','super_admin'],
   'url'=>base_url('admin/students')],
  ['key'=>'admin/payments','label'=>'จัดการการชำระ',  'icon'=>'⚙️',
   'roles'=>['treasurer','super_admin'],
   'url'=>base_url('admin/payments')],
  ['key'=>'settings',    'label'=>'ตั้งค่าระบบ',       'icon'=>'🔧',
   'roles'=>['treasurer','head_it','advisor','auditor','super_admin'],
   'url'=>base_url('settings')],
  // ─── บัญชีผู้ใช้ ───
  ['section'=>'บัญชีผู้ใช้'],
  ['key'=>'settings',    'label'=>'ตั้งค่า',            'icon'=>'⚙️',
   'roles'=>['student','activity_staff','academic_staff'],
   'url'=>base_url('settings')],
  ['key'=>'profile',     'label'=>'โปรไฟล์',            'icon'=>'👤',
   'roles'=>['student','activity_staff','academic_staff','treasurer','head_it','advisor','auditor','super_admin'],
   'url'=>base_url('profile')],
];
$current_page = uri_string();
?>
<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">
  <!-- Brand -->
  <div class="px-5 py-5" style="border-bottom:1px solid rgba(255,255,255,.06)">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-blue-500 flex items-center justify-center flex-shrink-0">
        <span class="text-white font-bold text-sm">IT</span>
      </div>
      <div class="min-w-0">
        <p class="text-white font-semibold text-sm leading-none">IT Finance</p>
        <p class="text-slate-500 text-xs mt-0.5">สาขาวิชา IT</p>
      </div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="flex-1 py-3 overflow-y-auto">
    <?php
    // Pre-process: which section indices have ≥1 visible item for this role
    $sec_visible = []; $cur_sec = null;
    foreach ($menus as $idx => $m) {
      if (isset($m['section'])) { $cur_sec = $idx; $sec_visible[$idx] = false; }
      elseif ($cur_sec !== null && in_array($role, $m['roles'])) { $sec_visible[$cur_sec] = true; }
    }
    $cur_sec = null;
    foreach ($menus as $idx => $m):
      if (isset($m['section'])):
        $cur_sec = $idx;
        if (!empty($sec_visible[$idx])): ?>
          <p class="nav-section"><?= $m['section'] ?></p>
        <?php endif;
      elseif (in_array($role, $m['roles'])): ?>
        <a href="<?= $m['url'] ?>"
           class="nav-item <?= (strpos($current_page, $m['key']) === 0) ? 'active' : '' ?>">
          <span style="font-size:18px;width:22px;text-align:center"><?= $m['icon'] ?></span>
          <span><?= $m['label'] ?></span>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <!-- User info -->
  <div class="px-4 py-4" style="border-top:1px solid rgba(255,255,255,.06)">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 text-white text-sm font-bold"
           style="background:<?= $current_user['color'] ?>">
        <?= mb_substr(preg_replace('/นาย|นางสาว|หัวหน้า|อาจารย์/u','',$current_user['name']), 0, 1) ?>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-white text-sm font-medium truncate"><?= htmlspecialchars($current_user['name']) ?></p>
        <p class="text-slate-500 text-xs truncate"><?= $current_user['roleLabel'] ?></p>
      </div>
      <a href="<?= base_url('profile') ?>" class="text-slate-500 hover:text-white text-lg transition-colors" title="โปรไฟล์">👤</a>
      <a href="<?= base_url('logout') ?>" class="text-slate-500 hover:text-white text-lg transition-colors" title="ออกจากระบบ">⏏</a>
    </div>
  </div>
</aside>

<!-- Main wrapper start -->
<div class="main" id="mainContent">
  <!-- Topbar -->
  <div class="flex items-center justify-between px-5 py-4 bg-white border-b border-slate-100 sticky top-0 z-50">
    <div class="flex items-center gap-3">
      <button class="btn-icon lg:hidden" onclick="toggleSidebar()">☰</button>
      <h1 class="text-slate-800 font-bold text-lg"><?= isset($title) ? htmlspecialchars($title) : 'IT Finance' ?></h1>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-slate-400 text-sm hidden sm:block"><?= date('j M') . ' ' . (date('Y')+543) ?></span>
      <a href="<?= base_url('logout') ?>" class="btn btn-gray btn-sm hidden sm:flex">ออกจากระบบ</a>
    </div>
  </div>
  <!-- Page content -->
  <div class="page-wrap p-5">
