<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_login();
        $this->load->model('order_model');
    }

    public function index()
    {
        $orders = $this->order_model->get_for_buyer($this->current_user->id);
        $this->_render('order/index', array(
            'page_title' => 'My Orders',
            'orders'     => $orders,
        ));
    }

    public function detail($id)
    {
        $order = $this->order_model->get_detail_for_buyer($id, $this->current_user->id);
        if (!$order) show_error('Order not found.', 404);

        $items   = $this->order_model->get_items($order->id);
        $address = json_decode($order->shipping_address);

        $this->_render('order/detail', array(
            'page_title' => 'Order #' . $order->id,
            'order'      => $order,
            'items'      => $items,
            'address'    => $address,
        ));
    }

    private function _render($view, $data = array())
    {
        $data['content_view'] = $view;
        $this->load->view('layouts/main', $data);
    }
}
