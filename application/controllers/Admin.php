<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Student_model','Payment_model','Fund_model','User_model']);
    }

    // ──────────────────────────── STUDENT MANAGEMENT ────────────────────────────

    public function students() {
        $this->require_role(['treasurer','super_admin']);
        $search   = $this->input->get('search') ?: '';
        $students = $this->Student_model->get_all($search);
        $this->render('admin/students', [
            'title'    => 'จัดการนิสิต',
            'students' => $students,
            'search'   => $search,
            'total'    => $this->Student_model->count(),
        ]);
    }

    public function add_student() {
        $this->require_role(['treasurer','super_admin']);
        $sid   = trim($this->input->post('student_id', TRUE));
        $name  = trim($this->input->post('name', TRUE));
        $email = trim($this->input->post('email', TRUE));
        if (!$sid || !$name) { $this->json(['error' => 'กรุณากรอกข้อมูลให้ครบ'], 400); return; }
        if ($this->Student_model->student_id_exists($sid)) {
            $this->json(['error' => 'รหัสนิสิต ' . $sid . ' มีอยู่แล้วในระบบ'], 400); return;
        }
        $this->Student_model->add(['student_id' => $sid, 'name' => $name, 'email' => $email ?: null]);
        $this->json(['success' => true, 'student_id' => $sid, 'name' => $name]);
    }

    public function edit_student() {
        $this->require_role(['treasurer','super_admin']);
        $sid  = trim($this->input->post('student_id', TRUE));
        $name = trim($this->input->post('name', TRUE));
        $email = trim($this->input->post('email', TRUE));
        if (!$sid || !$name) { $this->json(['error' => 'กรุณากรอกข้อมูล'], 400); return; }
        $this->Student_model->update($sid, ['name' => $name, 'email' => $email ?: null]);
        $this->json(['success' => true]);
    }

    public function delete_student() {
        $this->require_role(['super_admin','treasurer']);
        $sid = trim($this->input->post('student_id', TRUE));
        $this->Student_model->delete($sid);
        $this->json(['success' => true]);
    }

    // CSV import: POST file 'csv'
    public function import_students() {
        $this->require_role(['treasurer','super_admin']);
        if (empty($_FILES['csv']['tmp_name'])) {
            $this->json(['error' => 'ไม่พบไฟล์'], 400); return;
        }
        $handle = fopen($_FILES['csv']['tmp_name'], 'r');
        // detect BOM & encoding
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);
        $rows = []; $first = true;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if ($first) { $first = false; continue; } // skip header
            $rows[] = $row;
        }
        fclose($handle);
        $result = $this->Student_model->import_csv($rows, (float)($this->settings['monthly_fee'] ?? 50));
        $this->json(['success' => true, 'added' => $result['added'], 'skipped' => $result['skipped']]);
    }

    // Excel import: POST JSON rows [[student_id, name, email], ...]
    public function import_students_json() {
        $this->require_role(['treasurer','super_admin']);
        $body = json_decode(file_get_contents('php://input'), true);
        $rows = $body['rows'] ?? [];
        if (empty($rows)) { $this->json(['error' => 'ไม่พบข้อมูล'], 400); return; }
        $result = $this->Student_model->import_csv($rows, (float)($this->settings['monthly_fee'] ?? 50));
        $this->json(['success' => true, 'added' => $result['added'], 'skipped' => $result['skipped']]);
    }

    // ──────────────────────────── PAYMENT GENERATION ────────────────────────────

    public function payments() {
        $this->require_role(['treasurer','super_admin']);
        $year   = (int)($this->input->get('year') ?: $this->acad_year);
        $active = array_map('intval', explode(',', $this->settings['active_months'] ?? '1,2,3,4'));
        $th_months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        $summaries = [];
        foreach ($active as $m) {
            $s = $this->Payment_model->get_month_summary($year, $m);
            $summaries[$m] = array_merge($s, ['label' => $th_months[$m]]);
        }
        $total_students = $this->Student_model->count();
        $this->render('admin/payments', [
            'title'          => 'จัดการการชำระเงิน',
            'summaries'      => $summaries,
            'active_months'  => $active,
            'total_students' => $total_students,
        ]);
    }

    // AJAX: generate payment records for one month
    public function generate_month() {
        $this->require_role(['treasurer','super_admin']);
        $year   = (int)$this->input->post('year');
        $month  = (int)$this->input->post('month');
        $amount = (float)($this->input->post('amount') ?: $this->settings['monthly_fee'] ?? 50);
        $n = $this->Payment_model->generate_month($year, $month, $amount);
        $this->json(['success' => true, 'created' => $n]);
    }

    // AJAX: mark pending → overdue for a month
    public function mark_overdue() {
        $this->require_role(['treasurer','super_admin']);
        $year  = (int)$this->input->post('year');
        $month = (int)$this->input->post('month');
        $n = $this->Payment_model->mark_overdue($year, $month);
        $this->json(['success' => true, 'updated' => $n]);
    }

    // AJAX: recalculate penalties for overdue records
    public function recalc_penalties() {
        $this->require_role(['treasurer','super_admin']);
        $year    = (int)$this->input->post('year');
        $month   = (int)$this->input->post('month');
        $penalty = (float)($this->settings['penalty_per_day'] ?? 5);
        $due_day = (int)($this->settings['due_day'] ?? 8);
        $days = $this->Payment_model->recalc_penalties($year, $month, $penalty, $due_day);
        $this->json(['success' => true, 'days_overdue' => $days]);
    }

    // AJAX: get all payment records for a month (for detail modal)
    public function get_month_detail() {
        $this->require_role(['treasurer','super_admin']);
        $year  = (int)$this->input->get('year');
        $month = (int)$this->input->get('month');
        if (!$year || !$month) { $this->json(['error' => 'invalid params'], 400); return; }
        $records = $this->db
            ->select('pr.*, s.name as student_name')
            ->from('payment_records pr')
            ->join('students s', 'pr.student_id = s.student_id')
            ->where('pr.year', $year)
            ->where('pr.month', $month)
            ->order_by('s.name')
            ->get()->result();
        $this->json(['records' => array_map(fn($r) => [
            'id'         => (int)$r->id,
            'student_id' => $r->student_id,
            'name'       => $r->student_name,
            'status'     => $r->status,
            'amount'     => (float)$r->amount,
            'penalty'    => (float)$r->penalty,
            'paid_date'  => $r->paid_date ?? '',
            'slip_file'  => $r->slip_file ?? '',
        ], $records)]);
    }

    // AJAX: update a specific payment record (amount + penalty + status)
    public function update_payment_record() {
        $this->require_role(['treasurer','super_admin']);
        $id        = (int)$this->input->post('id');
        $status    = $this->input->post('status');
        $amount    = $this->input->post('amount');
        $penalty   = $this->input->post('penalty');
        $paid_date = $this->input->post('paid_date') ?: null;
        if (!$id) { $this->json(['error' => 'invalid id'], 400); return; }
        $valid = ['none','pending','paid','overdue'];
        if (!in_array($status, $valid)) { $this->json(['error' => 'invalid status'], 400); return; }
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($amount  !== null && $amount  !== '') $data['amount']  = (float)$amount;
        if ($penalty !== null && $penalty !== '') $data['penalty'] = (float)$penalty;
        if ($paid_date) $data['paid_date'] = $paid_date;
        $this->db->where('id', $id)->update('payment_records', $data);
        $this->json(['success' => true]);
    }

    // Seed January 2569 (year=2568, month=1) data from PDF records
    public function seed_january() {
        $this->require_role(['super_admin','treasurer']);
        $year = 2568; $month = 1;
        // [student_id, amount, penalty, status]
        // penalty = OUTSTANDING balance (0 = fully settled)
        $rows = [
            ['6821651931',35,0,'paid'],['6821651949',35,0,'paid'],['6821651957',35,0,'paid'],
            ['6821651965',35,0,'paid'],['6821651973',35,0,'paid'],['6821651981',35,0,'paid'],
            ['6821651990',35,0,'paid'],['6821652007',35,0,'paid'],['6821652015',35,0,'paid'],
            ['6821652023',35,0,'paid'],['6821652031',35,0,'paid'],['6821652040',35,0,'paid'],
            ['6821652058',35,0,'paid'],  // paid 40 (35+5 penalty, settled)
            ['6821652066',35,0,'paid'],['6821652074',35,0,'paid'],['6821652082',35,0,'paid'],
            ['6821652112',35,0,'paid'],['6821652139',35,0,'paid'],['6821652147',35,0,'paid'],
            ['6821652155',35,100,'overdue'], // ยังไม่จ่าย — ค้าง 135
            ['6821652163',35,0,'paid'],['6821652171',35,0,'paid'],['6821652180',35,0,'paid'],
            ['6821652198',35,0,'paid'],['6821652201',35,0,'paid'],['6821652210',35,0,'paid'],
            ['6821652228',35,0,'paid'],['6821652236',35,0,'paid'],['6821652244',35,0,'paid'],
            ['6821652252',35,0,'paid'],['6821652261',35,0,'paid'],['6821652279',35,0,'paid'],
            ['6821652287',35,0,'paid'],
            ['6821652295',35,10,'paid'],   // จ่าย 50 แต่ยังค้างค่าปรับ 10
            ['6821652309',35,0,'paid'],['6821652317',35,0,'paid'],['6821652325',35,0,'paid'],
            ['6821652333',35,0,'paid'],['6821652341',35,0,'paid'],['6821652350',35,0,'paid'],
            ['6821652376',35,0,'paid'],['6821652384',35,0,'paid'],['6821652392',35,0,'paid'],
            ['6821652406',35,0,'paid'],  // paid 60 (35+25 penalty, settled)
            ['6821652414',35,0,'paid'],['6821652422',35,0,'paid'],['6821652431',35,0,'paid'],
            ['6821652449',35,0,'paid'],
            ['6821652457',35,0,'paid'],  // was red in PDF — paid after PDF
            ['6821652465',35,0,'paid'],['6821652473',35,0,'paid'],['6821652481',35,0,'paid'],
            ['6821652490',35,0,'paid'],['6821652503',35,0,'paid'],['6821652511',35,0,'paid'],
            ['6821652520',35,0,'paid'],['6821652546',35,0,'paid'],
            ['6821652554',35,0,'paid'],  // paid 70 (35+35 penalty, settled)
            ['6821652571',35,0,'paid'],['6821652589',35,0,'paid'],['6821652597',35,0,'paid'],
            ['6821652601',35,0,'paid'],['6821652619',35,0,'paid'],['6821652627',35,0,'paid'],
            ['6821652635',35,0,'paid'],['6821652643',35,0,'paid'],['6821652651',35,0,'paid'],
            ['6821652660',35,0,'paid'],['6821652678',35,0,'paid'],['6821652686',35,0,'paid'],
            ['6821652694',35,0,'paid'],['6821652708',35,0,'paid'],['6821652716',35,0,'paid'],
            ['6821652724',35,100,'overdue'], // ยังไม่จ่าย — ค้าง 135
            ['6821652732',35,0,'paid'],['6821652741',35,0,'paid'],['6821652759',35,0,'paid'],
            ['6821652767',35,0,'paid'],['6821652775',35,0,'paid'],['6821652783',35,0,'paid'],
            ['6821652791',35,0,'paid'],['6821652805',35,0,'paid'],['6821652813',35,0,'paid'],
            ['6821652821',35,0,'paid'],['6821656258',35,0,'paid'],['6821656274',35,0,'paid'],
            ['6821656282',35,0,'paid'],  // paid 70 (35+35 penalty, settled)
            ['6821656291',35,0,'paid'],
            ['6821656304',35,0,'paid'],  // paid 45 (35+10 penalty, settled)
            ['6821656312',35,0,'paid'],['6821656321',35,0,'paid'],
            ['6821656339',35,25,'paid'], // จ่าย 70 แต่ยังค้างค่าปรับ 25
            ['6821656347',35,0,'paid'],
            ['6821656363',35,100,'overdue'], // ยังไม่จ่าย — ค้าง 135
            ['6821656398',35,0,'paid'],
        ];
        $updated = 0; $inserted = 0;
        foreach ($rows as [$sid, $amount, $penalty, $status]) {
            $existing = $this->db->where('student_id',$sid)->where('year',$year)->where('month',$month)->get('payment_records')->row();
            $rec = ['amount'=>$amount,'penalty'=>$penalty,'status'=>$status,'updated_at'=>date('Y-m-d H:i:s')];
            if ($status === 'paid') $rec['paid_date'] = '2025-01-31';
            if ($existing) {
                $this->db->where('id', $existing->id)->update('payment_records', $rec);
                $updated++;
            } else {
                $rec = array_merge($rec, ['student_id'=>$sid,'year'=>$year,'month'=>$month]);
                $this->db->insert('payment_records', $rec);
                $inserted++;
            }
        }
        $this->json(['success'=>true,'updated'=>$updated,'inserted'=>$inserted,'total'=>count($rows)]);
    }

    // Seed March 2569 (year=2568, month=3) data from PDF records
    public function seed_march() {
        $this->require_role(['super_admin','treasurer']);
        $year = 2568; $month = 3;
        // [student_id, amount, penalty, status]
        // RED  → status='overdue', amount=50, penalty=0  (haven't paid at all)
        // YELLOW → status='paid', amount=50, penalty=X   (paid base, owe outstanding penalty)
        // Others → status='paid', amount=50, penalty=0
        $rows = [
            ['6821651931',50,0,'paid'],['6821651949',50,0,'paid'],
            ['6821651957',50,15,'paid'],  // ค้างค่าปรับ 15
            ['6821651965',50,0,'paid'],['6821651973',50,0,'paid'],['6821651981',50,0,'paid'],
            ['6821651990',50,0,'paid'],['6821652007',50,0,'paid'],['6821652015',50,0,'paid'],
            ['6821652023',50,0,'paid'],['6821652031',50,0,'paid'],['6821652040',50,0,'paid'],
            ['6821652058',50,0,'paid'],['6821652066',50,0,'paid'],
            ['6821652074',50,55,'paid'],  // ค้างค่าปรับ 55
            ['6821652082',50,0,'paid'],
            ['6821652112',50,0,'overdue'], // ยังไม่จ่าย
            ['6821652139',50,0,'paid'],['6821652147',50,0,'paid'],
            ['6821652155',50,10,'paid'],  // ค้างค่าปรับ 10
            ['6821652163',50,0,'paid'],['6821652171',50,0,'paid'],['6821652180',50,0,'paid'],
            ['6821652198',50,0,'paid'],['6821652201',50,0,'paid'],['6821652210',50,0,'paid'],
            ['6821652228',50,0,'paid'],['6821652236',50,0,'paid'],['6821652244',50,0,'paid'],
            ['6821652252',50,0,'paid'],['6821652261',50,0,'paid'],['6821652279',50,0,'paid'],
            ['6821652287',50,0,'overdue'], // ยังไม่จ่าย
            ['6821652295',50,0,'paid'],['6821652309',50,0,'paid'],['6821652317',50,0,'paid'],
            ['6821652325',50,0,'paid'],['6821652333',50,0,'paid'],['6821652341',50,0,'paid'],
            ['6821652350',50,0,'paid'],['6821652376',50,0,'paid'],['6821652384',50,0,'paid'],
            ['6821652392',50,0,'paid'],
            ['6821652406',50,0,'overdue'], // ยังไม่จ่าย
            ['6821652414',50,0,'paid'],['6821652422',50,0,'paid'],['6821652431',50,0,'paid'],
            ['6821652449',50,0,'paid'],
            ['6821652457',50,10,'paid'],  // ค้างค่าปรับ 10
            ['6821652465',50,0,'paid'],['6821652473',50,0,'paid'],['6821652481',50,0,'paid'],
            ['6821652490',50,0,'paid'],['6821652503',50,0,'paid'],['6821652511',50,0,'paid'],
            ['6821652520',50,0,'paid'],['6821652546',50,0,'paid'],
            ['6821652554',50,0,'overdue'], // ยังไม่จ่าย
            ['6821652571',50,0,'paid'],
            ['6821652589',50,15,'paid'],  // ค้างค่าปรับ 15
            ['6821652597',50,0,'paid'],['6821652601',50,0,'paid'],['6821652619',50,0,'paid'],
            ['6821652627',50,0,'paid'],['6821652635',50,0,'paid'],['6821652643',50,0,'paid'],
            ['6821652651',50,0,'paid'],['6821652660',50,0,'paid'],['6821652678',50,0,'paid'],
            ['6821652686',50,0,'paid'],['6821652694',50,0,'paid'],['6821652708',50,0,'paid'],
            ['6821652716',50,0,'paid'],['6821652724',50,0,'paid'],
            ['6821652732',50,60,'paid'],  // ค้างค่าปรับ 60
            ['6821652741',50,0,'paid'],
            ['6821652759',50,10,'paid'],  // ค้างค่าปรับ 10
            ['6821652767',50,0,'paid'],['6821652775',50,0,'paid'],
            ['6821652783',50,25,'paid'],  // ค้างค่าปรับ 25
            ['6821652791',50,0,'paid'],['6821652805',50,0,'paid'],['6821652813',50,0,'paid'],
            ['6821652821',50,0,'paid'],['6821656258',50,0,'paid'],['6821656274',50,0,'paid'],
            ['6821656282',50,0,'paid'],['6821656291',50,0,'paid'],['6821656304',50,0,'paid'],
            ['6821656312',50,15,'paid'],  // ค้างค่าปรับ 15
            ['6821656321',50,0,'paid'],
            ['6821656339',50,0,'overdue'], // ยังไม่จ่าย
            ['6821656347',50,0,'paid'],
            ['6821656363',50,0,'overdue'], // ยังไม่จ่าย
            ['6821656398',50,0,'paid'],
        ];
        $updated = 0; $inserted = 0;
        foreach ($rows as [$sid, $amount, $penalty, $status]) {
            $existing = $this->db->where('student_id',$sid)->where('year',$year)->where('month',$month)->get('payment_records')->row();
            $rec = ['amount'=>$amount,'penalty'=>$penalty,'status'=>$status,'updated_at'=>date('Y-m-d H:i:s')];
            if ($status === 'paid') $rec['paid_date'] = '2025-03-31';
            if ($existing) {
                $this->db->where('id', $existing->id)->update('payment_records', $rec);
                $updated++;
            } else {
                $rec = array_merge($rec, ['student_id'=>$sid,'year'=>$year,'month'=>$month]);
                $this->db->insert('payment_records', $rec);
                $inserted++;
            }
        }
        $this->json(['success'=>true,'updated'=>$updated,'inserted'=>$inserted,'total'=>count($rows)]);
    }

    // ──────────────────────────── USER MANAGEMENT ────────────────────────────

    public function users() {
        $this->require_role(['treasurer','super_admin']);
        $search = $this->input->get('search') ?: '';
        $users  = $this->User_model->get_all($search);
        $this->render('admin/users', [
            'title'  => 'จัดการผู้ใช้งาน',
            'users'  => $users,
            'search' => $search,
            'total'  => $this->User_model->count_all(),
        ]);
    }

    public function add_user() {
        $this->require_role(['treasurer','super_admin']);
        $name       = trim($this->input->post('name', TRUE));
        $email      = trim($this->input->post('email', TRUE));
        $student_id = trim($this->input->post('student_id', TRUE)) ?: null;
        $role       = $this->input->post('role');
        $password   = $this->input->post('password');
        $valid_roles = ['student','activity_staff','academic_staff','treasurer','head_it','advisor','auditor','super_admin'];
        if (!$name || !$email || !$password || !in_array($role, $valid_roles)) {
            $this->json(['error' => 'กรุณากรอกข้อมูลให้ครบ'], 400); return;
        }
        if ($this->db->where('email', $email)->count_all_results('users') > 0) {
            $this->json(['error' => 'อีเมลนี้มีในระบบแล้ว'], 400); return;
        }
        $id = $this->User_model->create([
            'name'       => $name,
            'email'      => $email,
            'student_id' => $student_id,
            'role'       => $role,
            'password'   => $password,
            'is_active'  => 1,
        ]);
        $this->json(['success' => true, 'id' => $id]);
    }

    public function edit_user() {
        $this->require_role(['treasurer','super_admin']);
        $id         = (int)$this->input->post('id');
        $name       = trim($this->input->post('name', TRUE));
        $email      = trim($this->input->post('email', TRUE));
        $student_id = trim($this->input->post('student_id', TRUE)) ?: null;
        $role       = $this->input->post('role');
        $password   = $this->input->post('password');
        $valid_roles = ['student','activity_staff','academic_staff','treasurer','head_it','advisor','auditor','super_admin'];
        if (!$id || !$name || !$email || !in_array($role, $valid_roles)) {
            $this->json(['error' => 'ข้อมูลไม่ครบ'], 400); return;
        }
        if ($this->User_model->email_exists_for_other($email, $id)) {
            $this->json(['error' => 'อีเมลนี้ถูกใช้โดยผู้ใช้อื่นแล้ว'], 400); return;
        }
        $data = ['name' => $name, 'email' => $email, 'student_id' => $student_id, 'role' => $role];
        if ($password) {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }
        $this->User_model->update($id, $data);
        $this->json(['success' => true]);
    }

    public function toggle_user() {
        $this->require_role(['super_admin']);
        $id = (int)$this->input->post('id');
        $this->User_model->toggle_active($id);
        $this->json(['success' => true]);
    }

    public function delete_user() {
        $this->require_role(['super_admin']);
        $id         = (int)$this->input->post('id');
        $current_id = (int)$this->session->userdata('user_id');
        if ($id === $current_id) {
            $this->json(['error' => 'ไม่สามารถลบบัญชีของตัวเองได้'], 400); return;
        }
        $target = $this->User_model->get_by_id($id);
        if ($target && $target->role === 'super_admin') {
            $super_count = $this->db->where('role', 'super_admin')->where('is_active', 1)->count_all_results('users');
            if ($super_count <= 1) {
                $this->json(['error' => 'ไม่สามารถลบ Super Admin คนสุดท้ายได้'], 400); return;
            }
        }
        $this->User_model->delete($id);
        $this->json(['success' => true]);
    }

    // ──────────────────────────── DATA MANAGEMENT ────────────────────────────

    // Clear all transaction data (keep students + users)
    public function clear_transactions() {
        $this->require_role(['super_admin']);
        $this->db->empty_table('expense_items');
        $this->db->empty_table('expenses');
        $this->db->empty_table('payment_records');
        $this->db->empty_table('fund_ledger');
        $this->session->set_flashdata('success', 'ลบข้อมูลธุรกรรมทั้งหมดเรียบร้อย');
        $this->json(['success' => true]);
    }

    // Clear all student data (also deletes payments)
    public function clear_students() {
        $this->require_role(['super_admin']);
        $this->db->empty_table('payment_records');
        $this->db->empty_table('students');
        $this->json(['success' => true]);
    }
}
