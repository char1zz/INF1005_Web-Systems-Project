<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once 'includes/shop_helpers.php';
requireLogin('/spinfit/login.php');
$userId   = (int)$_SESSION['user_id'];
$items    = getCartItems($pdo, $userId);
$subtotal = array_sum(array_map(fn($i)=>$i['price']*$i['quantity'], $items));
$isMember = userHasActiveMembership($pdo, $userId);
$discount = $isMember ? round($subtotal * MEMBER_DISCOUNT, 2) : 0;
$total    = $subtotal - $discount;
$pageTitle = 'My Cart';
include '../includes/header.php';
?>
<div class="sf-page-inner">
    <h1 class="sf-page-title">My cart</h1>
    <?php if (empty($items)): ?>
        <div style="text-align:center;padding:60px 0;color:var(--ink-soft)">
            <div style="font-size:14px;margin-bottom:12px">Your cart is empty.</div>
            <a href="shop.php" class="sf-nav-btn sf-nav-btn-solid" style="display:inline-block">Browse shop</a>
        </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:1fr 320px;gap:32px;align-items:start">
        <div>
            <table class="sf-cart-table" style="width:100%">
                <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <div class="sf-cart-product">
                            <div class="sf-cart-img"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="#ccc" stroke-width="1.2"/></svg></div>
                            <div>
                                <div class="sf-cart-name"><a href="product_detail.php?id=<?= $item['id'] ?>" style="color:inherit;text-decoration:none"><?= htmlspecialchars($item['name']) ?></a></div>
                                <div class="sf-cart-subcat"><?= htmlspecialchars($item['category'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>$<?= number_format($item['price'],2) ?></td>
                    <td>
                        <form method="POST" action="cart_action.php">
                            <input type="hidden" name="action"   value="update">
                            <input type="hidden" name="cart_id"  value="<?= $item['cart_id'] ?>">
                            <input type="hidden" name="redirect" value="cart.php">
                            <input type="number" name="quantity" class="sf-qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" onchange="this.form.submit()">
                        </form>
                    </td>
                    <td style="font-weight:500">$<?= number_format($item['price']*$item['quantity'],2) ?></td>
                    <td>
                        <form method="POST" action="cart_action.php">
                            <input type="hidden" name="action"   value="remove">
                            <input type="hidden" name="cart_id"  value="<?= $item['cart_id'] ?>">
                            <input type="hidden" name="redirect" value="cart.php">
                            <button type="submit" class="sf-remove-btn" data-confirm="Remove this item?">&#x2715;</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:12px">
                <form method="POST" action="cart_action.php" style="display:inline">
                    <input type="hidden" name="action"   value="clear">
                    <input type="hidden" name="redirect" value="cart.php">
                    <button type="submit" class="sf-remove-btn" data-confirm="Clear your entire cart?" style="font-size:12px;padding:6px 12px">Clear cart</button>
                </form>
            </div>
        </div>
        <div class="sf-summary-card">
            <div style="font-size:14px;font-weight:500;margin-bottom:16px">Order summary</div>
            <?php if ($isMember): ?>
            <div class="sf-member-discount">Member discount (10%) applied!</div>
            <?php else: ?>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:12px"><a href="/spinfit/membership_plans.php" style="color:var(--brand)">Join as a member</a> to get 10% off.</div>
            <?php endif; ?>
            <div class="sf-summary-row"><span>Subtotal</span><span>$<?= number_format($subtotal,2) ?></span></div>
            <?php if ($isMember): ?><div class="sf-summary-row" style="color:#15803d"><span>Member discount</span><span>−$<?= number_format($discount,2) ?></span></div><?php endif; ?>
            <hr class="sf-summary-divider">
            <div class="sf-summary-total"><span>Total</span><span>$<?= number_format($total,2) ?></span></div>
            <a href="checkout.php" class="sf-add-btn" style="display:block;text-align:center;text-decoration:none;margin-top:16px">Proceed to checkout</a>
            <a href="shop.php" style="display:block;text-align:center;font-size:12px;color:var(--ink-soft);margin-top:10px">Continue shopping</a>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
