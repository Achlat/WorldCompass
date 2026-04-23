<?php
$pageTitle = 'Accueil';
require_once 'includes/functions.php';

$featuredProducts = getFeaturedProducts(8);
$categories       = getCategories();

// Latest arrivals
$latest = db()->query("SELECT p.*,c.name cat_name,c.slug cat_slug
    FROM products p LEFT JOIN categories c ON c.id=p.category_id
    WHERE p.active=1 ORDER BY p.created_at DESC LIMIT 8")->fetchAll();

// Stats
$totalProducts = db()->query("SELECT COUNT(*) FROM products WHERE active=1")->fetchColumn();
$totalOrders   = db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers    = db()->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
?>
<?php require_once 'includes/header.php'; ?>

<!--  HERO  -->
<section class="hero">
  <div class="hero-content">
    <h1>Trouvez tout ce dont<br>vous avez <span>besoin</span></h1>
    <p>Des milliers de produits de qualité. Shopping facile, paiement sécurisé.</p>
    <div class="hero-btns">
      <a href="products.php" class="btn btn-primary btn-lg">Découvrir les produits</a>
      <a href="products.php?featured=1" class="btn btn-outline btn-lg" style="border-color:#fff;color:#fff">Offres du moment</a>
    </div>
  </div>
</section>

<!--  CATEGORIES  -->
<div class="container">
  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Nos Catégories</h2>
      <a href="products.php" class="section-link">Tout voir →</a>
    </div>
    <div class="cat-cards">
      <?php foreach ($categories as $c): ?>
        <a href="category.php?slug=<?= h($c['slug']) ?>" class="cat-card">
          <div class="cat-name"><?= h($c['name']) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
</div>


<!--  FEATURED PRODUCTS  -->
<div class="container">
  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Produits Populaires</h2>
      <a href="products.php?featured=1" class="section-link">Voir tout →</a>
    </div>
    <?php if ($featuredProducts): ?>
      <div class="products-grid">
        <?php foreach ($featuredProducts as $p): echo productCard($p); endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state"><p>Aucun produit disponible.</p></div>
    <?php endif; ?>
  </section>
</div>

<!--  STATS BANNER  -->
<div class="stats-banner">
  <div class="container">
    <div class="stats-grid">
      <div>
        <div class="stat-number" data-animate-number data-target="<?= $totalProducts ?>" data-suffix="+">0</div>
        <div class="stat-label">Produits disponibles</div>
      </div>
      <div>
        <div class="stat-number" data-animate-number data-target="<?= $totalOrders ?>" data-suffix="+">0</div>
        <div class="stat-label">Commandes traitées</div>
      </div>
      <div>
        <div class="stat-number" data-animate-number data-target="<?= $totalUsers ?>" data-suffix="+">0</div>
        <div class="stat-label">Clients satisfaits</div>
      </div>
      <div>
        <div class="stat-number" data-animate-number data-target="24" data-suffix="h">0</div>
        <div class="stat-label">Support disponible</div>
      </div>
    </div>
  </div>
</div>

<!--  LATEST PRODUCTS  -->
<div class="container">
  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Nouveautés</h2>
      <a href="products.php" class="section-link">Voir tout →</a>
    </div>
    <div class="products-grid">
      <?php foreach ($latest as $p): echo productCard($p); endforeach; ?>
    </div>
  </section>
</div>

<!--  WHY US  -->
<div class="container">
  <section class="section">
    <h2 class="section-title mb-3">Pourquoi nous choisir ?</h2>
    <div class="grid-4">
      <?php
      $features = [
        ['Paiement Sécurisé','Toutes vos transactions sont protégées et chiffrées.'],
        ['Retours Faciles','Retour sous 7 jours sans justification.'],
        ['Support 24/7','Notre équipe répond à toutes vos questions.'],
      ];
      foreach ($features as [$title,$text]):
      ?>
        <div class="card">
          <div class="card-body text-center">
            <h4 style="margin-bottom:.4rem;font-size:1rem"><?= $title ?></h4>
            <p style="color:var(--text-3);font-size:.83rem;line-height:1.5"><?= $text ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<?php require_once 'includes/footer.php'; ?>
