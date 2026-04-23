<?php
require_once '../includes/functions.php';
requireLogin();
requireSeller();
$pageTitle = 'Mes commissions';
$u = currentUser();

$s = db()->prepare("SELECT c.*,o.order_number,o.created_at order_date FROM commissions c JOIN orders o ON o.id=c.order_id WHERE c.seller_id=? ORDER BY c.created_at DESC LIMIT 100");
$s->execute([$u['id']]);
$comms = $s->fetchAll();

$totalPending = 0; $totalPaid = 0;
foreach ($comms as $cm) {
    if ($cm['status']==='pending') $totalPending += $cm['commission_amount'];
    else $totalPaid += $cm['commission_amount'];
}
$rate = $u['seller_type'] === 'autonomous' ? 6 : 12;
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
.comm-stat{background:#fff;border-radius:12px;padding:1.25rem 1.5rem;box-shadow:0 1px 4px rgba(0,0,0,.06)}
</style>

<div class="seller-layout">
  <aside class="seller-sidebar">
    <div class="seller-sidebar-brand">
      <h3><?= h($u['business_name'] ?: $u['firstname'].' '.$u['lastname']) ?></h3>
      <p>Vendeur <?= $u['seller_type'] === 'managed' ? 'plateforme' : ($u['seller_type'] === 'autonomous' ? 'autonome' : 'partenaire') ?></p>
    </div>
    <nav class="seller-nav">
      <a href="index.php"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>Tableau de bord</a>
      <a href="products.php"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8l5 5-5 5"/></svg>Mes produits</a>
      <a href="orders.php"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>Commandes</a>
      <a href="commissions.php" class="active"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Commissions</a>
    </nav>
  </aside>

  <main class="seller-main">
    <h1 style="font-size:1.3rem;font-weight:800;margin:0 0 1.25rem">Mes commissions</h1>

    <!-- Explications -->
    <div style="background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border:1px solid #bae6fd;border-radius:12px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;display:flex;gap:1rem;align-items:center">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16" stroke-width="3"/></svg>
      <div>
        <strong style="font-size:.9rem">Modèle <?= $u['seller_type'] === 'managed' ? 'plateforme gérée' : ($u['seller_type'] === 'autonomous' ? 'vendeur autonome' : 'partenaire') ?></strong>
        — taux de commission : <strong><?= $rate ?>%</strong> sur chaque vente.
        World Compass collecte <?= $rate ?>% du montant vendu pour couvrir les frais de plateforme<?= $u['seller_type']==='managed'?' et de logistique':'' ?>.
        Le reste vous est reversé.
      </div>
    </div>

    <!-- Statistiques -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">
      <div class="comm-stat">
        <div style="font-size:1.4rem;font-weight:900;color:#d97706"><?= money($totalPending) ?></div>
        <div style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;margin-top:.2rem">En attente de versement</div>
      </div>
      <div class="comm-stat">
        <div style="font-size:1.4rem;font-weight:900;color:var(--success)"><?= money($totalPaid) ?></div>
        <div style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;margin-top:.2rem">Déjà versé</div>
      </div>
      <div class="comm-stat">
        <div style="font-size:1.4rem;font-weight:900;color:var(--primary)"><?= count($comms) ?></div>
        <div style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;margin-top:.2rem">Total transactions</div>
      </div>
    </div>

    <?php if ($comms): ?>
    <div class="scard">
      <div style="overflow-x:auto">
        <table class="a-table" style="margin:0">
          <thead><tr>
            <th>Commande</th><th>Date</th><th>Produit</th><th>Vente HT</th><th>Taux</th><th>Commission</th><th>Statut</th>
          </tr></thead>
          <tbody>
          <?php foreach ($comms as $c): ?>
            <tr>
              <td style="font-size:.82rem;font-weight:600"><?= h($c['order_number']) ?></td>
              <td style="font-size:.8rem;color:var(--text-2)"><?= date('d/m/Y',strtotime($c['order_date'])) ?></td>
              <td style="font-size:.82rem;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($c['product_name']) ?></td>
              <td style="font-size:.83rem"><?= money((float)$c['sale_amount']) ?></td>
              <td style="font-size:.82rem;color:var(--text-2)"><?= $c['commission_rate'] ?>%</td>
              <td style="font-weight:700;color:#dc2626;font-size:.84rem">-<?= money((float)$c['commission_amount']) ?></td>
              <td>
                <?php if ($c['status']==='paid'): ?>
                  <span style="display:inline-block;background:#d1fae5;color:#065f46;border-radius:50px;padding:.15rem .6rem;font-size:.72rem;font-weight:700">Versé</span>
                <?php else: ?>
                  <span style="display:inline-block;background:#fef3c7;color:#92400e;border-radius:50px;padding:.15rem .6rem;font-size:.72rem;font-weight:700">En attente</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php else: ?>
      <div class="scard"><div style="padding:3rem;text-align:center;color:var(--text-3)">Aucune commission enregistrée.</div></div>
    <?php endif; ?>
  </main>
</div>

<?php require_once '../includes/footer.php'; ?>
