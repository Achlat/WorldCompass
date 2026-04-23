<?php
require_once 'includes/functions.php';
$slug = $_GET['slug'] ?? '';
$cat  = $slug ? getCategoryBySlug($slug) : null;
if (!$cat) { header('Location: products.php'); exit; }
$pageTitle = $cat['name'];

$sort    = $_GET['sort'] ?? 'newest';
$page    = max(1,(int)($_GET['page']??1));
$perPage = (int)setting('items_per_page','12');
$offset  = ($page-1)*$perPage;
$sortMap = ['newest'=>'p.created_at DESC','price_asc'=>'p.price ASC','price_desc'=>'p.price DESC','popular'=>'p.views DESC'];
$orderBy = $sortMap[$sort] ?? 'p.created_at DESC';

$total = db()->prepare("SELECT COUNT(*) FROM products p WHERE p.category_id=? AND p.active=1");
$total->execute([$cat['id']]); $total = (int)$total->fetchColumn();

$stmt = db()->prepare("SELECT p.*,c.name cat_name,c.slug cat_slug
    FROM products p LEFT JOIN categories c ON c.id=p.category_id
    WHERE p.category_id=? AND p.active=1 ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute([$cat['id']]);
$products = $stmt->fetchAll();
$currentUrl = SITE_URL.'/category.php?slug='.urlencode($slug);
?>
<?php require_once 'includes/header.php'; ?>

<div class="breadcrumb"><div class="breadcrumb-inner">
  <a href="<?= SITE_URL ?>">Accueil</a><span>›</span>
  <a href="<?= SITE_URL ?>/products.php">Produits</a><span>›</span>
  <span class="current"><?= h($cat['name']) ?></span>
</div></div>

<!-- Category Hero -->
<div style="background:linear-gradient(135deg,<?= h($cat['color']??'#5469d4') ?>,<?= h($cat['color']??'#5469d4') ?>99);color:#fff;padding:2.5rem 0;text-align:center">
  <div style="font-size:3rem;margin-bottom:.5rem"><?= h($cat['icon']) ?></div>
  <h1 style="font-size:1.8rem;font-weight:800;margin-bottom:.35rem"><?= h($cat['name']) ?></h1>
  <?php if ($cat['description']): ?><p style="opacity:.85;max-width:500px;margin:0 auto;font-size:.92rem"><?= h($cat['description']) ?></p><?php endif; ?>
</div>

<div class="container" style="padding:2rem 1.25rem 3rem">
  <div class="flex-between mb-3" style="flex-wrap:wrap;gap:1rem">
    <p class="text-muted"><?= $total ?> produit<?= $total>1?'s':'' ?></p>
    <form method="GET" action="category.php" style="display:flex;align-items:center;gap:.5rem">
      <input type="hidden" name="slug" value="<?= h($slug) ?>">
      <label style="font-size:.85rem;color:var(--text-3)">Trier :</label>
      <select name="sort" class="form-control" style="width:auto" onchange="this.form.submit()">
        <option value="newest"    <?= $sort==='newest'?'selected':'' ?>>Plus récents</option>
        <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Prix croissant</option>
        <option value="price_desc"<?= $sort==='price_desc'?'selected':'' ?>>Prix décroissant</option>
        <option value="popular"   <?= $sort==='popular'?'selected':'' ?>>Popularité</option>
      </select>
    </form>
  </div>

  <?php if ($products): ?>
    <div class="products-grid">
      <?php foreach ($products as $p): echo productCard($p); endforeach; ?>
    </div>
    <?= paginate($total,$perPage,$page,$currentUrl) ?>
  <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon"></div>
      <div class="empty-title">Aucun produit dans cette catégorie</div>
      <a href="products.php" class="btn btn-primary mt-2">Voir tous les produits</a>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
