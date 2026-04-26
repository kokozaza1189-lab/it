  </div><!-- /page-wrap -->
</div><!-- /main -->

<!-- Bottom Nav (mobile) -->
<nav class="bottom-nav">
  <?php
  $role = $current_user['role'];
  $bnav = [
    ['label'=>'หน้าหลัก','icon'=>'📊','url'=>base_url('dashboard'),'key'=>'dashboard'],
    ['label'=>'ชำระเงิน','icon'=>'📱','url'=>base_url('pay'),      'key'=>'pay'],
    ['label'=>'เบิก',    'icon'=>'💸','url'=>base_url('expense'),  'key'=>'expense'],
    ['label'=>'เงินกลาง','icon'=>'🏦','url'=>base_url('fund'),     'key'=>'fund'],
    ['label'=>'นิสิต',   'icon'=>'👥','url'=>base_url('students'), 'key'=>'students'],
  ];
  $treasurer_roles = ['treasurer','head_it','advisor','auditor','super_admin'];
  $current_page = uri_string();
  foreach ($bnav as $m):
    if ($m['key']==='payment' && !in_array($role,['student','activity_staff','academic_staff','treasurer','super_admin'])) continue;
    if ($m['key']==='fund' && !in_array($role,$treasurer_roles)) continue;
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

// Universal modal handler — works without Vue
// Usage: data-modal-open="modalId" / data-modal-close / click backdrop
document.addEventListener('click', function(e) {
  var opener = e.target.closest('[data-modal-open]');
  if (opener) {
    var id = opener.getAttribute('data-modal-open');
    var m  = document.getElementById(id);
    if (m) { m.style.display = 'flex'; e.stopPropagation(); }
    // Populate form fields from data-* attributes if present
    var fields = opener.dataset;
    Object.keys(fields).forEach(function(k) {
      if (k === 'modalOpen') return;
      var inp = m && m.querySelector('[name="' + k + '"], [data-field="' + k + '"]');
      if (inp) inp.value = fields[k];
    });
    return;
  }
  var closer = e.target.closest('[data-modal-close]');
  if (closer) {
    var target = closer.getAttribute('data-modal-close');
    var m = target ? document.getElementById(target) : closer.closest('.modal-bg');
    if (m) m.style.display = 'none';
    return;
  }
  if (e.target.classList.contains('modal-bg')) {
    e.target.style.display = 'none';
  }
});
</script>

<!-- CDN libraries loaded at end of body — HTML renders first, no blocking -->
<script src="https://cdn.jsdelivr.net/npm/vue@3.4.21/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Configure axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Run all Vue app initializers queued by view scripts
(window.__vue_inits || []).forEach(function(fn) { try { fn(); } catch(e) { console.error(e); } });
</script>
</body>
</html>
