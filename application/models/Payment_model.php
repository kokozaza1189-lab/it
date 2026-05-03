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

    public function update_status($id, $status, $paid_date = null, $penalty = null, $amount = null) {
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($paid_date)        $data['paid_date'] = $paid_date;
        if ($penalty !== null) $data['penalty']   = (float)$penalty;
        if ($amount  !== null) $data['amount']    = (float)$amount;
        $this->db->where('id', $id)->update('payment_records', $data);
    }

    public function submit_payment($student_id, $year, $month, $slip_file = '', $amount = 50) {
        $existing = $this->get_month($student_id, $year, $month);
        if ($existing) {
            // Do not reset a record that has already been confirmed paid
            if ($existing->status === 'paid') return;
            $upd = ['status' => 'pending', 'updated_at' => date('Y-m-d H:i:s')];
            if ($slip_file) $upd['slip_file'] = $slip_file;
            $this->db->where('id', $existing->id)->update('payment_records', $upd);
        } else {
            // No record yet — create one so the slip is actually saved
            $this->db->insert('payment_records', [
                'student_id' => $student_id,
                'year'       => $year,
                'month'      => $month,
                'status'     => 'pending',
                'amount'     => $amount,
                'penalty'    => 0,
                'slip_file'  => $slip_file ?: null,
            ]);
        }
    }

    // Create payment records for all students in a month (if not already exists)
    public function generate_month($year, $month, $amount = 50) {
        $students = $this->db->get('students')->result();
        $created  = 0;
        foreach ($students as $s) {
            if (!$this->get_month($s->student_id, $year, $month)) {
                $this->db->insert('payment_records', [
                    'student_id' => $s->student_id,
                    'year'       => $year,
                    'month'      => $month,
                    'status'     => 'pending',
                    'amount'     => $amount,
                    'penalty'    => 0,
                ]);
                $created++;
            }
        }
        return $created;
    }

    // Move all pending → overdue for a given month
    public function mark_overdue($year, $month) {
        $this->db->where('year', $year)
            ->where('month', $month)
            ->where('status', 'pending')
            ->update('payment_records', [
                'status'     => 'overdue',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        return $this->db->affected_rows();
    }

    // Recalculate penalty for overdue records (days past due_day)
    public function recalc_penalties($year, $month, $daily_penalty, $due_day) {
        $due_date   = mktime(0,0,0, $month < 543 ? $month : $month, $due_day, $year - 543);
        $today      = time();
        $days_overdue = max(0, (int)(($today - $due_date) / 86400));
        $penalty    = round($days_overdue * $daily_penalty, 2);
        $this->db->where('year', $year)
            ->where('month', $month)
            ->where('status', 'overdue')
            ->update('payment_records', [
                'penalty'    => $penalty,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        return $days_overdue;
    }

    // Per-month summary counts
    public function get_month_summary($year, $month) {
        $paid    = $this->db->where('year',$year)->where('month',$month)->where('status','paid')->count_all_results('payment_records');
        $overdue = $this->db->where('year',$year)->where('month',$month)->where('status','overdue')->count_all_results('payment_records');
        $pending = $this->db->where('year',$year)->where('month',$month)->where('status','pending')->count_all_results('payment_records');
        $none    = $this->db->where('year',$year)->where('month',$month)->where('status','none')->count_all_results('payment_records');
        $total   = $paid + $overdue + $pending + $none;
        $income  = $this->db->select_sum('amount')->select_sum('penalty')
            ->where('year',$year)->where('month',$month)->where('status','paid')
            ->get('payment_records')->row();
        return [
            'paid'    => $paid,
            'overdue' => $overdue,
            'pending' => $pending,
            'total'   => $total,
            'income'  => $income ? (float)$income->amount + (float)$income->penalty : 0,
        ];
    }

    public function get_stats($year = 2568) {
        $total   = $this->db->count_all('students');
        $paid    = $this->db->where('year',$year)->where('status','paid')->count_all_results('payment_records');
        $overdue = $this->db->where('year',$year)->where('status','overdue')->count_all_results('payment_records');
        $pending = $this->db->where('year',$year)->where('status','pending')->count_all_results('payment_records');
        return compact('total','paid','overdue','pending');
    }

    public function get_monthly_income($year = 2568) {
        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $sum = $this->db->select_sum('amount')->select_sum('penalty')
                ->where('year',$year)->where('month',$m)->where('status','paid')
                ->get('payment_records')->row();
            $result[$m] = $sum ? (float)$sum->amount + (float)$sum->penalty : 0;
        }
        return $result;
    }

    public function get_all_overdue($year = 2568) {
        return $this->db
            ->select('pr.*, s.name as student_name')
            ->from('payment_records pr')
            ->join('students s','pr.student_id = s.student_id')
            ->where('pr.year', $year)
            ->where('pr.status','overdue')
            ->order_by('s.name')
            ->get()->result();
    }
}
