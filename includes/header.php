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
<link rel="icon" type="image/jpeg" href="<?= SITE_URL ?>/assets/images/logo.jpg">
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
      <img src="<?= SITE_URL ?>/assets/images/logo.jpg" alt="<?= h($siteName) ?>" class="logo-img">
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

    <!-- Mobile : panier rapide + hamburger -->
    <a href="<?= SITE_URL ?>/cart.php" class="mob-cart-quick" aria-label="Panier">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      <?php if ($cartCnt > 0): ?><span class="cart-badge"><?= $cartCnt ?></span><?php endif; ?>
    </a>
    <button class="mob-menu-btn" id="mobMenuBtn" aria-label="Menu">
      <svg id="mobIcoMenu" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      <svg id="mobIcoClose" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:none"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      <span id="mobMenuLabel">Menu</span>
    </button>
  </div>
</header>

<!-- ── MENU MOBILE ── -->
<div id="mobileNav" class="mobile-nav">
  <form action="<?= SITE_URL ?>/search.php" method="GET" class="mobile-search-form">
    <input type="text" name="q" value="<?= $search ?>" placeholder="Rechercher un produit…" class="mobile-search-input" autocomplete="off">
    <button type="submit" class="mobile-search-btn">🔍</button>
  </form>
  <?php if (isLoggedIn()): ?>
    <a href="<?= SITE_URL ?>/profile.php" class="mobile-nav-link">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Mon Compte
    </a>
    <a href="<?= SITE_URL ?>/orders.php" class="mobile-nav-link">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
      Mes Commandes
    </a>
    <?php if (isSeller() && !isAdmin()): ?>
      <a href="<?= SITE_URL ?>/seller/index.php" class="mobile-nav-link mob-seller">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
        Ma Boutique
      </a>
    <?php endif; ?>
    <?php if (isAdmin()): ?>
      <a href="<?= SITE_URL ?>/admin/index.php" class="mobile-nav-link mob-admin">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14"/></svg>
        Administration
      </a>
    <?php endif; ?>
    <a href="<?= SITE_URL ?>/logout.php" class="mobile-nav-link">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Déconnexion
    </a>
  <?php else: ?>
    <a href="<?= SITE_URL ?>/login.php" class="mobile-nav-link">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10,17 15,12 10,7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      Connexion
    </a>
    <a href="<?= SITE_URL ?>/register.php" class="mobile-nav-link">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
      S'inscrire
    </a>
  <?php endif; ?>
  <div class="mobile-nav-divider"></div>
  <a href="<?= SITE_URL ?>/cart.php" class="mobile-nav-link mob-accent">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
    Panier <?php if ($cartCnt > 0): ?>(<?= $cartCnt ?>)<?php endif; ?>
  </a>
  <a href="<?= SITE_URL ?>/devenir-vendeur.php" class="mobile-nav-link">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
    Vendre sur World Compass
  </a>
  <a href="<?= SITE_URL ?>/about.php" class="mobile-nav-link">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    À propos
  </a>
</div>

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
