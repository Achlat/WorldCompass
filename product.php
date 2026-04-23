<?php
require_once 'includes/functions.php';
$slug = $_GET['slug'] ?? '';
$p    = $slug ? getProductBySlug($slug) : null;
if (!$p) { header('Location: products.php'); exit; }

// Increment views
db()->prepare("UPDATE products SET views=views+1 WHERE id=?")->execute([$p['id']]);

$rating   = productAvgRating($p['id']);
$rCount   = productReviewCount($p['id']);
$related  = getRelatedProducts((int)$p['category_id'],(int)$p['id'],4);
$reviews  = db()->prepare("SELECT * FROM reviews WHERE product_id=? ORDER BY created_at DESC");
$reviews->execute([$p['id']]);
$reviews  = $reviews->fetchAll();
$discount = $p['old_price'] ? discount((float)$p['old_price'],(float)$p['price']) : 0;
$pageTitle = $p['name'];

// Handle review submission
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['submit_review'])) {
    verifyCsrf();
    $userName = trim($_POST['user_name']??'');
    $uRating  = (int)($_POST['rating']??0);
    $comment  = trim($_POST['comment']??'');
    if ($userName && $uRating>=1 && $uRating<=5 && $comment) {
        $uid = isLoggedIn() ? $_SESSION['user_id'] : null;
        if (!$uid) $userName = h($userName);
        $ins = db()->prepare("INSERT INTO reviews(product_id,user_id,user_name,rating,comment) VALUES(?,?,?,?,?)");
        $ins->execute([$p['id'],$uid,$userName,$uRating,$comment]);
        flash('success','Avis publié avec succès, merci !');
    } else {
        flash('error','Veuillez remplir tous les champs correctement.');
    }
    header('Location: product.php?slug='.urlencode($slug).'#reviews'); exit;
}
?>
<?php require_once 'includes/header.php'; ?>

<div class="breadcrumb"><div class="breadcrumb-inner">
  <a href="<?= SITE_URL ?>">Accueil</a><span>›</span>
  <a href="<?= SITE_URL ?>/products.php">Produits</a><span>›</span>
  <?php if ($p['cat_slug']): ?><a href="<?= SITE_URL ?>/category.php?slug=<?= h($p['cat_slug']) ?>"><?= h($p['cat_name']) ?></a><span>›</span><?php endif; ?>
  <span class="current"><?= h($p['name']) ?></span>
</div></div>

