<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penalty extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Payment_model','Student_model']);
    }

    public function index() {
        $this->require_login();
        // ทุก role เข้ามาเช็ค/ชำระค่าปรับของตัวเองได้ (แอดมินที่ไม่มีรหัสนิสิตจะเห็นว่าไม่มีค่าปรับ)
        $years = $this->Payment_model->get_available_years($this->acad_year);
        $year  = (int)($this->input->get('year') ?: $this->acad_year);
        if (!in_array($year, $years)) $year = $years[0];
        $this->_student_view($year, $years);
    }

    // ─── Student: pay-a-penalty form (looks like /pay, uses session id) ──
    public function pay($month) {
        $this->require_login();
        $user  = $this->get_user();
        $month = (int)$month;
        $year  = (int)($this->input->get('year') ?: $this->acad_year);
        $rec   = $this->Payment_model->get_month($user['student_id'], $year, $month);

        $fee     = $rec ? (float)$rec->amount  : (($month === 1)
                     ? (float)($this->settings['fee_january'] ?? 35)
                     : (float)($this->settings['monthly_fee']  ?? 50));
        $penalty = $rec ? (float)$rec->penalty : 0.0;

        // Due-date / overdue calc (same rule as Pay.php)
        $due_day = (int)($this->settings['due_day'] ?? 8);
        $ce_year = ($month <= 5) ? ($year - 543 + 1) : ($year - 543);
        $due_ts  = mktime(0, 0, 0, $month, $due_day, $ce_year);
        $now     = time();
        $is_past_due  = $now > $due_ts;
        $days_overdue = $is_past_due ? max(0, (int)(($now - $due_ts) / 86400)) : 0;
        $days_left    = !$is_past_due ? (int)(($due_ts - $now) / 86400) : 0;

        $this->render('penalty/payform', [
            'title'        => 'ชำระค่าปรับ',
            'month'        => $month,
            'year'         => $year,
            'fee'          => $fee,
            'penalty'      => $penalty,
            'total'        => $fee + $penalty,
            'status'       => $rec->status    ?? 'none',
            'slip_file'    => $rec->slip_file  ?? null,
            'due_day'      => $due_day,
            'is_past_due'  => $is_past_due,
            'days_overdue' => $days_overdue,
            'days_left'    => $days_left,
        ]);
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
