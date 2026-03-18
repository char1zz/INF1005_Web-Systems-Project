<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once 'includes/shop_helpers.php';

$userId    = $_SESSION['user_id'] ?? null;
$cartCount = $userId ? getCartCount($pdo, (int)$userId) : 0;

$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$sort     = $_GET['sort']          ?? 'newest';

$where = ['1=1']; $params = [];
if ($search !== '')   { $where[] = '(name LIKE ? OR description LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($category !== '') { $where[] = 'category = ?'; $params[] = $category; }

$orderBy = match($sort) {
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    'name_asc'   => 'name ASC',
    default      => 'created_at DESC',
};
$products = $pdo->prepare('SELECT * FROM products WHERE ' . implode(' AND ', $where) . " ORDER BY $orderBy");
$products->execute($params);
$products = $products->fetchAll();
$cats     = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category")->fetchAll(\PDO::FETCH_COLUMN);
$catLabel = $category ? ucfirst($category) : 'All products';
$pageTitle = 'Shop';
include '../includes/header.php';
?>

<!-- Category strip -->
<div class="sf-cat-strip">
    <a class="sf-cat-btn <?= $category===''?'active':'' ?>" href="shop.php?sort=<?= $sort ?>&search=<?= urlencode($search) ?>">All</a>
    <?php foreach ($cats as $cat): ?>
    <a class="sf-cat-btn <?= $category===$cat?'active':'' ?>" href="shop.php?category=<?= urlencode($cat) ?>&sort=<?= $sort ?>&search=<?= urlencode($search) ?>"><?= ucfirst(htmlspecialchars($cat)) ?></a>
    <?php endforeach; ?>
</div>

<!-- Header row -->
<div class="sf-section-head">
    <div>
        <div class="sf-section-title"><?= htmlspecialchars($catLabel) ?></div>
        <div class="sf-section-meta"><?= count($products) ?> item<?= count($products)!==1?'s':'' ?></div>
    </div>
</div>

<!-- Sort / search -->
<div class="sf-sort-row">
    <form method="GET" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;flex:1">
        <?php if ($category): ?><input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>"><?php endif; ?>
        <input type="text" name="search" class="sf-search-input" placeholder="Search products…" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" style="background:var(--ink);color:#fff;border:none;border-radius:var(--radius-sm);padding:7px 18px;font-size:12px;font-weight:500;font-family:inherit;cursor:pointer;letter-spacing:.05em;text-transform:uppercase">Search</button>
        <?php if ($search): ?><a href="shop.php?category=<?= urlencode($category) ?>&sort=<?= $sort ?>" style="font-size:12px;color:var(--ink-soft)">Clear</a><?php endif; ?>
    </form>
    <div style="display:flex;align-items:center;gap:8px">
        <span class="sf-sort-label">Sort</span>
        <select class="sf-sort-select" onchange="location.href='shop.php?sort='+this.value+'&category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>'">
            <option value="newest"     <?= $sort==='newest'    ?'selected':'' ?>>Newest</option>
            <option value="price_asc"  <?= $sort==='price_asc' ?'selected':'' ?>>Price: low → high</option>
            <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price: high → low</option>
            <option value="name_asc"   <?= $sort==='name_asc'  ?'selected':'' ?>>Name A–Z</option>
        </select>
    </div>
</div>

<!-- Product grid -->
<?php if (empty($products)): ?>
<div style="text-align:center;padding:60px 28px;color:var(--ink-soft)">
    <div style="font-size:14px">No products found.</div>
    <a href="shop.php" style="font-size:13px;color:var(--brand);display:block;margin-top:8px">Clear filters</a>
</div>
<?php else: ?>
<div class="sf-grid">
    <?php foreach ($products as $p):
        $isLow  = $p['stock'] > 0 && $p['stock'] <= 5;
        $isOut  = $p['stock'] === 0;
    ?>
    <a href="product_detail.php?id=<?= $p['id'] ?>" class="sf-card" style="display:block;text-decoration:none">
        <div class="sf-card-img">
            <svg width="60" height="60" viewBox="0 0 60 60" fill="none"><rect x="12" y="12" width="36" height="36" rx="5" stroke="#ccc" stroke-width="1.5"/><path d="M22 30h16M30 22v16" stroke="#ccc" stroke-width="1.5" stroke-linecap="round"/></svg>
            <?php if ($isOut):  ?><span class="sf-card-badge sf-badge-out">Sold out</span><?php endif; ?>
            <?php if ($isLow):  ?><span class="sf-card-badge sf-badge-low">Low stock</span><?php endif; ?>
        </div>
        <div class="sf-card-body">
            <div class="sf-card-cat"><?= htmlspecialchars($p['category'] ?? '') ?></div>
            <div class="sf-card-name"><?= htmlspecialchars($p['name']) ?></div>
            <div class="sf-card-foot">
                <span class="sf-card-price">$<?= number_format($p['price'],2) ?></span>
                <?php if ($isOut): ?><span class="sf-card-stock sf-stock-out">Sold out</span>
                <?php elseif ($isLow): ?><span class="sf-card-stock sf-stock-low"><?= $p['stock'] ?> left</span>
                <?php else: ?><span class="sf-card-stock sf-stock-ok">In stock</span><?php endif; ?>
            </div>
        </div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:10px">View details →</div>
        <?php if (!$isOut && $userId): ?>
        <div class="sf-quick-add" onclick="event.preventDefault()">
            <div class="sf-quick-add-label">Quick add</div>
            <form method="POST" action="cart_action.php" onclick="event.stopPropagation()">
                <input type="hidden" name="action"     value="add">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <input type="hidden" name="redirect"   value="shop.php?category=<?= urlencode($category) ?>&sort=<?= $sort ?>">
                <button type="submit" class="sf-quick-add-btn">Add to cart</button>
            </form>
        </div>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