<div class="container" style="padding:2rem 1.25rem 3rem">

  <!--  PRODUCT DETAIL  -->
  <div class="product-detail card card-body" style="border-radius:var(--radius);padding:2.5rem">
    <!-- Gallery -->
    <div class="product-gallery" style="background:linear-gradient(135deg,<?= h($p['image_color']??'#5469d4') ?>,<?= h($p['image_color']??'#5469d4') ?>cc);position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center">
      <?php
      $gallImg  = $p['image'] ?? '';
      $gallPath = __DIR__.'/uploads/products/'.$gallImg;
      if ($gallImg && file_exists($gallPath)): ?>
        <img src="<?= SITE_URL ?>/uploads/products/<?= h($gallImg) ?>" alt="<?= h($p['name']) ?>"
             style="width:100%;height:100%;object-fit:contain;position:absolute;top:0;left:0">
      <?php else: ?>
        <div style="color:rgba(255,255,255,.6)">
          <?= categoryIcon($p['cat_name']??'') ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="product-detail-info">
      <?php if ($p['cat_name']): ?>
        <a href="<?= SITE_URL ?>/category.php?slug=<?= h($p['cat_slug']) ?>" class="product-cat" style="font-size:.82rem;margin-bottom:.5rem;display:inline-block"><?= h($p['cat_name']) ?></a>
      <?php endif; ?>

      <h1><?= h($p['name']) ?></h1>

      <div class="detail-meta">
        <div class="flex" style="gap:.5rem">
          <span class="stars" style="font-size:1.1rem"><?= stars($rating) ?></span>
          <span style="font-size:.88rem;color:var(--text-3)"><?= number_format($rating,1) ?> (<?= $rCount ?> avis)</span>
        </div>
        <?php if ($p['stock']>0): ?>
          <span class="detail-stock-ok">En stock (<?= $p['stock'] ?>)</span>
        <?php else: ?>
          <span class="detail-stock-no">Rupture de stock</span>
        <?php endif; ?>
      </div>

      <div style="margin:1.25rem 0">
        <div class="detail-price"><?= money((float)$p['price']) ?></div>
        <?php if ($p['old_price']): ?>
          <div style="display:flex;align-items:center;gap:.75rem;margin-top:.35rem">
            <span class="detail-old-price"><?= money((float)$p['old_price']) ?></span>
            <span class="badge badge-sale" style="position:static">-<?= $discount ?>%</span>
            <span style="color:var(--success);font-size:.85rem;font-weight:600">Vous économisez <?= money($p['old_price']-$p['price']) ?></span>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($p['description']): ?>
        <p style="color:var(--text-2);font-size:.92rem;line-height:1.7;margin-bottom:1.5rem"><?= nl2br(h($p['description'])) ?></p>
      <?php endif; ?>

      <?php if ($p['stock']>0): ?>
        <div class="flex" style="gap:1rem;align-items:center;flex-wrap:wrap">
          <div class="qty-input">
            <button type="button" data-qty="-">−</button>
            <input type="number" id="detailQty" value="1" min="1" max="<?= $p['stock'] ?>">
            <button type="button" data-qty="+">+</button>
          </div>
          <div class="detail-actions" style="margin-top:0">
            <button class="btn btn-primary btn-add-cart btn-lg" data-id="<?= $p['id'] ?>">Ajouter au panier</button>
            <a href="<?= SITE_URL ?>/cart.php" class="btn btn-outline btn-lg">Voir le panier</a>
          </div>
        </div>
      <?php else: ?>
        <button class="btn btn-secondary" disabled>Rupture de stock</button>
      <?php endif; ?>

      <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border);display:flex;gap:2rem;font-size:.82rem;color:var(--text-3);flex-wrap:wrap">
        <span>Livraison rapide</span>
        <span>Retour 7 jours</span>
        <span>Paiement sécurisé</span>
        <span>Support 24/7</span>
      </div>
    </div>
  </div>

  <!--  TABS  -->
  <div class="tabs card card-body mt-3" style="margin-top:2rem">
    <div class="tab-nav">
      <button class="tab-btn active" data-tab="tab-desc">Description</button>
      <button class="tab-btn" data-tab="tab-reviews">Avis clients (<?= $rCount ?>)</button>
    </div>

    <div id="tab-desc" class="tab-pane active">
      <div style="color:var(--text-2);line-height:1.8;font-size:.92rem">
        <?= $p['description'] ? nl2br(h($p['description'])) : '<p>Aucune description disponible.</p>' ?>
      </div>
    </div>

    <div id="tab-reviews" class="tab-pane">
      <div id="reviews">
        <?php if ($reviews): ?>
          <?php foreach ($reviews as $r): ?>
            <div class="review-item">
              <div class="review-header">
                <div>
                  <span class="review-name"><?= h($r['user_name']??'Client') ?></span>
                  <span class="stars" style="font-size:.9rem;margin-left:.5rem"><?= stars((int)$r['rating']) ?></span>
                </div>
                <span class="review-date"><?= ago($r['created_at']) ?></span>
              </div>
              <p class="review-text"><?= h($r['comment']) ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="color:var(--text-3);padding:1rem 0">Soyez le premier à laisser un avis !</p>
        <?php endif; ?>

        <!-- Add review -->
        <div style="margin-top:2rem;padding-top:1.5rem;border-top:2px solid var(--border)">
          <h4 style="margin-bottom:1.25rem">Laisser un avis</h4>
          <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="submit_review" value="1">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Votre nom *</label>
                <?php $u = currentUser(); ?>
                <input class="form-control" name="user_name" value="<?= $u ? h($u['firstname'].' '.$u['lastname']) : '' ?>" required <?= $u?'readonly':'' ?>>
              </div>
              <div class="form-group">
                <label class="form-label">Note *</label>
                <select class="form-control" name="rating" required>
                  <option value="">-- Note --</option>
                  <?php for($i=5;$i>=1;$i--): ?>
                    <option value="<?= $i ?>"><?= str_repeat('★',$i) ?> – <?= ['','Mauvais','Passable','Bien','Tres bien','Excellent'][$i] ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Commentaire *</label>
              <textarea class="form-control" name="comment" rows="3" placeholder="Partagez votre expérience…" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Publier mon avis</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!--  RELATED PRODUCTS  -->
  <?php if ($related): ?>
    <section style="margin-top:3rem">
      <div class="section-header"><h2 class="section-title">Produits similaires</h2></div>
      <div class="products-grid">
        <?php foreach ($related as $rp): echo productCard($rp); endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
