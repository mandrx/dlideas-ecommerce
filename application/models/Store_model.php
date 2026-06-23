<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_model extends MY_Model
{
    protected $table   = 'stores';
    protected $primary = 'id';

    public function find_by_user($user_id)
    {
        return $this->db
            ->where('user_id', $user_id)
            ->get($this->table)
            ->row();
    }

    public function find_by_slug($slug)
    {
        return $this->db
            ->where('slug', $slug)
            ->where('status', STORE_ACTIVE)
            ->get($this->table)
            ->row();
    }

    public function slug_exists($slug, $exclude_id = null)
    {
        $this->db->where('slug', $slug);
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function create_for_user($user_id, $name, $description = '')
    {
        $slug = $this->generate_unique_slug($name);
        return $this->insert(array(
            'user_id'     => $user_id,
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'status'      => STORE_PENDING,
            'created_at'  => date('Y-m-d H:i:s'),
        ));
    }

    public function get_pending()
    {
        return $this->db
            ->where('status', STORE_PENDING)
            ->order_by('created_at', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_paginated($limit = 20, $offset = 0)
    {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get($this->table)
            ->result();
    }

    private function generate_unique_slug($name)
    {
        $base = slugify($name);
        $slug = $base;
        $i    = 1;
        while ($this->slug_exists($slug)) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}
