<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fund extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Fund_model');
    }

    public function index() {
        $this->require_role(['treasurer','head_it','advisor','auditor','super_admin']);
        $ledger  = $this->Fund_model->get_ledger();
        $balance = $this->Fund_model->get_balance();
        $monthly = $this->Fund_model->get_monthly_summary($this->acad_year);
        $this->render('fund/index', [
            'title'   => 'เงินกลาง',
            'ledger'  => $ledger,
            'balance' => $balance,
            'monthly' => $monthly,
        ]);
    }

    public function adjust() {
        $this->require_role(['super_admin','treasurer']);
        if ($this->input->post()) {
            $type    = $this->input->post('type');
            $amount  = (float)$this->input->post('amount');
            $balance = $this->Fund_model->get_balance();
            $new_bal = $type === 'income' ? $balance + $amount : $balance - $amount;
            $th        = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
            $raw_date  = $this->input->post('txn_date') ?: date('Y-m-d');
            $ts        = strtotime($raw_date) ?: time();
            $entry_date = date('j', $ts) . ' ' . $th[(int)date('n', $ts)] . ' ' . (date('Y', $ts) + 543);
            $this->Fund_model->add_entry([
                'entry_date' => $entry_date,
                'txn_date'   => date('Y-m-d', $ts),
                'title'      => $this->input->post('title', TRUE),
                'type'       => $type,
                'income'     => $type === 'income' ? $amount : null,
                'expense'    => $type === 'expense' ? $amount : null,
                'balance'    => $new_bal,
                'note'       => $this->input->post('note', TRUE),
                'created_by' => $this->get_user()['id'],
            ]);
        }
        redirect('fund');
    }

    public function delete($id) {
        $this->require_role(['super_admin']);
        $this->Fund_model->delete_entry($id);
        // Recalculate all balances after deletion
        $this->Fund_model->recalc_balances();
        redirect('fund');
    }
}
