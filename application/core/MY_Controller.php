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

        // Make current_user and nav categories available to all views
        $this->load->model('category_model');
        $nav_categories = $this->category_model->get_all();
        $this->load->vars([
            'current_user'   => $this->current_user,
            'categories'     => $nav_categories,
        ]);

        $this->load->model('visitor_model');
        $this->_track_visit();
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

    protected function require_role_in(array $roles)
    {
        $this->require_login();
        if (!in_array($this->current_user->role, $roles, true)) {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    protected function require_owner()
    {
        $this->require_login();
        if ($this->current_user->role !== ROLE_OWNER || $this->current_user->email !== OWNER_EMAIL) {
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

    private function _track_visit()
    {
        // Get real client IP (proxy-aware)
        $forwarded = $this->input->server('HTTP_X_FORWARDED_FOR');
        if ($forwarded) {
            $parts = explode(',', $forwarded);
            $ip = trim($parts[0]);
        } else {
            $ip = $this->input->server('REMOTE_ADDR');
        }

        $uri     = $this->uri->uri_string();
        $ua      = (string) $this->input->user_agent();
        $user_id = $this->current_user ? $this->current_user->id : null;

        $this->visitor_model->log_visit($ip, $uri, $ua, $user_id);
    }
}
