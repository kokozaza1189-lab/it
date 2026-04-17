<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Payment_model','Fund_model','Student_model','Expense_model']);
    }

    public function index() {
        $this->require_role(['treasurer','head_it','advisor','auditor','super_admin']);
        $year   = (int)($this->input->get('year') ?: $this->acad_year);
        $active = array_map('intval', explode(',', $this->settings['active_months'] ?? '1,2,3,4'));
        $th_months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        $monthly = [];
        $total_income  = 0;
        $total_overdue = 0;
        foreach ($active as $m) {
            $s = $this->Payment_model->get_month_summary($year, $m);
            $total_income  += $s['income'];
            $total_overdue += $s['overdue'];
            $monthly[$m] = array_merge($s, ['label' => $th_months[$m]]);
        }
        $total_students = $this->Student_model->count();
        $balance        = $this->Fund_model->get_balance();
        $overdue_list   = $this->Payment_model->get_all_overdue($year);
        $fund_ledger    = $this->Fund_model->get_ledger();
        $exp_stats      = $this->Expense_model->get_stats();
        $this->render('reports/index', [
            'title'          => 'รายงานสรุป',
            'monthly'        => $monthly,
            'active_months'  => $active,
            'total_income'   => $total_income,
            'total_overdue'  => $total_overdue,
            'total_students' => $total_students,
            'balance'        => $balance,
            'overdue_list'   => $overdue_list,
            'fund_ledger'    => $fund_ledger,
            'exp_stats'      => $exp_stats,
        ]);
    }
}
