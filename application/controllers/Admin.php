<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role_in([ROLE_ADMIN, ROLE_OWNER]);
        $this->load->model(['user_model', 'store_model', 'product_model', 'order_model', 'review_model', 'coupon_model', 'contact_model', 'category_model', 'visitor_model']);
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
            ->select('p.*, s.name AS store_name, c.name AS category_name, pi.image_path AS primary_image')
            ->from('products p')
            ->join('stores s', 's.id = p.store_id')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('product_images pi', 'pi.product_id = p.id AND pi.is_primary = 1', 'left')
            ->order_by('p.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->result();

        $total = $this->db->count_all('products');

        $this->_render('admin/products', [
            'page_title' => 'All Products',
            'products'   => $rows,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $limit,
        ]);
    }

    public function delete_product($id)
    {
        if ($this->input->method(TRUE) !== 'POST') show_error('Method Not Allowed', 405);
        $product = $this->product_model->find($id);
        if (!$product) show_error('Product not found.', 404);
        $this->db->delete('cart_items', ['product_id' => $id]);
        $this->product_model->delete($id);
        $this->redirect_with_message('admin/products', 'Product deleted.');
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

    // --- Contact Messages ---

    public function contact_messages()
    {
        $this->require_owner();
        $this->_render('admin/contact_messages', [
            'page_title' => 'Contact Messages',
            'messages'   => $this->contact_model->get_all(),
        ]);
    }

    public function view_message($id)
    {
        $this->require_owner();
        $message = $this->contact_model->get_by_id($id);
        if (!$message) {
            show_404();
        }
        $this->_render('admin/contact_message_detail', [
            'page_title' => 'Message #' . $id,
            'message'    => $message,
        ]);
    }

    // --- Categories ---

    public function categories()
    {
        $this->_render('admin/categories', [
            'page_title'  => 'Manage Categories',
            'categories'  => $this->category_model->get_all_with_parent(),
        ]);
    }

    public function category_form($id = NULL)
    {
        $category = $id ? $this->category_model->find($id) : NULL;
        if ($id && !$category) {
            show_404();
        }
        $this->_render('admin/category_form', [
            'page_title' => $id ? 'Edit Category' : 'New Category',
            'category'   => $category,
        ]);
    }

    public function category_save()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'required|max_length[100]');
        $this->form_validation->set_rules('sort_order', 'Sort Order', 'integer');

        $id = (int) $this->input->post('id');

        if (!$this->form_validation->run()) {
            return $this->category_form($id ?: NULL);
        }

        $data = [
            'name'       => $this->input->post('name'),
            'sort_order' => (int) $this->input->post('sort_order'),
        ];

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $upload_path = FCPATH . 'assets/img/categories/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, TRUE);
            }
            $this->load->library('upload', [
                'upload_path'   => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|gif|webp',
                'max_size'      => 2048,
                'file_name'     => 'cat-' . uniqid(),
            ]);
            if ($this->upload->do_upload('image')) {
                $info = $this->upload->data();
                $data['image'] = 'assets/img/categories/' . $info['file_name'];

                // Delete old image on edit
                if ($id) {
                    $old = $this->category_model->find($id);
                    if ($old && !empty($old->image) && file_exists(FCPATH . $old->image)) {
                        @unlink(FCPATH . $old->image);
                    }
                }
            } else {
                $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                return redirect('admin/categories' . ($id ? '/edit/' . $id : '/new'));
            }
        }

        if ($id) {
            $this->category_model->update($id, $data);
            $this->session->set_flashdata('success', 'Category updated.');
        } else {
            $this->category_model->create($data);
            $this->session->set_flashdata('success', 'Category created.');
        }

        redirect('admin/categories');
    }

    public function category_delete($id)
    {
        $this->category_model->delete($id);
        $this->session->set_flashdata('success', 'Category deleted.');
        redirect('admin/categories');
    }

    // --- Visitors ---

    public function visitors()
    {
        $this->require_owner();

        $limit  = 25;
        $page   = max(1, (int) $this->input->get('page'));
        $offset = ($page - 1) * $limit;

        $filters = [
            'date_from' => $this->input->get('date_from') ?: '',
            'date_to'   => $this->input->get('date_to')   ?: '',
            'country'   => $this->input->get('country')   ?: '',
            'bot'       => $this->input->get('bot')        ?: '',
        ];

        $total = $this->visitor_model->count_logs($filters);

        $this->load->library('pagination');
        $this->pagination->initialize([
            'base_url'             => base_url('admin/visitors'),
            'total_rows'           => $total,
            'per_page'             => $limit,
            'uri_segment'          => 0,
            'use_page_numbers'     => TRUE,
            'query_string_segment' => 'page',
            'full_tag_open'        => '<ul class="pagination mb-0">',
            'full_tag_close'       => '</ul>',
            'first_tag_open'       => '<li class="page-item">',  'first_tag_close' => '</li>',
            'last_tag_open'        => '<li class="page-item">',  'last_tag_close'  => '</li>',
            'next_tag_open'        => '<li class="page-item">',  'next_tag_close'  => '</li>',
            'prev_tag_open'        => '<li class="page-item">',  'prev_tag_close'  => '</li>',
            'cur_tag_open'         => '<li class="page-item active"><a class="page-link" href="#">',
            'cur_tag_close'        => '</a></li>',
            'num_tag_open'         => '<li class="page-item">',  'num_tag_close'   => '</li>',
            'num_links'            => 3,
            'attributes'           => ['class' => 'page-link'],
            'reuse_query_string'   => TRUE,
        ]);

        $this->_render('admin/visitors', [
            'page_title'    => 'Visitor Analytics',
            'stats'         => $this->visitor_model->get_stats(),
            'top_countries' => $this->visitor_model->get_top_countries(5),
            'logs'          => $this->visitor_model->get_logs($filters, $limit, $offset),
            'total'         => $total,
            'filters'       => $filters,
            'pagination'    => $this->pagination->create_links(),
        ]);
    }

    private function _render($view, $data = [])
    {
        $data['content_view'] = $view;
        $this->load->view('layouts/dashboard', $data);
    }
}
