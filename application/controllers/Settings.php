<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Setting_model','User_model']);
    }

    public function index() {
        $this->require_login();
        $users = $this->get_user()['role'] === 'super_admin'
            ? $this->db->order_by('name')->get('users')->result()
            : [];
        $this->render('settings/index', [
            'title' => 'ตั้งค่าระบบ',
            'users' => $users,
        ]);
    }

    // Save general settings (treasurer/super_admin)
    public function save() {
        $this->require_role(['super_admin','treasurer']);
        $keys = ['academic_year','monthly_fee','penalty_per_day','due_day','active_months'];
        foreach ($keys as $k) {
            $val = $this->input->post($k, TRUE);
            if ($val !== null && $val !== '') {
                $this->Setting_model->set($k, $val);
            }
        }
        // Refresh session year cache
        $this->session->set_flashdata('success', 'บันทึกการตั้งค่าเรียบร้อย');
        redirect('settings');
    }

    // Update a single user's role / active status (super_admin only)
    public function save_user() {
        $this->require_role(['super_admin']);
        $id     = (int)$this->input->post('id');
        $role   = $this->input->post('role', TRUE);
        $active = (int)$this->input->post('is_active');
        $valid_roles = ['student','activity_staff','academic_staff','treasurer',
                        'head_it','advisor','auditor','super_admin'];
        if ($id && in_array($role, $valid_roles)) {
            $this->db->where('id', $id)->update('users', [
                'role'       => $role,
                'is_active'  => $active,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        if ($this->input->is_ajax_request()) {
            $this->json(['success' => true]);
        } else {
            $this->session->set_flashdata('success', 'อัปเดตผู้ใช้แล้ว');
            redirect('settings');
        }
    }

    // Reset user password to a new random one (super_admin only)
    public function reset_pass() {
        $this->require_role(['super_admin']);
        $id      = (int)$this->input->post('id');
        $newpass = $this->input->post('password') ?: substr(str_shuffle('abcdefghjkmnpqrstuvwxyz23456789'), 0, 8);
        $this->db->where('id', $id)->update('users', [
            'password'   => password_hash($newpass, PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->json(['success' => true, 'new_password' => $newpass]);
    }
}
