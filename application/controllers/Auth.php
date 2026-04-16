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
        if ($this->input->post()) {
            $this->form_validation->set_rules('email',    'Email',    'required|trim');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[4]');
            if ($this->form_validation->run()) {
                $user = $this->User_model->get_by_email($this->input->post('email', TRUE));
                if ($user && $this->User_model->verify_password($this->input->post('password'), $user->password)) {
                    $this->session->set_userdata([
                        'logged_in'  => TRUE,
                        'user_id'    => $user->id,
                        'name'       => $user->name,
                        'email'      => $user->email,
                        'role'       => $user->role,
                        'student_id' => $user->student_id,
                    ]);
                    redirect('dashboard');
                } else {
                    $data['error'] = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
                }
            } else {
                $data['errors'] = $this->form_validation->error_array();
            }
        }
        $data['title'] = 'เข้าสู่ระบบ — IT Finance System';
        $this->load->view('auth/login', $data ?? []);
    }

    public function register() {
        if ($this->session->userdata('logged_in')) redirect('dashboard');
        if ($this->input->post()) {
            $this->form_validation->set_rules('name',       'Name',     'required|trim|min_length[3]');
            $this->form_validation->set_rules('student_id', 'Student ID','required|min_length[10]|max_length[10]');
            $this->form_validation->set_rules('email',      'Email',    'required|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('password',   'Password', 'required|min_length[8]');
            $this->form_validation->set_rules('confirm',    'Confirm',  'required|matches[password]');
            $this->form_validation->set_rules('role',       'Role',     'required');
            if ($this->form_validation->run()) {
                $this->User_model->create([
                    'name'       => $this->input->post('name', TRUE),
                    'student_id' => $this->input->post('student_id', TRUE),
                    'email'      => $this->input->post('email', TRUE),
                    'password'   => $this->input->post('password'),
                    'role'       => $this->input->post('role', TRUE),
                ]);
                $data['success'] = 'สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ';
            } else {
                $data['errors'] = $this->form_validation->error_array();
            }
        }
        $data['title'] = 'สมัครสมาชิก — IT Finance System';
        $this->load->view('auth/login', array_merge($data ?? [], ['tab' => 'signup']));
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('login');
    }
}
