<?php
// One-time patch: updates deploy.php to latest version from GitHub, then self-deletes.
$raw = 'https://raw.githubusercontent.com/kokozaza1189-lab/it/master/deploy.php';
$content = @file_get_contents($raw);
if ($content === false) {
    die('❌ ดาวน์โหลด deploy.php ไม่สำเร็จ');
}
if (file_put_contents(__DIR__ . '/deploy.php', $content) === false) {
    die('❌ เขียนไฟล์ไม่สำเร็จ กรุณาตรวจสอบ permission');
}
@unlink(__FILE__);
echo '✅ deploy.php อัปเดตแล้ว — ไปรัน <a href="deploy.php">deploy.php</a> ได้เลย';
