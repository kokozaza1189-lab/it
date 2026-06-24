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
        $this->require_role(['activity_staff','academic_staff','treasurer','head_it','advisor','super_admin']);
        if ($this->input->post()) {
            $names     = $this->input->post('item_name')     ?: [];
            $prices    = $this->input->post('item_price')    ?: [];
            $qtys      = $this->input->post('item_qty')      ?: [];
            $discounts = $this->input->post('item_discount') ?: [];
            $items  = [];
            foreach ($names as $i => $name) {
                if (!empty(trim($name))) {
                    $items[] = [
                        'item_name' => trim($name),
                        'price'     => (float)($prices[$i] ?? 0),
                        'quantity'  => (int)($qtys[$i] ?? 1),
                        'discount'  => (float)($discounts[$i] ?? 0),
                    ];
                }
            }
            $user  = $this->get_user();
            $total = array_sum(array_map(fn($it) => max(0, $it['price'] * $it['quantity'] - $it['discount']), $items));
            $attachment = $this->_handle_attachment();
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
                'attachment'     => $attachment,
                'bank_name'      => $this->input->post('bank_name', TRUE),
                'bank_account'   => $this->input->post('bank_account', TRUE),
            ], $items);
            redirect('expense/' . $id);
        }
        $balance = $this->Fund_model->get_balance();
        $this->render('expense/create', ['title' => 'สร้างคำขอเบิกเงิน', 'fund_balance' => $balance]);
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
            $names     = $this->input->post('item_name')     ?: [];
            $prices    = $this->input->post('item_price')    ?: [];
            $qtys      = $this->input->post('item_qty')      ?: [];
            $discounts = $this->input->post('item_discount') ?: [];
            $items  = [];
            foreach ($names as $i => $name) {
                if (!empty(trim($name))) {
                    $items[] = [
                        'item_name' => trim($name),
                        'price'     => (float)($prices[$i] ?? 0),
                        'quantity'  => (int)($qtys[$i] ?? 1),
                        'discount'  => (float)($discounts[$i] ?? 0),
                    ];
                }
            }
            $total  = array_sum(array_map(fn($it) => max(0, $it['price'] * $it['quantity'] - $it['discount']), $items));
            $status = $this->input->post('submit_type') === 'submit' ? 'submitted' : 'draft';
            $upd = [
                'title'        => $this->input->post('title', TRUE),
                'department'   => $this->input->post('department', TRUE),
                'category'     => $this->input->post('category', TRUE),
                'reason'       => $this->input->post('reason', TRUE),
                'amount'       => $total,
                'status'       => $status,
                'bank_name'    => $this->input->post('bank_name', TRUE),
                'bank_account' => $this->input->post('bank_account', TRUE),
            ];
            $newFile = $this->_handle_attachment();
            if ($newFile !== null) $upd['attachment'] = $newFile;
            $this->Expense_model->update_expense($id, $upd, $items);
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

    private function _handle_attachment() {
        if (empty($_FILES['attachment']['name'])) return null;
        $uploadPath = rtrim(FCPATH, '/') . '/assets/uploads/expense_docs/';
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);
        // Always initialize fresh (CI3 library is singleton — must call initialize())
        $this->load->library('upload');
        $this->upload->initialize([
            'upload_path'     => $uploadPath,
            'allowed_types'   => 'jpg|jpeg|png|pdf',
            'max_size'        => 10240,
            'file_name'       => 'exp_' . time() . '_' . mt_rand(1000, 9999),
            'overwrite'       => false,
            'encrypt_name'    => false,
            'detect_mime'     => true,
            'mod_mime_fix'    => true,
        ]);
        if ($this->upload->do_upload('attachment')) {
            return $this->upload->data('file_name');
        }
        log_message('error', 'Expense attachment upload error: ' . strip_tags($this->upload->display_errors()));
        return null;
    }

    private function _thai_date() {
        $th = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        return date('j') . ' ' . $th[(int)date('n')] . ' ' . (date('Y') + 543);
    }
}
