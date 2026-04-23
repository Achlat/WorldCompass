<?php
require_once 'includes/functions.php';

//  AJAX handler
if (isset($_GET['action']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    if ($action === 'add') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = max(1,(int)($_POST['qty'] ?? 1));
        $ok  = addToCart($pid, $qty);
        echo json_encode(['success'=>$ok,'count'=>cartCount(),'message'=>$ok?'Produit ajouté au panier !':'Produit indisponible.']);
        exit;
    }
    if ($action === 'update') {
        $cid = (int)($_POST['cart_id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? 1);
        if ($cid) updateCartQty($cid, $qty);
        echo json_encode(['success'=>true,'count'=>cartCount()]);
        exit;
    }
    if ($action === 'remove') {
        $cid = (int)($_POST['cart_id'] ?? 0);
        if ($cid) removeFromCart($cid);
        echo json_encode(['success'=>true,'count'=>cartCount()]);
        exit;
    }
    echo json_encode(['success'=>false]);
    exit;
}

//  Standard form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (isset($_POST['clear_cart'])) { clearCart(); flash('info','Panier vidé.'); }
    header('Location: cart.php'); exit;
}

$items      = getCartItems();
$total      = cartTotal();
$shipping   = $total > 0 && $total < (float)setting('free_shipping_threshold','50000') ? (float)setting('shipping_cost','2000') : 0;
$grandTotal = $total + $shipping;
$pageTitle  = 'Mon Panier';
$uploadDir  = __DIR__.'/uploads/products/';
?>
<?php require_once 'includes/header.php'; ?>

<div class="page-hdr"><div class="page-hdr-inner">
  <h1>Mon Panier <?php if ($items): ?><span style="font-size:1rem;font-weight:500;color:var(--text-3)">(<?= count($items) ?> article<?= count($items)>1?'s':'' ?>)</span><?php endif; ?></h1>
</div></div>

<div class="container" style="padding-bottom:3rem">
  <?php if ($items): ?>
    <div class="cart-layout">

      <!-- Articles du panier -->
      <div>
        <div class="cart-items">
          <?php foreach ($items as $item): ?>
            <div class="cart-item">
              <?php $imgFile = $item['image'] ?? ''; ?>
              <?php if ($imgFile && file_exists($uploadDir.$imgFile)): ?>
                <img src="<?= SITE_URL ?>/uploads/products/<?= h($imgFile) ?>" alt="<?= h($item['name']) ?>"
                     class="cart-img" style="object-fit:cover">
              <?php else: ?>
                <div class="cart-img" style="background:linear-gradient(135deg,<?= h($item['image_color']??'#5469d4') ?>,<?= h($item['image_color']??'#5469d4') ?>99);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.7)">
                  <?= str_replace('width="56" height="56"','width="28" height="28"', categoryIcon($item['cat_name']??'')) ?>
                </div>
              <?php endif; ?>
              <div>
                <a href="product.php?slug=<?= h($item['slug']) ?>" class="cart-item-name"><?= h($item['name']) ?></a>
                <div class="cart-item-price"><?= money((float)$item['price']) ?></div>
                <div class="cart-item-actions">
                  <div class="qty-input" style="scale:.85;transform-origin:left">
                    <button onclick="let i=this.nextElementSibling;let v=Math.max(1,parseInt(i.value)-1);i.value=v;i.dispatchEvent(new Event('change'))">&#8722;</button>
                    <input type="number" class="cart-qty-input" data-cart-id="<?= $item['id'] ?>" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
                    <button onclick="let i=this.previousElementSibling;let v=Math.min(<?= $item['stock'] ?>,parseInt(i.value)+1);i.value=v;i.dispatchEvent(new Event('change'))">+</button>
                  </div>
                  <button class="cart-remove" data-cart-id="<?= $item['id'] ?>">Retirer</button>
                </div>
              </div>
              <div style="text-align:right;min-width:100px">
                <strong style="font-size:1rem;color:var(--accent)"><?= money($item['price']*$item['quantity']) ?></strong>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div style="display:flex;gap:1rem;margin-top:1rem;flex-wrap:wrap">
          <a href="products.php" class="btn btn-outline">← Continuer mes achats</a>
          <form method="POST" onsubmit="return confirm('Vider le panier ?')">
            <?= csrfField() ?>
            <button type="submit" name="clear_cart" class="btn btn-danger btn-sm">Vider le panier</button>
          </form>
        </div>
      </div>

      <!-- Résumé -->
      <div class="cart-summary">
        <div class="cart-summary-title">Résumé de la commande</div>
        <div class="summary-row"><span>Sous-total</span><span><?= money($total) ?></span></div>
        <div class="summary-row">
          <span>Livraison</span>
          <span><?= $shipping > 0 ? money($shipping) : '<span class="free">Gratuite</span>' ?></span>
        </div>
        <?php if ($shipping > 0): ?>
          <p style="font-size:.78rem;color:var(--text-3);margin:.5rem 0">
            Ajoutez encore <?= money((float)setting('free_shipping_threshold','50000')-$total) ?> pour la livraison gratuite.
          </p>
        <?php endif; ?>
        <div class="summary-row total"><span>Total</span><span><?= money($grandTotal) ?></span></div>
        <a href="checkout.php" class="btn btn-primary btn-full mt-2" style="border-radius:var(--radius-sm);font-size:1rem;padding:.85rem">
          Passer la commande
        </a>
        <div style="display:flex;justify-content:center;gap:1rem;margin-top:1.25rem;font-size:.75rem;color:var(--text-3)">
          <span>Paiement sécurisé</span>
          <span>Retours faciles</span>
        </div>
      </div>

    </div>
  <?php else: ?>
    <div class="empty-state">
      <div class="empty-title">Votre panier est vide</div>
      <p class="empty-text">Ajoutez des produits pour commencer vos achats.</p>
      <a href="products.php" class="btn btn-primary btn-lg">Découvrir nos produits</a>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
