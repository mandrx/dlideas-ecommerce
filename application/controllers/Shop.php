<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends MY_Controller
{
    public function index()
    {
        $data['page_title']   = 'Shop';
        $data['content_view'] = 'shop/index';
        $this->load->view('layouts/main', $data);
    }
}
