<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Payment_model','Expense_model','Fund_model','Student_model']);
    }

    public function index() {
        $this->require_login();
        $user = $this->get_user();
        $year = 2568;

        $stats    = $this->Payment_model->get_stats($year);
        $balance  = $this->Fund_model->get_balance();
        $pending  = $this->Expense_model->get_pending_count();
        $monthly  = $this->Payment_model->get_monthly_income($year);
        $ledger   = $this->Fund_model->get_ledger();
        $expenses = $this->Expense_model->get_all(['status' => 'pending']);
        $overdue  = $this->Payment_model->get_all_overdue($year);

        // My payment info (for student role)
        $my_payments = [];
        if (in_array($user['role'], ['student','activity_staff','academic_staff'])) {
            $my_payments = $this->Payment_model->get_by_student($user['student_id'], $year);
        }

        $data = [
            'title'       => 'Dashboard — IT Finance',
            'stats'       => $stats,
            'balance'     => $balance,
            'pending_exp' => $pending,
            'monthly'     => $monthly,
            'ledger'      => array_slice($ledger, 0, 5),
            'expenses'    => array_slice($expenses, 0, 5),
            'overdue'     => $overdue,
            'my_payments' => $my_payments,
            'year'        => $year,
        ];
        $this->render('dashboard/index', $data);
    }
}
