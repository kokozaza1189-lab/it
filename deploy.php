<?php
// One-time deploy script — upload to server root, visit URL, then delete.
// Pulls latest files from GitHub and writes them to the correct paths.

define('DEPLOY_SECRET', 'itfinance2568');
define('GITHUB_RAW', 'https://raw.githubusercontent.com/kokozaza1189-lab/it/master/');

$files = [
    'application/config/config.php',
    'application/config/routes.php',
    'application/controllers/Admin.php',
    'application/controllers/Auth.php',
    'application/controllers/Dashboard.php',
    'application/controllers/Expense.php',
    'application/controllers/Fund.php',
    'application/controllers/Notifications.php',
    'application/controllers/Pay.php',
    'application/controllers/Penalty.php',
    'application/controllers/Payment.php',
    'application/controllers/Profile.php',
    'application/controllers/Reports.php',
    'application/controllers/Settings.php',
    'application/controllers/Students.php',
    'application/core/MY_Controller.php',
    'application/models/Expense_model.php',
    'application/models/Fund_model.php',
    'application/models/Payment_model.php',
    'application/models/Setting_model.php',
    'application/models/Student_model.php',
    'application/models/User_model.php',
    'application/views/admin/payments.php',
    'application/views/admin/students.php',
    'application/views/admin/users.php',
    'application/views/auth/login.php',
    'application/views/dashboard/index.php',
    'application/views/expense/create.php',
    'application/views/expense/detail.php',
    'application/views/expense/edit.php',
    'application/views/fund/index.php',
    'application/views/notifications/index.php',
    'application/views/pay/index.php',
    'application/views/penalty/all.php',
    'application/views/penalty/index.php',
    'application/views/payment/all.php',
    'application/views/payment/index.php',
    'application/views/profile/index.php',
    'application/views/reports/index.php',
    'application/views/settings/index.php',
    'application/views/students/index.php',
    'application/views/templates/footer.php',
    'application/views/templates/header.php',
    'application/views/templates/sidebar.php',
    'index.php',
];

$secret = $_POST['secret'] ?? $_GET['secret'] ?? '';

// Files that must also be synced into the pay/ subdirectory
$pay_subdir_files = [
    'application/controllers/Pay.php',
    'application/core/MY_Controller.php',
    'application/models/Payment_model.php',
    'application/models/Setting_model.php',
    'application/models/Student_model.php',
    'application/views/pay/index.php',
    'application/views/templates/footer.php',
    'application/views/templates/header.php',
    'application/views/templates/sidebar.php',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $secret === DEPLOY_SECRET) {
    $results = [];
    $ok = 0;
    $fail = 0;

    // ── DB Migrations ─────────────────────────────────────────────────────────
    $db_cfg = __DIR__ . '/application/config/database.php';
    if (file_exists($db_cfg)) {
        $db = [];
        require $db_cfg;   // populates $db['default']
        $cfg = $db['default'];
        try {
            $pdo = new PDO(
                "mysql:host={$cfg['hostname']};dbname={$cfg['database']};charset=utf8mb4",
                $cfg['username'], $cfg['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $migrations = [
                // expenses — add missing columns
                "ALTER TABLE `expenses` ADD COLUMN `bank_name`   VARCHAR(100)  DEFAULT NULL AFTER `reason`",
                "ALTER TABLE `expenses` ADD COLUMN `bank_account` VARCHAR(50)  DEFAULT NULL AFTER `bank_name`",
                "ALTER TABLE `expenses` ADD COLUMN `attachment`  VARCHAR(255)  DEFAULT NULL AFTER `bank_account`",
                // expense_items — add discount column
                "ALTER TABLE `expense_items` ADD COLUMN `discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `quantity`",
            ];
            foreach ($migrations as $sql) {
                try {
                    $pdo->exec($sql);
                    $results[] = "✅ DB migration OK: " . preg_replace('/\s+/',' ', substr($sql, 0, 70));
                    $ok++;
                } catch (PDOException $e) {
                    // "Duplicate column name" = already applied — not a failure
                    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                        $results[] = "ℹ️  DB already up-to-date: " . preg_replace('/ADD COLUMN.*/', '', $sql);
                    } else {
                        $results[] = "❌ DB migration FAIL: " . $e->getMessage();
                        $fail++;
                    }
                }
            }
        } catch (PDOException $e) {
            $results[] = "❌ DB connect failed: " . $e->getMessage();
            $fail++;
        }
    }

    // Deploy to main app
    foreach ($files as $file) {
        $url = GITHUB_RAW . $file;
        $content = @file_get_contents($url);
        if ($content === false) {
            $results[] = ['file' => $file, 'status' => 'FAIL - could not download'];
            $fail++;
            continue;
        }
        $dest = __DIR__ . '/' . $file;
        $dir = dirname($dest);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (file_put_contents($dest, $content) !== false) {
            $results[] = ['file' => $file, 'status' => 'OK'];
            $ok++;
        } else {
            $results[] = ['file' => $file, 'status' => 'FAIL - could not write'];
            $fail++;
        }
    }

    // Also sync critical files into pay/ subdirectory (separate CI3 install)
    $pay_dir = __DIR__ . '/pay/';
    if (is_dir($pay_dir)) {
        foreach ($pay_subdir_files as $file) {
            $dest = $pay_dir . $file;
            $dir = dirname($dest);
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            // Reuse already-downloaded content from main deploy
            $src = __DIR__ . '/' . $file;
            if (file_exists($src) && copy($src, $dest)) {
                $results[] = ['file' => 'pay/' . $file, 'status' => 'OK'];
                $ok++;
            } else {
                $results[] = ['file' => 'pay/' . $file, 'status' => 'FAIL - could not copy to pay/'];
                $fail++;
            }
        }
    }

    header('Content-Type: text/plain; charset=utf-8');
    echo "Deploy complete: $ok OK, $fail FAIL\n\n";
    foreach ($results as $r) {
        echo ($r['status'] === 'OK' ? '✓' : '✗') . ' ' . $r['file'] . ' — ' . $r['status'] . "\n";
    }
    if ($fail === 0) {
        echo "\nAll files updated. You can delete this deploy.php now.\n";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>IT Finance — Deploy</title>
<style>
body{font-family:sans-serif;max-width:500px;margin:60px auto;padding:0 20px;background:#f0f2f8}
.card{background:white;border-radius:14px;padding:30px;box-shadow:0 2px 10px rgba(0,0,0,.1)}
h2{margin:0 0 8px;color:#0f172a}
p{color:#64748b;font-size:14px;margin:0 0 24px}
input{width:100%;border:1.5px solid #e2e8f0;border-radius:9px;padding:10px 14px;font-size:14px;box-sizing:border-box;outline:none}
input:focus{border-color:#3b82f6}
button{margin-top:12px;width:100%;padding:11px;background:#3b82f6;color:white;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer}
button:hover{background:#2563eb}
</style>
</head>
<body>
<div class="card">
  <h2>🚀 IT Finance Deploy</h2>
  <p>ดึงไฟล์ล่าสุดจาก GitHub มาติดตั้งบน server</p>
  <form method="POST">
    <input type="password" name="secret" placeholder="รหัสผ่าน deploy..." required>
    <button type="submit">Deploy ตอนนี้</button>
  </form>
</div>
</body>
</html>
