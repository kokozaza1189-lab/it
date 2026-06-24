<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('form_validation');
    }

    public function index() {
        $this->login();
    }

    public function login() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        $data = [];
        if ($this->input->post()) {
            $this->form_validation->set_rules('identifier', 'อีเมล/รหัสนิสิต', 'required|trim');
            $this->form_validation->set_rules('password',   'Password',          'required|min_length[4]');
            if ($this->form_validation->run()) {
                $identifier = $this->input->post('identifier', TRUE);
                $user = $this->User_model->get_by_identifier($identifier);
                if ($user && $this->User_model->verify_password($this->input->post('password'), $user->password)) {
                    if (!$user->is_active) {
                        $data['error'] = 'บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ';
                    } else {
                        $this->session->set_userdata([
                            'logged_in'  => TRUE,
                            'user_id'    => $user->id,
                            'name'       => $user->name,
                            'email'      => $user->email,
                            'role'       => $user->role,
                            'student_id' => $user->student_id,
                        ]);
                        redirect('dashboard');
                    }
                } else {
                    $data['error'] = 'อีเมล/รหัสนิสิต หรือรหัสผ่านไม่ถูกต้อง';
                }
            } else {
                $data['errors'] = $this->form_validation->error_array();
            }
        }
        // Pick up flash messages from register/reset redirects
        $data['flash_success'] = $this->session->flashdata('success');
        $data['flash_error']   = $this->session->flashdata('error');
        $data['title'] = 'เข้าสู่ระบบ — IT Finance System';
        $this->load->view('auth/login', $data);
    }

    public function register() {
        if ($this->session->userdata('logged_in')) { redirect('dashboard'); return; }
        $data = [];
        if ($this->input->post()) {
            $this->form_validation->set_rules('name',       'ชื่อ-นามสกุล', 'required|trim|min_length[3]');
            $this->form_validation->set_rules('student_id', 'รหัสนิสิต',    'required|min_length[10]|max_length[10]');
            $this->form_validation->set_rules('email',      'อีเมล',        'required|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('password',   'รหัสผ่าน',    'required|min_length[8]');
            $this->form_validation->set_rules('confirm',    'ยืนยันรหัสผ่าน','required|matches[password]');
            $this->form_validation->set_rules('role',       'สถานะ',        'required');
            if ($this->form_validation->run()) {
                $role    = $this->input->post('role', TRUE);
                $blocked = ['super_admin', 'treasurer', 'head_it', 'advisor', 'auditor'];
                if (in_array($role, $blocked)) {
                    $data['errors']['role'] = 'ไม่สามารถสมัครสมาชิกในตำแหน่งนี้ได้ กรุณาติดต่อผู้ดูแลระบบ';
                } else {
                    $this->User_model->create([
                        'name'       => $this->input->post('name', TRUE),
                        'student_id' => $this->input->post('student_id', TRUE),
                        'email'      => $this->input->post('email', TRUE),
                        'password'   => $this->input->post('password'),
                        'role'       => $role,
                    ]);
                    $this->session->set_flashdata('success', 'สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ');
                    redirect('login');
                    return;
                }
            } else {
                $data['errors'] = $this->form_validation->error_array();
            }
        }
        $data['title'] = 'สมัครสมาชิก — IT Finance System';
        $this->load->view('auth/register', $data);
    }

    // ---- Forgot Password -----------------------------------------------
    // GET  → show forgot-password page
    // POST (AJAX) → generate token, return JSON
    public function forgot() {
        if ($this->session->userdata('logged_in')) { redirect('dashboard'); return; }

        if (!$this->input->is_ajax_request()) {
            // GET: show the standalone forgot-password page
            $data = ['title' => 'ลืมรหัสผ่าน — IT Finance System'];
            // Pick up flash error (e.g. expired link redirected here)
            $data['flash_error'] = $this->session->flashdata('error');
            $this->load->view('auth/forgot', $data);
            return;
        }

        // AJAX POST: validate email → generate token
        $email = strtolower(trim($this->input->post('email', TRUE)));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['ok' => false, 'msg' => 'กรุณากรอกอีเมลให้ถูกต้อง']); return;
        }

        $user = $this->db->where('email', $email)->where('is_active', 1)->get('users')->row();
        if (!$user) {
            $this->json(['ok' => false, 'msg' => 'ไม่พบบัญชีที่ใช้อีเมลนี้ กรุณาตรวจสอบอีเมลอีกครั้ง']); return;
        }

        // Delete old unused tokens for this email
        $this->db->where('email', $email)->delete('password_resets');

        // Generate secure 64-char hex token, valid 30 min
        $token = bin2hex(random_bytes(32));
        $this->db->insert('password_resets', [
            'email'      => $email,
            'token'      => $token,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json([
            'ok'        => true,
            'name'      => $user->name,
            'reset_url' => base_url('reset/' . $token),
        ]);
    }

    // GET/POST: show reset form, handle new password submission
    public function reset($token = '') {
        if (!$token) { redirect('login'); return; }

        $reset = $this->db
            ->where('token', $token)
            ->where('used', 0)
            ->where('created_at >', date('Y-m-d H:i:s', strtotime('-30 minutes')))
            ->get('password_resets')->row();

        if (!$reset) {
            $this->session->set_flashdata('error', 'ลิงก์หมดอายุหรือไม่ถูกต้อง กรุณาขอลิงก์ใหม่อีกครั้ง');
            redirect('forgot');
            return;
        }

        $data = ['token' => $token, 'title' => 'ตั้งรหัสผ่านใหม่ — IT Finance System'];

        if ($this->input->post()) {
            $newpass = $this->input->post('new_password');
            $confirm = $this->input->post('confirm_password');
            if (!$newpass || strlen($newpass) < 8) {
                $data['reset_error'] = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
            } elseif ($newpass !== $confirm) {
                $data['reset_error'] = 'รหัสผ่านไม่ตรงกัน';
            } else {
                $this->db->where('email', $reset->email)->update('users', [
                    'password'   => password_hash($newpass, PASSWORD_BCRYPT),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $this->db->where('id', $reset->id)->update('password_resets', ['used' => 1]);
                $this->session->set_flashdata('success', 'ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว! กรุณาเข้าสู่ระบบอีกครั้ง');
                redirect('login');
                return;
            }
        }

        $this->load->view('auth/reset', $data);
    }

    // ---- Logout --------------------------------------------------------

    public function logout() {
        $this->session->sess_destroy();
        redirect('login');
    }
}
