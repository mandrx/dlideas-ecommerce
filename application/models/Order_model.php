<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends MY_Model
{
    protected $table   = 'orders';
    protected $primary = 'id';

    public function create_from_cart($user_id, $cart_items, $shipping_address, $shipping_cost = 0, $discount = 0)
    {
        // Group items by store
        $by_store = array();
        foreach ($cart_items as $item) {
            $by_store[$item->store_id][] = $item;
        }

        $order_ids = array();
        $this->db->trans_start();

        foreach ($by_store as $store_id => $items) {
            $subtotal = 0;
            foreach ($items as $item) {
                $unit = $item->sale_price ?: $item->price;
                $subtotal += $unit * $item->quantity;
            }
            $total = $subtotal + $shipping_cost - $discount;

            $this->db->insert($this->table, array(
                'user_id'          => $user_id,
                'store_id'         => $store_id,
                'status'           => ORDER_PENDING,
                'subtotal'         => $subtotal,
                'shipping_cost'    => $shipping_cost,
                'discount'         => $discount,
                'total'            => max(0, $total),
                'shipping_address' => json_encode($shipping_address),
                'created_at'       => date('Y-m-d H:i:s'),
            ));
            $order_id = $this->db->insert_id();

            foreach ($items as $item) {
                $unit = $item->sale_price ?: $item->price;
                $this->db->insert('order_items', array(
                    'order_id'             => $order_id,
                    'product_id'           => $item->product_id,
                    'product_name_snapshot'=> $item->name,
                    'quantity'             => $item->quantity,
                    'unit_price'           => $unit,
                ));
                // Decrement stock
                $this->db->set('stock', 'stock - ' . (int)$item->quantity, FALSE)
                         ->where('id', $item->product_id)
                         ->update('products');
            }
            $order_ids[] = $order_id;
        }

        $this->db->trans_complete();
        return $order_ids;
    }

    public function get_for_buyer($user_id, $limit = 20, $offset = 0)
    {
        return $this->db
            ->select('o.*, s.name AS store_name')
            ->from('orders o')
            ->join('stores s', 's.id = o.store_id')
            ->where('o.user_id', $user_id)
            ->order_by('o.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result();
    }

    public function get_detail_for_buyer($order_id, $user_id)
    {
        return $this->db
            ->select('o.*, s.name AS store_name, s.slug AS store_slug')
            ->from('orders o')
            ->join('stores s', 's.id = o.store_id')
            ->where('o.id', $order_id)
            ->where('o.user_id', $user_id)
            ->get()->row();
    }

    public function get_items($order_id)
    {
        return $this->db
            ->select('oi.*, p.slug AS product_slug')
            ->from('order_items oi')
            ->join('products p', 'p.id = oi.product_id', 'left')
            ->where('oi.order_id', $order_id)
            ->get()->result();
    }

    public function get_for_seller($store_id, $limit = 20, $offset = 0)
    {
        return $this->db
            ->select('o.*, u.full_name AS buyer_name, u.email AS buyer_email')
            ->from('orders o')
            ->join('users u', 'u.id = o.user_id')
            ->where('o.store_id', $store_id)
            ->order_by('o.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result();
    }

    public function count_for_seller($store_id)
    {
        return $this->db->where('store_id', $store_id)->count_all_results($this->table);
    }

    public function count_pending_for_seller($store_id)
    {
        return $this->db->where('store_id', $store_id)->where('status', ORDER_PENDING)->count_all_results($this->table);
    }

    public function revenue_for_seller($store_id)
    {
        $row = $this->db->select_sum('total')->where('store_id', $store_id)->get($this->table)->row();
        return $row ? (float) $row->total : 0.0;
    }

    public function get_detail_for_seller($order_id, $store_id)
    {
        return $this->db
            ->select('o.*, u.full_name AS buyer_name, u.email AS buyer_email')
            ->from('orders o')
            ->join('users u', 'u.id = o.user_id')
            ->where('o.id', $order_id)
            ->where('o.store_id', $store_id)
            ->get()->row();
    }
}
