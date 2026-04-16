<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Expense_model');
    }

    public function index() {
        $this->require_login();
        $user    = $this->get_user();
        $filters = [];
        if (!$this->can('view_all')) {
            $filters['requester'] = $user['student_id'];
        }
        $filters['status'] = $this->input->get('status') ?: '';
        $filters['search'] = $this->input->get('search') ?: '';
        $expenses = $this->Expense_model->get_all($filters);
        $stats    = $this->Expense_model->get_stats();
        $data = [
            'title'    => 'เบิกเงิน',
            'expenses' => $expenses,
            'stats'    => $stats,
            'filters'  => $filters,
        ];
        $this->render('expense/index', $data);
    }

    public function detail($id) {
        $this->require_login();
        $expense = $this->Expense_model->get_by_id($id);
        if (!$expense) show_404();
        $data = ['title' => 'รายละเอียดคำขอเบิก', 'expense' => $expense];
        $this->render('expense/detail', $data);
    }

    public function create() {
        $this->require_role(['activity_staff','academic_staff','super_admin']);
        if ($this->input->post()) {
            $items = [];
            $names  = $this->input->post('item_name');
            $prices = $this->input->post('item_price');
            $qtys   = $this->input->post('item_qty');
            if ($names) {
                foreach ($names as $i => $name) {
                    if (!empty($name)) {
                        $items[] = ['item_name' => $name, 'price' => (float)$prices[$i], 'quantity' => (int)$qtys[$i]];
                    }
                }
            }
            $user = $this->get_user();
            $total = array_sum(array_map(fn($it) => $it['price'] * $it['quantity'], $items));
            $id = $this->Expense_model->create([
                'title'          => $this->input->post('title', TRUE),
                'department'     => $this->input->post('department', TRUE),
                'category'       => $this->input->post('category', TRUE),
                'requester_id'   => $user['student_id'],
                'requester_name' => $user['name'],
                'amount'         => $total,
                'status'         => $this->input->post('submit_type') === 'submit' ? 'submitted' : 'draft',
                'expense_date'   => date('Y-m-d'),
                'reason'         => $this->input->post('reason', TRUE),
            ], $items);
            redirect('expense/' . $id);
        }
        $data = ['title' => 'สร้างคำขอเบิกเงิน'];
        $this->render('expense/create', $data);
    }

    public function approve($id) {
        $this->require_role(['treasurer','super_admin']);
        $this->Expense_model->update_status($id, 'approved', '', $this->get_user()['id']);
        redirect('expense/' . $id);
    }

    public function reject($id) {
        $this->require_role(['treasurer','super_admin']);
        $note = $this->input->post('note', TRUE);
        $this->Expense_model->update_status($id, 'rejected', $note);
        redirect('expense/' . $id);
    }

    public function complete($id) {
        $this->require_role(['treasurer','super_admin']);
        $this->Expense_model->update_status($id, 'completed', '', $this->get_user()['id']);
        redirect('expense/' . $id);
    }
}
