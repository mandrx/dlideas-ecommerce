<?php
$current_uri = uri_string();
function dl_nav_active($path, $current) {
    return ($path !== '' && strpos($current, $path) === 0) || ($path === '' && $current === '') ? 'active' : '';
}
?>
<div class="dl-demo-banner" role="alert">
    <strong>Demo Build</strong> — A portfolio mock for <a href="https://dlideas.com/" target="_blank" rel="noopener">DL Ideas Pte. Ltd.</a> All content &amp; design &copy; DL Ideas Pte. Ltd. Products and sellers are fictional. <strong>Do not place real orders.</strong>
    <a href="<?= base_url('our-story') ?>">Learn more</a>
</div>
<header class="dl-header">
    <div class="container">
        <div class="dl-header-top">
            <a href="<?= base_url() ?>" class="dl-logo">
                <img src="<?= base_url('assets/img/logo-black.png') ?>" alt="DLIdeas">
            </a>

            <div id="live-search"></div>

            <div class="dl-header-actions">
                <?php if ($current_user): ?>
                    <span class="dl-btn-ghost" style="cursor:default;"><?= htmlspecialchars($current_user->full_name) ?></span>
                    <?php if ($current_user->role === 'seller'): ?>
                        <a href="<?= base_url('seller') ?>" class="dl-btn-ghost">Dashboard</a>
                    <?php elseif ($current_user->role === 'admin' || $current_user->role === 'owner'): ?>
                        <a href="<?= base_url('admin') ?>" class="dl-btn-ghost">Admin</a>
                    <?php endif; ?>
                    <a href="<?= base_url('orders') ?>" class="dl-btn-ghost">Orders</a>
                    <a href="<?= base_url('logout') ?>" class="dl-btn-ghost">Logout</a>
                <?php else: ?>
                    <a href="<?= base_url('login') ?>" class="dl-btn-ghost">Sign In</a>
                    <a href="<?= base_url('register') ?>" class="dl-btn-ghost">Register</a>
                <?php endif; ?>
                <a href="<?= base_url('cart') ?>" class="dl-btn-cart">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    Cart
                </a>
            </div>
        </div>

        <?php if (!isset($hide_subnav) || !$hide_subnav): ?>
        <nav class="dl-subnav" aria-label="Shop navigation">
            <a href="<?= base_url() ?>" class="<?= dl_nav_active('', $current_uri) ?>">Home</a>
            <a href="<?= base_url('shop') ?>" class="<?= dl_nav_active('shop', $current_uri) ?>">All Products</a>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= base_url('shop/' . $cat->slug) ?>"
                       class="<?= dl_nav_active('shop/' . $cat->slug, $current_uri) ?>">
                        <?= htmlspecialchars($cat->name) ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            <a href="<?= base_url('register') ?>">Sell on DLIdeas</a>
        </nav>
        <?php elseif ($current_user && $current_user->role === 'seller'): ?>
        <nav class="dl-subnav" aria-label="Seller navigation">
            <a href="<?= base_url('seller') ?>" class="<?= dl_nav_active('seller', $current_uri) === 'active' && $current_uri === 'seller' ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= base_url('seller/products') ?>" class="<?= dl_nav_active('seller/products', $current_uri) ?>">Products</a>
            <a href="<?= base_url('seller/orders') ?>" class="<?= dl_nav_active('seller/orders', $current_uri) ?>">Orders</a>
            <a href="<?= base_url('seller/store') ?>" class="<?= dl_nav_active('seller/store', $current_uri) ?>">Store Settings</a>
        </nav>
        <?php elseif ($current_user && ($current_user->role === 'admin' || $current_user->role === 'owner')): ?>
        <nav class="dl-subnav" aria-label="Admin navigation">
            <a href="<?= base_url('admin') ?>" class="<?= $current_uri === 'admin' ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= base_url('admin/users') ?>" class="<?= dl_nav_active('admin/users', $current_uri) ?>">Users</a>
            <a href="<?= base_url('admin/stores') ?>" class="<?= dl_nav_active('admin/stores', $current_uri) ?>">Stores</a>
            <a href="<?= base_url('admin/products') ?>" class="<?= dl_nav_active('admin/products', $current_uri) ?>">Products</a>
            <a href="<?= base_url('admin/orders') ?>" class="<?= dl_nav_active('admin/orders', $current_uri) ?>">Orders</a>
            <a href="<?= base_url('admin/coupons') ?>" class="<?= dl_nav_active('admin/coupons', $current_uri) ?>">Coupons</a>
            <a href="<?= base_url('admin/categories') ?>" class="<?= dl_nav_active('admin/categories', $current_uri) ?>">Categories</a>
            <a href="<?= base_url('admin/reviews') ?>" class="<?= dl_nav_active('admin/reviews', $current_uri) ?>">Reviews</a>
            <?php if (isset($current_user) && $current_user->role === 'owner'): ?>
            <a href="<?= base_url('admin/contact-messages') ?>" class="<?= dl_nav_active('admin/contact-messages', $current_uri) ?>">Messages</a>
            <a href="<?= base_url('admin/visitors') ?>" class="<?= dl_nav_active('admin/visitors', $current_uri) ?>">Visitors</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</header>
