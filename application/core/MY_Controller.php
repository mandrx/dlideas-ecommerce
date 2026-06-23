<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $current_user = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');

        $user_id = $this->session->userdata('user_id');
        if ($user_id) {
            $this->current_user = $this->user_model->find($user_id);

            // Guard: if user was banned after login, destroy session
            if ($this->current_user && $this->current_user->status === USER_BANNED) {
                $this->session->sess_destroy();
                $this->current_user = null;
                redirect('login');
            }
        }

        // Make current_user available to all views automatically
        $this->load->vars(array('current_user' => $this->current_user));
    }

    protected function require_login()
    {
        if (!$this->current_user) {
            $this->session->set_userdata('redirect_after_login', current_url());
            redirect('login');
        }
    }

    protected function require_role($role)
    {
        $this->require_login();
        if ($this->current_user->role !== $role) {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    protected function json_response($data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    protected function redirect_with_message($url, $message, $type = 'success')
    {
        $this->session->set_flashdata($type, $message);
        redirect($url);
    }
}
