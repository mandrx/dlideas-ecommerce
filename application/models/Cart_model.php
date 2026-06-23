<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart_model extends MY_Model
{
    protected $table   = 'carts';
    protected $primary = 'id';

    public function merge_guest_cart($session_id, $user_id)
    {
        $guest_cart = $this->db
            ->where('session_id', $session_id)
            ->where('user_id IS NULL')
            ->get($this->table)
            ->row();

        if (!$guest_cart) return;

        $user_cart = $this->db
            ->where('user_id', $user_id)
            ->get($this->table)
            ->row();

        if (!$user_cart) {
            $this->db->where('id', $guest_cart->id)
                     ->update($this->table, array('user_id' => $user_id));
            return;
        }

        // Move guest items into user cart
        $this->db->trans_start();
        $this->db->where('cart_id', $guest_cart->id)
                 ->update('cart_items', array('cart_id' => $user_cart->id));
        $this->db->delete($this->table, array('id' => $guest_cart->id));
        $this->db->trans_complete();
    }
}
