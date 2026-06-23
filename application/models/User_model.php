<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model
{
    protected $table   = 'users';
    protected $primary = 'id';

    public function find_by_email($email)
    {
        return $this->db
            ->where('email', $email)
            ->get($this->table)
            ->row();
    }

    public function email_exists($email, $exclude_id = null)
    {
        $this->db->where('email', $email);
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function register($data)
    {
        return $this->insert(array(
            'email'      => $data['email'],
            'password'   => password_hash($data['password'], PASSWORD_BCRYPT),
            'full_name'  => $data['full_name'],
            'phone'      => isset($data['phone']) ? $data['phone'] : null,
            'role'       => ROLE_BUYER,
            'status'     => USER_ACTIVE,
            'created_at' => date('Y-m-d H:i:s'),
        ));
    }

    public function set_reset_token($user_id, $token, $expires)
    {
        return $this->update($user_id, array(
            'reset_token'   => $token,
            'reset_expires' => $expires,
        ));
    }

    public function find_by_reset_token($token)
    {
        return $this->db
            ->where('reset_token', $token)
            ->where('reset_expires >', date('Y-m-d H:i:s'))
            ->get($this->table)
            ->row();
    }

    public function clear_reset_token($user_id)
    {
        return $this->update($user_id, array(
            'reset_token'   => null,
            'reset_expires' => null,
        ));
    }

    public function update_password($user_id, $new_password)
    {
        return $this->update($user_id, array(
            'password' => password_hash($new_password, PASSWORD_BCRYPT),
        ));
    }

    public function promote_to_seller($user_id)
    {
        return $this->update($user_id, array('role' => ROLE_SELLER));
    }

    public function get_paginated($limit = 20, $offset = 0)
    {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get($this->table)
            ->result();
    }
}
