<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact_model extends MY_Model
{
    protected $table   = 'contact_messages';
    protected $primary = 'id';

    public function save(array $data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function get_all()
    {
        return $this->db
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result();
    }
}
