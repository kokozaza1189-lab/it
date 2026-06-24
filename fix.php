<?php
// IT Finance Fix Script — visit ?s=itfix to run
// Pulls latest files from GitHub (public repo) or applies embedded patches as fallback.
if (($_GET['s'] ?? '') !== 'itfix') {
    echo '<!DOCTYPE html><html><body style="font:14px sans-serif;max-width:480px;margin:60px auto;text-align:center">
    <h2>IT Finance Fix</h2>
    <p>ไปที่: <a href="?s=itfix"><strong>' . htmlspecialchars($_SERVER['REQUEST_URI']) . '?s=itfix</strong></a></p>
    </body></html>';
    exit;
}

$base = rtrim(__DIR__, '/') . '/';
$ok = 0; $fail = 0; $results = [];

$gh = 'https://raw.githubusercontent.com/kokozaza1189-lab/it/master/';

// ── All files to pull from GitHub ────────────────────────────────────────────
$files = [
    // Controllers
    'application/controllers/Pay.php',
    'application/controllers/Payment.php',
    'application/controllers/Dashboard.php',
    'application/controllers/Penalty.php',
    'application/controllers/Settings.php',
    'application/controllers/Admin.php',
    // Models
    'application/models/Payment_model.php',
    // Views
    'application/views/pay/index.php',
    'application/views/payment/index.php',
    'application/views/payment/all.php',
    'application/views/dashboard/index.php',
    'application/views/penalty/all.php',
    'application/views/settings/index.php',
    'application/views/templates/sidebar.php',
];

// ── Try GitHub pull ───────────────────────────────────────────────────────────
$gh_ok = true;
foreach ($files as $f) {
    $c = @file_get_contents($gh . $f);
    if ($c === false || strlen($c) < 80) { $gh_ok = false; break; }
}

if ($gh_ok) {
    foreach ($files as $f) {
        $c = @file_get_contents($gh . $f);
        $dir = $base . dirname($f);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        if ($c && file_put_contents($base . $f, $c) !== false) {
            $results[] = "✅ $f";
            $ok++;
        } else {
            $results[] = "❌ $f — write failed";
            $fail++;
        }
    }
} else {
    // ── Fallback: surgical patches for critical bugs ──────────────────────────
    $results[] = "⚠️ GitHub unreachable — applying surgical patches";

    // PATCH 1: Pay.php — fix active_months key-0 bug (CRITICAL)
    $pay_ctrl = $base . 'application/controllers/Pay.php';
    if (file_exists($pay_ctrl)) {
        $src = file_get_contents($pay_ctrl);
        // Replace old single-line parse with safe version
        $old = "\$active_months = array_map('intval', explode(',', \$settings['active_months'] ?? '1,2,3,4'));";
        $new = "\$active_months = array_values(array_filter(\n            array_map('intval', explode(',', \$settings['active_months'] ?? '')),\n            fn(\$m) => \$m >= 1 && \$m <= 12\n        ));\n        if (empty(\$active_months)) \$active_months = [1, 2, 3, 4];";
        if (strpos($src, $old) !== false) {
            $patched = str_replace($old, $new, $src);
            if (file_put_contents($pay_ctrl, $patched) !== false) {
                $results[] = "✅ Pay.php — active_months bug patched"; $ok++;
            } else {
                $results[] = "❌ Pay.php — write failed"; $fail++;
            }
        } elseif (strpos($src, 'fn($m) => $m >= 1') !== false) {
            $results[] = "ℹ️ Pay.php — already patched";
        } else {
            $results[] = "⚠️ Pay.php — pattern not found; patch manually"; $fail++;
        }
    } else {
        $results[] = "❌ Pay.php — file not found"; $fail++;
    }

    // PATCH 2: pay/index.php view — add null-safe on month_names lookup
    $pay_view = $base . 'application/views/pay/index.php';
    if (file_exists($pay_view)) {
        $src = file_get_contents($pay_view);
        // Guard: replace $month_names[$m] with $month_names[$m] ?? ''
        $patched = preg_replace(
            '/\$month_names\[(\$m)\](?!\s*\?\?)/',
            '$month_names[$1] ?? \'\'',
            $src
        );
        if ($patched && $patched !== $src) {
            file_put_contents($pay_view, $patched);
            $results[] = "✅ pay/index.php — month_names null-safe patched"; $ok++;
        } else {
            $results[] = "ℹ️ pay/index.php — already safe or pattern not found";
        }
    }

    $results[] = "⚠️ Controllers/models not fully updated — use GitHub pull for full deploy";
}

// ── Create uploads/qr dir if missing ─────────────────────────────────────────
$qr_dir = $base . 'assets/uploads/qr/';
if (!is_dir($qr_dir)) {
    @mkdir($qr_dir, 0755, true);
    $results[] = "✅ Created assets/uploads/qr/";
}

// ── Output ────────────────────────────────────────────────────────────────────
header('Content-Type: text/plain; charset=utf-8');
echo "IT Finance Fix (" . date('Y-m-d H:i:s') . ")\n";
echo str_repeat('─', 50) . "\n";
echo ($gh_ok ? "Mode: GitHub pull ✅" : "Mode: Surgical patch ⚠️") . "\n\n";
foreach ($results as $r) echo $r . "\n";
echo "\n" . str_repeat('─', 50) . "\n";
echo "✅ $ok fixed   ❌ $fail failed\n";
if ($fail === 0) echo "\nAll done! You can delete fix.php from the server now.\n";
