-- ============================================================
-- IT Finance System — Production Schema (Clean)
-- Version 1.0 | สาขาวิชาเทคโนโลยีสารสนเทศ
-- ============================================================

CREATE DATABASE IF NOT EXISTS `it`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `it`;

-- ──────────────────────────────────────────────────────────
-- Users
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(20)  DEFAULT NULL,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(100) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('student','activity_staff','academic_staff','treasurer',
                    'head_it','advisor','auditor','super_admin') DEFAULT 'student',
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ──────────────────────────────────────────────────────────
-- Students
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `students` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(20)  NOT NULL UNIQUE,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(100) DEFAULT NULL,
  `user_id`    INT          DEFAULT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ──────────────────────────────────────────────────────────
-- Payment Records
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `payment_records` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `student_id`  VARCHAR(20)   NOT NULL,
  `year`        INT           NOT NULL,
  `month`       INT           NOT NULL,
  `status`      ENUM('none','pending','paid','overdue') NOT NULL DEFAULT 'pending',
  `amount`      DECIMAL(10,2) NOT NULL DEFAULT 50.00,
  `penalty`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `paid_date`   DATE          DEFAULT NULL,
  `slip_file`   VARCHAR(255)  DEFAULT NULL,
  `reviewed_by` INT           DEFAULT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_student_month` (`student_id`, `year`, `month`),
  FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ──────────────────────────────────────────────────────────
-- Expenses
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `expenses` (
  `id`             VARCHAR(20)   PRIMARY KEY,
  `title`          VARCHAR(200)  NOT NULL,
  `department`     VARCHAR(100)  DEFAULT NULL,
  `category`       VARCHAR(100)  DEFAULT NULL,
  `requester_id`   VARCHAR(20)   DEFAULT NULL,
  `requester_name` VARCHAR(100)  DEFAULT NULL,
  `amount`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status`         ENUM('draft','submitted','pending','approved','rejected','completed')
                                 NOT NULL DEFAULT 'draft',
  `expense_date`   DATE          DEFAULT NULL,
  `reason`         TEXT          DEFAULT NULL,
  `reject_note`    TEXT          DEFAULT NULL,
  `slip_file`      VARCHAR(255)  DEFAULT NULL,
  `approved_by`    INT           DEFAULT NULL,
  `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `expense_items` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `expense_id` VARCHAR(20)   NOT NULL,
  `item_name`  VARCHAR(200)  NOT NULL,
  `price`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `quantity`   INT           NOT NULL DEFAULT 1,
  FOREIGN KEY (`expense_id`) REFERENCES `expenses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ──────────────────────────────────────────────────────────
-- Fund Ledger
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `fund_ledger` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `entry_date`  VARCHAR(50)   NOT NULL,
  `txn_date`    DATE          DEFAULT NULL,
  `title`       VARCHAR(200)  NOT NULL,
  `type`        ENUM('income','expense') NOT NULL,
  `income`      DECIMAL(10,2) DEFAULT NULL,
  `expense`     DECIMAL(10,2) DEFAULT NULL,
  `balance`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `note`        TEXT          DEFAULT NULL,
  `created_by`  INT           DEFAULT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ──────────────────────────────────────────────────────────
-- System Settings
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
  `key`        VARCHAR(100) PRIMARY KEY,
  `value`      TEXT         DEFAULT NULL,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DEFAULT SETTINGS (ค่าเริ่มต้น — แก้ไขได้ในหน้าตั้งค่า)
-- ============================================================
INSERT INTO `settings` (`key`, `value`) VALUES
  ('monthly_fee',      '50'),
  ('due_day',          '8'),
  ('penalty_per_day',  '5'),
  ('academic_year',    '2568'),
  ('active_months',    '1,3,4,5,6,7,8,9,10,11')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- ============================================================
-- DEFAULT ADMIN ACCOUNT
-- Email: admin@it.ku.th | Password: Admin@2568
-- ⚠️ เปลี่ยนรหัสผ่านทันทีหลังเข้าสู่ระบบครั้งแรก
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password`, `role`, `student_id`) VALUES
  ('ผู้ดูแลระบบ', 'admin@it.ku.th',
   '$2y$10$VNDIMIz0CyTcvlbUfaVlju71ZTqI7qxVIlHLzBuZpZyafEGHJqsxG',
   'super_admin', NULL)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
