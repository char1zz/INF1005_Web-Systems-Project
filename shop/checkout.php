<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once 'includes/shop_helpers.php';
requireLogin('/spinfit/login.php');
$userId=$_SESSION['user_id'];$items=getCartItems($pdo,(int)$userId);
if(empty($items)){setFlash('warning','Your cart is empty.');redirect('shop.php');}
$isMember=userHasActiveMembership($pdo,(int)$userId);
$subtotal=array_sum(array_map(fn($i)=>$i['price']*$i['quantity'],$items));
$discountAmount=$isMember?round($subtotal*MEMBER_DISCOUNT,2):0;
$total=$subtotal-$discountAmount;
if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $pdo->beginTransaction();
        $pdo->prepare("INSERT INTO orders(user_id,subtotal,discount_applied,discount_amount,total,status) VALUES(?,?,?,?,?,'pending')")
            ->execute([$userId,$subtotal,$isMember?1:0,$discountAmount,$total]);
        $orderId=(int)$pdo->lastInsertId();
        $ii=$pdo->prepare("INSERT INTO order_items(order_id,product_id,name,quantity,unit_price) VALUES(?,?,?,?,?)");
        foreach($items as $item){
            $ii->execute([$orderId,$item['id'],$item['name'],$item['quantity'],$item['price']]);
            $pdo->prepare('UPDATE products SET stock=stock-? WHERE id=? AND stock>=?')->execute([$item['quantity'],$item['id'],$item['quantity']]);
        }
        $pdo->prepare('DELETE FROM cart_items WHERE user_id=?')->execute([$userId]);
        $pdo->commit();
        setFlash('success',"Order #$orderId placed successfully! Thank you.");
        redirect("order_confirm.php?order_id=$orderId");
    }catch(Exception $e){$pdo->rollBack();setFlash('error','Something went wrong. Please try again.');redirect('checkout.php');}
}
$pageTitle='Checkout';include '../includes/header.php';
?>
<div class="sf-page-inner">
    <h1 class="sf-page-title">Checkout</h1>
    <div style="display:grid;grid-template-columns:1fr 320px;gap:32px;align-items:start">
        <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
            <div style="padding:16px 20px;border-bottom:.5px solid var(--border);font-size:13px;font-weight:500">Review your order (<?= count($items) ?> item<?= count($items)>1?'s':'' ?>)</div>
            <?php foreach($items as $item): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:.5px solid var(--border)">
                <div style="display:flex;align-items:center;gap:14px">
                    <div class="sf-cart-img"><svg width="22" height="22" viewBox="0 0 22 22" fill="none"><rect x="2" y="2" width="18" height="18" rx="3" stroke="#ccc" stroke-width="1.2"/></svg></div>
                    <div><div class="sf-cart-name"><?= htmlspecialchars($item['name']) ?></div><div class="sf-cart-subcat">$<?= number_format($item['price'],2) ?> × <?= $item['quantity'] ?></div></div>
                </div>
                <span style="font-size:13px;font-weight:500">$<?= number_format($item['price']*$item['quantity'],2) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="sf-summary-card">
            <div style="font-size:14px;font-weight:500;margin-bottom:16px">Payment summary</div>
            <div class="sf-summary-row"><span>Subtotal</span><span>$<?= number_format($subtotal,2) ?></span></div>
            <?php if($isMember): ?><div class="sf-summary-row" style="color:#15803d"><span>Member discount (10%)</span><span>−$<?= number_format($discountAmount,2) ?></span></div><?php endif; ?>
            <hr class="sf-summary-divider">
            <div class="sf-summary-total"><span>Total</span><span>$<?= number_format($total,2) ?></span></div>
            <form method="POST"><button type="submit" class="sf-add-btn" style="margin-top:16px">Place order</button></form>
            <a href="cart.php" style="display:block;text-align:center;font-size:12px;color:var(--ink-soft);margin-top:10px">← Back to cart</a>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
