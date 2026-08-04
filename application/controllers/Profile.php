<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller {

    public function index() {
        $this->require_login();
        header('X-LiteSpeed-Cache-Control: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $user_id = $this->get_user()['id'];
        $user    = $this->db->where('id', $user_id)->get('users')->row();
        $this->render('profile/index', [
            'title'    => 'โปรไฟล์',
            'db_user'  => $user,
        ]);
    }

    // Native form POST (not AJAX) so browser-autofilled values are submitted reliably —
    // Chrome hides autofilled password values from JS, which broke the old axios flow.
    public function change_password() {
        $this->require_login();
        $current = (string)$this->input->post('current_password');
        $new     = (string)$this->input->post('new_password');
        $confirm = (string)$this->input->post('confirm_password');

        $err = null;
        if ($current === '' || $new === '' || $confirm === '') $err = 'กรุณากรอกข้อมูลให้ครบ';
        elseif ($new !== $confirm)                             $err = 'รหัสผ่านใหม่ไม่ตรงกัน';
        elseif (strlen($new) < 8)                              $err = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
        else {
            $user_id = $this->get_user()['id'];
            $user    = $this->db->where('id', $user_id)->get('users')->row();
            if (!$user || !password_verify($current, $user->password)) {
                $err = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
            } else {
                $this->db->where('id', $user_id)->update('users', [
                    'password'   => password_hash($new, PASSWORD_BCRYPT),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        // Support both AJAX (legacy) and native form POST
        if (strtolower((string)$this->input->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest') {
            $err ? $this->json(['error' => $err], 400) : $this->json(['success' => true]);
            return;
        }
        $this->session->set_flashdata($err ? 'pw_error' : 'pw_success', $err ?: 'เปลี่ยนรหัสผ่านสำเร็จ');
        redirect('profile');
    }
}
