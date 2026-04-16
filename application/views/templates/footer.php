  </div><!-- /page-wrap -->
</div><!-- /main -->

<!-- Bottom Nav (mobile) -->
<nav class="bottom-nav">
  <?php
  $role = $current_user['role'];
  $bnav = [
    ['label'=>'หน้าหลัก','icon'=>'📊','url'=>base_url('dashboard'),'key'=>'dashboard'],
    ['label'=>'ชำระ',    'icon'=>'💳','url'=>base_url('payment'),  'key'=>'payment'],
    ['label'=>'เบิก',    'icon'=>'💸','url'=>base_url('expense'),  'key'=>'expense'],
    ['label'=>'เงินกลาง','icon'=>'🏦','url'=>base_url('fund'),     'key'=>'fund'],
    ['label'=>'นิสิต',   'icon'=>'👥','url'=>base_url('students'), 'key'=>'students'],
  ];
  $treasurer_roles = ['treasurer','head_it','advisor','auditor','super_admin'];
  $current_page = uri_string();
  foreach ($bnav as $m):
    if ($m['key']==='payment' && !in_array($role,['student','activity_staff','academic_staff','treasurer','super_admin'])) continue;
    if (in_array($m['key'],['fund','students']) && !in_array($role,$treasurer_roles)) continue;
  ?>
    <a href="<?= $m['url'] ?>" class="bnav-item <?= strpos($current_page,$m['key'])===0?'active':'' ?>">
      <span style="font-size:20px;line-height:1"><?= $m['icon'] ?></span>
      <span><?= $m['label'] ?></span>
    </a>
  <?php endforeach; ?>
</nav>

<!-- Toast notification -->
<div id="toast"></div>

<script>
// Sidebar toggle
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('show');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('show');
}

// Toast utility
function showToast(msg, ok = true) {
  const t = document.getElementById('toast');
  t.textContent = (ok ? '✅ ' : '❌ ') + msg;
  t.style.background = ok ? '#059669' : '#dc2626';
  t.style.display = 'block';
  setTimeout(() => t.style.display = 'none', 3000);
}

// Axios CSRF (CI3 uses POST with form_key if needed; here simplified)
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
</script>
</body>
</html>
