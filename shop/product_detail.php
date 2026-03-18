<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once 'includes/shop_helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT * FROM products WHERE id=?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { setFlash('error','Product not found.'); redirect('shop.php'); }

$userId    = $_SESSION['user_id'] ?? null;
$isMember  = $userId ? userHasActiveMembership($pdo, (int)$userId) : false;
$inCart    = false;
if ($userId) {
    $chk = $pdo->prepare('SELECT id FROM cart_items WHERE user_id=? AND product_id=?');
    $chk->execute([$userId, $id]);
    $inCart = (bool)$chk->fetch();
}

$relStmt = $pdo->prepare('SELECT * FROM products WHERE category=? AND id!=? ORDER BY created_at DESC LIMIT 4');
$relStmt->execute([$product['category'], $id]);
$related = $relStmt->fetchAll();

$isOut = (int)$product['stock'] === 0;
$isLow = (int)$product['stock'] > 0 && (int)$product['stock'] <= 5;

$pageTitle = htmlspecialchars($product['name']);
include '../includes/header.php';
?>
<div class="sf-breadcrumb">
    <a href="/spinfit/shop/shop.php">Shop</a>
    <span class="sf-breadcrumb-sep">/</span>
    <a href="/spinfit/shop/shop.php?category=<?= urlencode($product['category'] ?? '') ?>"><?= ucfirst(htmlspecialchars($product['category'] ?? '')) ?></a>
    <span class="sf-breadcrumb-sep">/</span>
    <span class="sf-breadcrumb-current"><?= htmlspecialchars($product['name']) ?></span>
</div>

