<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_Controller
{
    private static $ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private static $MAX_SIZE_KB   = 4096; // 4 MB

    // POST /api/images/upload
    // Body: multipart/form-data  { file, product_id }
    // Returns: { id, url, is_primary }
    public function upload_image()
    {
        $this->_require_seller_json();

        $product_id = (int) $this->input->post('product_id');
        if (!$product_id) {
            return $this->_json_error('product_id required', 422);
        }

        // Verify this product belongs to the logged-in seller's store
        $this->load->model('product_model');
        $this->load->model('store_model');
        $store = $this->store_model->find_by_user($this->current_user->id);
        if (!$store) {
            return $this->_json_error('No store found', 403);
        }

        $product = $this->product_model->find($product_id);
        if (!$product || (int)$product->store_id !== (int)$store->id) {
            return $this->_json_error('Product not found or not yours', 403);
        }

        if (empty($_FILES['file']['name'])) {
            return $this->_json_error('No file uploaded', 422);
        }

        // Validate MIME and size before handing to CI Upload
        $tmp  = $_FILES['file']['tmp_name'];
        $mime = mime_content_type($tmp);
        if (!in_array($mime, self::$ALLOWED_TYPES, true)) {
            return $this->_json_error('Only JPEG, PNG, WebP or GIF images are allowed', 422);
        }
        if ($_FILES['file']['size'] > self::$MAX_SIZE_KB * 1024) {
            return $this->_json_error('File too large (max 4 MB)', 422);
        }

        $upload_dir = FCPATH . 'uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $this->load->library('upload', [
            'upload_path'   => $upload_dir,
            'allowed_types' => 'jpg|jpeg|png|webp|gif',
            'max_size'      => self::$MAX_SIZE_KB,
            'encrypt_name'  => TRUE,
        ]);

        if (!$this->upload->do_upload('file')) {
            return $this->_json_error($this->upload->display_errors('', ''), 422);
        }

        $info       = $this->upload->data();
        $image_path = 'uploads/products/' . $info['file_name'];

        // First image for this product becomes primary
        $existing = $this->db->where('product_id', $product_id)->count_all_results('product_images');
        $is_primary = ($existing === 0) ? 1 : 0;

        $sort_order = $existing;
        $id = $this->db->insert('product_images', [
            'product_id' => $product_id,
            'image_path' => $image_path,
            'is_primary' => $is_primary,
            'sort_order' => $sort_order,
        ]);
        $image_id = $this->db->insert_id();

        $this->output
            ->set_status_header(201)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'id'         => $image_id,
                'url'        => base_url($image_path),
                'is_primary' => (bool) $is_primary,
                'csrf'       => $this->_new_csrf(),
            ]));
    }

    // DELETE /api/images/:id/delete
    public function delete_image($image_id)
    {
        $this->_require_seller_json();

        $image = $this->db->get_where('product_images', ['id' => $image_id])->row();
        if (!$image) {
            return $this->_json_error('Image not found', 404);
        }

        // Verify ownership
        $this->load->model('store_model');
        $this->load->model('product_model');
        $store   = $this->store_model->find_by_user($this->current_user->id);
        $product = $this->product_model->find($image->product_id);
        if (!$store || !$product || (int)$product->store_id !== (int)$store->id) {
            return $this->_json_error('Forbidden', 403);
        }

        // Delete file from disk
        $file_path = FCPATH . $image->image_path;
        if (is_file($file_path)) {
            unlink($file_path);
        }

        $this->db->delete('product_images', ['id' => $image_id]);

        // If deleted image was primary, promote the next one
        $new_primary_id = null;
        if ($image->is_primary) {
            $next = $this->db
                ->where('product_id', $image->product_id)
                ->order_by('sort_order', 'ASC')
                ->limit(1)
                ->get('product_images')
                ->row();
            if ($next) {
                $this->db->update('product_images', ['is_primary' => 1], ['id' => $next->id]);
                $new_primary_id = (int) $next->id;
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'        => true,
                'new_primary_id' => $new_primary_id,
                'csrf'           => $this->_new_csrf(),
            ]));
    }

    // POST /api/images/:id/set-primary
    public function set_primary_image($image_id)
    {
        $this->_require_seller_json();

        $image = $this->db->get_where('product_images', ['id' => $image_id])->row();
        if (!$image) {
            return $this->_json_error('Image not found', 404);
        }

        $this->load->model('store_model');
        $this->load->model('product_model');
        $store   = $this->store_model->find_by_user($this->current_user->id);
        $product = $this->product_model->find($image->product_id);
        if (!$store || !$product || (int)$product->store_id !== (int)$store->id) {
            return $this->_json_error('Forbidden', 403);
        }

        // Clear all primaries for this product, then set the chosen one
        $this->db->update('product_images', ['is_primary' => 0], ['product_id' => $image->product_id]);
        $this->db->update('product_images', ['is_primary' => 1], ['id' => $image_id]);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'    => true,
                'primary_id' => (int) $image_id,
                'csrf'       => $this->_new_csrf(),
            ]));
    }

    // GET /api/search?q=<term>
    public function search()
    {
        $q = trim($this->input->get('q'));
        if (strlen($q) < 2) {
            $this->_json(['results' => []]);
            return;
        }

        $this->load->model('product_model');

        $rows = $this->db
            ->select('p.id, p.name, p.slug, p.price, p.sale_price,
                (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS image')
            ->from('products p')
            ->join('stores s', 's.id = p.store_id')
            ->like('p.name', $q)
            ->where('p.status', PRODUCT_ACTIVE)
            ->where('s.status', STORE_ACTIVE)
            ->limit(10)
            ->get()
            ->result_array();

        foreach ($rows as &$row) {
            $row['image'] = $row['image'] ? base_url($row['image']) : null;
        }

        $this->_json(['results' => $rows]);
    }

    // GET /api/product/:id/reviews
    public function reviews($product_id)
    {
        $this->load->model('review_model');
        $product_id = (int) $product_id;

        $reviews = $this->review_model->get_for_product($product_id);
        $can_review  = false;
        $has_reviewed = false;

        if ($this->current_user) {
            $has_reviewed = $this->review_model->has_reviewed($this->current_user->id, $product_id);
            $can_review   = $this->review_model->can_review($this->current_user->id, $product_id);
        }

        $this->_json([
            'reviews'      => array_map(function($r) {
                return [
                    'id'            => (int)$r->id,
                    'rating'        => (int)$r->rating,
                    'body'          => $r->body,
                    'reviewer_name' => $r->reviewer_name,
                    'created_at'    => $r->created_at,
                ];
            }, $reviews),
            'can_review'   => $can_review,
            'has_reviewed' => $has_reviewed,
        ]);
    }

    // POST /api/product/:id/reviews/submit
    public function submit_review($product_id)
    {
        if (!$this->current_user) {
            $this->_json(['error' => 'Login required'], 401);
            return;
        }

        $product_id = (int) $product_id;
        $this->load->model('review_model');

        if (!$this->review_model->can_review($this->current_user->id, $product_id)) {
            $this->_json(['error' => 'You can only review products from delivered orders you have not yet reviewed.'], 403);
            return;
        }

        $rating = (int) $this->input->post('rating');
        $body   = trim($this->input->post('body'));

        if ($rating < 1 || $rating > 5) {
            $this->_json(['error' => 'Rating must be 1–5'], 422);
            return;
        }
        if (strlen($body) < 10) {
            $this->_json(['error' => 'Review must be at least 10 characters'], 422);
            return;
        }

        $this->review_model->insert([
            'product_id' => $product_id,
            'user_id'    => $this->current_user->id,
            'rating'     => $rating,
            'body'       => $body,
            'status'     => REVIEW_PENDING,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->_json([
            'success' => true,
            'message' => 'Review submitted! It will appear after admin approval.',
            'csrf'    => $this->_new_csrf(),
        ], 201);
    }

    // --- Helpers ---

    private function _require_seller_json()
    {
        if (!$this->current_user) {
            $this->_json_error('Unauthenticated', 401);
            exit;
        }
        if (!in_array($this->current_user->role, [ROLE_SELLER, ROLE_ADMIN], true)) {
            $this->_json_error('Forbidden', 403);
            exit;
        }
    }

    private function _json($data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    private function _json_error($message, $status = 400)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode(['error' => $message, 'csrf' => $this->_new_csrf()]));
    }

    private function _new_csrf()
    {
        return [
            'name'  => $this->security->get_csrf_token_name(),
            'hash'  => $this->security->get_csrf_hash(),
        ];
    }
}
