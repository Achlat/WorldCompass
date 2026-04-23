<?php
require_once '../includes/functions.php';
requireLogin();
requireSeller();
$pageTitle = 'Mes commandes';
$u = currentUser();
$orders = getSellerOrders((int)$u['id'], 100);
$statusLabels = ['pending'=>'En attente','processing'=>'En cours','shipped'=>'Expédiée','delivered'=>'Livrée','cancelled'=>'Annulée'];
?>
<?php require_once '../includes/header.php'; ?>

<style>
.seller-layout{display:grid;grid-template-columns:220px 1fr;gap:0;min-height:70vh}
@media(max-width:768px){.seller-layout{grid-template-columns:1fr}}
.seller-sidebar{background:#1B2A41;color:#e2e8f0;padding:1.75rem 0;min-height:70vh}
.seller-sidebar-brand{padding:.5rem 1.5rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:1rem}
.seller-sidebar-brand h3{font-size:.9rem;font-weight:800;color:#fff;margin:0}
.seller-sidebar-brand p{font-size:.72rem;color:#64748b;margin:.2rem 0 0}
.seller-nav a{display:flex;align-items:center;gap:.7rem;padding:.72rem 1.5rem;color:#94a3b8;font-size:.85rem;font-weight:500;transition:all .15s}
.seller-nav a:hover,.seller-nav a.active{background:rgba(255,255,255,.07);color:#fff}
.seller-nav a.active{border-right:3px solid var(--accent)}
.seller-main{padding:2rem 2rem 3rem;background:var(--bg-2)}
.scard{background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.06);overflow:hidden;margin-bottom:1.5rem}
.status-pill{display:inline-block;padding:.2rem .65rem;border-radius:50px;font-size:.72rem;font-weight:600}
.s-pending{background:#fef3c7;color:#92400e}.s-processing{background:#dbeafe;color:#1e40af}
.s-shipped{background:#ede9fe;color:#5b21b6}.s-delivered{background:#d1fae5;color:#065f46}
.s-cancelled{background:#fee2e2;color:#991b1b}
</style>

<div class="seller-layout">
  <aside class="seller-sidebar">
    <div class="seller-sidebar-brand">
      <h3><?= h($u['business_name'] ?: $u['firstname'].' '.$u['lastname']) ?></h3>
      <p>Vendeur <?= $u['seller_type'] === 'managed' ? 'plateforme' : ($u['seller_type'] === 'autonomous' ? 'autonome' : 'partenaire') ?></p>
    </div>
    <nav class="seller-nav">
      <a href="index.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Tableau de bord
      </a>
      <a href="products.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8l5 5-5 5"/></svg>
        Mes produits
      </a>
      <a href="orders.php" class="active">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
        Commandes
      </a>
      <a href="commissions.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Commissions
      </a>
    </nav>
  </aside>

  <main class="seller-main">
    <h1 style="font-size:1.3rem;font-weight:800;margin:0 0 1.5rem">Mes commandes (<?= count($orders) ?>)</h1>

    <?php if ($orders): ?>
    <div class="scard">
      <div style="padding:0;overflow-x:auto">
        <table class="a-table" style="margin:0;min-width:700px">
          <thead><tr>
            <th>Commande</th>
            <th>Date</th>
            <th>Client</th>
            <th>Produit</th>
            <th>Qté</th>
            <th>Montant</th>
            <th>Statut</th>
          </tr></thead>
          <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td style="font-weight:600;font-size:.83rem"><?= h($o['order_number']) ?></td>
              <td style="font-size:.8rem;color:var(--text-2)"><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
              <td style="font-size:.83rem"><?= h($o['full_name']) ?></td>
              <td style="font-size:.82rem;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($o['product_name']) ?></td>
              <td style="font-size:.83rem;text-align:center"><?= $o['quantity'] ?></td>
              <td style="font-weight:700;font-size:.84rem"><?= money((float)$o['item_subtotal']) ?></td>
              <td><span class="status-pill s-<?= h($o['status']) ?>"><?= $statusLabels[$o['status']]??$o['status'] ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php else: ?>
      <div class="scard"><div style="padding:3rem;text-align:center;color:var(--text-3)">Aucune commande pour l'instant.</div></div>
    <?php endif; ?>
  </main>
</div>

<?php require_once '../includes/footer.php'; ?>
