<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->output->set_content_type('application/json');
    }

    // GET /api/students
    public function students() {
        $this->require_login();
        $this->load->model(['Student_model','Payment_model']);
        $year     = $this->input->get('year') ?: 2568;
        $students = $this->Student_model->get_with_payments($year);
        $this->json($students);
    }

    // GET /api/expenses
    public function expenses() {
        $this->require_login();
        $this->load->model('Expense_model');
        $filters = [
            'status' => $this->input->get('status') ?: '',
            'search' => $this->input->get('search') ?: '',
        ];
        if (!$this->can('view_all')) {
            $user = $this->get_user();
            $exps = $this->Expense_model->get_by_requester($user['student_id']);
        } else {
            $exps = $this->Expense_model->get_all($filters);
        }
        $this->json($exps);
    }

    // GET /api/fund
    public function fund() {
        $this->require_role(['treasurer','head_it','advisor','auditor','super_admin']);
        $this->load->model('Fund_model');
        $ledger  = $this->Fund_model->get_ledger();
        $balance = $this->Fund_model->get_balance();
        $this->json(['ledger' => $ledger, 'balance' => $balance]);
    }

    // GET /api/dashboard
    public function dashboard() {
        $this->require_login();
        $this->load->model(['Payment_model','Expense_model','Fund_model']);
        $year = 2568;
        $this->json([
            'payment_stats' => $this->Payment_model->get_stats($year),
            'fund_balance'  => $this->Fund_model->get_balance(),
            'pending_exp'   => $this->Expense_model->get_pending_count(),
            'monthly'       => $this->Payment_model->get_monthly_income($year),
        ]);
    }

    // POST /api/expense/status
    public function expense_status() {
        $this->require_role(['treasurer','super_admin']);
        $this->load->model('Expense_model');
        $id     = $this->input->post('id');
        $status = $this->input->post('status');
        $note   = $this->input->post('note', TRUE) ?: '';
        $this->Expense_model->update_status($id, $status, $note, $this->get_user()['id']);
        $this->json(['success' => true]);
    }

    // POST /api/payment/status
    public function payment_status() {
        $this->require_role(['treasurer','super_admin']);
        $this->load->model('Payment_model');
        $id     = $this->input->post('id');
        $status = $this->input->post('status');
        $date   = $this->input->post('paid_date') ?: null;
        $this->Payment_model->update_status($id, $status, $date);
        $this->json(['success' => true]);
    }
}
