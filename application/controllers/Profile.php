<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller {

    public function index() {
        $this->require_login();
        $user_id = $this->get_user()['id'];
        $user    = $this->db->where('id', $user_id)->get('users')->row();
        $this->render('profile/index', [
            'title'    => 'โปรไฟล์',
            'db_user'  => $user,
        ]);
    }

    public function change_password() {
        $this->require_login();
        $current = $this->input->post('current_password');
        $new     = $this->input->post('new_password');
        $confirm = $this->input->post('confirm_password');

        if (!$current || !$new || !$confirm) {
            $this->json(['error' => 'กรุณากรอกข้อมูลให้ครบ'], 400); return;
        }
        if ($new !== $confirm) {
            $this->json(['error' => 'รหัสผ่านใหม่ไม่ตรงกัน'], 400); return;
        }
        if (strlen($new) < 8) {
            $this->json(['error' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร'], 400); return;
        }
        $user_id = $this->get_user()['id'];
        $user    = $this->db->where('id', $user_id)->get('users')->row();
        if (!password_verify($current, $user->password)) {
            $this->json(['error' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง'], 400); return;
        }
        $this->db->where('id', $user_id)->update('users', [
            'password'   => password_hash($new, PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->json(['success' => true]);
    }
}
