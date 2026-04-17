<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Payment_model','Expense_model','Student_model']);
    }

    public function index() {
        $this->require_login();
        $user  = $this->get_user();
        $year  = $this->acad_year;
        $role  = $user['role'];

        $overdue_payments  = [];
        $submitted_exp     = [];
        $pending_exp       = [];
        $my_overdue        = [];

        $treasurer_roles = ['treasurer','head_it','advisor','auditor','super_admin'];

        if (in_array($role, $treasurer_roles)) {
            $overdue_payments = $this->Payment_model->get_all_overdue($year);
            $submitted_exp    = $this->Expense_model->get_all(['status' => 'submitted']);
            $pending_exp      = $this->Expense_model->get_all(['status' => 'pending']);
        }

        // Personal overdue for students/staff
        if ($user['student_id']) {
            $my_payments = $this->Payment_model->get_by_student($user['student_id'], $year);
            foreach ($my_payments as $p) {
                if ($p->status === 'overdue') $my_overdue[] = $p;
            }
        }

        $this->render('notifications/index', [
            'title'            => 'การแจ้งเตือน',
            'overdue_payments' => $overdue_payments,
            'submitted_exp'    => $submitted_exp,
            'pending_exp'      => $pending_exp,
            'my_overdue'       => $my_overdue,
        ]);
    }
}
