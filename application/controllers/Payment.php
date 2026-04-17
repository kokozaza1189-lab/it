<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Payment_model','Student_model']);
    }

    public function index() {
        $this->require_login();
        $user = $this->get_user();
        $year = $this->acad_year;
        $payments = $this->Payment_model->get_by_student($user['student_id'], $year);
        $this->render('payment/index', [
            'title'    => 'การชำระเงินของฉัน',
            'payments' => $payments,
        ]);
    }

    public function all() {
        $this->require_role(['treasurer','head_it','advisor','auditor','super_admin']);
        $year   = (int)($this->input->get('year') ?: $this->acad_year);
        $search = $this->input->get('search') ?: '';
        $students = $this->Student_model->get_with_payments($year);
        $stats    = $this->Payment_model->get_stats($year);
        $this->render('payment/all', [
            'title'    => 'ภาพรวมการชำระเงิน',
            'students' => $students,
            'stats'    => $stats,
            'search'   => $search,
        ]);
    }

    public function submit() {
        $this->require_login();
        $user  = $this->get_user();
        $month = (int)$this->input->post('month');
        $year  = (int)($this->input->post('year') ?: $this->acad_year);
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

    public function update_status() {
        $this->require_role(['treasurer','super_admin']);
        $id     = (int)$this->input->post('id');
        $status = $this->input->post('status');
        $date   = $this->input->post('paid_date') ?: null;
        $this->Payment_model->update_status($id, $status, $date);
        $this->json(['success' => true]);
    }
}
