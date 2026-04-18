-- ============================================================
-- Update payment records: March 2569 (month=3, year=2568)
-- Source: เเบบฟอร์มเก็บเงิน IT ภาคพิ - จำนวนเงิน มี.ค.69
-- Color legend: เขียว=จ่ายแล้ว, เหลือง=ค้างค่าปรับ, แดง=ค้างชำระ
-- ค่าธรรมเนียมมีนาคม = 50 บาท
-- ============================================================

-- Fix active_months setting
UPDATE settings SET value = '1,3,4' WHERE `key` = 'active_months';

-- -------------------------------------------------------
-- STEP 1: Mark ALL 95 March records as paid (80 คนจ่ายแล้ว)
-- -------------------------------------------------------
UPDATE payment_records
SET status='paid', amount=50, penalty=0
WHERE year=2568 AND month=3;

-- -------------------------------------------------------
-- STEP 2: 6 RED (สีแดง = ค้างชำระ) ยังไม่จ่ายอะไรเลย
-- -------------------------------------------------------
UPDATE payment_records
SET status='overdue', amount=50, penalty=0
WHERE year=2568 AND month=3 AND student_id IN (
  '6821652112',
  '6821652287',
  '6821652406',
  '6821652554',
  '6821656339',
  '6821656363'
);

-- -------------------------------------------------------
-- STEP 3: 9 YELLOW (สีเหลือง = ค้างค่าปรับ) จ่ายค่าธรรมเนียม 50 แล้ว แต่ยังค้างค่าปรับ
-- amount=0 (base already paid), penalty=owed
-- -------------------------------------------------------

UPDATE payment_records SET status='overdue', amount=0, penalty=55
WHERE year=2568 AND month=3 AND student_id='6821652074';  -- ณัฐกฤตา

UPDATE payment_records SET status='overdue', amount=0, penalty=15
WHERE year=2568 AND month=3 AND student_id='6821651957';  -- กันตภณ

UPDATE payment_records SET status='overdue', amount=0, penalty=10
WHERE year=2568 AND month=3 AND student_id='6821652155';  -- ธนดล

UPDATE payment_records SET status='overdue', amount=0, penalty=10
WHERE year=2568 AND month=3 AND student_id='6821652457';  -- พิญชาณี

UPDATE payment_records SET status='overdue', amount=0, penalty=10
WHERE year=2568 AND month=3 AND student_id='6821652759';  -- อธิคม

UPDATE payment_records SET status='overdue', amount=0, penalty=25
WHERE year=2568 AND month=3 AND student_id='6821652783';  -- อัครพล

UPDATE payment_records SET status='overdue', amount=0, penalty=60
WHERE year=2568 AND month=3 AND student_id='6821652732';  -- สิรภัทร ชินนาผา

UPDATE payment_records SET status='overdue', amount=0, penalty=15
WHERE year=2568 AND month=3 AND student_id='6821656312';  -- ปัณณวิชญ์

UPDATE payment_records SET status='overdue', amount=0, penalty=15
WHERE year=2568 AND month=3 AND student_id='6821652589';  -- ภาวิดา

-- -------------------------------------------------------
-- Verify results
-- -------------------------------------------------------
SELECT month, status, COUNT(*) AS cnt,
       SUM(amount) AS sum_amount, SUM(penalty) AS sum_penalty
FROM payment_records WHERE year=2568 AND month=3 GROUP BY status;
-- Expected: paid=80 (sum=4000), overdue=15 (6 red sum_amt=300, 9 yellow sum_pen=215)
