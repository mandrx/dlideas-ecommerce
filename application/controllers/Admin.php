<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role(ROLE_ADMIN);
        $this->load->model(['user_model', 'store_model', 'product_model', 'order_model', 'review_model', 'coupon_model']);
    }

    public function dashboard()
    {
        $this->_render('admin/dashboard', [
            'page_title'     => 'Admin Dashboard',
            'total_users'    => $this->db->count_all('users'),
            'total_stores'   => $this->db->count_all('stores'),
            'total_orders'   => $this->db->count_all('orders'),
            'total_products' => $this->db->count_all('products'),
            'pending_stores' => $this->store_model->get_pending(),
            'pending_reviews'=> $this->review_model->count_all(REVIEW_PENDING),
        ]);
    }

    // --- Users ---

    public function users()
    {
        $page   = max(1, (int) $this->input->get('page'));
        $limit  = 20;
        $offset = ($page - 1) * $limit;
        $total  = $this->db->count_all('users');

        $this->load->library('pagination');
        $this->pagination->initialize([
            'base_url'    => base_url('admin/users'),
            'total_rows'  => $total,
            'per_page'    => $limit,
            'uri_segment' => 0,
            'use_page_numbers' => TRUE,
            'query_string_segment' => 'page',
            'full_tag_open'  => '<ul class="pagination mb-0">',
            'full_tag_close' => '</ul>',
            'first_tag_open' => '<li class="page-item">',  'first_tag_close' => '</li>',
            'last_tag_open'  => '<li class="page-item">',  'last_tag_close'  => '</li>',
            'next_tag_open'  => '<li class="page-item">',  'next_tag_close'  => '</li>',
            'prev_tag_open'  => '<li class="page-item">',  'prev_tag_close'  => '</li>',
            'cur_tag_open'   => '<li class="page-item active"><a class="page-link" href="#">',
            'cur_tag_close'  => '</a></li>',
            'num_tag_open'   => '<li class="page-item">',  'num_tag_close'   => '</li>',
            'attributes'     => ['class' => 'page-link'],
        ]);

        $this->_render('admin/users', [
            'page_title' => 'Manage Users',
            'users'      => $this->user_model->get_paginated($limit, $offset),
            'pagination' => $this->pagination->create_links(),
        ]);
    }

    public function ban_user($id)
    {
        $user = $this->user_model->find($id);
        if (!$user || $user->role === ROLE_ADMIN) show_error('Not allowed.', 403);
        $this->user_model->update($id, ['status' => USER_BANNED]);
        $this->redirect_with_message('admin/users', 'User banned.');
    }

    public function unban_user($id)
    {
        $this->user_model->update($id, ['status' => USER_ACTIVE]);
        $this->redirect_with_message('admin/users', 'User unbanned.');
    }

    // --- Stores ---

    public function stores()
    {
        $page   = max(1, (int) $this->input->get('page'));
        $limit  = 20;
        $offset = ($page - 1) * $limit;
        $total  = $this->db->count_all('stores');

        $this->load->library('pagination');
        $this->pagination->initialize([
            'base_url'             => base_url('admin/stores'),
            'total_rows'           => $total,
            'per_page'             => $limit,
            'use_page_numbers'     => TRUE,
            'query_string_segment' => 'page',
            'full_tag_open'  => '<ul class="pagination mb-0">',
            'full_tag_close' => '</ul>',
            'num_tag_open'   => '<li class="page-item">',
            'num_tag_close'  => '</li>',
            'cur_tag_open'   => '<li class="page-item active"><a class="page-link" href="#">',
            'cur_tag_close'  => '</a></li>',
            'attributes'     => ['class' => 'page-link'],
        ]);

        $this->_render('admin/stores', [
            'page_title' => 'Manage Stores',
            'stores'     => $this->store_model->get_paginated($limit, $offset),
            'pagination' => $this->pagination->create_links(),
        ]);
    }

    public function approve_store($id)
    {
        $store = $this->store_model->find($id);
        if (!$store) show_error('Store not found.', 404);
        $this->store_model->update($id, ['status' => STORE_ACTIVE]);
        $this->redirect_with_message('admin/stores', 'Store approved.');
    }

    public function suspend_store($id)
    {
        $this->store_model->update($id, ['status' => STORE_SUSPENDED]);
        $this->redirect_with_message('admin/stores', 'Store suspended.');
    }

    // --- Products ---

    public function products()
    {
        $page   = max(1, (int) $this->input->get('page'));
        $limit  = 20;
        $offset = ($page - 1) * $limit;

        $rows = $this->db
            ->select('p.*, s.name AS store_name, c.name AS category_name')
            ->from('products p')
            ->join('stores s', 's.id = p.store_id')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->order_by('p.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->result();

        $total = $this->db->count_all('products');

        $this->_render('admin/products', [
            'page_title' => 'All Products',
            'products'   => $rows,
            'total'      => $total,
        ]);
    }

    // --- Orders ---

    public function orders()
    {
        $page   = max(1, (int) $this->input->get('page'));
        $limit  = 20;
        $offset = ($page - 1) * $limit;

        $rows = $this->db
            ->select('o.*, s.name AS store_name, u.full_name AS buyer_name')
            ->from('orders o')
            ->join('stores s', 's.id = o.store_id')
            ->join('users u', 'u.id = o.user_id')
            ->order_by('o.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->result();

        $total = $this->db->count_all('orders');

        $this->_render('admin/orders', [
            'page_title' => 'All Orders',
            'orders'     => $rows,
            'total'      => $total,
        ]);
    }

    // --- Reviews ---

    public function reviews()
    {
        $page   = max(1, (int) $this->input->get('page'));
        $limit  = 20;
        $offset = ($page - 1) * $limit;

        $this->_render('admin/reviews', [
            'page_title' => 'Manage Reviews',
            'reviews'    => $this->review_model->get_paginated($limit, $offset),
        ]);
    }

    public function approve_review($id)
    {
        $this->review_model->set_status($id, REVIEW_APPROVED);
        $this->redirect_with_message('admin/reviews', 'Review approved.');
    }

    public function reject_review($id)
    {
        $this->review_model->set_status($id, REVIEW_REJECTED);
        $this->redirect_with_message('admin/reviews', 'Review rejected.');
    }

    // --- Coupons ---

    public function coupons()
    {
        $this->_render('admin/coupons', [
            'page_title' => 'Manage Coupons',
            'coupons'    => $this->coupon_model->get_all(),
        ]);
    }

    public function add_coupon()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('code',       'Code',       'required|max_length[50]');
        $this->form_validation->set_rules('type',       'Type',       'required|in_list[percent,fixed]');
        $this->form_validation->set_rules('value',      'Value',      'required|numeric');
        $this->form_validation->set_rules('min_order',  'Min Order',  'numeric');
        $this->form_validation->set_rules('max_uses',   'Max Uses',   'required|is_natural_no_zero');
        $this->form_validation->set_rules('expires_at', 'Expires At', 'required');

        if ($this->form_validation->run()) {
            $this->coupon_model->create($this->input->post());
            $this->redirect_with_message('admin/coupons', 'Coupon created.');
        } else {
            $this->_render('admin/coupon_form', ['page_title' => 'Add Coupon']);
        }
    }

    public function toggle_coupon($id)
    {
        $this->coupon_model->toggle_status($id);
        $this->redirect_with_message('admin/coupons', 'Coupon status updated.');
    }

    private function _render($view, $data = [])
    {
        $data['content_view'] = $view;
        $this->load->view('layouts/dashboard', $data);
    }
}
