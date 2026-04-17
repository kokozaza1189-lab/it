<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Student_model','Payment_model','Fund_model']);
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
