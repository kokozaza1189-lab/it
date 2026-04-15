# req.md — IT Department Finance System (Final Consolidated Requirements)

โครงการ: ระบบการเงินสาขาวิชา IT (สาขาเดียว) ภายในมหาวิทยาลัย
เวอร์ชัน: Final Consolidated v2
ภาษา: ไทย / English
แพลตฟอร์ม: Mobile-First Web + PWA

---

## 1) Objectives

* จัดการรายรับ รายจ่าย เงินค้าง และเงินกลางของสาขา IT
* รองรับชำระเงินรายเดือนผ่าน Mobile Banking
* รองรับจ่ายล่วงหน้าแบบเลือกเดือนเอง
* คิดค่าปรับเฉพาะเดือนที่ค้าง
* รองรับระบบเบิกเงินและอนุมัติโดยเหรัญญิก / แอดมิน
* รองรับอัปโหลดสลิป ใบเสร็จ บิล PDF
* รองรับ 2 ภาษา ไทย / อังกฤษ
* รองรับมือถือเต็มรูปแบบ

## 2) Roles (Final)

1. Super Admin
2. Treasurer
3. Head of IT
4. Advisor
5. Student
6. Auditor
7. Activity Staff
8. Academic Staff

**Expense approval**

* Treasurer = approver หลัก
* Super Admin = override / force approve

## 3) Authentication

* Signup ได้ด้วยตนเอง
* ใช้อีเมลมหาวิทยาลัย
* Email verification
* Login ด้วย email หรือ student id
* Forgot password
* Role default = Student
* Contact support email
* Optional 2FA
* Mobile biometric login

## 4) Payment System

### 4.1 Monthly Payment

* ยอดชำระคงที่ **50 บาท/เดือน** (Super Admin ปรับค่าได้)
* แสดงสถานะเดือนที่จ่ายแล้ว
* แสดงยอดต้องชำระรายเดือน
* แสดงค่าปรับของเดือนที่ค้าง (แยกรายเดือน)
* แสดงยอดรวมทั้งหมดที่ต้องชำระ (กรณีค้างหลายเดือน) เพื่อให้จ่ายทีเดียวได้

### 4.2 Advance Payment

* ผู้ใช้เลือกเดือนที่จะจ่ายได้เอง
* ไม่บังคับ FIFO
* เลือกข้ามเดือนได้
* ระบบรวมยอดอัตโนมัติ

### 4.3 Penalty (ค่าปรับ)

* ครบกำหนดชำระ = **วันที่ 8 ของทุกเดือน** (เฉพาะช่วงเปิดการศึกษา)
* เริ่มคิดค่าปรับตั้งแต่ **วันที่ 9** ของเดือนนั้น
* อัตรา = **5 บาท/วัน**
* คิดเฉพาะเดือนที่ค้าง แยกคำนวณทีละเดือน
* **Cap**: หยุดนับเมื่อขึ้นเดือนใหม่ (ค่าปรับของเดือนนั้นถูก fixed)
* เดือนถัดไปเริ่มคิดใหม่หากยังค้างชำระ

**ตัวอย่าง:**
```
ค้างเดือน มี.ค. → ครบกำหนด 8 มี.ค.
  คิดค่าปรับ 5 บาท/วัน ตั้งแต่ 9 มี.ค. ถึง 31 มี.ค. = 23 วัน = 115 บาท (cap)

ขึ้น 1 เม.ย. → ค่าปรับ มี.ค. = 115 บาท (หยุดคิดแล้ว)
  เดือน เม.ย. เริ่มนับใหม่ → ถ้าไม่จ่าย เริ่มคิดตั้งแต่ 9 เม.ย.
```

**การแสดงผลกรณีค้างหลายเดือน:**
```
เดือน ก.พ. : 50 + 115 บาท (ค่าปรับ) = 165 บาท
เดือน มี.ค. : 50 + 115 บาท (ค่าปรับ) = 165 บาท
เดือน เม.ย. : 50 + 45 บาท (ค่าปรับ 9 วัน) = 95 บาท
─────────────────────────────────
ยอดรวมทั้งหมด : 425 บาท
```

### 4.4 Academic Calendar (ช่วงเปิดการศึกษา)

* Super Admin กำหนดช่วงเปิด–ปิดการศึกษาเองในระบบ
* ระบบคิดค่าปรับ และเรียกเก็บเงิน **เฉพาะช่วงเปิดการศึกษาเท่านั้น**
* ช่วงปิดเทอม: หยุดคิดค่าปรับ และไม่มียอดเรียกเก็บเดือนนั้น

### 4.5 Payment Channels

* จ่ายผ่าน Mobile Banking เท่านั้น
* ไม่มี wallet / credit balance ในเว็บ
* รองรับ QR พร้อมเพย์ / เลขบัญชี

### 4.6 Slip Upload

* อัปโหลดสลิปจากมือถือ
* OCR อ่านยอด / วันเวลา / Ref
* ถ้า OCR อ่านไม่ได้ → ส่งให้ Treasurer ตรวจสอบเอง (manual review)
* ตรวจสลิปซ้ำ
* Flag สลิปน่าสงสัย

### 4.7 Month Status Table

* Paid
* Overdue
* Advance Paid
* Pending Verification

## 5) Expense / Reimbursement System

### 5.1 Request Form

* ผู้เบิก
* ฝ่าย (กิจกรรม / การเรียน)
* จำนวนเงิน
* ประเภทสินค้า
* ชื่อสินค้า
* ราคาสินค้า
* จำนวน
* ราคารวม
* เหตุผลการเบิก
* วันที่เบิก

### 5.2 Evidence

* สลิปโอน
* ใบเสร็จ
* บิล PDF
* invoice
* รูปสินค้า

