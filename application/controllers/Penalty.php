<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penalty extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Payment_model','Student_model']);
    }

    public function index() {
        $this->require_login();
        // Staff/admin inspect penalties via the "ค่าปรับ" tab on ภาพรวมการชำระ (payment/all).
        if ($this->can('view_all')) {
            redirect('payment/all');
            return;
        }
        $years = $this->Payment_model->get_available_years($this->acad_year);
        $year  = (int)($this->input->get('year') ?: $this->acad_year);
        if (!in_array($year, $years)) $year = $years[0];
        $this->_student_view($year, $years);
    }

    // ─── Student: own penalties ───────────────────────────────────────
    private function _student_view($year, $years) {
        $user    = $this->get_user();
        $records = $this->Payment_model->get_by_student($user['student_id'], $year);

        $penalties = array_values(array_filter($records,
            fn($r) => in_array($r->status, ['overdue','pending'])
        ));

        $total_due = array_sum(array_map(
            fn($r) => $r->status === 'overdue' ? (float)$r->amount + (float)$r->penalty : 0,
            $penalties
        ));

        $this->render('penalty/index', [
            'title'     => 'ค่าปรับของฉัน',
            'penalties' => $penalties,
            'total_due' => $total_due,
            'year'      => $year,
            'years'     => $years,
        ]);
    }
}
