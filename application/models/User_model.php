<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function get_by_email($email) {
        return $this->db->where('email', $email)->where('is_active', 1)->get('users')->row();
    }

    public function get_by_identifier($identifier) {
        // Accept email OR student_id
        return $this->db
            ->group_start()
                ->where('email', $identifier)
                ->or_where('student_id', $identifier)
            ->group_end()
            ->where('is_active', 1)
            ->get('users')->row();
    }

    public function get_by_id($id) {
        return $this->db->where('id', $id)->get('users')->row();
    }

    public function create($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    public function verify_password($plain, $hash) {
        return password_verify($plain, $hash);
    }

    public function get_all($search = '') {
        if ($search !== '') {
            $this->db->group_start()
                ->like('name', $search)
                ->or_like('email', $search)
                ->or_like('student_id', $search)
                ->group_end();
        }
        return $this->db->order_by('role')->order_by('name')->get('users')->result();
    }

    public function count_all() {
        return $this->db->count_all('users');
    }

    public function update($id, $data) {
        $this->db->where('id', $id)->update('users', $data);
    }

    public function email_exists_for_other($email, $exclude_id) {
        return $this->db->where('email', $email)->where('id !=', $exclude_id)->count_all_results('users') > 0;
    }

    public function toggle_active($id) {
        $user = $this->get_by_id($id);
        if ($user) {
            $this->db->where('id', $id)->update('users', ['is_active' => $user->is_active ? 0 : 1]);
        }
    }

    public function delete($id) {
        $this->db->where('id', $id)->delete('users');
    }
}
