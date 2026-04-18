<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    protected $role_map = [
        'student'        => ['label' => 'Student',       'color' => '#6366f1'],
        'activity_staff' => ['label' => 'Activity Staff','color' => '#8b5cf6'],
        'academic_staff' => ['label' => 'Academic Staff','color' => '#06b6d4'],
        'treasurer'      => ['label' => 'Treasurer',     'color' => '#f59e0b'],
        'head_it'        => ['label' => 'Head of IT',    'color' => '#10b981'],
        'advisor'        => ['label' => 'Advisor',       'color' => '#14b8a6'],
        'auditor'        => ['label' => 'Auditor',       'color' => '#ef4444'],
        'super_admin'    => ['label' => 'Super Admin',   'color' => '#f97316'],
    ];

    protected $settings  = [];
    protected $acad_year = 2568;

    public function __construct() {
        parent::__construct();
        $this->load->model('Setting_model');
        $this->settings  = $this->Setting_model->get_all();
        $this->acad_year = (int)($this->settings['academic_year'] ?? 2568);
    }

    protected function require_login() {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
            exit;
        }
    }

    protected function require_role($roles) {
        $this->require_login();
        $current = $this->session->userdata('role');
        if (!in_array($current, (array)$roles)) {
            show_error('คุณไม่มีสิทธิ์เข้าถึงหน้านี้', 403);
        }
    }

    protected function get_user() {
        return [
            'id'         => $this->session->userdata('user_id'),
            'name'       => $this->session->userdata('name'),
            'email'      => $this->session->userdata('email'),
            'role'       => $this->session->userdata('role'),
            'student_id' => $this->session->userdata('student_id'),
            'roleLabel'  => $this->role_map[$this->session->userdata('role')]['label'] ?? '',
            'color'      => $this->role_map[$this->session->userdata('role')]['color'] ?? '#6366f1',
        ];
    }

    protected function render($view, $data = []) {
        $data['current_user'] = $this->get_user();
        $data['page']         = $view;
        $data['settings']     = $this->settings;
        $data['acad_year']    = $this->acad_year;
        if (!isset($data['year'])) $data['year'] = $this->acad_year;
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/footer', $data);
    }

    protected function json($data, $status = 200) {
        $this->output->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
        exit;
    }

    protected function can($action) {
        $role  = $this->session->userdata('role');
        $perms = [
            'create_expense'  => ['activity_staff','academic_staff','super_admin'],
            'approve_expense' => ['treasurer','super_admin'],
            'edit_payment'    => ['treasurer','super_admin'],
            'view_all'        => ['treasurer','head_it','advisor','auditor','super_admin'],
            'manage_settings' => ['super_admin','treasurer'],
            'super'           => ['super_admin'],
        ];
        return in_array($role, $perms[$action] ?? []);
    }
}
