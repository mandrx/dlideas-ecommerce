<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url() ?>">CI3 Shop</a>
        <div class="collapse navbar-collapse">
            <div id="live-search" class="ms-auto me-3"></div>
            <ul class="navbar-nav">
                <?php if ($current_user): ?>
                    <?php if ($current_user->role === 'seller'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('seller') ?>">Dashboard</a></li>
                    <?php elseif ($current_user->role === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin') ?>">Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('orders') ?>">Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('logout') ?>">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('login') ?>">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('register') ?>">Register</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('cart') ?>">Cart</a></li>
            </ul>
        </div>
    </div>
</nav>
