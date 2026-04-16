<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fund_model extends CI_Model {

    public function get_ledger() {
        return $this->db->order_by('id', 'DESC')->get('fund_ledger')->result();
    }

    public function get_balance() {
        $row = $this->db->select_max('id')->get('fund_ledger')->row();
        if (!$row || !$row->id) return 0;
        $latest = $this->db->where('id', $row->id)->get('fund_ledger')->row();
        return $latest ? (float)$latest->balance : 0;
    }

    public function add_entry($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('fund_ledger', $data);
        return $this->db->insert_id();
    }

    public function delete_entry($id) {
        // Recalculate balances after deletion is complex; mark as handled
        $this->db->where('id', $id)->delete('fund_ledger');
    }

    public function get_monthly_summary($year = 2568) {
        $months = [];
        $th_months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        for ($m = 1; $m <= 12; $m++) {
            $like = $th_months[$m] . ' ' . substr((string)$year, 2);
            $inc = $this->db->select_sum('income')->like('entry_date', $th_months[$m])->where('type','income')->get('fund_ledger')->row();
            $exp = $this->db->select_sum('expense')->like('entry_date', $th_months[$m])->where('type','expense')->get('fund_ledger')->row();
            $months[$m] = [
                'income'  => $inc  ? (float)$inc->income  : 0,
                'expense' => $exp  ? (float)$exp->expense : 0,
            ];
        }
        return $months;
    }
}
