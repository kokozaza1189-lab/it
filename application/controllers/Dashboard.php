<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Payment_model','Expense_model','Fund_model','Student_model']);
    }

    public function index() {
        $this->require_login();
        $user  = $this->get_user();
        $years = $this->Payment_model->get_available_years($this->acad_year);
        $year  = (int)($this->input->get('year') ?: $this->acad_year);
        // Clamp to known years so bad GET params can't cause issues
        if (!in_array($year, $years)) $year = $years[0];

        $stats    = $this->Payment_model->get_stats($year);
        $balance  = $this->Fund_model->get_balance();
        $pending  = $this->Expense_model->get_pending_count();
        $monthly  = $this->Payment_model->get_monthly_income($year);
        $ledger   = $this->Fund_model->get_ledger();
        $expenses = $this->Expense_model->get_all(['status' => 'pending']);
        $overdue  = $this->Payment_model->get_all_overdue($year);

        $my_payments = [];
        if (!in_array($user['role'], ['treasurer','head_it','advisor','auditor','super_admin'])) {
            $my_payments = $this->Payment_model->get_by_student($user['student_id'], $year);
        }

        $this->render('dashboard/index', [
            'title'       => 'Dashboard — IT Finance',
            'year'        => $year,
            'years'       => $years,
            'stats'       => $stats,
            'balance'     => $balance,
            'pending_exp' => $pending,
            'monthly'     => $monthly,
            'ledger'      => array_slice($ledger, 0, 5),
            'expenses'    => array_slice($expenses, 0, 5),
            'overdue'     => $overdue,
            'my_payments' => $my_payments,
        ]);
    }
}
