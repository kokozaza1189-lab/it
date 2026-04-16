<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Settings extends MY_Controller {
    public function index() {
        $this->require_login();
        $this->render('settings/index', ['title'=>'ตั้งค่า']);
    }
}
