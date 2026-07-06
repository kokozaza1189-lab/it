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
        header('X-LiteSpeed-Cache-Control: no-cache');   // ensure fresh HTML (LiteSpeed ignores std Cache-Control)
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        // pay-own roles may only see their own data (not everyone's overview)
        if (in_array($this->session->userdata('role'), ['student','activity_staff','academic_staff'])) {
            redirect('payment');
            return;
        }
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

        // AUTO: keep the CURRENT month's penalty up to date on every overview load.
        // Only the current calendar month of the current academic year is recalculated
        // (recalc_penalties touches only 'overdue' rows), so past months' settled penalties stay intact.
        if ($year == $this->acad_year) {
            $cur_month = (int)date('n');
            if (in_array($cur_month, $active)) {
                $this->Payment_model->recalc_penalties(
                    $year, $cur_month,
                    (float)($this->settings['penalty_per_day'] ?? 5),
                    (int)($this->settings['due_day'] ?? 8)
                );
            }
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
            'penalty_page'  => ($tab_seg === 'penalty'),   // true = standalone ค่าปรับ page (/payment/penalty)
            'year'          => $year,
            'years'         => $years,
            'students'      => array_values($students),
            'stats'         => $stats,
            'search'        => $search,
            'active_months' => $active,
        ]);
    }

    // Central slip-review queue: every 'pending' payment (slip submitted, awaiting confirmation)
    public function pending() {
        $this->require_login();
        if (in_array($this->session->userdata('role'), ['student','activity_staff','academic_staff'])) {
            redirect('payment');
            return;
        }
        header('X-LiteSpeed-Cache-Control: no-cache');
        $rows = $this->db
            ->select('pr.id, pr.student_id, s.name, pr.year, pr.month, pr.amount, pr.penalty, pr.slip_file, pr.updated_at')
            ->from('payment_records pr')
            ->join('students s', 'pr.student_id = s.student_id')
            ->where('pr.status', 'pending')
            ->order_by('pr.updated_at', 'DESC')
            ->get()->result();
        $this->render('payment/pending', [
            'title' => 'รอตรวจสอบสลิป',
            'rows'  => $rows,
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

        // AUTO slip verification via SlipOK — if the slip is a real transfer of the
        // right amount to our account, mark it paid immediately; otherwise it stays
        // 'pending' and lands in the /payment/pending review queue.
        $auto = 'pending';
        if ($file) {
            $rec = $this->Payment_model->get_month($sid, $year, $month);
            $expected = $rec ? ((float)$rec->amount + (float)$rec->penalty) : $this->fee_for_month($month);
            if ($expected <= 0) $expected = $this->fee_for_month($month);
            $v = $this->_slipok_verify(FCPATH . 'assets/uploads/slips/' . $file);
            if ($v['verified'] && $rec && ($v['amount'] + 0.01) >= $expected) {
                $this->Payment_model->update_status($rec->id, 'paid', date('Y-m-d'), 0, null);
                $auto = 'paid';
            }
        }
        $this->json(['success' => true, 'auto' => $auto]);
    }

    // Verify a payment slip with SlipOK (returns ['verified'=>bool,'amount'=>float,...]).
    // Reads the real transfer amount + confirms it went to our registered account.
    private function _slipok_verify($local_path) {
        $key    = $this->settings['slipok_api_key']   ?? '';
        $branch = $this->settings['slipok_branch_id'] ?? '';
        if (!$key || !$branch || !is_file($local_path) || !function_exists('curl_init')) {
            return ['verified' => false, 'message' => 'no_config_or_file'];
        }
        $ch = curl_init('https://api.slipok.com/api/line/apikey/' . $branch);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_HTTPHEADER     => ['x-authorization: ' . $key],
            CURLOPT_POSTFIELDS     => ['files' => new CURLFile($local_path), 'log' => 'true'],
        ]);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $j = json_decode($resp, true) ?: [];
        $d = isset($j['data']) && is_array($j['data']) ? $j['data'] : $j;
        $amount = isset($d['amount']) ? (float)$d['amount'] : (isset($d['paidLocalAmount']) ? (float)$d['paidLocalAmount'] : 0);
        if ($http == 200 && !empty($j['success']) && $amount > 0) {
            return ['verified' => true, 'amount' => $amount, 'transRef' => $d['transRef'] ?? ''];
        }
        return ['verified' => false, 'amount' => $amount, 'message' => ($j['message'] ?? ('http ' . $http))];
    }

    public function update_status() {
        $this->require_role(['treasurer','head_it','super_admin']);
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
