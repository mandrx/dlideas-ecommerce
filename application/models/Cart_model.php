<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart_model extends MY_Model
{
    protected $table   = 'carts';
    protected $primary = 'id';

    public function get_or_create($session_id, $user_id = null)
    {
        if ($user_id) {
            $cart = $this->db->where('user_id', $user_id)->get($this->table)->row();
        } else {
            $cart = $this->db->where('session_id', $session_id)->where('user_id IS NULL')->get($this->table)->row();
        }
        if ($cart) return $cart;

        $id = $this->insert(array(
            'user_id'    => $user_id,
            'session_id' => $session_id,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return $this->find($id);
    }

    public function get_items($cart_id)
    {
        return $this->db
            ->select('ci.*, p.name, p.slug, p.price, p.sale_price, p.stock, p.status,
                s.name AS store_name, s.id AS store_id,
                (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS image')
            ->from('cart_items ci')
            ->join('products p', 'p.id = ci.product_id')
            ->join('stores s', 's.id = p.store_id')
            ->where('ci.cart_id', $cart_id)
            ->get()->result();
    }

    public function add_item($cart_id, $product_id, $quantity, $price)
    {
        $existing = $this->db
            ->where('cart_id', $cart_id)
            ->where('product_id', $product_id)
            ->get('cart_items')->row();

        if ($existing) {
            $this->db->where('id', $existing->id)
                     ->update('cart_items', array('quantity' => $existing->quantity + $quantity));
        } else {
            $this->db->insert('cart_items', array(
                'cart_id'        => $cart_id,
                'product_id'     => $product_id,
                'quantity'       => $quantity,
                'price_snapshot' => $price,
            ));
        }
    }

    public function update_item($cart_id, $item_id, $quantity)
    {
        $this->db->where('id', $item_id)->where('cart_id', $cart_id)
                 ->update('cart_items', array('quantity' => $quantity));
    }

    public function remove_item($cart_id, $item_id)
    {
        $this->db->delete('cart_items', array('id' => $item_id, 'cart_id' => $cart_id));
    }

    public function clear($cart_id)
    {
        $this->db->delete('cart_items', array('cart_id' => $cart_id));
    }

    public function item_count($cart_id)
    {
        return $this->db->where('cart_id', $cart_id)->count_all_results('cart_items');
    }

    public function merge_guest_cart($session_id, $user_id)
    {
        $guest_cart = $this->db
            ->where('session_id', $session_id)
            ->where('user_id IS NULL')
            ->get($this->table)->row();

        if (!$guest_cart) return;

        $user_cart = $this->db->where('user_id', $user_id)->get($this->table)->row();

        if (!$user_cart) {
            $this->db->where('id', $guest_cart->id)
                     ->update($this->table, array('user_id' => $user_id));
            return;
        }

        $this->db->trans_start();
        $this->db->where('cart_id', $guest_cart->id)
                 ->update('cart_items', array('cart_id' => $user_cart->id));
        $this->db->delete($this->table, array('id' => $guest_cart->id));
        $this->db->trans_complete();
    }
}
