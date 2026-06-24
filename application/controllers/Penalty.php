<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penalty extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Payment_model','Student_model']);
    }

    public function index() {
        $this->require_login();
        $years = $this->Payment_model->get_available_years($this->acad_year);
        $year  = (int)($this->input->get('year') ?: $this->acad_year);
        if (!in_array($year, $years)) $year = $years[0];

        if ($this->can('view_all')) {
            $this->_all_view($year, $years);
        } else {
            $this->_student_view($year, $years);
        }
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

    // ─── Staff / Treasurer: all students ─────────────────────────────
    private function _all_view($year, $years) {
        $this->require_login();
        $search  = trim($this->input->get('search') ?: '');
        $status  = $this->input->get('status') ?: 'overdue';

        $cases   = $this->Payment_model->get_penalty_cases($year, $status, $search);
        $summary = $this->Payment_model->get_penalty_totals($year);

        $this->render('penalty/all', [
            'title'   => 'ภาพรวมค่าปรับ',
            'cases'   => $cases,
            'summary' => $summary,
            'year'    => $year,
            'years'   => $years,
            'search'  => $search,
            'status'  => $status,
        ]);
    }
}
