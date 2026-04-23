<?php
$adminPage = 'dashboard';
$pageTitle = 'Tableau de bord';
require_once 'includes/auth.php';

// Stats globales
$totalProducts = db()->query("SELECT COUNT(*) FROM products WHERE active=1")->fetchColumn();
$totalOrders   = db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers    = db()->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$totalRevenue  = db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status!='cancelled'")->fetchColumn();
$revenueDelivered = db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='delivered'")->fetchColumn();
$revenueProcessing= db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='processing'")->fetchColumn();
$pendingOrders = db()->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$todayOrders   = db()->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$lowStock      = db()->query("SELECT COUNT(*) FROM products WHERE stock<=5 AND active=1")->fetchColumn();
$stockValue    = db()->query("SELECT COALESCE(SUM(price*stock),0) FROM products WHERE active=1")->fetchColumn();
$totalSellers  = db()->query("SELECT COUNT(*) FROM users WHERE role='seller'")->fetchColumn();
$pendingSellers= (int)db()->query("SELECT COUNT(*) FROM seller_applications WHERE status='pending'")->fetchColumn();

// Commandes récentes
$recentOrders = db()->query("SELECT o.*,CONCAT(o.firstname,' ',o.lastname) full_name FROM orders o ORDER BY created_at DESC LIMIT 10")->fetchAll();

// Top produits
$topProducts = db()->query("SELECT p.name,p.price,p.stock,p.views,c.name cat FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.active=1 ORDER BY p.views DESC LIMIT 5")->fetchAll();

// Revenus 6 derniers mois
$monthly = db()->query("SELECT DATE_FORMAT(created_at,'%b %Y') mon, SUM(total) rev FROM orders WHERE status!='cancelled' AND created_at>=DATE_SUB(NOW(),INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY created_at")->fetchAll();

// Statistiques par statut
$orderStats = db()->query("SELECT status, COUNT(*) cnt, COALESCE(SUM(total),0) total FROM orders GROUP BY status ORDER BY FIELD(status,'pending','processing','shipped','delivered','cancelled')")->fetchAll();