### 5.3 Central Fund (เงินกลาง)

* **ที่มา**: เงินสะสมจากทุกคนที่ชำระผ่านระบบ
* **ยอดปัจจุบัน**: 7,138.37 บาท (ณ วันเริ่มต้นระบบ)
* **ผู้อัปเดตยอด**: Super Admin เท่านั้น
* แสดงยอดเงินกลางคงเหลือ
* แสดงยอดรออนุมัติ
* กันงบติดลบ (ไม่อนุมัติถ้าเงินไม่พอ)

### 5.4 Approval Flow

```text
Draft
-> Submitted
-> Pending Treasurer
-> Approved by Treasurer  (Treasurer อนุมัติจ่ายคืนให้ผู้เบิก)
-> Ledger Posted
-> Completed
```

**Admin Override**

```text
Submitted
-> Pending Treasurer
-> Super Admin Force Approve
-> Ledger Posted
```

### 5.5 Budget Control

* อนุมัติได้เมื่อเงินกลางเพียงพอเท่านั้น

## 6) RBAC (Final)

### Student
* ดูยอดชำระของตัวเอง
* อัปโหลดสลิป
* จ่ายล่วงหน้าเลือกเดือนเอง

### Requester (Activity Staff / Academic Staff)
* create request
* edit before submit
* attach evidence
* view own requests

### Treasurer
* approve / reject expense
* request more evidence
* reverse transaction (อนุมัติจ่ายเงินคืนให้ผู้เบิก)
* manual review สลิปที่ OCR อ่านไม่ได้
* export ledger

### Auditor
* ดูยอดเงินคงเหลือ / ยอดใช้จ่าย (read-only)
* ดูรายชื่อนิสิตทั้งหมด
* ดูสถานะการชำระเงินรายคน (จ่ายแล้ว / ยังไม่จ่าย)

### Head of IT / Advisor
* ตรวจสอบกระแสการเงิน (cashflow)
* ดูยอดเงินคงเหลือ / ใช้จ่าย
* ดูรายชื่อผู้ชำระและผู้ค้างชำระ
* ตรวจสอบรายการเบิกเงินทั้งหมด
* (read-only ทั้งหมด)

### Super Admin
* force approve
* manual adjust ยอดเงินกลาง
* กำหนดช่วงเปิด-ปิดการศึกษา
* กำหนด / ปรับยอดชำระรายเดือน (default 50 บาท)
* reverse
* all access

**Permission Keys**

```text
expense.create
expense.submit
expense.approve
expense.reject
expense.force_approve
expense.ledger.export
expense.reverse
payment.view_own
payment.view_all
payment.status.update
fund.view
fund.adjust
academic_calendar.manage
settings.monthly_fee
```

## 7) Bill / Receipt Management

* อัปโหลดรูป / PDF
* OCR อ่านชื่อร้าน วันที่ รายการสินค้า ราคา
* ถ้า OCR อ่านไม่ได้ → ส่ง Treasurer ตรวจ manual
* จำแนกประเภทสินค้า
  * อุปกรณ์ IT
  * งบกิจกรรม
  * การเรียน / Lab
  * อาหาร / เดินทาง
* ตรวจบิลซ้ำ
* วิเคราะห์ราคาเฉลี่ยย้อนหลัง

## 8) Dashboard & Ledger

### Dashboard Cards

* รายรับรวม
* รายจ่ายรวม
* เงินค้าง
* เงินกลางคงเหลือ
* รออนุมัติเบิก
* ค่าปรับรวม

### Ledger

* รายการรายรับ
* รายการรายจ่าย
* รายการเบิก
* ย้อนดูรายเดือน
* Export PDF / Excel / CSV

## 9) Mobile-First Requirements

* Responsive mobile-first
* Bottom navigation
* กล้องถ่ายสลิป / บิล
* PWA installable
* รองรับเน็ตช้า
* lazy load image
* offline draft upload
* biometric login
* in-app notification

## 10) 2 Languages (TH/EN)

**Supported**

* th (default)
* en

**Features**

* language switcher
* translate all pages
* month names TH/EN
* payment labels TH/EN
* expense approval TH/EN
* export report TH/EN
* notification TH/EN
* save preferred_language per user

## 11) User Management

* Import รายชื่อนิสิตผ่าน Excel / CSV
* Bulk add users
* Quick role assignment
* Multi-select assign role
* Search by student id / name / year

## 12) Notifications

### LINE
* เตือนก่อนครบกำหนด (ก่อนวันที่ 8)
* เตือนค้างชำระ
* เตือนสลิปอนุมัติ
* เตือนเบิกเงินผ่าน

### Email
* signup verification
* reset password
* overdue payment
* expense approved

## 13) Audit & Security

* Audit log ทุกการแก้ไข
* Login history
* Device tracking
* bcrypt / argon2 password hash
* rate limit
* lock failed login
* session timeout
* role-based route guard
* file virus scan (optional)

## 14) Recommended Stack

### Frontend
* Next.js
* React
* Tailwind CSS
* PWA
* next-intl / i18next

### Backend
* Node.js / NestJS
* PostgreSQL → **MySQL 5.7** (MAMP local)
* Prisma

### Database
* Host: 127.0.0.1
* Port: 3306
* Database: `it`
* Username: `paisan`
* Password: `08032550`

### Integrations
* LINE OA API
* Google Drive
* OCR engine
* PromptPay QR
* Cloudinary / S3

## 15) Future Enhancements

* AI ตรวจสลิปปลอม
* AI จำแนกหมวดบิล
* วิเคราะห์ผู้ค้างชำระ
* Forecast cash flow
* ระบบ stock อุปกรณ์ Lab
* BI dashboard
