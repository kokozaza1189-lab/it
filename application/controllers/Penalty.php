<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penalty extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Payment_model','Student_model']);
    }

    // roles ที่เห็น "ภาพรวมค่าปรับทั้งหมด" แทนค่าปรับส่วนตัว
    private $overview_roles = ['super_admin', 'head_it', 'treasurer'];

    public function index() {
        $this->require_login();
        // admin/หัวหน้าสาขา/เหรัญญิก → ดูภาพรวมค่าปรับทั้งหมด; role อื่นเช็ค/ชำระของตัวเอง
        if (in_array($this->get_user()['role'] ?? '', $this->overview_roles)) {
            redirect('payment/penalty');
            return;
        }
        $years = $this->Payment_model->get_available_years($this->acad_year);
        $year  = (int)($this->input->get('year') ?: $this->acad_year);
        if (!in_array($year, $years)) $year = $years[0];
        $this->_student_view($year, $years);
    }

    // ─── Student: pay-a-penalty form (looks like /pay, uses session id) ──
    public function pay($month) {
        $this->require_login();
        $user  = $this->get_user();
        if (in_array($user['role'] ?? '', $this->overview_roles)) { redirect('payment/penalty'); return; }
        $month = (int)$month;
        $year  = (int)($this->input->get('year') ?: $this->acad_year);
        $rec   = $this->Payment_model->get_month($user['student_id'], $year, $month);

        $fee     = $rec ? (float)$rec->amount  : (($month === 1)
                     ? (float)($this->settings['fee_january'] ?? 35)
                     : (float)($this->settings['monthly_fee']  ?? 50));
        $penalty = $rec ? (float)$rec->penalty : 0.0;
        // Fee already settled → this is a penalty-only payment (charge just the penalty)
        $fee_paid = $rec && $rec->status === 'paid';

        // Due-date / overdue calc (same rule as Pay.php)
        $due_day = (int)($this->settings['due_day'] ?? 8);
        $ce_year = ($month <= 5) ? ($year - 543 + 1) : ($year - 543);
        $due_ts  = mktime(0, 0, 0, $month, $due_day, $ce_year);
        $now     = time();
        $is_past_due  = $now > $due_ts;
        $days_overdue = $is_past_due ? max(0, (int)(($now - $due_ts) / 86400)) : 0;
        // whole calendar days from today (midnight) to due date — 1 Aug → 6 Aug = 5, not 4
        $days_left    = !$is_past_due ? max(0, (int)round(($due_ts - mktime(0, 0, 0)) / 86400)) : 0;

        $this->render('penalty/payform', [
            'title'        => 'ชำระค่าปรับ',
            'month'        => $month,
            'year'         => $year,
            'fee'          => $fee,
            'penalty'      => $penalty,
            'total'        => $fee_paid ? $penalty : ($fee + $penalty),
            'fee_paid'     => $fee_paid,
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
                   || ($r->status === 'paid' && (float)$r->penalty > 0)   // fee paid, penalty still owed
        ));

        $total_due = array_sum(array_map(function($r) {
            if ($r->status === 'overdue') return (float)$r->amount + (float)$r->penalty;
            if ($r->status === 'paid')    return (float)$r->penalty;   // residual penalty only (fee already paid)
            return 0;                                                  // pending → awaiting review
        }, $penalties));

        $this->render('penalty/index', [
            'title'     => 'ค่าปรับของฉัน',
            'penalties' => $penalties,
            'total_due' => $total_due,
            'year'      => $year,
            'years'     => $years,
        ]);
    }
}
