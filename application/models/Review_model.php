<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Review_model extends MY_Model
{
    protected $table   = 'reviews';
    protected $primary = 'id';

    public function get_paginated($limit = 20, $offset = 0, $status = null)
    {
        $this->db->select('r.*, u.full_name AS reviewer_name, p.name AS product_name, p.slug AS product_slug')
                 ->from('reviews r')
                 ->join('users u', 'u.id = r.user_id')
                 ->join('products p', 'p.id = r.product_id')
                 ->order_by('r.created_at', 'DESC')
                 ->limit($limit, $offset);
        if ($status) {
            $this->db->where('r.status', $status);
        }
        return $this->db->get()->result();
    }

    public function count_all($status = null)
    {
        if ($status) {
            $this->db->where('status', $status);
        }
        return $this->db->count_all_results($this->table);
    }

    public function set_status($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }

    public function get_for_product($product_id)
    {
        return $this->db
            ->select('r.*, u.full_name AS reviewer_name')
            ->from('reviews r')
            ->join('users u', 'u.id = r.user_id')
            ->where('r.product_id', $product_id)
            ->where('r.status', REVIEW_APPROVED)
            ->order_by('r.created_at', 'DESC')
            ->get()
            ->result();
    }

    public function has_reviewed($user_id, $product_id)
    {
        return $this->db
            ->where('user_id', $user_id)
            ->where('product_id', $product_id)
            ->count_all_results($this->table) > 0;
    }

    public function can_review($user_id, $product_id)
    {
        // User must have a delivered order containing this product
        $has_order = $this->db
            ->select('oi.id')
            ->from('order_items oi')
            ->join('orders o', 'o.id = oi.order_id')
            ->where('o.user_id', $user_id)
            ->where('oi.product_id', $product_id)
            ->where('o.status', ORDER_DELIVERED)
            ->limit(1)
            ->get()
            ->row();

        if (!$has_order) return false;
        return !$this->has_reviewed($user_id, $product_id);
    }
}
