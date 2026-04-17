<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Students extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Student_model','Payment_model']);
    }

    public function index() {
        $this->require_role(['treasurer','head_it','advisor','auditor','super_admin']);
        $year   = (int)($this->input->get('year') ?: $this->acad_year);
        $search = $this->input->get('search') ?: '';
        $active = array_map('intval', explode(',', $this->settings['active_months'] ?? '1,2,3,4'));
        $students = $this->Student_model->get_with_payments($year, $active);
        if ($search) {
            $students = array_filter($students, fn($s) =>
                mb_stripos($s->name, $search) !== false ||
                mb_stripos($s->student_id, $search) !== false
            );
        }
        $this->render('students/index', [
            'title'         => 'รายชื่อนิสิต',
            'students'      => array_values($students),
            'search'        => $search,
            'active_months' => $active,
        ]);
    }

    public function update_payment() {
        $this->require_role(['treasurer','super_admin']);
        $id     = (int)$this->input->post('id');
        $status = $this->input->post('status');
        $date   = $this->input->post('paid_date') ?: null;
        $this->Payment_model->update_status($id, $status, $date);
        $this->json(['success' => true]);
    }
}
