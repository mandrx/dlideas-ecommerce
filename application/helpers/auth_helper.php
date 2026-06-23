<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('logged_in')) {
    function logged_in()
    {
        $CI =& get_instance();
        return $CI->session->userdata('user_id') !== false;
    }
}

if (!function_exists('current_role')) {
    function current_role()
    {
        $CI =& get_instance();
        return $CI->session->userdata('role');
    }
}

if (!function_exists('is_role')) {
    function is_role($role)
    {
        return current_role() === $role;
    }
}

if (!function_exists('current_user_id')) {
    function current_user_id()
    {
        $CI =& get_instance();
        return $CI->session->userdata('user_id');
    }
}
