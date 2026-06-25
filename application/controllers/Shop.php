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

    public function home()
    {
        $products   = $this->_random_products(10);
        $categories = $this->category_model->get_all();

        $this->_render('shop/home', array(
            'page_title' => 'CI3 Shop — Home',
            'products'   => $products,
            'categories' => $categories,
        ));
    }

    public function index()
    {
        $per_page = 20;
        $page     = max(1, (int) $this->input->get('page'));
        $offset   = ($page - 1) * $per_page;
        $q        = trim($this->input->get('q'));

        $base = function() {
            $this->db
                ->from('products p')
                ->join('stores s', 's.id = p.store_id')
                ->where('p.status', PRODUCT_ACTIVE)
                ->where('s.status', STORE_ACTIVE);
        };

        if ($q) {
            $products = $this->db
                ->select('p.*, s.name AS store_name, s.slug AS store_slug,
                    (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image')
                ->from('products p')
                ->join('stores s', 's.id = p.store_id')
                ->where('p.status', PRODUCT_ACTIVE)
                ->where('s.status', STORE_ACTIVE)
                ->like('p.name', $q)
                ->order_by('p.created_at', 'DESC')
                ->limit($per_page, $offset)
                ->get()->result();

            $total = $this->db
                ->from('products p')
                ->join('stores s', 's.id = p.store_id')
                ->where('p.status', PRODUCT_ACTIVE)
                ->where('s.status', STORE_ACTIVE)
                ->like('p.name', $q)
                ->count_all_results();
        } else {
            $products = $this->_active_products($per_page, $offset);
            $total    = $this->db
                ->from('products p')
                ->join('stores s', 's.id = p.store_id')
                ->where('p.status', PRODUCT_ACTIVE)
                ->where('s.status', STORE_ACTIVE)
                ->count_all_results();
        }

        $this->_render('shop/index', array(
            'page_title' => $q ? 'Search: ' . $q : 'All Products',
            'products'   => $products,
            'total'      => $total,
            'per_page'   => $per_page,
            'page'       => $page,
            'q'          => $q,
        ));
    }

    public function category($slug)
    {
        $category = $this->category_model->find_where('slug', $slug);
        if (!$category) show_404();

        $per_page = 20;
        $page     = max(1, (int) $this->input->get('page'));
        $offset   = ($page - 1) * $per_page;

        // Include products from child categories too
        $child_ids = $this->db->select('id')->where('parent_id', $category->id)->get('categories')->result();
        $cat_ids   = array_merge([$category->id], array_column($child_ids, 'id'));

        $products = $this->db
            ->select('p.*, s.name AS store_name, s.slug AS store_slug, c.name AS category_name, c.slug AS category_slug,
                (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image')
            ->from('products p')
            ->join('stores s', 's.id = p.store_id')
            ->join('categories c', 'c.id = p.category_id', 'left')
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
            'page_title'       => $category->name,
            'category'         => $category,
            'is_parent'        => count($cat_ids) > 1,
            'products'         => $products,
            'total'            => $total,
            'per_page'         => $per_page,
            'page'             => $page,
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

        $per_page = 20;
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

    private function _active_products($limit = 20, $offset = 0)
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

    private function _random_products($limit = 10)
    {
        return $this->db
            ->select('p.*, s.name AS store_name, s.slug AS store_slug,
                (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image')
            ->from('products p')
            ->join('stores s', 's.id = p.store_id')
            ->where('p.status', PRODUCT_ACTIVE)
            ->where('s.status', STORE_ACTIVE)
            ->order_by('RAND()')
            ->limit($limit)
            ->get()->result();
    }

    private function _render($view, $data = array())
    {
        $data['content_view'] = $view;
        $this->load->view('layouts/main', $data);
    }
}
