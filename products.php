<?php
require_once 'includes/functions.php';

$perPage  = (int)setting('items_per_page','12');
$page     = max(1,(int)($_GET['page']??1));
$offset   = ($page-1)*$perPage;
$catSlug  = $_GET['cat']??'';
$sort     = $_GET['sort']??'newest';
$featured = isset($_GET['featured']);
$minPrice = (float)($_GET['min']??0);
$maxPrice = (float)($_GET['max']??0);

// Build WHERE
$where = ['p.active=1'];
$params = [];
if ($catSlug) {
    $cat = getCategoryBySlug($catSlug);
    if ($cat) { $where[] = 'p.category_id=?'; $params[] = $cat['id']; }
}
if ($featured) { $where[] = 'p.featured=1'; }
if ($minPrice > 0) { $where[] = 'p.price>=?'; $params[] = $minPrice; }
if ($maxPrice > 0) { $where[] = 'p.price<=?'; $params[] = $maxPrice; }

$sortMap = [
    'newest'    => 'p.created_at DESC',
    'oldest'    => 'p.created_at ASC',
    'price_asc' => 'p.price ASC',
    'price_desc'=> 'p.price DESC',
    'popular'   => 'p.views DESC',
];
$orderBy = $sortMap[$sort] ?? 'p.created_at DESC';
$whereSQL = implode(' AND ',$where);

$total = db()->prepare("SELECT COUNT(*) FROM products p WHERE $whereSQL");
$total->execute($params);
$total = (int)$total->fetchColumn();

$stmt = db()->prepare("SELECT p.*,c.name cat_name,c.slug cat_slug
    FROM products p LEFT JOIN categories c ON c.id=p.category_id
    WHERE $whereSQL ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories  = getCategories();
$pageTitle   = $catSlug ? ($cat['name']??'Produits') : 'Tous les produits';
$currentUrl  = SITE_URL.'/products.php?'.http_build_query(array_filter(['cat'=>$catSlug,'sort'=>$sort,'featured'=>$featured?'1':'','min'=>$minPrice?:null,'max'=>$maxPrice?:null]));
?>
<?php require_once 'includes/header.php'; ?>

<div class="breadcrumb"><div class="breadcrumb-inner">
  <a href="<?= SITE_URL ?>">Accueil</a><span>›</span>
  <span class="current"><?= h($pageTitle) ?></span>
</div></div>

<div class="container" style="padding-top:2rem;padding-bottom:3rem">
  <div class="page-layout">

    <!--  SIDEBAR  -->
    <aside class="sidebar">
      <div class="sidebar-title"> Filtres</div>

      <div class="sidebar-section">
        <h4>Catégories</h4>
        <ul class="filter-list">
          <li><a href="products.php" class="<?= !$catSlug?'active':'' ?>">Tout (<?= db()->query("SELECT COUNT(*) FROM products WHERE active=1")->fetchColumn() ?>)</a></li>
          <?php foreach ($categories as $c):
            $cnt = db()->prepare("SELECT COUNT(*) FROM products WHERE category_id=? AND active=1");
            $cnt->execute([$c['id']]);
          ?>
            <li><a href="products.php?cat=<?= h($c['slug']) ?>" class="<?= $catSlug===$c['slug']?'active':'' ?>">
              <?= h($c['icon'].' '.$c['name']) ?> (<?= $cnt->fetchColumn() ?>)
            </a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="sidebar-section">
        <h4>Prix (<?= CURRENCY ?>)</h4>
        <form method="GET" action="products.php">
          <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= h($catSlug) ?>"><?php endif; ?>
          <div class="price-range">
            <input type="number" name="min" class="form-control" placeholder="Min" value="<?= $minPrice?:'' ?>" min="0">
            <input type="number" name="max" class="form-control" placeholder="Max" value="<?= $maxPrice?:'' ?>" min="0">
          </div>
          <button type="submit" class="btn btn-primary btn-full mt-1" style="border-radius:var(--radius-sm)">Appliquer</button>
        </form>
      </div>

      <div class="sidebar-section">
        <h4>Disponibilité</h4>
        <ul class="filter-list">
          <li><a href="products.php?<?= $catSlug?"cat=$catSlug&":'' ?>">Tous</a></li>
          <li><a href="products.php?<?= $catSlug?"cat=$catSlug&":'' ?>featured=1" class="<?= $featured?'active':'' ?>"> Populaires</a></li>
        </ul>
      </div>
    </aside>

    <!--  MAIN CONTENT  -->
    <div>
      <div class="flex-between mb-3" style="flex-wrap:wrap;gap:1rem">
        <div>
          <h1 style="font-size:1.35rem;font-weight:800"><?= h($pageTitle) ?></h1>
          <p class="text-muted"><?= $total ?> produit<?= $total>1?'s':'' ?> trouvé<?= $total>1?'s':'' ?></p>
        </div>
        <form method="GET" action="products.php" style="display:flex;align-items:center;gap:.5rem">
          <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= h($catSlug) ?>"><?php endif; ?>
          <?php if ($featured): ?><input type="hidden" name="featured" value="1"><?php endif; ?>
          <label for="sortSel" style="font-size:.85rem;color:var(--text-3)">Trier :</label>
          <select name="sort" id="sortSel" class="form-control" style="width:auto" onchange="this.form.submit()">
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
          <div class="empty-title">Aucun produit trouvé</div>
          <p class="empty-text">Essayez d'autres filtres ou consultez toutes les catégories.</p>
          <a href="products.php" class="btn btn-primary">Voir tous les produits</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
