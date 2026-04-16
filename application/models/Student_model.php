<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_model extends CI_Model {

    public function get_all() {
        return $this->db->order_by('name')->get('students')->result();
    }

    public function get_by_id($student_id) {
        return $this->db->where('student_id', $student_id)->get('students')->row();
    }

    public function get_with_payments($year = 2568) {
        $students = $this->get_all();
        $months   = [1, 2, 3, 4];
        foreach ($students as &$s) {
            $s->payments = [];
            $s->total_due = 0;
            foreach ($months as $m) {
                $rec = $this->db
                    ->where('student_id', $s->student_id)
                    ->where('year', $year)
                    ->where('month', $m)
                    ->get('payment_records')->row();
                $s->payments[$m] = $rec ?: (object)['status' => 'pending', 'amount' => 50, 'penalty' => 0];
                if ($rec && in_array($rec->status, ['overdue'])) {
                    $s->total_due += $rec->amount + $rec->penalty;
                } elseif ($rec && $rec->penalty > 0 && $rec->status === 'paid') {
                    $s->total_due += $rec->penalty;
                }
            }
        }
        return $students;
    }

    public function search($keyword, $status_filter = '') {
        $this->db->from('students s');
        if ($keyword) {
            $this->db->group_start()
                ->like('s.name', $keyword)
                ->or_like('s.student_id', $keyword)
                ->group_end();
        }
        return $this->db->get()->result();
    }
}
