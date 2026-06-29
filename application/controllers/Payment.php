<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Payment_model','Student_model']);
    }

    public function index() {
        $this->require_login();
        $user   = $this->get_user();
        $years  = $this->Payment_model->get_available_years($this->acad_year);
        $year   = (int)($this->input->get('year') ?: $this->acad_year);
        if (!in_array($year, $years)) $year = $years[0];
        $active = $this->_parse_months($this->settings['active_months'] ?? '', [1, 3]);
        $all    = $this->Payment_model->get_by_student($user['student_id'], $year);
        // Only surface months that are configured for collection
        $payments = array_values(array_filter($all, fn($p) => in_array((int)$p->month, $active)));
        $this->render('payment/index', [
            'title'    => 'การชำระเงินของฉัน',
            'year'     => $year,
            'years'    => $years,
            'payments' => $payments,
        ]);
    }

    public function all($tab_seg = null) {
        $this->require_login();
        $years  = $this->Payment_model->get_available_years($this->acad_year);
        $year   = (int)($this->input->get('year') ?: $this->acad_year);
        if (!in_array($year, $years)) $year = $years[0];
        $search = $this->input->get('search') ?: '';

        // For current year use active_months setting; for past years detect from DB
        $setting_months = $this->_parse_months($this->settings['active_months'] ?? '', [1, 2, 3, 4]);
        if ($year != $this->acad_year) {
            $q = $this->db->select('month')->distinct()->where('year', $year)
                          ->order_by('month', 'ASC')->get('payment_records');
            $db_months = $q ? array_map(fn($r) => (int)$r->month, $q->result()) : [];
            $active = !empty($db_months) ? $db_months : $setting_months;
        } else {
            $active = $setting_months;
        }

        $students = $this->Student_model->get_with_payments($year, $active);
        if ($search) {
            $students = array_filter($students, fn($s) =>
                mb_stripos($s->name, $search) !== false ||
                mb_stripos($s->student_id, $search) !== false
            );
        }
        $stats = $this->Payment_model->get_stats($year);
        // tab via clean path segment (/payment/penalty) survives the host's query-param stripping
        $tab   = ($tab_seg === 'penalty') ? 'penalty' : $this->input->get('tab');
        $this->render('payment/all', [
            'title'         => ($tab === 'penalty') ? 'ค่าปรับ' : 'ภาพรวมการชำระเงิน',
            'active_tab'    => $tab,
            'year'          => $year,
            'years'         => $years,
            'students'      => array_values($students),
            'stats'         => $stats,
            'search'        => $search,
            'active_months' => $active,
        ]);
    }

    public function submit() {
        $this->require_login();
        $user  = $this->get_user();
        $month = (int)$this->input->post('month');
        $year  = (int)($this->input->post('year') ?: $this->acad_year);
        $sid   = $user['student_id'];
        if (!$sid) { $this->json(['success' => false, 'error' => 'บัญชีนี้ไม่มีรหัสนิสิต'], 400); return; }
        $file  = '';
        if (!empty($_FILES['slip']['name'])) {
            $config = [
                'upload_path'   => FCPATH . 'assets/uploads/slips/',
                'allowed_types' => 'jpg|jpeg|png|pdf',
                'max_size'      => 5120,
                'file_name'     => 'slip_' . $sid . '_' . $year . '_' . $month . '_' . time(),
            ];
            $this->load->library('upload', $config);
            if ($this->upload->do_upload('slip')) {
                $file = $this->upload->data('file_name');
            }
        }
        $this->Payment_model->submit_payment($sid, $year, $month, $file);
        $this->json(['success' => true]);
    }

    public function update_status() {
        $this->require_role(['treasurer','super_admin']);
        $id      = (int)$this->input->post('id');
        $status  = $this->input->post('status');
        $date    = $this->input->post('paid_date') ?: null;
        $penalty = $this->input->post('penalty') !== null && $this->input->post('penalty') !== ''
                   ? (float)$this->input->post('penalty') : null;
        $amount  = $this->input->post('amount') !== null && $this->input->post('amount') !== ''
                   ? (float)$this->input->post('amount') : null;
        $this->Payment_model->update_status($id, $status, $date, $penalty, $amount);
        $this->json(['success' => true]);
    }

    /**
     * Parse comma-separated month setting safely.
     * Filters out zeros and non-month numbers; falls back to $default if empty.
     */
    private function _parse_months($raw, array $default = [1,2,3,4]) {
        $months = array_values(array_filter(
            array_map('intval', explode(',', $raw)),
            fn($m) => $m >= 1 && $m <= 12
        ));
        return empty($months) ? $default : $months;
    }
}
