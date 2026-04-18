<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense_model extends CI_Model {

    public function get_all($filters = []) {
        $this->db->select('e.*')->from('expenses e');
        if (!empty($filters['status']))  $this->db->where('e.status', $filters['status']);
        if (!empty($filters['dept']))    $this->db->where('e.department', $filters['dept']);
        if (!empty($filters['search'])) {
            $this->db->group_start()
                ->like('e.title', $filters['search'])
                ->or_like('e.requester_name', $filters['search'])
                ->group_end();
        }
        return $this->db->order_by('e.created_at', 'DESC')->get()->result();
    }

    public function get_by_id($id) {
        $exp = $this->db->where('id', $id)->get('expenses')->row();
        if ($exp) {
            $exp->items = $this->db->where('expense_id', $id)->get('expense_items')->result();
        }
        return $exp;
    }

    public function get_by_requester($student_id) {
        return $this->db->where('requester_id', $student_id)
            ->order_by('created_at', 'DESC')->get('expenses')->result();
    }

    public function next_id() {
        $max = $this->db->select_max('id')->get('expenses')->row();
        if (!$max || !$max->id) return 'EXP-001';
        $num = (int)substr($max->id, 4) + 1;
        return 'EXP-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    public function create($data, $items = []) {
        $data['id'] = $this->next_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('expenses', $data);
        foreach ($items as $item) {
            $item['expense_id'] = $data['id'];
            $this->db->insert('expense_items', $item);
        }
        return $data['id'];
    }

    public function update_expense($id, $data, $items = []) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id)->update('expenses', $data);
        if (!empty($items)) {
            $this->db->where('expense_id', $id)->delete('expense_items');
            foreach ($items as $item) {
                $item['expense_id'] = $id;
                $this->db->insert('expense_items', $item);
            }
        }
    }

    public function update_status($id, $status, $note = '', $approved_by = null) {
        $upd = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($note)        $upd['reject_note']  = $note;
        if ($approved_by) $upd['approved_by']  = $approved_by;
        $this->db->where('id', $id)->update('expenses', $upd);
    }

    public function get_pending_count() {
        return $this->db->where('status', 'pending')->count_all_results('expenses');
    }

    public function get_stats() {
        $statuses = ['draft','submitted','pending','approved','rejected','completed'];
        $result = [];
        foreach ($statuses as $s) {
            $result[$s] = $this->db->where('status', $s)->count_all_results('expenses');
        }
        return $result;
    }
}
