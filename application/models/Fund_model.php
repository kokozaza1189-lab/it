<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fund_model extends CI_Model {

    public function get_ledger() {
        return $this->db->order_by('id', 'DESC')->get('fund_ledger')->result();
    }

    public function get_balance() {
        $row = $this->db->select('balance')->order_by('id','DESC')->limit(1)->get('fund_ledger')->row();
        return $row ? (float)$row->balance : 0;
    }

    public function add_entry($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('fund_ledger', $data);
        return $this->db->insert_id();
    }

    public function delete_entry($id) {
        $this->db->where('id', $id)->delete('fund_ledger');
    }

    // Recalculate running balance for all entries in chronological order
    public function recalc_balances() {
        $entries = $this->db->order_by('id','ASC')->get('fund_ledger')->result();
        $bal = 0;
        foreach ($entries as $e) {
            $bal += (float)$e->income - (float)$e->expense;
            $this->db->where('id', $e->id)->update('fund_ledger', ['balance' => $bal]);
        }
    }

    // Monthly summary using txn_date (ISO date column)
    public function get_monthly_summary($year_be = 2568) {
        $year_ad = $year_be - 543;
        $months  = [];
        for ($m = 1; $m <= 12; $m++) {
            $row_i = $this->db->query(
                "SELECT COALESCE(SUM(income),0)  AS total FROM fund_ledger
                 WHERE type='income'  AND YEAR(txn_date)=? AND MONTH(txn_date)=?",
                [$year_ad, $m]
            )->row();
            $row_e = $this->db->query(
                "SELECT COALESCE(SUM(expense),0) AS total FROM fund_ledger
                 WHERE type='expense' AND YEAR(txn_date)=? AND MONTH(txn_date)=?",
                [$year_ad, $m]
            )->row();
            $months[$m] = [
                'income'  => $row_i ? (float)$row_i->total : 0,
                'expense' => $row_e ? (float)$row_e->total : 0,
            ];
        }
        return $months;
    }
}
