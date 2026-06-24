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
}