// Revenus par catégorie
$catRevenue = db()->query("SELECT c.name, COUNT(DISTINCT o.id) nb_orders, COALESCE(SUM(oi.subtotal),0) rev
    FROM categories c
    JOIN products p ON p.category_id=c.id
    JOIN order_items oi ON oi.product_id=p.id
    JOIN orders o ON o.id=oi.order_id AND o.status!='cancelled'
    GROUP BY c.id,c.name ORDER BY rev DESC LIMIT 6")->fetchAll();

$statusLabels = ['pending'=>'En attente','processing'=>'En cours','shipped'=>'Expédiée','delivered'=>'Livrée','cancelled'=>'Annulée'];
$statusColors = ['pending'=>'var(--a-warning)','processing'=>'var(--a-blue)','shipped'=>'#8b5cf6','delivered'=>'var(--a-success)','cancelled'=>'var(--a-danger)'];
?>
<?php require_once 'includes/admin_header.php'; ?>

<!-- ═══ RECTANGLE CHIFFRE D'AFFAIRES ═══ -->
<div style="background:linear-gradient(135deg,#1B2A41 0%,#FF6B2B 100%);border-radius:14px;padding:2rem 2.5rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;box-shadow:0 8px 32px rgba(255,107,43,.25)">
  <div>
    <div style="font-size:.78rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:1px;margin-bottom:.4rem">Chiffre d'affaires total</div>
    <div style="font-size:2.8rem;font-weight:900;color:#fff;line-height:1"><?= money((float)$totalRevenue) ?></div>
    <div style="font-size:.82rem;color:rgba(255,255,255,.75);margin-top:.5rem">Toutes commandes non annulées</div>
  </div>
  <div style="display:flex;gap:2rem;flex-wrap:wrap">
    <div style="text-align:center">
      <div style="font-size:1.4rem;font-weight:800;color:#fff"><?= money((float)$revenueDelivered) ?></div>
      <div style="font-size:.72rem;color:rgba(255,255,255,.65)">Livrées</div>
    </div>
    <div style="text-align:center">
      <div style="font-size:1.4rem;font-weight:800;color:#fff"><?= money((float)$revenueProcessing) ?></div>
      <div style="font-size:.72rem;color:rgba(255,255,255,.65)">En cours</div>
    </div>
    <div style="text-align:center">
      <div style="font-size:1.4rem;font-weight:800;color:#fff"><?= money((float)$stockValue) ?></div>
      <div style="font-size:.72rem;color:rgba(255,255,255,.65)">Valeur du stock</div>
    </div>
  </div>
</div>

<!-- ═══ STAT CARDS ═══ -->
<div class="grid-4 mb-3">
  <div class="stat-card">
    <div class="stat-icon" style="background:#fff0e8;font-size:1.4rem;color:#FF6B2B">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
    </div>
    <div>
      <div class="stat-label">Chiffre d'affaires</div>
      <div class="stat-value"><?= money((float)$totalRevenue) ?></div>
      <div class="stat-sub">Commandes non annulées</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#dbeafe;color:#1e40af">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
    </div>
    <div>
      <div class="stat-label">Commandes</div>
      <div class="stat-value"><?= $totalOrders ?></div>
      <div class="stat-sub"><span style="color:var(--a-warning);font-weight:600"><?= $pendingOrders ?></span> en attente</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#d1fae5;color:#065f46">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
    </div>
    <div>
      <div class="stat-label">Produits</div>
      <div class="stat-value"><?= $totalProducts ?></div>
      <div class="stat-sub"><span style="color:var(--a-danger);font-weight:600"><?= $lowStock ?></span> stock faible</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#ede9fe;color:#5b21b6">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div>
      <div class="stat-label">Clients</div>
      <div class="stat-value"><?= $totalUsers ?></div>
      <div class="stat-sub"><?= $todayOrders ?> commande(s) aujourd'hui</div>
    </div>
  </div>
</div>

<!-- ═══ ACTIONS RAPIDES ═══ -->
<div class="a-card mb-3">
  <div class="a-card-header">Actions rapides</div>
  <div class="a-card-body" style="display:flex;gap:.75rem;flex-wrap:wrap">
    <a href="products.php?action=add" class="btn btn-primary">+ Nouveau produit</a>
    <a href="categories.php?action=add" class="btn btn-secondary">+ Nouvelle catégorie</a>
    <a href="orders.php?status=pending" class="btn btn-outline">Commandes en attente (<?= $pendingOrders ?>)</a>
    <?php if ($lowStock > 0): ?>
      <a href="products.php?low_stock=1" class="btn btn-danger">Stock faible (<?= $lowStock ?>)</a>
    <?php endif; ?>
    <?php if ($pendingSellers > 0): ?>
      <a href="sellers.php" class="btn btn-outline" style="border-color:var(--a-warning);color:var(--a-warning)">Vendeurs en attente (<?= $pendingSellers ?>)</a>
    <?php endif; ?>
    <a href="sellers.php" class="btn btn-secondary">Vendeurs (<?= $totalSellers ?>)</a>
  </div>
</div>

<!-- ═══ TABLEAU STATISTIQUES ═══ -->
<div class="grid-2 mb-3" style="align-items:start">

  <!-- Statuts des commandes -->
  <div class="a-card">
    <div class="a-card-header">Statistiques des commandes</div>
    <div class="table-wrap">
      <table class="a-table">
        <thead><tr><th>Statut</th><th>Nombre</th><th>Montant total</th><th>Part</th></tr></thead>
        <tbody>
          <?php
          $grandTotal = array_sum(array_column($orderStats,'total')) ?: 1;
          foreach ($orderStats as $s):
            $pct = round($s['total']/$grandTotal*100);
          ?>
            <tr>
              <td><span class="status status-<?= $s['status'] ?>"><?= $statusLabels[$s['status']] ?></span></td>
              <td style="font-weight:700"><?= $s['cnt'] ?></td>
              <td style="font-weight:600"><?= money((float)$s['total']) ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:.5rem">
                  <div style="flex:1;background:#f1f5f9;border-radius:4px;height:6px;overflow:hidden">
                    <div style="width:<?= $pct ?>%;height:100%;background:<?= $statusColors[$s['status']] ?? '#94a3b8' ?>;border-radius:4px"></div>
                  </div>
                  <span style="font-size:.75rem;color:var(--a-text3);min-width:32px"><?= $pct ?>%</span>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Revenus par catégorie -->
  <div class="a-card">
    <div class="a-card-header">Revenus par catégorie</div>
    <div class="table-wrap">
      <table class="a-table">
        <thead><tr><th>Catégorie</th><th>Commandes</th><th>Revenus</th></tr></thead>
        <tbody>
          <?php if ($catRevenue): ?>
            <?php foreach ($catRevenue as $c): ?>
              <tr>
                <td style="font-weight:600"><?= h($c['name']) ?></td>
                <td><?= $c['nb_orders'] ?></td>
                <td style="font-weight:700;color:var(--a-success)"><?= money((float)$c['rev']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="3" style="text-align:center;color:var(--a-text3);padding:1.5rem">Aucune donnée</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ═══ COMMANDES RÉCENTES + TOP PRODUITS ═══ -->
<div class="grid-2 mb-3" style="align-items:start">

  <div class="a-card">
    <div class="a-card-header">
      <span>Commandes récentes</span>
      <a href="orders.php" class="btn btn-outline btn-sm">Voir tout</a>
    </div>
    <div class="table-wrap">
      <table class="a-table">
        <thead><tr><th>N°</th><th>Client</th><th>Total</th><th>Statut</th></tr></thead>
        <tbody>
          <?php foreach ($recentOrders as $o): ?>
            <tr>
              <td><a href="orders.php?id=<?= $o['id'] ?>" style="font-weight:600;color:var(--a-blue)">#<?= h($o['order_number']) ?></a></td>
              <td><?= h($o['full_name']) ?></td>
              <td style="font-weight:600"><?= money((float)$o['total']) ?></td>
              <td><span class="status status-<?= $o['status'] ?>"><?= $statusLabels[$o['status']] ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$recentOrders): ?>
            <tr><td colspan="4" style="text-align:center;padding:1.5rem;color:var(--a-text3)">Aucune commande</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="a-card">
    <div class="a-card-header">
      <span>Produits populaires</span>
      <a href="products.php" class="btn btn-outline btn-sm">Gérer</a>
    </div>
    <div class="table-wrap">
      <table class="a-table">
        <thead><tr><th>Produit</th><th>Prix</th><th>Stock</th><th>Vues</th></tr></thead>
        <tbody>
          <?php foreach ($topProducts as $p): ?>
            <tr>
              <td>
                <div style="font-weight:600;font-size:.85rem"><?= h($p['name']) ?></div>
                <div class="text-muted"><?= h($p['cat']??'') ?></div>
              </td>
              <td><?= money((float)$p['price']) ?></td>
              <td><span style="font-weight:600;color:<?= $p['stock']<=5?'var(--a-danger)':'var(--a-success)' ?>"><?= $p['stock'] ?></span></td>
              <td><?= number_format($p['views']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ═══ GRAPHIQUE REVENUS ═══ -->
<?php if ($monthly): ?>
<div class="a-card">
  <div class="a-card-header">Revenus – 6 derniers mois</div>
  <div class="a-card-body">
    <div style="display:flex;align-items:flex-end;gap:.75rem;height:160px;padding-top:1rem">
      <?php
      $maxRev = max(array_column($monthly,'rev')) ?: 1;
      foreach ($monthly as $m):
        $pct = max(5,round($m['rev']/$maxRev*100));
      ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.4rem">
          <span style="font-size:.68rem;color:var(--a-text3);font-weight:600;text-align:center"><?= money((float)$m['rev']) ?></span>
          <div style="width:100%;height:<?= $pct ?>%;background:linear-gradient(to top,var(--a-accent),#ff9e6b);border-radius:4px 4px 0 0;min-height:8px"></div>
          <span style="font-size:.7rem;color:var(--a-text3)"><?= h($m['mon']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
