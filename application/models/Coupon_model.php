<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupon_model extends MY_Model
{
    protected $table   = 'coupons';
    protected $primary = 'id';

    public function get_all()
    {
        return $this->db
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function find_by_code($code)
    {
        return $this->db
            ->where('code', strtoupper($code))
            ->where('status', 'active')
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get($this->table)
            ->row();
    }

    public function toggle_status($id)
    {
        $coupon = $this->find($id);
        if (!$coupon) return false;
        $new_status = $coupon->status === 'active' ? 'inactive' : 'active';
        return $this->update($id, ['status' => $new_status]);
    }

    public function create($data)
    {
        return $this->insert([
            'code'       => strtoupper($data['code']),
            'type'       => $data['type'],
            'value'      => $data['value'],
            'min_order'  => $data['min_order'] ?? 0,
            'max_uses'   => $data['max_uses'] ?? 1,
            'expires_at' => $data['expires_at'],
            'status'     => 'active',
        ]);
    }

    public function apply_discount($coupon, $subtotal)
    {
        if ($coupon->type === 'percent') {
            return round($subtotal * $coupon->value / 100, 2);
        }
        return min($coupon->value, $subtotal);
    }

    public function validate($code, $user_id, $subtotal)
    {
        $coupon = $this->find_by_code($code);

        if (!$coupon) {
            return ['ok' => false, 'error' => 'Invalid or expired coupon code.'];
        }
        if ($coupon->used_count >= $coupon->max_uses) {
            return ['ok' => false, 'error' => 'This coupon has reached its usage limit.'];
        }
        if ($subtotal < $coupon->min_order) {
            return ['ok' => false, 'error' => 'Minimum order of RM ' . number_format($coupon->min_order, 2) . ' required.'];
        }
        // Check this user hasn't already used it
        $already = $this->db
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $user_id)
            ->count_all_results('coupon_uses');
        if ($already > 0) {
            return ['ok' => false, 'error' => 'You have already used this coupon.'];
        }

        $discount = $this->apply_discount($coupon, $subtotal);
        return ['ok' => true, 'coupon' => $coupon, 'discount' => $discount];
    }

    public function redeem($coupon_id, $user_id, $order_id)
    {
        $this->db->trans_start();
        $this->db->insert('coupon_uses', [
            'coupon_id' => $coupon_id,
            'user_id'   => $user_id,
            'order_id'  => $order_id,
            'used_at'   => date('Y-m-d H:i:s'),
        ]);
        $this->db->set('used_count', 'used_count + 1', FALSE)
                 ->where('id', $coupon_id)
                 ->update($this->table);
        $this->db->trans_complete();
    }
}
