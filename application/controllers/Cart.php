<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('cart_model');
        $this->load->model('product_model');
        $this->load->model('order_model');
    }

    public function index()
    {
        $cart  = $this->_get_cart();
        $items = $this->cart_model->get_items($cart->id);

        $subtotal = 0;
        foreach ($items as $item) {
            $unit      = $item->sale_price ?: $item->price;
            $item->unit_price = $unit;
            $item->line_total = $unit * $item->quantity;
            $subtotal += $item->line_total;
        }

        $this->_render('cart/index', array(
            'page_title' => 'Your Cart',
            'items'      => $items,
            'subtotal'   => $subtotal,
        ));
    }

    public function add()
    {
        $product_id = (int) $this->input->post('product_id');
        $quantity   = max(1, (int) $this->input->post('quantity'));

        $product = $this->product_model->find($product_id);
        if (!$product || $product->status !== PRODUCT_ACTIVE) {
            $this->redirect_with_message('cart', 'Product not available.', 'error');
        }
        if ($product->stock < $quantity) {
            $this->redirect_with_message(
                'product/' . $product->slug,
                'Not enough stock. Only ' . $product->stock . ' left.',
                'error'
            );
        }

        $cart  = $this->_get_cart();
        $price = $product->sale_price ?: $product->price;
        $this->cart_model->add_item($cart->id, $product_id, $quantity, $price);

        $this->redirect_with_message('cart', 'Item added to cart.');
    }

    public function update()
    {
        $item_id  = (int) $this->input->post('item_id');
        $quantity = (int) $this->input->post('quantity');
        $cart     = $this->_get_cart();

        if ($quantity <= 0) {
            $this->cart_model->remove_item($cart->id, $item_id);
        } else {
            $this->cart_model->update_item($cart->id, $item_id, $quantity);
        }
        redirect('cart');
    }

    public function remove($item_id)
    {
        $cart = $this->_get_cart();
        $this->cart_model->remove_item($cart->id, (int)$item_id);
        redirect('cart');
    }

    public function checkout()
    {
        $this->require_login();

        $cart  = $this->_get_cart();
        $items = $this->cart_model->get_items($cart->id);

        if (empty($items)) {
            $this->redirect_with_message('cart', 'Your cart is empty.', 'error');
        }

        $this->load->library('payment');

        $subtotal = 0;
        foreach ($items as $item) {
            $unit             = $item->sale_price ?: $item->price;
            $item->unit_price = $unit;
            $item->line_total = $unit * $item->quantity;
            $subtotal        += $item->line_total;
        }

        $out_items = array_map(function($item) {
            return array(
                'id'         => $item->id,
                'name'       => $item->name,
                'image'      => $item->image ? base_url($item->image) : null,
                'unit_price' => (float)$item->unit_price,
                'quantity'   => (int)$item->quantity,
                'line_total' => (float)$item->line_total,
                'store_name' => $item->store_name,
            );
        }, $items);

        $this->_render('cart/checkout', array(
            'page_title'    => 'Checkout',
            'items_json'    => json_encode($out_items),
            'subtotal'      => $subtotal,
            'shipping_cost' => 10.00,
            'stripe_key'    => $this->payment->get_publishable_key(),
            'scripts'       => array('checkout'),
        ));
    }

    /**
     * POST /cart/save-checkout-session
     * Called by CheckoutForm.vue before confirmCardPayment.
     * Saves shipping address and coupon code to the CI3 session so
     * Cart::confirm() can read them on the GET redirect from Stripe.
     */
    public function save_checkout_session()
    {
        $this->require_login();

        $shipping_raw = $this->input->post('shipping', TRUE);
        $coupon_code  = $this->input->post('coupon_code', TRUE) ?: '';

        $shipping = array();
        if ($shipping_raw) {
            $decoded = json_decode($shipping_raw, TRUE);
            if (is_array($decoded)) {
                $allowed  = array('full_name', 'phone', 'address_line', 'city', 'postcode', 'state');
                foreach ($allowed as $key) {
                    $shipping[$key] = isset($decoded[$key]) ? trim($decoded[$key]) : '';
                }
            }
        }

        $this->session->set_userdata('checkout_shipping', $shipping);
        $this->session->set_userdata('checkout_coupon',   $coupon_code);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'ok'   => TRUE,
                'csrf' => array(
                    'name' => $this->security->get_csrf_token_name(),
                    'hash' => $this->security->get_csrf_hash(),
                ),
            )));
    }

    public function confirm()
    {
        $this->require_login();

        $payment_intent_id = $this->input->get('payment_intent');
        if (!$payment_intent_id) {
            $this->redirect_with_message('checkout', 'Invalid payment reference.', 'error');
        }

        $this->load->library('payment');
        $this->load->model('coupon_model');

        try {
            $intent = $this->payment->retrieve_payment_intent($payment_intent_id);
        } catch (Exception $e) {
            $this->redirect_with_message('checkout', 'Payment verification failed.', 'error');
            return;
        }

        if ($intent->status !== 'succeeded') {
            $this->redirect_with_message('checkout', 'Payment was not completed. Please try again.', 'error');
            return;
        }

        $cart  = $this->_get_cart();
        $items = $this->cart_model->get_items($cart->id);

        if (empty($items)) {
            redirect('orders');
            return;
        }

        // Shipping stored in session by save_checkout_session() before Stripe redirect
        $session_shipping = $this->session->userdata('checkout_shipping');
        $shipping_address = is_array($session_shipping) ? $session_shipping : array(
            'full_name'    => '',
            'phone'        => '',
            'address_line' => '',
            'city'         => '',
            'postcode'     => '',
            'state'        => '',
        );

        $coupon_code   = $this->session->userdata('checkout_coupon') ?: ($intent->metadata->coupon_code ?? '');
        $shipping_cost = 10.00;
        $discount      = 0.00;
        $validation    = null;

        if ($coupon_code) {
            $subtotal = 0;
            foreach ($items as $item) {
                $unit      = $item->sale_price ?: $item->price;
                $subtotal += $unit * $item->quantity;
            }
            $validation = $this->coupon_model->validate($coupon_code, $this->current_user->id, $subtotal);
            if ($validation['ok']) {
                $discount = $validation['discount'];
            }
        }

        $order_ids = $this->order_model->create_from_cart(
            $this->current_user->id,
            $items,
            $shipping_address,
            $shipping_cost,
            $discount
        );

        // Record payment rows and mark orders paid
        foreach ($order_ids as $order_id) {
            $this->db->insert('payments', array(
                'order_id'    => $order_id,
                'gateway'     => 'stripe',
                'gateway_ref' => $intent->id,
                'amount'      => $intent->amount / 100,
                'status'      => 'paid',
                'paid_at'     => date('Y-m-d H:i:s'),
                'payload'     => json_encode($intent->toArray()),
            ));
            $this->db->update('orders', array('status' => ORDER_PAID), array('id' => $order_id));

            if ($coupon_code && $validation && $validation['ok']) {
                $this->coupon_model->redeem($validation['coupon']->id, $this->current_user->id, $order_id);
            }
        }

        // Clean up session and cart
        $this->session->unset_userdata('checkout_shipping');
        $this->session->unset_userdata('checkout_coupon');
        $this->cart_model->clear($cart->id);

        $this->session->set_flashdata('success', 'Payment confirmed! Order #' . implode(', #', $order_ids));
        redirect('orders');
    }

    // ----------------------------------------------------------------

    private function _get_cart()
    {
        $user_id    = $this->current_user ? $this->current_user->id : null;
        $session_id = $this->session->session_id;
        return $this->cart_model->get_or_create($session_id, $user_id);
    }

    private function _render($view, $data = array())
    {
        $data['content_view'] = $view;
        $this->load->view('layouts/main', $data);
    }
}
