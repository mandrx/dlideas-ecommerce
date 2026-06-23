<?php $this->load->view('partials/header', array('page_title' => isset($page_title) ? $page_title : 'Dashboard')); ?>
<?php $this->load->view('partials/nav'); ?>
<div class="container-fluid py-4">
    <div class="row">
        <aside class="col-md-2">
            <?php if ($current_user->role === 'seller'): ?>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><a href="<?= base_url('seller') ?>">Dashboard</a></li>
                <li class="list-group-item"><a href="<?= base_url('seller/products') ?>">Products</a></li>
                <li class="list-group-item"><a href="<?= base_url('seller/orders') ?>">Orders</a></li>
                <li class="list-group-item"><a href="<?= base_url('seller/store') ?>">Store Settings</a></li>
            </ul>
            <?php elseif ($current_user->role === 'admin'): ?>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><a href="<?= base_url('admin') ?>">Dashboard</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/users') ?>">Users</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/stores') ?>">Stores</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/products') ?>">Products</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/orders') ?>">Orders</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/coupons') ?>">Coupons</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/reviews') ?>">Reviews</a></li>
            </ul>
            <?php endif; ?>
        </aside>
        <main class="col-md-10">
            <?php $this->load->view('partials/flash_messages'); ?>
            <?php $this->load->view($content_view, get_defined_vars()); ?>
        </main>
    </div>
</div>
<?php $this->load->view('partials/footer'); ?>
