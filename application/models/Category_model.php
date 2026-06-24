<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends MY_Model
{
    protected $table   = 'categories';
    protected $primary = 'id';

    public function get_all_with_parent()
    {
        return $this->db
            ->select('c.*, p.name AS parent_name')
            ->from('categories c')
            ->join('categories p', 'p.id = c.parent_id', 'left')
            ->order_by('c.parent_id ASC, c.sort_order ASC, c.name ASC')
            ->get()
            ->result();
    }

    public function get_parents()
    {
        return $this->db
            ->where('parent_id', NULL)
            ->order_by('sort_order ASC, name ASC')
            ->get($this->table)
            ->result();
    }

    public function get_all()
    {
        return $this->db
            ->order_by('sort_order ASC, name ASC')
            ->get($this->table)
            ->result();
    }

    public function get_dropdown()
    {
        $rows = $this->get_all_with_parent();
        $out  = array('' => '— Select Category —');
        foreach ($rows as $row) {
            $label       = $row->parent_name ? $row->parent_name . ' › ' . $row->name : $row->name;
            $out[$row->id] = $label;
        }
        return $out;
    }

    public function slug_exists($slug, $exclude_id = 0)
    {
        $this->db->where('slug', $slug);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function make_unique_slug($name, $exclude_id = 0)
    {
        $this->load->helper('url');
        $base = url_title(strtolower($name), '-', TRUE);
        $slug = $base;
        $i    = 2;
        while ($this->slug_exists($slug, $exclude_id)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function create($data)
    {
        $data['slug'] = $this->make_unique_slug($data['name']);
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        if (!empty($data['name'])) {
            $data['slug'] = $this->make_unique_slug($data['name'], $id);
        }
        $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        $cat = $this->db->where('id', $id)->get($this->table)->row();
        if ($cat && !empty($cat->image) && file_exists(FCPATH . $cat->image)) {
            @unlink(FCPATH . $cat->image);
        }
        $this->db->where('id', $id)->delete($this->table);
    }
}
