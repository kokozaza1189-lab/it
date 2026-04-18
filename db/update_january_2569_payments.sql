-- ============================================================
-- Update payment records: January 2569 (month=1, year=2568)
-- Source: เเบบฟอร์มเก็บเงินIT ภาคพิ - จำนวนเงิน ม.ค.69
-- Color legend: เขียว=จ่ายแล้ว, เหลือง=ค้างค่าปรับ, แดง=ค้างชำระ
-- ค่าธรรมเนียมมกราคม = 35 บาท
-- ============================================================

-- STEP 1: Reset all month=1 to 'paid', amount=35, penalty=0 (ส่วนใหญ่จ่ายแล้ว)
UPDATE payment_records
SET status='paid', amount=35, penalty=0
WHERE year=2568 AND month=1;

-- STEP 2: 3 RED (สีแดง = ค้างชำระ) ยังไม่จ่ายอะไรเลย ค้าง 135 = 35 + ค่าปรับ 100
UPDATE payment_records
SET status='overdue', amount=35, penalty=100
WHERE year=2568 AND month=1 AND student_id IN (
  '6821652155',  -- ธนดล อินทร
  '6821652724',  -- สัชฌุกร พันธุ์โสภณ
  '6821656363'   -- สิรภัทร สวัสดิภาพพันธ์
);

-- STEP 3: 2 YELLOW (สีเหลือง = ค้างค่าปรับ) จ่ายค่าธรรมเนียม 35 แล้ว แต่ยังค้างค่าปรับ
-- amount=0 (base already paid), penalty=owed

-- 6821656339 ภัทรพล สามงามเสือ — ค้างค่าปรับ 25
UPDATE payment_records SET status='overdue', amount=0, penalty=25
WHERE year=2568 AND month=1 AND student_id='6821656339';

-- 6821652295 นภัสวรรณ ใจหาญ — ค้างค่าปรับ 10
UPDATE payment_records SET status='overdue', amount=0, penalty=10
WHERE year=2568 AND month=1 AND student_id='6821652295';

-- ตรวจสอบ
SELECT month, status, COUNT(*) AS cnt,
       SUM(amount) AS sum_amount, SUM(penalty) AS sum_penalty,
       SUM(amount+penalty) AS total_owed
FROM payment_records WHERE year=2568 AND month=1 GROUP BY status;
-- Expected: paid=90, overdue=5
