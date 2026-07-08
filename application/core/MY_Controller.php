<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    protected $role_map = [
        'student'        => ['label' => 'Student',       'color' => '#6366f1'],
        'activity_staff' => ['label' => 'Activity Staff','color' => '#8b5cf6'],
        'academic_staff' => ['label' => 'Academic Staff','color' => '#06b6d4'],
        'treasurer'      => ['label' => 'Treasurer',     'color' => '#f59e0b'],
        'head_it'        => ['label' => 'Head of IT',    'color' => '#10b981'],
        'advisor'        => ['label' => 'Advisor',       'color' => '#14b8a6'],
        'auditor'        => ['label' => 'Auditor',       'color' => '#ef4444'],
        'super_admin'    => ['label' => 'Super Admin',   'color' => '#f97316'],
    ];

    protected $settings  = [];
    protected $acad_year = 2569;

    public function __construct() {
        parent::__construct();
        $this->load->model('Setting_model');
        $this->settings  = $this->Setting_model->get_all();
        $this->acad_year = (int)($this->settings['academic_year'] ?? 2569);
    }

    protected function require_login() {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
            exit;
        }
    }

    protected function require_role($roles) {
        $this->require_login();
        $current = $this->session->userdata('role');
        if (!in_array($current, (array)$roles)) {
            show_error('คุณไม่มีสิทธิ์เข้าถึงหน้านี้', 403);
        }
    }

    protected function get_user() {
        return [
            'id'         => $this->session->userdata('user_id'),
            'name'       => $this->session->userdata('name'),
            'email'      => $this->session->userdata('email'),
            'role'       => $this->session->userdata('role'),
            'student_id' => $this->session->userdata('student_id'),
            'roleLabel'  => $this->role_map[$this->session->userdata('role')]['label'] ?? '',
            'color'      => $this->role_map[$this->session->userdata('role')]['color'] ?? '#6366f1',
        ];
    }

    protected function render($view, $data = []) {
        $data['current_user'] = $this->get_user();
        $data['page']         = $view;
        $data['settings']     = $this->settings;
        $data['acad_year']    = $this->acad_year;
        if (!isset($data['year'])) $data['year'] = $this->acad_year;
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/footer', $data);
    }

    protected function json($data, $status = 200) {
        // Send the body DIRECTLY. CI's set_output()/_display() pipeline is skipped by the
        // exit below, which would otherwise drop the body entirely (empty 200 response).
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data);
        exit;
    }

    /**
     * Return the fee for a given month.
     * Month 1 (January) uses the `fee_january` setting (default 35 ฿).
     * All other months use `monthly_fee` (default 50 ฿).
     */
    protected function fee_for_month($month) {
        $month = (int)$month;
        if ($month === 1) {
            return (float)($this->settings['fee_january'] ?? 35);
        }
        return (float)($this->settings['monthly_fee'] ?? 50);
    }

    /**
     * Weekend auto roll-up: once per weekend, when an admin opens the site,
     * sum every payment confirmed since the last sweep and book it as ONE
     * income line in the central fund. A watermark (fund_last_sweep_at) in
     * settings guarantees no payment is ever counted twice.
     *
     * First run only sets the baseline watermark (so already-booked history,
     * e.g. the manually-entered July collection, is never double counted).
     */
    protected function maybe_weekly_fund_sweep() {
        $role = $this->session->userdata('role');
        if (!in_array($role, ['treasurer','head_it','super_admin'])) return;

        $last = $this->settings['fund_last_sweep_at'] ?? '';
        if (!$last) {                                            // first admin visit: set baseline NOW
            $this->Setting_model->set('fund_last_sweep_at', date('Y-m-d H:i:s'));
            return;                                              // so pre-existing (manual) history is never booked again
        }
        if ((int)date('N') < 6) return;                          // book only on the weekend (Sat=6, Sun=7)
        if (strtotime($last) > strtotime('-3 days')) return;     // already swept this weekend

        $this->load->model('Fund_model');
        $res = $this->Fund_model->sweep_paid_since($last);
        if ($res && $res['amount'] > 0) {
            $th = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
            $ts = time();
            $this->Fund_model->add_entry([
                'entry_date' => date('j', $ts).' '.$th[(int)date('n', $ts)].' '.(date('Y', $ts) + 543),
                'txn_date'   => date('Y-m-d', $ts),
                'title'      => 'สรุปค่าห้อง+ค่าปรับ (อัตโนมัติ '.$res['count'].' รายการ)',
                'type'       => 'income',
                'income'     => $res['amount'],
                'expense'    => null,
                'balance'    => $this->Fund_model->get_balance() + $res['amount'],
                'note'       => 'รวมยอดชำระที่ยืนยันแล้วประจำสัปดาห์ (ระบบเพิ่มอัตโนมัติ)',
                'created_by' => $this->session->userdata('user_id'),
            ]);
            $this->Fund_model->recalc_balances();                // keep running balance correct
        }
        // advance watermark even when amount is 0 so we don't rescan the same window
        $this->Setting_model->set('fund_last_sweep_at', $res ? $res['watermark'] : date('Y-m-d H:i:s'));
        $this->settings = $this->Setting_model->get_all();       // refresh cache for this request
    }

    /**
     * Book a collected residual penalty into the central fund, then clear it from the
     * payment record (without bumping updated_at, so the already-swept fee is not
     * re-counted). Returns true if a penalty was booked, false if there was none.
     * Used by both student self-pay (auto-verified) and admin manual clearing.
     */
    protected function collect_penalty_to_fund($rec) {
        $amt = (float)($rec->penalty ?? 0);
        if ($amt <= 0 || empty($rec->id)) return false;
        $this->load->model(['Fund_model','Payment_model']);
        $srow = $this->db->select('name')->where('student_id', $rec->student_id)->get('students')->row();
        $name = $srow->name ?? ($rec->student_id ?? '');
        $th   = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        $ts   = time();
        $this->Fund_model->add_entry([
            'entry_date' => date('j', $ts).' '.$th[(int)date('n', $ts)].' '.(date('Y', $ts) + 543),
            'txn_date'   => date('Y-m-d', $ts),
            'title'      => 'ค่าปรับ '.$name.' ('.($th[(int)($rec->month ?? 0)] ?? '').')',
            'type'       => 'income',
            'income'     => $amt,
            'expense'    => null,
            'balance'    => $this->Fund_model->get_balance() + $amt,
            'note'       => 'ชำระค่าปรับคงค้าง',
            'created_by' => $this->session->userdata('user_id'),
        ]);
        $this->Fund_model->recalc_balances();
        $this->Payment_model->clear_penalty($rec->id);
        return true;
    }

    protected function can($action) {
        $role  = $this->session->userdata('role');
        $perms = [
            'create_expense'  => ['activity_staff','academic_staff','treasurer','head_it','advisor','super_admin'],
            'approve_expense' => ['treasurer','super_admin'],
            'edit_payment'    => ['treasurer','super_admin'],
            'view_all'        => ['activity_staff','academic_staff','treasurer','head_it','advisor','auditor','super_admin'],
            'manage_settings' => ['super_admin','treasurer'],
            'super'           => ['super_admin'],
        ];
        return in_array($role, $perms[$action] ?? []);
    }
}
