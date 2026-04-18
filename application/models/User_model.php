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

    public function get_all() {
        return $this->db->order_by('role')->get('users')->result();
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
