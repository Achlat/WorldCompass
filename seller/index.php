<?php
require_once '../includes/functions.php';
requireLogin();
requireSeller();
$pageTitle = 'Mon espace vendeur';
$u = currentUser();
$stats = getSellerStats((int)$u['id']);
$recentOrders = getSellerOrders((int)$u['id'], 8);
$sellerProducts = getSellerProducts((int)$u['id']);
?>
<?php require_once '../includes/header.php'; ?>

<style>
.seller-layout { display:grid; grid-template-columns:220px 1fr; gap:0; min-height:70vh }
@media(max-width:768px){ .seller-layout{ grid-template-columns:1fr } }
.seller-sidebar {
  background:#1B2A41;
  color:#e2e8f0;
  padding:1.75rem 0;
  min-height:70vh;
}
.seller-sidebar-brand {
  padding:.5rem 1.5rem 1.5rem;
  border-bottom:1px solid rgba(255,255,255,.08);
  margin-bottom:1rem;
}
.seller-sidebar-brand h3 { font-size:.9rem; font-weight:800; color:#fff; margin:0 }
.seller-sidebar-brand p  { font-size:.72rem; color:#64748b; margin:.2rem 0 0 }
.seller-nav a {
  display:flex; align-items:center; gap:.7rem;
  padding:.72rem 1.5rem; color:#94a3b8;
  font-size:.85rem; font-weight:500;
  transition:all .15s;
}
.seller-nav a:hover, .seller-nav a.active { background:rgba(255,255,255,.07); color:#fff }
.seller-nav a.active { border-right:3px solid var(--accent) }
.seller-nav svg { flex-shrink:0 }
.seller-main { padding:2rem 2rem 3rem; background:var(--bg-2) }
.seller-stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:2rem }
@media(max-width:900px){ .seller-stat-grid{ grid-template-columns:repeat(2,1fr) } }
.seller-stat { background:#fff; border-radius:12px; padding:1.25rem 1.5rem; box-shadow:0 1px 4px rgba(0,0,0,.06) }
.seller-stat-val { font-size:1.5rem; font-weight:900; color:var(--primary); margin-bottom:.2rem }
.seller-stat-lbl { font-size:.75rem; color:var(--text-3); text-transform:uppercase; letter-spacing:.4px }
.scard { background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.06); overflow:hidden; margin-bottom:1.5rem }
.scard-hdr { padding:1rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between }
.scard-hdr h3 { font-size:.95rem; font-weight:700; margin:0 }
.scard-body { padding:1.25rem }
.status-pill { display:inline-block; padding:.2rem .65rem; border-radius:50px; font-size:.72rem; font-weight:600 }
.s-pending    { background:#fef3c7; color:#92400e }
.s-processing { background:#dbeafe; color:#1e40af }
.s-shipped    { background:#ede9fe; color:#5b21b6 }
.s-delivered  { background:#d1fae5; color:#065f46 }
.s-cancelled  { background:#fee2e2; color:#991b1b }
</style>

<div class="seller-layout">

  <!-- Sidebar -->
  <aside class="seller-sidebar">
    <div class="seller-sidebar-brand">
      <h3><?= h($u['business_name'] ?: $u['firstname'].' '.$u['lastname']) ?></h3>
      <p>Vendeur <?= $u['seller_type'] === 'managed' ? 'plateforme' : ($u['seller_type'] === 'autonomous' ? 'autonome' : 'partenaire') ?></p>
    </div>
    <nav class="seller-nav">
      <a href="index.php" class="active">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Tableau de bord
      </a>
      <a href="products.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8l5 5-5 5"/></svg>
        Mes produits
      </a>
      <a href="orders.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
        Commandes
      </a>
      <a href="commissions.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Commissions
      </a>
      <a href="../profile.php" style="margin-top:auto;border-top:1px solid rgba(255,255,255,.06);padding-top:1rem">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Mon profil
      </a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="seller-main">
    <div style="margin-bottom:1.75rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
      <div>
        <h1 style="font-size:1.4rem;font-weight:800;margin:0">Bonjour, <?= h($u['firstname']) ?></h1>
        <p style="color:var(--text-2);font-size:.87rem;margin:.2rem 0 0">Voici le résumé de votre activité</p>
      </div>
      <a href="products.php?action=add" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:-.1em;margin-right:5px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Ajouter un produit
      </a>
    </div>

    <!-- Stats -->
    <div class="seller-stat-grid">
      <div class="seller-stat">
        <div class="seller-stat-val"><?= money($stats['total_sales']) ?></div>
        <div class="seller-stat-lbl">Ventes totales</div>
      </div>
      <div class="seller-stat">
        <div class="seller-stat-val"><?= $stats['total_orders'] ?></div>
        <div class="seller-stat-lbl">Commandes</div>
      </div>
      <div class="seller-stat">
        <div class="seller-stat-val"><?= $stats['total_products'] ?></div>
        <div class="seller-stat-lbl">Produits actifs</div>
      </div>
      <div class="seller-stat">
        <div class="seller-stat-val"><?= money($stats['pending_comm']) ?></div>
        <div class="seller-stat-lbl">Commissions en attente</div>
      </div>
    </div>

    <!-- Commandes récentes -->
    <div class="scard">
      <div class="scard-hdr">
        <h3>Commandes récentes</h3>
        <a href="orders.php" style="font-size:.82rem;color:var(--accent)">Voir tout</a>
      </div>
      <div class="scard-body" style="padding:0">
        <?php if ($recentOrders): ?>
        <table class="a-table" style="margin:0">
          <thead><tr>
            <th>Commande</th>
            <th>Client</th>
            <th>Produit</th>
            <th>Montant</th>
            <th>Statut</th>
          </tr></thead>
          <tbody>
          <?php foreach ($recentOrders as $o): ?>
            <tr>
              <td style="font-weight:600;font-size:.82rem"><?= h($o['order_number']) ?></td>
              <td style="font-size:.83rem"><?= h($o['full_name']) ?></td>
              <td style="font-size:.82rem;max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($o['product_name']) ?> ×<?= $o['quantity'] ?></td>
              <td style="font-size:.83rem;font-weight:600"><?= money((float)$o['item_subtotal']) ?></td>
              <td><span class="status-pill s-<?= h($o['status']) ?>"><?= ['pending'=>'En attente','processing'=>'En cours','shipped'=>'Expédiée','delivered'=>'Livrée','cancelled'=>'Annulée'][$o['status']]??$o['status'] ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <div style="padding:2rem;text-align:center;color:var(--text-3)">Aucune commande pour le moment.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Mes produits -->
    <div class="scard">
      <div class="scard-hdr">
        <h3>Mes produits</h3>
        <a href="products.php" style="font-size:.82rem;color:var(--accent)">Gérer</a>
      </div>
      <div class="scard-body" style="padding:0">
        <?php if ($sellerProducts): ?>
        <table class="a-table" style="margin:0">
          <thead><tr><th>Produit</th><th>Prix</th><th>Stock</th><th>Vues</th></tr></thead>
          <tbody>
          <?php foreach (array_slice($sellerProducts,0,6) as $sp): ?>
            <tr>
              <td style="font-size:.84rem;font-weight:500"><?= h($sp['name']) ?></td>
              <td style="font-size:.83rem"><?= money((float)$sp['price']) ?></td>
              <td>
                <span style="color:<?= $sp['stock']<5?'var(--danger)':'var(--text)' ?>">
                  <?= $sp['stock'] ?>
                  <?php if ($sp['stock']<5): ?><span style="font-size:.7rem;margin-left:4px;color:var(--danger)">(faible)</span><?php endif; ?>
                </span>
              </td>
              <td style="font-size:.83rem;color:var(--text-3)"><?= $sp['views'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <div style="padding:2rem;text-align:center;color:var(--text-3)">
            Aucun produit. <a href="products.php?action=add">Ajoutez votre premier produit</a>.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<?php require_once '../includes/footer.php'; ?>
