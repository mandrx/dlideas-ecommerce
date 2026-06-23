<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Seller extends MY_Controller
{
    private $store;

    public function __construct()
    {
        parent::__construct();
        $this->require_role(ROLE_SELLER);
        $this->load->model('store_model');
        $this->load->model('product_model');
        $this->load->model('category_model');

        $this->store = $this->store_model->find_by_user($this->current_user->id);
        if (!$this->store) {
            show_error('No store found for your account.', 404);
        }
        $this->load->vars(array('store' => $this->store));
    }

    public function dashboard()
    {
        $total    = $this->product_model->count_by_store($this->store->id);
        $products = $this->product_model->get_by_store($this->store->id, 5, 0);

        $this->_render('seller/dashboard', array(
            'page_title'     => 'Seller Dashboard',
            'total_products' => $total,
            'recent_products'=> $products,
        ));
    }

    public function products()
    {
        $per_page = 15;
        $page     = max(1, (int) $this->input->get('page'));
        $offset   = ($page - 1) * $per_page;
        $total    = $this->product_model->count_by_store($this->store->id);
        $products = $this->product_model->get_by_store($this->store->id, $per_page, $offset);

        $this->_render('seller/products', array(
            'page_title' => 'My Products',
            'products'   => $products,
            'total'      => $total,
            'per_page'   => $per_page,
            'page'       => $page,
        ));
    }

    public function add_product()
    {
        if ($this->store->status !== STORE_ACTIVE) {
            $this->redirect_with_message('seller', 'Your store must be active before you can add products.', 'error');
        }

        $this->_set_product_validation();

        if ($this->form_validation->run() === FALSE) {
            $this->_render('seller/product_form', array(
                'page_title'  => 'Add Product',
                'categories'  => $this->category_model->get_dropdown(),
                'action'      => 'add',
                'product'     => null,
                'images'      => array(),
            ));
            return;
        }

        $tags       = $this->_parse_tags($this->input->post('tags'));
        $product_id = $this->product_model->create(
            $this->store->id,
            $this->_collect_post(),
            $tags
        );

        // Redirect to edit so seller can upload images immediately
        $this->session->set_flashdata('success', 'Product created! Now add some images.');
        redirect('seller/products/edit/' . $product_id);
    }

    public function edit_product($id)
    {
        $product = $this->product_model->find($id);
        if (!$product || $product->store_id != $this->store->id) {
            show_error('Product not found.', 404);
        }

        $tags_raw = $this->product_model->get_tags($product->id);
        $tag_str  = implode(', ', array_column($tags_raw, 'tag'));

        $this->_set_product_validation();

        if ($this->form_validation->run() === FALSE) {
            $this->_render('seller/product_form', array(
                'page_title' => 'Edit Product',
                'categories' => $this->category_model->get_dropdown(),
                'action'     => 'edit',
                'product'    => $product,
                'tag_str'    => $tag_str,
                'images'     => $this->product_model->get_images($product->id),
            ));
            return;
        }

        $tags = $this->_parse_tags($this->input->post('tags'));
        $this->product_model->update_product($id, $this->_collect_post(), $tags);

        $this->redirect_with_message('seller/products', 'Product updated successfully.');
    }

    public function delete_product($id)
    {
        $product = $this->product_model->find($id);
        if (!$product || $product->store_id != $this->store->id) {
            show_error('Product not found.', 404);
        }
        $this->product_model->delete($id);
        $this->redirect_with_message('seller/products', 'Product deleted.');
    }

    public function store_settings()
    {
        $this->_render('seller/store_settings', array(
            'page_title' => 'Store Settings',
        ));
    }

    public function save_store_settings()
    {
        $this->form_validation->set_rules('name',        'Store Name',  'required|trim|max_length[255]');
        $this->form_validation->set_rules('description', 'Description', 'trim');

        if ($this->form_validation->run() === FALSE) {
            $this->redirect_with_message('seller/store-settings', 'Please fill in all required fields.', 'error');
            return;
        }

        $data = array(
            'name'        => $this->input->post('name',        TRUE),
            'description' => $this->input->post('description', TRUE),
        );
        $this->store_model->update($this->store->id, $data);
        $this->redirect_with_message('seller/store-settings', 'Store settings saved.');
    }

    public function orders()
    {
        $per_page = 15;
        $page     = max(1, (int) $this->input->get('page'));
        $offset   = ($page - 1) * $per_page;
        $this->load->model('order_model');

        $this->_render('seller/orders', array(
            'page_title' => 'My Orders',
            'orders'     => $this->order_model->get_for_seller($this->store->id, $per_page, $offset),
            'total'      => $this->order_model->count_for_seller($this->store->id),
            'per_page'   => $per_page,
            'page'       => $page,
        ));
    }

    public function order_detail($id)
    {
        $this->load->model('order_model');
        $order = $this->order_model->get_detail_for_seller((int)$id, $this->store->id);
        if (!$order) show_error('Order not found.', 404);

        $this->form_validation->set_rules('tracking_number', 'Tracking Number', 'trim|max_length[100]');

        if ($this->input->post('tracking_number') !== false && $this->form_validation->run()) {
            $tracking = $this->input->post('tracking_number', TRUE);
            $update   = array('tracking_number' => $tracking);
            if ($order->status === ORDER_PROCESSING || $order->status === ORDER_PAID) {
                $update['status'] = ORDER_SHIPPED;
            }
            $this->db->update('orders', $update, array('id' => $order->id));
            if ($tracking) {
                $this->db->update('shipments', array(
                    'tracking_number' => $tracking,
                    'status'          => 'in_transit',
                ), array('order_id' => $order->id));
            }
            $this->redirect_with_message('seller/orders', 'Order updated.');
        }

        $this->_render('seller/order_detail', array(
            'page_title' => 'Order #' . $order->id,
            'order'      => $order,
            'items'      => $this->order_model->get_items($order->id),
            'address'    => json_decode($order->shipping_address),
        ));
    }

    // ----------------------------------------------------------------

    private function _set_product_validation()
    {
        $this->form_validation->set_rules('name',        'Product Name', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('description', 'Description',  'trim');
        $this->form_validation->set_rules('price',       'Price',        'required|numeric');
        $this->form_validation->set_rules('sale_price',  'Sale Price',   'trim|numeric');
        $this->form_validation->set_rules('stock',       'Stock',        'required|integer');
        $this->form_validation->set_rules('weight',      'Weight',       'trim|numeric');
        $this->form_validation->set_rules('status',      'Status',       'required|in_list[draft,active,inactive]');
    }

    private function _collect_post()
    {
        return array(
            'name'        => $this->input->post('name'),
            'description' => $this->input->post('description'),
            'category_id' => (int) $this->input->post('category_id') ?: null,
            'price'       => $this->input->post('price'),
            'sale_price'  => $this->input->post('sale_price'),
            'stock'       => $this->input->post('stock'),
            'weight'      => $this->input->post('weight'),
            'status'      => $this->input->post('status'),
        );
    }

    private function _parse_tags($raw)
    {
        if (!$raw) return array();
        return array_filter(array_map('trim', explode(',', $raw)));
    }

    private function _render($view, $data = array())
    {
        $data['content_view'] = $view;
        $this->load->view('layouts/dashboard', $data);
    }
}
