<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('store_model');
    }

    // GET /login
    public function login()
    {
        if ($this->current_user) redirect('/');

        $data['page_title']   = 'Login';
        $data['content_view'] = 'auth/login';
        $this->load->view('layouts/auth', $data);
    }

    // POST /login
    public function login_post()
    {
        $this->form_validation->set_rules('email',    'Email',    'required|valid_email|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('login');
        }

        $user = $this->user_model->find_by_email($this->input->post('email'));

        if (!$user || !password_verify($this->input->post('password'), $user->password)) {
            $this->session->set_flashdata('error', 'Invalid email or password.');
            redirect('login');
        }

        if ($user->status === USER_BANNED) {
            $this->session->set_flashdata('error', 'Your account has been suspended.');
            redirect('login');
        }

        $store_id = null;
        if ($user->role === ROLE_SELLER) {
            $store = $this->store_model->find_by_user($user->id);
            $store_id = $store ? $store->id : null;
        }

        $this->session->set_userdata(array(
            'user_id'   => $user->id,
            'role'      => $user->role,
            'full_name' => $user->full_name,
            'store_id'  => $store_id,
        ));

        // Merge guest cart on login
        $this->load->model('cart_model');
        $this->cart_model->merge_guest_cart($this->session->session_id, $user->id);

        $redirect = $this->session->userdata('redirect_after_login') ?: '/';
        $this->session->unset_userdata('redirect_after_login');
        redirect($redirect);
    }

    // GET /register
    public function register()
    {
        if ($this->current_user) redirect('/');

        $data['page_title']   = 'Create Account';
        $data['content_view'] = 'auth/register';
        $this->load->view('layouts/auth', $data);
    }

    // POST /register
    public function register_post()
    {
        $this->form_validation->set_rules('full_name', 'Full Name', 'required|trim|min_length[2]|max_length[150]');
        $this->form_validation->set_rules('email',     'Email',     'required|valid_email|trim|is_unique[users.email]');
        $this->form_validation->set_rules('password',  'Password',  'required|min_length[8]');
        $this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('register');
        }

        $user_id = $this->user_model->register(array(
            'email'     => $this->input->post('email'),
            'password'  => $this->input->post('password'),
            'full_name' => $this->input->post('full_name'),
            'phone'     => $this->input->post('phone'),
        ));

        $this->session->set_userdata(array(
            'user_id'   => $user_id,
            'role'      => ROLE_BUYER,
            'full_name' => $this->input->post('full_name'),
            'store_id'  => null,
        ));

        $this->redirect_with_message('/', 'Welcome! Your account has been created.');
    }

    // GET /logout
    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }

    // GET /apply-seller
    public function apply_seller()
    {
        $this->require_login();

        if ($this->current_user->role !== ROLE_BUYER) {
            redirect('/');
        }

        $data['page_title']   = 'Become a Seller';
        $data['content_view'] = 'auth/apply_seller';
        $this->load->view('layouts/auth', $data);
    }

    // POST /apply-seller
    public function apply_seller_post()
    {
        $this->require_login();

        $this->form_validation->set_rules('store_name',        'Store Name',        'required|trim|min_length[3]|max_length[150]');
        $this->form_validation->set_rules('store_description', 'Store Description', 'trim|max_length[1000]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('apply-seller');
        }

        // Check if already applied
        $existing = $this->store_model->find_by_user($this->current_user->id);
        if ($existing) {
            $this->redirect_with_message('/', 'You have already submitted a store application.');
        }

        $this->store_model->create_for_user(
            $this->current_user->id,
            $this->input->post('store_name'),
            $this->input->post('store_description')
        );

        $this->redirect_with_message('/', 'Your seller application has been submitted. We will review it shortly.');
    }

    // GET /forgot-password
    public function forgot_password()
    {
        $data['page_title']   = 'Reset Password';
        $data['content_view'] = 'auth/forgot_password';
        $this->load->view('layouts/auth', $data);
    }

    // POST /forgot-password
    public function forgot_password_post()
    {
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('forgot-password');
        }

        $user = $this->user_model->find_by_email($this->input->post('email'));

        // Always show success — prevents email enumeration
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $this->user_model->set_reset_token($user->id, $token, $expires);

            $reset_url = base_url('reset-password/' . $token);
            $this->_send_reset_email($user->email, $user->full_name, $reset_url);
        }

        $this->redirect_with_message('login', 'If that email exists, a reset link has been sent.');
    }

    // GET /reset-password/:token
    public function reset_password($token)
    {
        $user = $this->user_model->find_by_reset_token($token);
        if (!$user) {
            $this->redirect_with_message('forgot-password', 'This reset link is invalid or has expired.');
        }

        $data['page_title']   = 'Set New Password';
        $data['token']        = $token;
        $data['content_view'] = 'auth/reset_password';
        $this->load->view('layouts/auth', $data);
    }

    // POST /reset-password/:token
    public function reset_password_post($token)
    {
        $user = $this->user_model->find_by_reset_token($token);
        if (!$user) {
            $this->redirect_with_message('forgot-password', 'This reset link is invalid or has expired.');
        }

        $this->form_validation->set_rules('password',         'Password',             'required|min_length[8]');
        $this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('reset-password/' . $token);
        }

        $this->user_model->update_password($user->id, $this->input->post('password'));
        $this->user_model->clear_reset_token($user->id);

        $this->redirect_with_message('login', 'Password updated. Please log in.');
    }

    private function _send_reset_email($to_email, $to_name, $reset_url)
    {
        $this->load->config('email');
        $this->email->initialize($this->config->config);
        $this->email->to($to_email);
        $this->email->subject('Password Reset — CI3 Shop');
        $this->email->message(
            '<p>Hi ' . htmlspecialchars($to_name) . ',</p>' .
            '<p>Click the link below to reset your password. This link expires in 1 hour.</p>' .
            '<p><a href="' . $reset_url . '">' . $reset_url . '</a></p>'
        );
        $this->email->send();
    }
}
