<?php
require_once __DIR__.'/functions.php';
$cats     = getCategories();
$cartCnt  = cartCount();
$siteName = setting('site_name', SITE_NAME);
$search   = h($_GET['q'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= isset($pageTitle) ? h($pageTitle).' – '.$siteName : $siteName ?></title>
<meta name="description" content="<?= isset($pageDesc) ? h($pageDesc) : 'Votre boutique en ligne – '.$siteName ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!--  MAIN HEADER  -->
<header class="site-header">
  <div class="header-inner">
    <!-- Logo -->
    <a href="<?= SITE_URL ?>" class="logo">
      <span class="logo-text"><?= $siteName ?></span>
    </a>

    <!-- Search -->
    <form action="<?= SITE_URL ?>/search.php" method="GET" class="search-form" role="search">
      <div class="search-wrap">
        <input type="text" name="q" value="<?= $search ?>" placeholder="Rechercher un produit…" autocomplete="off" class="search-input">
        <button type="submit" class="search-btn">Rechercher</button>
      </div>
    </form>

    <!-- Actions -->
    <div class="header-actions">
      <?php if (isLoggedIn()): ?>
        <a href="<?= SITE_URL ?>/profile.php" class="hdr-link">
          <span class="hdr-lbl">Mon Compte</span>
        </a>
        <a href="<?= SITE_URL ?>/orders.php" class="hdr-link">
          <span class="hdr-lbl">Commandes</span>
        </a>
        <?php if (isSeller() && !isAdmin()): ?>
          <a href="<?= SITE_URL ?>/seller/index.php" class="hdr-link seller-link" title="Espace vendeur">
            <span class="hdr-lbl">Ma Boutique</span>
          </a>
        <?php endif; ?>
      <?php else: ?>
        <a href="<?= SITE_URL ?>/login.php" class="hdr-link">
          <span class="hdr-lbl">Connexion</span>
        </a>
      <?php endif; ?>
      <a href="<?= SITE_URL ?>/cart.php" class="hdr-link cart-link">
        <span class="hdr-lbl">Panier</span>
        <?php if ($cartCnt > 0): ?>
          <span class="cart-badge" id="cart-badge"><?= $cartCnt ?></span>
        <?php else: ?>
          <span class="cart-badge" id="cart-badge" style="display:none"></span>
        <?php endif; ?>
      </a>
      <?php if (isAdmin()): ?>
        <a href="<?= SITE_URL ?>/admin/index.php" class="hdr-link admin-link" title="Administration">
          <span class="hdr-lbl">Admin</span>
        </a>
      <?php endif; ?>
    </div>

    <!-- Mobile menu btn -->
    <button class="mob-menu-btn" id="mobMenuBtn" aria-label="Menu">Menu</button>
  </div>
</header>

<!--  CATEGORY NAV  -->
<nav class="cat-nav" id="catNav">
  <div class="cat-nav-inner">
    <a href="<?= SITE_URL ?>/products.php" class="cat-nav-link">Tous les produits</a>
    <?php foreach ($cats as $c): ?>
      <a href="<?= SITE_URL ?>/category.php?slug=<?= h($c['slug']) ?>" class="cat-nav-link">
        <?= h($c['name']) ?>
      </a>
    <?php endforeach; ?>
    <?php if (hasActiveFlash()): ?>
    <a href="<?= SITE_URL ?>/ventes-flash.php" class="cat-nav-link flash-nav-link">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:-.1em;margin-right:3px"><polygon points="13,2 3,14 12,14 11,22 21,10 12,10"/></svg>Ventes Flash
    </a>
    <?php endif; ?>
    <a href="<?= SITE_URL ?>/devenir-vendeur.php" class="cat-nav-link" style="color:var(--text-2)">Vendre</a>
    <a href="<?= SITE_URL ?>/about.php" class="cat-nav-link" style="margin-left:auto;color:var(--accent);font-weight:600">
      A propos
    </a>
  </div>
</nav>

<!--  FLASH MESSAGE  -->
<div class="flash-container">
  <?php renderFlash(); ?>
</div>

<main class="site-main">
