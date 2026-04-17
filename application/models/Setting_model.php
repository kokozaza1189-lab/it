<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting_model extends CI_Model {

    public function get_all() {
        $rows   = $this->db->get('settings')->result();
        $result = [];
        foreach ($rows as $row) { $result[$row->key] = $row->value; }
        return $result;
    }

    public function get($key, $default = null) {
        $row = $this->db->where('key', $key)->get('settings')->row();
        return $row ? $row->value : $default;
    }

    public function set($key, $value) {
        $exists = $this->db->where('key', $key)->count_all_results('settings');
        if ($exists) {
            $this->db->where('key', $key)->update('settings', [
                'value'      => $value,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db->insert('settings', ['key' => $key, 'value' => $value]);
        }
    }

    public function set_many(array $data) {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }
}
