<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_model extends CI_Model {

    public function get_all($search = '') {
        if ($search) {
            $this->db->group_start()
                ->like('name', $search)
                ->or_like('student_id', $search)
                ->group_end();
        }
        return $this->db->order_by('student_id')->get('students')->result();
    }

    public function get_by_id($student_id) {
        return $this->db->where('student_id', $student_id)->get('students')->row();
    }

    public function student_id_exists($student_id) {
        return $this->db->where('student_id', $student_id)->count_all_results('students') > 0;
    }

    public function add($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('students', $data);
        return $this->db->insert_id();
    }

    public function update($student_id, $data) {
        $this->db->where('student_id', $student_id)->update('students', $data);
    }

    public function delete($student_id) {
        // Delete payment records first (no FK cascade)
        $this->db->where('student_id', $student_id)->delete('payment_records');
        $this->db->where('student_id', $student_id)->delete('students');
    }

    // Import from CSV array — returns ['added'=>N, 'skipped'=>N]
    public function import_csv($rows, $fee = 50) {
        $added = 0; $skipped = 0;
        foreach ($rows as $row) {
            $sid  = trim($row[0] ?? '');
            $name = trim($row[1] ?? '');
            if (!$sid || !$name) { $skipped++; continue; }
            if ($this->student_id_exists($sid)) { $skipped++; continue; }
            $this->add([
                'student_id' => $sid,
                'name'       => $name,
                'email'      => trim($row[2] ?? '') ?: null,
            ]);
            $added++;
        }
        return compact('added','skipped');
    }

    public function count() {
        return $this->db->count_all('students');
    }

    // Load students with payment data for given months
    public function get_with_payments($year = 2568, $months = [1,2,3,4]) {
        $students = $this->get_all();
        foreach ($students as &$s) {
            $s->payments  = [];
            $s->total_due = 0;
            // Load all months in one query
            $recs = $this->db
                ->where('student_id', $s->student_id)
                ->where('year', $year)
                ->where_in('month', $months)
                ->get('payment_records')->result();
            $rec_map = [];
            foreach ($recs as $r) { $rec_map[$r->month] = $r; }
            foreach ($months as $m) {
                $s->payments[$m] = $rec_map[$m] ?? (object)['id'=>null,'status'=>'none','amount'=>50,'penalty'=>0,'paid_date'=>null,'slip_file'=>null];
                $pr = $s->payments[$m];
                if ($pr->status === 'overdue') {
                    $s->total_due += $pr->amount + $pr->penalty;
                }
            }
        }
        return $students;
    }
}
