<?php
require_once 'includes/functions.php';
$q       = trim($_GET['q'] ?? '');
$page    = max(1,(int)($_GET['page']??1));
$perPage = (int)setting('items_per_page','12');
$offset  = ($page-1)*$perPage;
$products= [];
$total   = 0;
$pageTitle = 'Recherche';

if (strlen($q) >= 2) {
    $like = '%'.$q.'%';
    $cntStmt = db()->prepare("SELECT COUNT(*) FROM products p WHERE p.active=1 AND (p.name LIKE ? OR p.description LIKE ?)");
    $cntStmt->execute([$like,$like]);
    $total = (int)$cntStmt->fetchColumn();

    $stmt = db()->prepare("SELECT p.*,c.name cat_name,c.slug cat_slug
        FROM products p LEFT JOIN categories c ON c.id=p.category_id
        WHERE p.active=1 AND (p.name LIKE ? OR p.description LIKE ?)
        ORDER BY p.featured DESC, p.views DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute([$like,$like]);
    $products = $stmt->fetchAll();
}
$currentUrl = SITE_URL.'/search.php?q='.urlencode($q);
?>
<?php require_once 'includes/header.php'; ?>

<div class="page-hdr"><div class="page-hdr-inner">
  <h1>Résultats pour "<?= h($q) ?>"</h1>
  <p><?= $total ?> produit<?= $total>1?'s':'' ?> trouvé<?= $total>1?'s':'' ?></p>
</div></div>

<div class="container" style="padding-bottom:3rem">
  <?php if (strlen($q) < 2): ?>
    <div class="empty-state">
      <div class="empty-icon"></div>
      <div class="empty-title">Entrez au moins 2 caractères</div>
      <p class="empty-text">Utilisez la barre de recherche en haut pour trouver vos produits.</p>
    </div>
  <?php elseif ($products): ?>
    <div class="products-grid" style="margin-top:1.5rem">
      <?php foreach ($products as $p): echo productCard($p); endforeach; ?>
    </div>
    <?= paginate($total,$perPage,$page,$currentUrl) ?>
  <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon"></div>
      <div class="empty-title">Aucun résultat</div>
      <p class="empty-text">Essayez d'autres termes ou parcourez nos catégories.</p>
      <a href="products.php" class="btn btn-primary">Voir tous les produits</a>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
