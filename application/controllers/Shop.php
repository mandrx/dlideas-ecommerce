<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('product_model');
        $this->load->model('category_model');
        $this->load->model('store_model');
    }

    public function index()
    {
        $featured   = $this->_active_products(8, 0);
        $categories = $this->category_model->get_parents();

        $this->_render('shop/index', array(
            'page_title' => 'CI3 Shop — Home',
            'featured'   => $featured,
            'categories' => $categories,
        ));
    }

    public function category($slug)
    {
        $category = $this->category_model->find_where('slug', $slug);
        if (!$category) show_404();

        $per_page = 12;
        $page     = max(1, (int) $this->input->get('page'));
        $offset   = ($page - 1) * $per_page;

        // Include products from child categories too
        $child_ids = $this->db->select('id')->where('parent_id', $category->id)->get('categories')->result();
        $cat_ids   = array_merge([$category->id], array_column($child_ids, 'id'));

        $products = $this->db
            ->select('p.*, s.name AS store_name, s.slug AS store_slug,
                (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image')
            ->from('products p')
            ->join('stores s', 's.id = p.store_id')
            ->where_in('p.category_id', $cat_ids)
            ->where('p.status', PRODUCT_ACTIVE)
            ->where('s.status', STORE_ACTIVE)
            ->order_by('p.created_at', 'DESC')
            ->limit($per_page, $offset)
            ->get()->result();

        $total = $this->db
            ->from('products p')
            ->join('stores s', 's.id = p.store_id')
            ->where_in('p.category_id', $cat_ids)
            ->where('p.status', PRODUCT_ACTIVE)
            ->where('s.status', STORE_ACTIVE)
            ->count_all_results();

        $this->_render('shop/category', array(
            'page_title' => $category->name,
            'category'   => $category,
            'products'   => $products,
            'total'      => $total,
            'per_page'   => $per_page,
            'page'       => $page,
        ));
    }

    public function product($slug)
    {
        $product = $this->product_model->get_detail($slug);
        if (!$product || $product->status !== PRODUCT_ACTIVE) show_404();

        $images = $this->product_model->get_images($product->id);
        $tags   = $this->product_model->get_tags($product->id);

        $related = $this->db
            ->select('p.*, s.name AS store_name, s.slug AS store_slug,
                (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image')
            ->from('products p')
            ->join('stores s', 's.id = p.store_id')
            ->where('p.category_id', $product->category_id)
            ->where('p.id !=', $product->id)
            ->where('p.status', PRODUCT_ACTIVE)
            ->where('s.status', STORE_ACTIVE)
            ->limit(4)
            ->get()->result();

        $this->_render('shop/product_detail', array(
            'page_title' => $product->name,
            'product'    => $product,
            'images'     => $images,
            'tags'       => $tags,
            'related'    => $related,
            'scripts'    => array('product'),
        ));
    }

    public function store($slug)
    {
        $store = $this->store_model->find_by_slug($slug);
        if (!$store) show_404();

        $per_page = 12;
        $page     = max(1, (int) $this->input->get('page'));
        $offset   = ($page - 1) * $per_page;

        $products = $this->db
            ->select('p.*, (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image')
            ->from('products p')
            ->where('p.store_id', $store->id)
            ->where('p.status', PRODUCT_ACTIVE)
            ->order_by('p.created_at', 'DESC')
            ->limit($per_page, $offset)
            ->get()->result();
        foreach ($products as $p) {
            $p->store_name = $store->name;
            $p->store_slug = $store->slug;
        }

        $total = $this->db
            ->where('store_id', $store->id)
            ->where('status', PRODUCT_ACTIVE)
            ->count_all_results('products');

        $this->_render('shop/store', array(
            'page_title' => $store->name,
            'store'      => $store,
            'products'   => $products,
            'total'      => $total,
            'per_page'   => $per_page,
            'page'       => $page,
        ));
    }

    private function _active_products($limit = 12, $offset = 0)
    {
        return $this->db
            ->select('p.*, s.name AS store_name, s.slug AS store_slug,
                (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image')
            ->from('products p')
            ->join('stores s', 's.id = p.store_id')
            ->where('p.status', PRODUCT_ACTIVE)
            ->where('s.status', STORE_ACTIVE)
            ->order_by('p.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result();
    }

    private function _render($view, $data = array())
    {
        $data['content_view'] = $view;
        $this->load->view('layouts/main', $data);
    }
}
