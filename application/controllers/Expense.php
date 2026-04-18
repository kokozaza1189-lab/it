<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Expense_model','Fund_model']);
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
        $this->render('expense/index', [
            'title'    => 'เบิกเงิน',
            'expenses' => $expenses,
            'stats'    => $stats,
            'filters'  => $filters,
        ]);
    }

    public function detail($id) {
        $this->require_login();
        $expense = $this->Expense_model->get_by_id($id);
        if (!$expense) show_404();
        $this->render('expense/detail', [
            'title'   => 'รายละเอียดคำขอเบิก',
            'expense' => $expense,
        ]);
    }

    public function create() {
        $this->require_role(['activity_staff','academic_staff','super_admin']);
        if ($this->input->post()) {
            $names  = $this->input->post('item_name')  ?: [];
            $prices = $this->input->post('item_price') ?: [];
            $qtys   = $this->input->post('item_qty')   ?: [];
            $items  = [];
            foreach ($names as $i => $name) {
                if (!empty(trim($name))) {
                    $items[] = [
                        'item_name' => trim($name),
                        'price'     => (float)($prices[$i] ?? 0),
                        'quantity'  => (int)($qtys[$i] ?? 1),
                    ];
                }
            }
            $user  = $this->get_user();
            $total = array_sum(array_map(fn($it) => $it['price'] * $it['quantity'], $items));
            $id    = $this->Expense_model->create([
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
        $this->render('expense/create', ['title' => 'สร้างคำขอเบิกเงิน']);
    }

    public function edit($id) {
        $this->require_login();
        $expense = $this->Expense_model->get_by_id($id);
        if (!$expense) show_404();
        $user = $this->get_user();
        // Only requester or super_admin can edit draft
        if ($expense->status !== 'draft') {
            show_error('แก้ไขได้เฉพาะคำขอที่เป็นร่างเท่านั้น', 403);
        }
        if ($expense->requester_id !== $user['student_id'] && $user['role'] !== 'super_admin') {
            show_error('คุณไม่มีสิทธิ์แก้ไขคำขอนี้', 403);
        }
        if ($this->input->post()) {
            $names  = $this->input->post('item_name')  ?: [];
            $prices = $this->input->post('item_price') ?: [];
            $qtys   = $this->input->post('item_qty')   ?: [];
            $items  = [];
            foreach ($names as $i => $name) {
                if (!empty(trim($name))) {
                    $items[] = [
                        'item_name' => trim($name),
                        'price'     => (float)($prices[$i] ?? 0),
                        'quantity'  => (int)($qtys[$i] ?? 1),
                    ];
                }
            }
            $total  = array_sum(array_map(fn($it) => $it['price'] * $it['quantity'], $items));
            $status = $this->input->post('submit_type') === 'submit' ? 'submitted' : 'draft';
            $this->Expense_model->update_expense($id, [
                'title'      => $this->input->post('title', TRUE),
                'department' => $this->input->post('department', TRUE),
                'category'   => $this->input->post('category', TRUE),
                'reason'     => $this->input->post('reason', TRUE),
                'amount'     => $total,
                'status'     => $status,
            ], $items);
            redirect('expense/' . $id);
        }
        $this->render('expense/edit', [
            'title'   => 'แก้ไขคำขอเบิกเงิน',
            'expense' => $expense,
        ]);
    }

    // Treasurer picks up submitted request → pending (under review)
    public function pending($id) {
        $this->require_role(['treasurer','super_admin']);
        $expense = $this->Expense_model->get_by_id($id);
        if ($expense && $expense->status === 'submitted') {
            $this->Expense_model->update_status($id, 'pending', '', $this->get_user()['id']);
        }
        if ($this->input->is_ajax_request()) {
            $this->json(['success' => true]);
        } else {
            redirect('expense/' . $id);
        }
    }

    public function approve($id) {
        $this->require_role(['treasurer','super_admin']);
        $expense = $this->Expense_model->get_by_id($id);
        if (!$expense) { $this->json(['error' => 'not found'], 404); return; }
        $this->Expense_model->update_status($id, 'approved', '', $this->get_user()['id']);
        // Auto-create fund ledger entry
        $balance = $this->Fund_model->get_balance();
        $this->Fund_model->add_entry([
            'entry_date' => $this->_thai_date(),
            'txn_date'   => date('Y-m-d'),
            'title'      => 'อนุมัติเบิก: ' . $expense->title,
            'type'       => 'expense',
            'income'     => null,
            'expense'    => $expense->amount,
            'balance'    => $balance - $expense->amount,
            'note'       => 'Ref: ' . $id,
            'created_by' => $this->get_user()['id'],
        ]);
        if ($this->input->is_ajax_request()) {
            $this->json(['success' => true]);
        } else {
            redirect('expense/' . $id);
        }
    }

    public function reject($id) {
        $note = $this->input->post('note', TRUE) ?: 'ปฏิเสธ';
        $this->Expense_model->update_status($id, 'rejected', $note);
        if ($this->input->is_ajax_request()) {
            $this->json(['success' => true]);
        } else {
            redirect('expense/' . $id);
        }
    }

    public function complete($id) {
        $this->require_role(['treasurer','super_admin']);
        $this->Expense_model->update_status($id, 'completed', '', $this->get_user()['id']);
        if ($this->input->is_ajax_request()) {
            $this->json(['success' => true]);
        } else {
            redirect('expense/' . $id);
        }
    }

    private function _thai_date() {
        $th = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        return date('j') . ' ' . $th[(int)date('n')] . ' ' . (date('Y') + 543);
    }
}
