<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pay — Standalone public payment slip form (no login required)
 * Students can submit payment slips directly from this page.
 */
class Pay extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Student_model','Payment_model','Setting_model']);
        $this->load->library(['session']);
        $this->load->helper(['url','form']);
    }

    public function index() {
        $settings   = $this->Setting_model->get_all();
        $month      = (int)($this->input->get('month') ?: date('n'));
        $year       = (int)($this->input->get('year')  ?: ((int)date('Y') + 543));
        $monthly_fee = (float)($settings['monthly_fee'] ?? 50);
        $due_day     = (int)($settings['due_day'] ?? 8);
        $penalty_per_day = (float)($settings['penalty_per_day'] ?? 5);

        // Calculate penalty
        $now = time();
        $due_date = mktime(0, 0, 0, $month, $due_day, $year - 543);
        $days_overdue = 0;
        $penalty = 0;
        if ($now > $due_date) {
            // Cap at end of month
            $end_of_month = mktime(0, 0, 0, $month + 1, 1, $year - 543) - 86400;
            $cap = min($now, $end_of_month);
            $days_overdue = max(0, (int)(($cap - $due_date) / 86400));
            $penalty = round($days_overdue * $penalty_per_day, 2);
        }

        $month_names = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                        7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
        $this->load->view('pay/index', [
            'title'           => 'ฟอร์มชำระเงิน — สาขา IT',
            'settings'        => $settings,
            'month'           => $month,
            'year'            => $year,
            'monthly_fee'     => $monthly_fee,
            'due_day'         => $due_day,
            'penalty_per_day' => $penalty_per_day,
            'days_overdue'    => $days_overdue,
            'penalty'         => $penalty,
            'total'           => $monthly_fee + $penalty,
            'month_names'     => $month_names,
        ]);
    }

    // AJAX: look up student by ID
    public function lookup() {
        $sid = trim($this->input->get('id', TRUE));
        if (!$sid) { $this->_json(['found' => false]); return; }
        $s = $this->db->where('student_id', $sid)->get('students')->row();
        if ($s) {
            $this->_json(['found' => true, 'name' => $s->name, 'student_id' => $s->student_id]);
        } else {
            $this->_json(['found' => false]);
        }
    }

    // POST: submit payment slip
    public function submit() {
        $sid   = trim($this->input->post('student_id', TRUE));
        $month = (int)$this->input->post('month');
        $year  = (int)$this->input->post('year');

        if (!$sid || !$month || !$year) {
            $this->_json(['error' => 'ข้อมูลไม่ครบ'], 400); return;
        }

        $s = $this->db->where('student_id', $sid)->get('students')->row();
        if (!$s) { $this->_json(['error' => 'ไม่พบรหัสนิสิต'], 404); return; }

        $file = '';
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
            } else {
                $this->_json(['error' => 'อัปโหลดไฟล์ไม่สำเร็จ: '.$this->upload->display_errors()], 400); return;
            }
        }

        $this->Payment_model->submit_payment($sid, $year, $month, $file);
        $this->_json(['success' => true, 'name' => $s->name]);
    }

    private function _json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
