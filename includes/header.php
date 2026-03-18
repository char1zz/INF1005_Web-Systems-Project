<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
$isShop      = $currentDir === 'shop' || in_array($currentPage, ['shop.php','product_detail.php','cart.php','checkout.php','order_confirm.php','my_orders.php']);
$isClasses   = in_array($currentPage, ['classes.php','class_detail.php','my_bookings.php']);
$isMember    = in_array($currentPage, ['membership_plans.php','membership_status.php']);
$isAdmin     = $currentDir === 'admin';

$cartCount = 0;
if (isLoggedIn() && isset($pdo)) {
    try {
        $cStmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE user_id=?");
        $cStmt->execute([$_SESSION['user_id']]);
        $cartCount = (int)$cStmt->fetchColumn();
    } catch (Exception $e) { $cartCount = 0; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — SpinFit' : 'SpinFit' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/spinfit/assets/css/style.css">
    <?php if (isset($extraCss)) echo $extraCss; ?>
</head>
<body>

<div class="sf-announce">
    Free shipping on orders above $80
    <span style="opacity:.4">|</span>
    New classes added weekly
    <span style="opacity:.4">|</span>
    Members get 10% off the shop
</div>

<nav class="sf-nav" style="position:relative">
    <a class="sf-logo" href="/spinfit/index.php"><span>SPIN</span>FIT</a>

    <div class="sf-nav-links">
        <a class="sf-nav-link <?= $currentPage==='index.php'?'active':'' ?>" href="/spinfit/index.php">Home</a>
        <a class="sf-nav-link <?= $isClasses?'active':'' ?>" href="/spinfit/classes.php" data-mega="mega-classes">Book Classes</a>
        <a class="sf-nav-link <?= $isShop?'active':'' ?>" href="/spinfit/shop/shop.php" data-mega="mega-shop">Shop</a>
        <a class="sf-nav-link <?= $isMember?'active':'' ?>" href="/spinfit/membership_plans.php">Membership</a>
        <a class="sf-nav-link <?= $currentPage==='about.php'?'active':'' ?>" href="/spinfit/about.php">About Us</a>
    </div>

    <div class="sf-nav-right">
        <?php if (isLoggedIn()): ?>
            <a class="sf-nav-icon" href="/spinfit/profile.php" title="My account">
                <svg width="17" height="17" viewBox="0 0 17 17" fill="none"><circle cx="8.5" cy="5.5" r="3.5" stroke="currentColor" stroke-width="1.4"/><path d="M1.5 15.5c0-3.866 3.134-7 7-7s7 3.134 7 7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
            </a>
            <a class="sf-nav-icon" href="/spinfit/shop/cart.php" title="Cart" style="position:relative">
                <svg width="17" height="17" viewBox="0 0 17 17" fill="none"><path d="M2 2h1.5L5.2 9.5h7.2l1.8-6H5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="6.5" cy="13" r="1" fill="currentColor"/><circle cx="11.5" cy="13" r="1" fill="currentColor"/></svg>
                <?php if ($cartCount > 0): ?><span class="sf-cart-badge"><?= $cartCount ?></span><?php endif; ?>
            </a>
            <?php if (isAdmin()): ?>
                <a class="sf-nav-btn sf-nav-btn-outline" href="/spinfit/admin/dashboard.php">Admin</a>
            <?php endif; ?>
            <a class="sf-nav-btn sf-nav-btn-outline" href="/spinfit/logout.php">Log out</a>
        <?php else: ?>
            <a class="sf-nav-icon" href="/spinfit/shop/cart.php" title="Cart" style="position:relative">
                <svg width="17" height="17" viewBox="0 0 17 17" fill="none"><path d="M2 2h1.5L5.2 9.5h7.2l1.8-6H5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="6.5" cy="13" r="1" fill="currentColor"/><circle cx="11.5" cy="13" r="1" fill="currentColor"/></svg>
            </a>
            <a class="sf-nav-btn sf-nav-btn-outline" href="/spinfit/login.php">Log in</a>
            <a class="sf-nav-btn sf-nav-btn-solid" href="/spinfit/register.php">Sign up</a>
        <?php endif; ?>
        <button class="sf-hamburger" id="sf-hamburger" aria-label="Menu">
            <svg width="20" height="14" viewBox="0 0 20 14" fill="none"><rect width="20" height="1.5" rx=".75" fill="currentColor"/><rect y="6" width="14" height="1.5" rx=".75" fill="currentColor"/><rect y="12" width="20" height="1.5" rx=".75" fill="currentColor"/></svg>
        </button>
    </div>

    <!-- Shop mega menu -->
    <div class="sf-mega" id="mega-shop">
        <div class="sf-mega-col">
            <div class="sf-mega-title">Category</div>
            <a class="sf-mega-link" href="/spinfit/shop/shop.php">All products</a>
            <a class="sf-mega-link" href="/spinfit/shop/shop.php?category=equipment">Equipment</a>
            <a class="sf-mega-link" href="/spinfit/shop/shop.php?category=accessories">Accessories</a>
            <a class="sf-mega-link" href="/spinfit/shop/shop.php?category=apparel">Apparel</a>
            <a class="sf-mega-link" href="/spinfit/shop/shop.php?category=nutrition">Nutrition</a>
        </div>
        <div class="sf-mega-col">
            <div class="sf-mega-title">Collections</div>
            <a class="sf-mega-link" href="/spinfit/shop/shop.php">Spin essentials</a>
            <a class="sf-mega-link" href="/spinfit/shop/shop.php">HIIT gear</a>
            <a class="sf-mega-link" href="/spinfit/shop/shop.php">Member bundles</a>
            <a class="sf-mega-link" href="/spinfit/shop/shop.php">New arrivals</a>
        </div>
        <div class="sf-mega-col">
            <div class="sf-mega-title">Account</div>
            <a class="sf-mega-link" href="/spinfit/shop/cart.php">My cart</a>
            <a class="sf-mega-link" href="/spinfit/shop/my_orders.php">My orders</a>
            <a class="sf-mega-link" href="/spinfit/membership_plans.php">Get 10% off</a>
        </div>
        <div style="flex:1"></div>
        <div class="sf-mega-promo">
            <div class="sf-mega-promo-img">
                <svg width="56" height="56" viewBox="0 0 56 56" fill="none"><circle cx="28" cy="28" r="20" stroke="#ccc" stroke-width="1.5"/><path d="M18 28c0-5.5 4.5-10 10-10s10 4.5 10 10-4.5 10-10 10" stroke="#ccc" stroke-width="1.5" stroke-linecap="round"/><circle cx="28" cy="28" r="3" fill="#ccc"/></svg>
            </div>
            <div class="sf-mega-promo-name">Spin essentials</div>
            <div class="sf-mega-promo-sub">Shop the kit →</div>
        </div>
    </div>

    <!-- Classes mega menu -->
    <div class="sf-mega" id="mega-classes">
        <div class="sf-mega-col">
            <div class="sf-mega-title">Class types</div>
            <a class="sf-mega-link" href="/spinfit/classes.php">All classes</a>
            <a class="sf-mega-link" href="/spinfit/classes.php?type=spin">Spin</a>
            <a class="sf-mega-link" href="/spinfit/classes.php?type=hiit">HIIT</a>
        </div>
        <div class="sf-mega-col">
            <div class="sf-mega-title">My bookings</div>
            <a class="sf-mega-link" href="/spinfit/classes.php">Book a class</a>
            <a class="sf-mega-link" href="/spinfit/my_bookings.php">My bookings</a>
        </div>
    </div>
</nav>

<!-- Mobile drawer overlay -->
<div class="sf-overlay" id="sf-overlay">
    <div class="sf-drawer" id="sf-drawer">
        <div class="sf-drawer-head">
            <span>Menu</span>
            <button class="sf-drawer-close" id="sf-drawer-close">&#x2715;</button>
        </div>
        <a class="sf-drawer-item" href="/spinfit/index.php">Home</a>
        <div class="sf-drawer-item" data-sub="drawer-classes">
            Book Classes <span class="sf-drawer-arrow">&#8250;</span>
        </div>
        <div class="sf-drawer-sub open" id="drawer-classes">
            <a class="sf-drawer-sub-item" href="/spinfit/classes.php">All classes</a>
            <a class="sf-drawer-sub-item" href="/spinfit/classes.php?type=spin">Spin</a>
            <a class="sf-drawer-sub-item" href="/spinfit/classes.php?type=hiit">HIIT</a>
            <a class="sf-drawer-sub-item" href="/spinfit/my_bookings.php">My bookings</a>
        </div>
        <div class="sf-drawer-item" data-sub="drawer-shop">
            Shop <span class="sf-drawer-arrow">&#8250;</span>
        </div>
        <div class="sf-drawer-sub" id="drawer-shop">
            <a class="sf-drawer-sub-item" href="/spinfit/shop/shop.php">All products</a>
            <a class="sf-drawer-sub-item" href="/spinfit/shop/shop.php?category=equipment">Equipment</a>
            <a class="sf-drawer-sub-item" href="/spinfit/shop/shop.php?category=accessories">Accessories</a>
            <a class="sf-drawer-sub-item" href="/spinfit/shop/shop.php?category=apparel">Apparel</a>
            <a class="sf-drawer-sub-item" href="/spinfit/shop/shop.php?category=nutrition">Nutrition</a>
        </div>
        <a class="sf-drawer-item" href="/spinfit/membership_plans.php">Membership</a>
        <a class="sf-drawer-item" href="/spinfit/about.php">About Us</a>
        <?php if (isLoggedIn()): ?>
            <a class="sf-drawer-item" href="/spinfit/profile.php">My Account</a>
            <a class="sf-drawer-item" href="/spinfit/shop/cart.php">Cart <?= $cartCount > 0 ? "($cartCount)" : '' ?></a>
            <a class="sf-drawer-item" href="/spinfit/logout.php">Log out</a>
        <?php else: ?>
            <a class="sf-drawer-item" href="/spinfit/login.php">Log in</a>
            <a class="sf-drawer-item" href="/spinfit/register.php">Sign up</a>
        <?php endif; ?>
    </div>
</div>

<div class="sf-page">
<?php
// Show flash message if any
if (!empty($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $cls = match($f['type'] ?? 'info') {
        'success' => 'sf-alert-success',
        'error'   => 'sf-alert-danger',
        'warning' => 'sf-alert-warning',
        default   => 'sf-alert-info',
    };
    echo "<div style='padding:0 28px;margin-top:16px'><div class='sf-alert $cls'>" . htmlspecialchars($f['msg']) . "</div></div>";
}
?>
