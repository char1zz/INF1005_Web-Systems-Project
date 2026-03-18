<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
requireAdmin();

$error=''; $success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';
    $id=(int)($_POST['id']??0);
    $name=trim($_POST['name']??'');
    $description=trim($_POST['description']??'');
    $category=trim($_POST['category']??'general');
    $price=(float)($_POST['price']??0);
    $stock=(int)($_POST['stock']??0);
    $sizes=trim($_POST['sizes']??'');
    $measurements=trim($_POST['measurements']??'');

    if(in_array($action,['add','edit'],true)){
        if($name==='' || $price<=0 || $stock<0) $error='Please fill in the required fields correctly.';
        else {
            if($action==='add'){
                $pdo->prepare('INSERT INTO products(name,description,category,price,stock,sizes,measurements) VALUES(?,?,?,?,?,?,?)')->execute([$name,$description,$category,$price,$stock,$sizes,$measurements]);
                $success='Product added successfully.';
            } else {
                $pdo->prepare('UPDATE products SET name=?, description=?, category=?, price=?, stock=?, sizes=?, measurements=? WHERE id=?')->execute([$name,$description,$category,$price,$stock,$sizes,$measurements,$id]);
                $success='Product updated successfully.';
            }
        }
    }
    if($action==='delete'){
        $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
        $success='Product deleted successfully.';
    }
}

$editProduct=null;
if(isset($_GET['edit'])){
    $s=$pdo->prepare('SELECT * FROM products WHERE id=?');
    $s->execute([(int)$_GET['edit']]);
    $editProduct=$s->fetch();
}
$products=$pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();
$pageTitle='Admin — Products';
include '../../includes/header.php';
?>
<div class="sf-admin-wrap">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
        <h1 class="sf-admin-title" style="margin:0">Shop products</h1>
        <a href="/spinfit/admin/dashboard.php" class="sf-nav-btn sf-nav-btn-outline" style="text-decoration:none">← Dashboard</a>
    </div>
    <?php if($error): ?><div class="sf-alert sf-alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="sf-alert sf-alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <div style="display:grid;grid-template-columns:360px 1fr;gap:24px;align-items:start">
        <div class="sf-admin-form-card">
            <div class="sf-admin-form-title"><?= $editProduct?'Edit product':'Add new product' ?></div>
            <form method="POST">
                <input type="hidden" name="action" value="<?= $editProduct?'edit':'add' ?>">
                <?php if($editProduct): ?><input type="hidden" name="id" value="<?= $editProduct['id'] ?>"><?php endif; ?>
                <div class="sf-form-group"><label class="sf-label">Product name *</label><input type="text" name="name" class="sf-input" required value="<?= htmlspecialchars($editProduct['name']??'') ?>"></div>
                <div class="sf-form-group"><label class="sf-label">Description</label><textarea name="description" class="sf-input" rows="3" style="height:auto;resize:vertical"><?= htmlspecialchars($editProduct['description']??'') ?></textarea></div>
                <div class="sf-form-group"><label class="sf-label">Category</label>
                    <select name="category" class="sf-input" style="padding:10px 12px">
                        <?php foreach(['equipment','accessories','apparel','nutrition'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($editProduct['category']??'')===$cat?'selected':'' ?>><?= ucfirst($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="sf-form-group"><label class="sf-label">Price ($) *</label><input type="number" name="price" class="sf-input" step="0.01" min="0.01" required value="<?= $editProduct['price']??'' ?>"></div>
                    <div class="sf-form-group"><label class="sf-label">Stock *</label><input type="number" name="stock" class="sf-input" min="0" required value="<?= $editProduct['stock']??0 ?>"></div>
                </div>
                <div class="sf-form-group"><label class="sf-label">Available sizes</label><input type="text" name="sizes" class="sf-input" value="<?= htmlspecialchars($editProduct['sizes']??'') ?>" placeholder="e.g. XS, S, M, L"></div>
                <div class="sf-form-group"><label class="sf-label">Measurements</label><textarea name="measurements" class="sf-input" rows="4" style="height:auto;resize:vertical" placeholder="e.g. Chest width, waist, inseam, bottle capacity..."><?= htmlspecialchars($editProduct['measurements']??'') ?></textarea></div>
                <div style="display:flex;gap:10px">
                    <button type="submit" class="sf-submit-btn" style="margin-top:0"><?= $editProduct?'Save changes':'Add product' ?></button>
                    <?php if($editProduct): ?><a href="admin_products.php" class="sf-back-btn" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;padding:12px 16px;white-space:nowrap">Cancel</a><?php endif; ?>
                </div>
            </form>
        </div>
        <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
            <div style="padding:14px 20px;border-bottom:.5px solid var(--border);font-size:13px;font-weight:500">All products (<?= count($products) ?>)</div>
            <table class="sf-table" style="width:100%">
                <thead><tr><th>#</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Sizes</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if(empty($products)): ?><tr><td colspan="7" style="text-align:center;color:var(--ink-soft);padding:32px">No products yet.</td></tr>
                <?php else: foreach($products as $p): ?>
                <tr>
                    <td style="color:var(--ink-soft)"><?= $p['id'] ?></td>
                    <td><div style="font-weight:500"><?= htmlspecialchars($p['name']) ?></div><div style="font-size:11px;color:var(--ink-soft)"><?= htmlspecialchars(mb_strimwidth($p['description']??'',0,48,'…')) ?></div></td>
                    <td><span class="sf-pill sf-pill-gray"><?= ucfirst($p['category']??'') ?></span></td>
                    <td style="font-weight:500">$<?= number_format($p['price'],2) ?></td>
                    <td><span class="sf-pill <?= $p['stock']==0?'sf-pill-red':($p['stock']<=5?'sf-pill-amber':'sf-pill-green') ?>"><?= $p['stock'] ?></span></td>
                    <td style="font-size:12px;color:var(--ink-soft)"><?= htmlspecialchars($p['sizes'] ?: '—') ?></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="?edit=<?= $p['id'] ?>" class="sf-nav-btn sf-nav-btn-outline" style="padding:5px 10px;font-size:11px;text-decoration:none">Edit</a>
                            <form method="POST" style="display:inline"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button type="submit" class="sf-nav-btn" style="padding:5px 10px;font-size:11px;border:1px solid #fca5a5;color:#991b1b;background:none" data-confirm="Delete '<?= htmlspecialchars(addslashes($p['name'])) ?>'?">Delete</button></form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
