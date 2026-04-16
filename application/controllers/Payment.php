<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Payment_model','Student_model']);
    }

    // My payment page
    public function index() {
        $this->require_login();
        $user = $this->get_user();
        $year = 2568;
        $payments = $this->Payment_model->get_by_student($user['student_id'], $year);
        $data = [
            'title'    => 'การชำระเงินของฉัน',
            'payments' => $payments,
            'year'     => $year,
        ];
        $this->render('payment/index', $data);
    }

    // All payments overview (treasurer+)
    public function all() {
        $this->require_role(['treasurer','head_it','advisor','auditor','super_admin']);
        $year     = $this->input->get('year') ?: 2568;
        $search   = $this->input->get('search') ?: '';
        $students = $this->Student_model->get_with_payments($year);
        $stats    = $this->Payment_model->get_stats($year);
        $data = [
            'title'    => 'ภาพรวมการชำระเงิน',
            'students' => $students,
            'stats'    => $stats,
            'year'     => $year,
            'search'   => $search,
        ];
        $this->render('payment/all', $data);
    }

    // Submit payment slip (AJAX/POST)
    public function submit() {
        $this->require_login();
        $user  = $this->get_user();
        $month = (int)$this->input->post('month');
        $year  = (int)$this->input->post('year') ?: 2568;
        $file  = '';
        if (!empty($_FILES['slip']['name'])) {
            $config = [
                'upload_path'   => FCPATH . 'assets/uploads/slips/',
                'allowed_types' => 'jpg|jpeg|png|pdf',
                'max_size'      => 5120,
                'file_name'     => 'slip_' . $user['student_id'] . '_' . $year . '_' . $month . '_' . time(),
            ];
            $this->load->library('upload', $config);
            if ($this->upload->do_upload('slip')) {
                $file = $this->upload->data('file_name');
            }
        }
        $this->Payment_model->submit_payment($user['student_id'], $year, $month, $file);
        $this->json(['success' => true]);
    }

    // Update payment status (treasurer/admin, AJAX)
    public function update_status() {
        $this->require_role(['treasurer','super_admin']);
        $id     = $this->input->post('id');
        $status = $this->input->post('status');
        $date   = $this->input->post('paid_date') ?: null;
        $this->Payment_model->update_status($id, $status, $date);
        $this->json(['success' => true]);
    }
}
