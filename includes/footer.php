<?php $cats = $cats ?? getCategories(); ?>
</main><!-- /site-main -->

<footer class="site-footer">
  <div class="footer-top">

    <div class="footer-col">
      <div class="footer-logo"><?= h(setting('site_name', SITE_NAME)) ?></div>
      <p class="footer-tagline"><?= h(setting('site_tagline','Votre destination shopping mondiale')) ?></p>
      <div class="footer-social">
        <a href="#" class="social-btn">Facebook</a>
        <a href="#" class="social-btn">Instagram</a>
      </div>
    </div>

    <div class="footer-col">
      <h4 class="footer-title">Categories</h4>
      <ul class="footer-links">
        <?php foreach ($cats as $c): ?>
          <li><a href="<?= SITE_URL ?>/category.php?slug=<?= h($c['slug']) ?>"><?= h($c['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="footer-col">
      <h4 class="footer-title">Mon Compte</h4>
      <ul class="footer-links">
        <li><a href="<?= SITE_URL ?>/login.php">Connexion</a></li>
        <li><a href="<?= SITE_URL ?>/register.php">Inscription</a></li>
        <li><a href="<?= SITE_URL ?>/profile.php">Mon Profil</a></li>
        <li><a href="<?= SITE_URL ?>/orders.php">Mes Commandes</a></li>
        <li><a href="<?= SITE_URL ?>/cart.php">Mon Panier</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4 class="footer-title">La Plateforme</h4>
      <ul class="footer-links">
        <li><a href="<?= SITE_URL ?>/about.php">Qui sommes-nous</a></li>
        <li><a href="<?= SITE_URL ?>/about.php#acheteur">Guide acheteur</a></li>
        <li><a href="<?= SITE_URL ?>/devenir-vendeur.php">Devenir vendeur</a></li>
        <li><a href="<?= SITE_URL ?>/ventes-flash.php">Ventes Flash</a></li>
        <li><a href="<?= SITE_URL ?>/about.php#logistique">Notre logistique</a></li>
        <li><a href="<?= SITE_URL ?>/profile.php?tab=fidelite">Programme fidélité</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4 class="footer-title">Contact</h4>
      <ul class="footer-contact">
        <?php if (setting('site_address','')): ?>
          <li><?= h(setting('site_address','')) ?></li>
        <?php endif; ?>
        <?php if (setting('site_email','')): ?>
          <li><a href="mailto:<?= h(setting('site_email','')) ?>" style="color:inherit"><?= h(setting('site_email','')) ?></a></li>
        <?php endif; ?>
        <?php if (setting('site_phone','')): ?>
          <li><?= h(setting('site_phone','')) ?></li>
        <?php endif; ?>
      </ul>
    </div>

  </div>

  <div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> <?= h(setting('site_name', SITE_NAME)) ?> — Tous droits reserves</p>
    <p>
      <a href="<?= SITE_URL ?>/about.php" style="color:inherit;text-decoration:underline;margin-right:1rem">A propos</a>
      Paiement securise — Livraison fiable — Support disponible
    </p>
  </div>
</footer>

<div class="toast" id="cartToast"></div>
<button class="back-to-top" id="backToTop" aria-label="Haut de page">&#8593;</button>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