<div class="sf-detail-grid">
    <div class="sf-detail-gallery">
        <div class="sf-detail-main">
            <svg width="100" height="100" viewBox="0 0 100 100" fill="none"><rect x="12" y="12" width="76" height="76" rx="8" stroke="#ccc" stroke-width="1.5"/><path d="M35 50h30M50 35v30" stroke="#ccc" stroke-width="1.5" stroke-linecap="round"/></svg>
        </div>
        <div class="sf-detail-thumbs">
            <?php for ($i=0;$i<3;$i++): ?>
            <div class="sf-detail-thumb <?= $i===0?'active':'' ?>" onclick="this.parentElement.querySelectorAll('.sf-detail-thumb').forEach((t,j)=>t.classList.toggle('active',j==<?= $i ?>))">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="#bbb" stroke-width="1.2"/></svg>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="sf-detail-info">
        <div class="sf-detail-cat"><?= ucfirst(htmlspecialchars($product['category'] ?? '')) ?></div>
        <div class="sf-detail-name"><?= htmlspecialchars($product['name']) ?></div>
        <div class="sf-detail-price">
            <?php if ($isMember): ?>
                <span style="text-decoration:line-through;color:var(--ink-soft);font-size:15px;font-weight:400;margin-right:8px">$<?= number_format($product['price'],2) ?></span>
                <span class="text-brand">$<?= number_format($product['price'] * 0.9, 2) ?></span>
                <span style="font-size:11px;background:#fef2f2;color:var(--brand);padding:2px 7px;border-radius:4px;margin-left:6px">Member 10% off</span>
            <?php else: ?>
                $<?= number_format($product['price'],2) ?>
            <?php endif; ?>
        </div>

        <hr class="sf-detail-divider">
        <div class="sf-detail-desc"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></div>

        <div class="sf-detail-meta-label">Product details</div>
        <div class="sf-detail-tags">
            <span class="sf-detail-tag"><?= htmlspecialchars(ucfirst($product['category'] ?? 'General')) ?></span>
            <?php if (!empty($product['sizes'])): ?><span class="sf-detail-tag">Sizes: <?= htmlspecialchars($product['sizes']) ?></span><?php endif; ?>
            <?php if ($isOut): ?><span class="sf-detail-tag" style="color:#991b1b;border-color:#fecaca">Sold out</span>
            <?php elseif ($isLow): ?><span class="sf-detail-tag" style="color:#92400e;border-color:#fde68a">Only <?= (int)$product['stock'] ?> left</span>
            <?php else: ?><span class="sf-detail-tag" style="color:#166534;border-color:#bbf7d0">In stock</span><?php endif; ?>
        </div>

        <?php if (!empty($product['measurements'])): ?>
        <div class="sf-detail-meta-label" style="margin-top:18px">Measurements / sizing</div>
        <div style="font-size:13px;color:var(--ink-mid);line-height:1.75;background:var(--surface);padding:14px 16px;border-radius:var(--radius-sm)"><?= nl2br(htmlspecialchars($product['measurements'])) ?></div>
        <?php endif; ?>

        <div class="sf-detail-meta-label" style="margin-top:18px">Why you'll love it</div>
        <ul style="padding-left:18px;font-size:13px;color:var(--ink-mid);line-height:1.8;margin-bottom:16px">
            <li>Designed to support your Spin and HIIT routines.</li>
            <li>Pairs with the studio aesthetic and member experience.</li>
            <li>Great for both first-timers and regular members.</li>
        </ul>

        <div class="sf-stock-line">
            <span><?= $isOut ? 'Currently unavailable' : ($isLow ? 'Low stock' : 'Ready to ship') ?></span>
            <span><?= (int)$product['stock'] ?> unit<?= (int)$product['stock']===1?'':'s' ?> available</span>
        </div>

        <?php if ($isOut): ?>
            <button class="sf-back-btn" disabled style="width:100%;cursor:not-allowed">Sold out</button>
        <?php elseif (!$userId): ?>
            <a href="/spinfit/login.php" class="sf-add-btn" style="display:block;text-align:center;text-decoration:none">Log in to purchase</a>
        <?php else: ?>
            <form method="POST" action="cart_action.php">
                <input type="hidden" name="action"     value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="redirect"   value="product_detail.php?id=<?= $product['id'] ?>">
                <div class="sf-qty-row">
                    <span class="sf-qty-label">Quantity</span>
                    <div class="sf-qty-ctrl">
                        <button type="button" class="sf-qty-btn" data-dir="-">−</button>
                        <input type="number" name="quantity" value="1" min="1" max="<?= (int)$product['stock'] ?>" class="sf-qty-input" />
                        <button type="button" class="sf-qty-btn" data-dir="+">+</button>
                    </div>
                </div>
                <button type="submit" class="sf-add-btn"><?= $inCart ? 'Add more to cart' : 'Add to cart' ?></button>
            </form>
        <?php endif; ?>

        <a href="shop.php" class="sf-back-btn" style="display:block;text-align:center;text-decoration:none;margin-top:0">← Back to <?= ucfirst(htmlspecialchars($product['category'] ?? 'shop')) ?></a>

        <p class="sf-member-note">
            <?php if (!$isMember): ?>
                <a href="/spinfit/membership_plans.php">Join as a member</a> to get 10% off all shop purchases.
            <?php else: ?>
                Your member discount has been applied automatically.
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (!empty($related)): ?>
<div class="sf-related">
    <div class="sf-related-head">You might also like</div>
    <div class="sf-related-grid">
        <?php foreach ($related as $r): ?>
        <a href="product_detail.php?id=<?= $r['id'] ?>" class="sf-rel-card" style="text-decoration:none;color:inherit">
            <div class="sf-rel-img">
                <svg width="36" height="36" viewBox="0 0 36 36" fill="none"><rect x="6" y="6" width="24" height="24" rx="4" stroke="#ccc" stroke-width="1.2"/></svg>
            </div>
            <div class="sf-rel-body">
                <div class="sf-rel-name"><?= htmlspecialchars($r['name']) ?></div>
                <div class="sf-rel-price">$<?= number_format($r['price'],2) ?></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
