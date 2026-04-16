<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model extends CI_Model {

    public function get_by_student($student_id, $year = 2568) {
        return $this->db
            ->where('student_id', $student_id)
            ->where('year', $year)
            ->order_by('month')
            ->get('payment_records')->result();
    }

    public function get_month($student_id, $year, $month) {
        return $this->db
            ->where('student_id', $student_id)
            ->where('year', $year)
            ->where('month', $month)
            ->get('payment_records')->row();
    }

    public function update_status($id, $status, $paid_date = null) {
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($paid_date) $data['paid_date'] = $paid_date;
        $this->db->where('id', $id)->update('payment_records', $data);
    }

    public function submit_payment($student_id, $year, $month, $slip_file = '') {
        $existing = $this->get_month($student_id, $year, $month);
        if ($existing) {
            $this->db->where('id', $existing->id)->update('payment_records', [
                'status'   => 'pending',
                'slip_file' => $slip_file,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function get_stats($year = 2568) {
        $total   = $this->db->count_all('students');
        $paid    = $this->db->where('year', $year)->where('status', 'paid')->count_all_results('payment_records');
        $overdue = $this->db->where('year', $year)->where('status', 'overdue')->count_all_results('payment_records');
        $pending = $this->db->where('year', $year)->where('status', 'pending')->count_all_results('payment_records');
        return compact('total', 'paid', 'overdue', 'pending');
    }

    public function get_monthly_income($year = 2568) {
        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $sum = $this->db->select_sum('amount')->select_sum('penalty')
                ->where('year', $year)->where('month', $m)->where('status', 'paid')
                ->get('payment_records')->row();
            $result[$m] = $sum ? ($sum->amount + $sum->penalty) : 0;
        }
        return $result;
    }

    public function get_all_overdue($year = 2568) {
        return $this->db
            ->select('pr.*, s.name as student_name')
            ->from('payment_records pr')
            ->join('students s', 'pr.student_id = s.student_id')
            ->where('pr.year', $year)
            ->where('pr.status', 'overdue')
            ->order_by('s.name')
            ->get()->result();
    }
}
