<?php
require_once 'includes/functions.php';
$pageTitle = 'Ventes Flash';
$pageDesc  = 'Offres limitées dans le temps avec jusqu\'à 70% de réduction. Dépêchez-vous, les stocks sont limités !';
$flashProducts = getFlashProducts(40);
?>
<?php require_once 'includes/header.php'; ?>

<style>
.flash-hero {
  background: linear-gradient(135deg, #1a0000 0%, #7f1d1d 50%, #dc2626 100%);
  color: #fff;
  padding: 3.5rem 1.25rem 4rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.flash-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 30% 50%, rgba(255,255,255,.04) 0%, transparent 60%),
              radial-gradient(circle at 70% 50%, rgba(255,200,0,.05) 0%, transparent 60%);
}
.flash-hero-inner { position: relative; max-width: 700px; margin: 0 auto }
.flash-hero h1 { font-size: clamp(2rem,5vw,3.2rem); font-weight: 900; margin-bottom: .75rem }
.flash-hero h1 svg { vertical-align: -.12em; margin-right: .35rem }
.flash-hero p  { color: #fca5a5; font-size: 1rem; margin-bottom: 2rem }

/* Countdown global */
.flash-global-timer {
  display: inline-flex;
  gap: .75rem;
  justify-content: center;
  flex-wrap: wrap;
  background: rgba(0,0,0,.3);
  padding: .85rem 2rem;
  border-radius: 12px;
}
.flash-timer-unit { text-align: center }
.flash-timer-num  { display: block; font-size: 2rem; font-weight: 900; line-height: 1; font-variant-numeric: tabular-nums }
.flash-timer-lbl  { display: block; font-size: .65rem; color: #fca5a5; text-transform: uppercase; letter-spacing: 1px; margin-top: .2rem }
.flash-sep { font-size: 2rem; font-weight: 900; color: rgba(255,255,255,.4); line-height: 1; padding-top: .1rem }

/* Section flash */
.flash-section { padding: 3rem 0 4rem }
.flash-header   { max-width: 1100px; margin: 0 auto 2rem; padding: 0 1.25rem; display: flex; align-items: center; justify-content: space-between }
.flash-count-badge { background: #dc2626; color: #fff; border-radius: 50px; padding: .25rem .85rem; font-size: .78rem; font-weight: 700 }

/* Product cards flash override */
.product-card--flash { border: 2px solid #dc2626 !important }
.badge-flash { background: #dc2626 !important; color: #fff !important; font-size: .7rem; font-weight: 800; letter-spacing: .3px }
.flash-countdown { font-size: .75rem; color: #dc2626; font-weight: 600; margin: .3rem 0; display: flex; align-items: center; gap: .3rem }
.flash-countdown::before { content: ''; display: inline-block; width: 6px; height: 6px; background: #dc2626; border-radius: 50%; animation: blink 1s infinite }
@keyframes blink { 0%,100%{ opacity:1 } 50%{ opacity:.2 } }
</style>

<!-- Hero -->
<div class="flash-hero">
  <div class="flash-hero-inner">
    <div style="display:inline-block;background:rgba(255,255,255,.12);color:#fca5a5;border:1px solid rgba(255,255,255,.2);padding:.35rem 1rem;border-radius:50px;font-size:.74rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:1.25rem">Offres limitées</div>
    <h1>
      <svg width="32" height="32" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="13,2 3,14 12,14 11,22 21,10 12,10"/></svg>
      Ventes Flash
    </h1>
    <p>Des prix imbattables, pour une durée limitée seulement.<br>Profitez-en avant que les stocks s'épuisent !</p>
    <?php if ($flashProducts): ?>
    <div class="flash-global-timer" id="flashGlobalTimer">
      <div class="flash-timer-unit"><span class="flash-timer-num" id="gH">--</span><span class="flash-timer-lbl">heures</span></div>
      <div class="flash-sep">:</div>
      <div class="flash-timer-unit"><span class="flash-timer-num" id="gM">--</span><span class="flash-timer-lbl">min</span></div>
      <div class="flash-sep">:</div>
      <div class="flash-timer-unit"><span class="flash-timer-num" id="gS">--</span><span class="flash-timer-lbl">sec</span></div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Produits flash -->
<div class="flash-section">
  <div class="flash-header">
    <div style="display:flex;align-items:center;gap:.85rem">
      <h2 style="font-size:1.35rem;font-weight:800;margin:0">Offres du moment</h2>
      <span class="flash-count-badge"><?= count($flashProducts) ?> offre<?= count($flashProducts)>1?'s':'' ?></span>
    </div>
    <a href="products.php" style="font-size:.84rem;color:var(--text-2)">Tous les produits</a>
  </div>

  <div style="max-width:1100px;margin:0 auto;padding:0 1.25rem">
    <?php if ($flashProducts): ?>
      <div class="products-grid">
        <?php foreach ($flashProducts as $p): ?>
          <?= productCard($p) ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="text-align:center;padding:4rem 1rem">
        <div style="font-size:3rem;margin-bottom:1rem">
          <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><polygon points="13,2 3,14 12,14 11,22 21,10 12,10"/></svg>
        </div>
        <h3 style="color:var(--text-2);font-size:1.2rem;margin-bottom:.5rem">Aucune vente flash en cours</h3>
        <p style="color:var(--text-3);margin-bottom:1.5rem">Revenez bientôt pour découvrir nos offres exclusives.</p>
        <a href="products.php" class="btn btn-primary">Voir tous les produits</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Informations -->
<div style="background:var(--bg-2);padding:3rem 1.25rem;border-top:1px solid var(--border)">
  <div style="max-width:900px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:2rem;text-align:center">
    <div>
      <div style="width:48px;height:48px;background:#fee2e2;border-radius:12px;margin:0 auto .75rem;display:flex;align-items:center;justify-content:center">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
      </div>
      <h4 style="font-size:.9rem;font-weight:700;margin-bottom:.4rem">Durée limitée</h4>
      <p style="font-size:.82rem;color:var(--text-3)">Chaque offre flash est valable uniquement jusqu'à la date affichée.</p>
    </div>
    <div>
      <div style="width:48px;height:48px;background:#fff3cd;border-radius:12px;margin:0 auto .75rem;display:flex;align-items:center;justify-content:center">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7" stroke-width="3"/></svg>
      </div>
      <h4 style="font-size:.9rem;font-weight:700;margin-bottom:.4rem">Prix garantis</h4>
      <p style="font-size:.82rem;color:var(--text-3)">Le prix flash est appliqué automatiquement dans votre panier.</p>
    </div>
    <div>
      <div style="width:48px;height:48px;background:#d1fae5;border-radius:12px;margin:0 auto .75rem;display:flex;align-items:center;justify-content:center">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><polyline points="20,6 9,17 4,12"/></svg>
      </div>
      <h4 style="font-size:.9rem;font-weight:700;margin-bottom:.4rem">Stocks limités</h4>
      <p style="font-size:.82rem;color:var(--text-3)">Ajoutez vite au panier — les quantités sont limitées.</p>
    </div>
  </div>
</div>

<script>
// Countdown vers la première vente flash à expirer
const flashProducts = <?= json_encode(array_map(fn($p) => $p['flash_sale_end'], $flashProducts)) ?>;
if (flashProducts.length > 0) {
  // Trouver la fin la plus proche
  const ends = flashProducts.map(e => new Date(e).getTime()).filter(t => t > Date.now());
  if (ends.length) {
    const nearestEnd = Math.min(...ends);
    const gH = document.getElementById('gH');
    const gM = document.getElementById('gM');
    const gS = document.getElementById('gS');
    function updateGlobal() {
      const diff = Math.max(0, nearestEnd - Date.now());
      const h = Math.floor(diff/3600000);
      const m = Math.floor((diff%3600000)/60000);
      const s = Math.floor((diff%60000)/1000);
      if(gH) gH.textContent = String(h).padStart(2,'0');
      if(gM) gM.textContent = String(m).padStart(2,'0');
      if(gS) gS.textContent = String(s).padStart(2,'0');
    }
    updateGlobal();
    setInterval(updateGlobal, 1000);
  }
}

// Countdown par produit
document.querySelectorAll('.flash-countdown[data-end]').forEach(el => {
  const end = new Date(el.dataset.end).getTime();
  const span = el.querySelector('.flash-timer');
  if (!span) return;
  function tick() {
    const diff = Math.max(0, end - Date.now());
    if (diff === 0) { span.textContent = 'Terminée'; return; }
    const h = Math.floor(diff/3600000);
    const m = Math.floor((diff%3600000)/60000);
    const s = Math.floor((diff%60000)/1000);
    span.textContent = h>0 ? `${h}h ${String(m).padStart(2,'0')}min` : `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  }
  tick();
  setInterval(tick, 1000);
});
</script>

<?php require_once 'includes/footer.php'; ?>
