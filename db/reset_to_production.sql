-- ============================================================
-- IT Finance System — Reset to Production
-- รันสคริปต์นี้เพื่อล้าง Demo Data และเริ่มต้นใช้งานจริง
-- ⚠️ คำเตือน: ข้อมูลทั้งหมดจะถูกลบอย่างถาวร
-- ============================================================
USE `it`;

-- ล้างข้อมูลทั้งหมด (เรียงตาม FK)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `expense_items`;
TRUNCATE TABLE `expenses`;
TRUNCATE TABLE `payment_records`;
TRUNCATE TABLE `fund_ledger`;
TRUNCATE TABLE `students`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- เพิ่ม admin account เริ่มต้น
-- Email: admin@it.ku.th | Password: Admin@2568
-- ⚠️ เปลี่ยนรหัสผ่านทันทีหลังเข้าสู่ระบบ
INSERT INTO `users` (`name`, `email`, `password`, `role`, `student_id`) VALUES
  ('ผู้ดูแลระบบ', 'admin@it.ku.th',
   '$2y$10$VNDIMIz0CyTcvlbUfaVlju71ZTqI7qxVIlHLzBuZpZyafEGHJqsxG',
   'super_admin', NULL);

-- ตั้งค่าระบบ (ไม่ล้าง)
INSERT INTO `settings` (`key`, `value`) VALUES
  ('monthly_fee',      '50'),
  ('due_day',          '8'),
  ('penalty_per_day',  '5'),
  ('academic_year',    '2568'),
  ('active_months',    '1,3,4,5,6,7,8,9,10,11')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

SELECT 'Reset complete. Login: admin@it.ku.th / Admin@2568' AS message;
