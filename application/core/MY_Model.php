<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model
{
    protected $table   = '';
    protected $primary = 'id';

    public function find($id)
    {
        return $this->db
            ->where($this->primary, $id)
            ->get($this->table)
            ->row();
    }

    public function find_all($conditions = array())
    {
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        return $this->db->get($this->table)->result();
    }

    public function find_where($column, $value)
    {
        return $this->db
            ->where($column, $value)
            ->get($this->table)
            ->row();
    }

    public function find_many_where($column, $value)
    {
        return $this->db
            ->where($column, $value)
            ->get($this->table)
            ->result();
    }

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where($this->primary, $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, array($this->primary => $id));
    }

    public function count_all($conditions = array())
    {
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        return $this->db->count_all_results($this->table);
    }

    public function exists($column, $value, $exclude_id = null)
    {
        $this->db->where($column, $value);
        if ($exclude_id !== null) {
            $this->db->where($this->primary . ' !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }
}
