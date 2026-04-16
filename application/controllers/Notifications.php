<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Notifications extends MY_Controller {
    public function index() {
        $this->require_login();
        $this->render('notifications/index', ['title'=>'การแจ้งเตือน']);
    }
}
