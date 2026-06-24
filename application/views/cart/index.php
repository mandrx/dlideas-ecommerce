<div class="dl-page-top">
    <h2 style="font-family:'Baloo 2','Nunito',sans-serif;font-size:1.8rem;font-weight:900;margin:0;">Your Cart</h2>
</div>

<?php if (empty($items)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">🛒</div>
    <h3>Your cart is empty</h3>
    <p>Looks like you haven't added anything yet. Let's fix that!</p>
    <a href="<?= base_url() ?>" class="btn btn-primary">Start Shopping</a>
</div>
<?php else: ?>
<div class="row g-4">
    <div class="col-lg-8">
        <div style="background:var(--bg-card);border-radius:var(--radius-md);border:1.5px solid var(--border);overflow:hidden;box-shadow:var(--shadow-card);">
            <div class="table-responsive">
            <table class="dl-cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <?php if ($item->image): ?>
                            <img src="<?= base_url($item->image) ?>" class="dl-cart-img" alt="<?= htmlspecialchars($item->name) ?>">
                            <?php endif; ?>
                            <div>
                                <a href="<?= base_url('product/' . $item->slug) ?>" class="dl-cart-product-name">
                                    <?= htmlspecialchars($item->name) ?>
                                </a>
                                <div class="dl-cart-store"><?= htmlspecialchars($item->store_name) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="dl-cart-price">S$ <?= number_format($item->unit_price, 2) ?></td>
                    <td style="width:130px;">
                        <form action="<?= base_url('cart/update') ?>" method="post" class="dl-qty-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="item_id" value="<?= $item->id ?>">
                            <input type="number" name="quantity" value="<?= $item->quantity ?>" min="0"
                                   max="<?= $item->stock ?>" class="dl-qty-input" aria-label="Quantity">
                            <button type="submit" class="dl-qty-btn" title="Update quantity">↺</button>
                        </form>
                    </td>
                    <td class="dl-cart-total">S$ <?= number_format($item->line_total, 2) ?></td>
                    <td>
                        <a href="<?= base_url('cart/remove/' . $item->id) ?>"
                           class="dl-remove-btn"
                           onclick="return confirm('Remove this item from your cart?')"
                           title="Remove item">✕</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
        <div style="margin-top:var(--space-4);">
            <a href="<?= base_url() ?>" class="dl-back-link">Continue Shopping</a>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="dl-summary-card">
            <div class="dl-summary-header">Order Summary</div>
            <div class="dl-summary-body">
                <div class="dl-summary-row">
                    <span>Subtotal</span>
                    <span>S$ <?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="dl-summary-row" style="color:var(--text-muted);">
                    <span>Shipping</span>
                    <span>S$ 10.00</span>
                </div>
                <div class="dl-summary-divider"></div>
                <div class="dl-summary-row total">
                    <span>Total</span>
                    <span class="dl-summary-amount">S$ <?= number_format($subtotal + 10, 2) ?></span>
                </div>
            </div>
            <div class="dl-summary-footer">
                <?php if ($current_user): ?>
                <a href="<?= base_url('checkout') ?>" class="btn btn-primary w-100 btn-lg">Proceed to Checkout</a>
                <?php else: ?>
                <a href="<?= base_url('login') ?>" class="btn btn-primary w-100">Login to Checkout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
