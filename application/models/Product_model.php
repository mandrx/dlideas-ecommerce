<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends MY_Model
{
    protected $table   = 'products';
    protected $primary = 'id';

    public function get_by_store($store_id, $limit = 20, $offset = 0)
    {
        return $this->db
            ->select('p.*, c.name AS category_name, pi.image_path AS primary_image')
            ->from('products p')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('product_images pi', 'pi.product_id = p.id AND pi.is_primary = 1', 'left')
            ->where('p.store_id', $store_id)
            ->order_by('p.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    public function count_by_store($store_id)
    {
        return $this->db
            ->where('store_id', $store_id)
            ->count_all_results($this->table);
    }

    public function get_detail($slug)
    {
        return $this->db
            ->select('p.*, s.name AS store_name, s.slug AS store_slug, s.status AS store_status, c.name AS category_name')
            ->from('products p')
            ->join('stores s', 's.id = p.store_id')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('p.slug', $slug)
            ->get()
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

    public function get_images($product_id)
    {
        return $this->db
            ->where('product_id', $product_id)
            ->order_by('sort_order ASC')
            ->get('product_images')
            ->result();
    }

    public function get_primary_image($product_id)
    {
        return $this->db
            ->where('product_id', $product_id)
            ->where('is_primary', 1)
            ->get('product_images')
            ->row();
    }

    public function get_tags($product_id)
    {
        return $this->db
            ->select('tag')
            ->where('product_id', $product_id)
            ->get('product_tags')
            ->result_array();
    }

    public function save_tags($product_id, array $tags)
    {
        $this->db->delete('product_tags', array('product_id' => $product_id));
        foreach (array_unique($tags) as $tag) {
            $tag = trim($tag);
            if ($tag !== '') {
                $this->db->insert('product_tags', array('product_id' => $product_id, 'tag' => $tag));
            }
        }
    }

    public function create($store_id, $data, $tags = array())
    {
        $slug       = $this->_unique_slug($data['name']);
        $insert     = array(
            'store_id'    => $store_id,
            'category_id' => $data['category_id'] ?: NULL,
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'],
            'price'       => $data['price'],
            'sale_price'  => ($data['sale_price'] !== '' && $data['sale_price'] !== null) ? $data['sale_price'] : NULL,
            'stock'       => $data['stock'],
            'weight'      => ($data['weight'] !== '' && $data['weight'] !== null) ? $data['weight'] : NULL,
            'status'      => $data['status'],
            'created_at'  => date('Y-m-d H:i:s'),
        );
        $product_id = $this->insert($insert);
        if ($tags) {
            $this->save_tags($product_id, $tags);
        }
        return $product_id;
    }

    public function update_product($product_id, $data, $tags = array())
    {
        $update = array(
            'category_id' => $data['category_id'] ?: NULL,
            'name'        => $data['name'],
            'description' => $data['description'],
            'price'       => $data['price'],
            'sale_price'  => ($data['sale_price'] !== '' && $data['sale_price'] !== null) ? $data['sale_price'] : NULL,
            'stock'       => $data['stock'],
            'weight'      => ($data['weight'] !== '' && $data['weight'] !== null) ? $data['weight'] : NULL,
            'status'      => $data['status'],
        );
        // Rename slug only if name changed
        if (isset($data['name'])) {
            $current = $this->find($product_id);
            if ($current && $current->name !== $data['name']) {
                $update['slug'] = $this->_unique_slug($data['name'], $product_id);
            }
        }
        $this->update($product_id, $update);
        $this->save_tags($product_id, $tags);
        return true;
    }

    private function _unique_slug($name, $exclude_id = null)
    {
        $base = slugify($name);
        $slug = $base;
        $i    = 1;
        while ($this->slug_exists($slug, $exclude_id)) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}
