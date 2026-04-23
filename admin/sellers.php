<?php
$adminPage = 'sellers';
$pageTitle = 'Vendeurs partenaires';
require_once 'includes/auth.php';

// Approve / Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verifyCsrf();
    $appId = (int)($_POST['app_id']??0);
    $action = $_POST['action'];
    $note  = trim($_POST['admin_note']??'');

    if ($action === 'approve') {
        $app = db()->prepare("SELECT * FROM seller_applications WHERE id=?");
        $app->execute([$appId]);
        $a = $app->fetch();
        if ($a) {
            db()->prepare("UPDATE seller_applications SET status='approved', admin_note=? WHERE id=?")->execute([$note, $appId]);
            db()->prepare("UPDATE users SET role='seller', business_name=?, seller_type=?, account_type=? WHERE id=?")->execute([$a['business_name'], $a['seller_type'], $a['account_type']??'individual', $a['user_id']]);
            flash('success','Vendeur approuvé. Son compte est maintenant actif.');
        }
    } elseif ($action === 'reject') {
        db()->prepare("UPDATE seller_applications SET status='rejected', admin_note=? WHERE id=?")->execute([$note, $appId]);
        flash('info','Demande rejetée.');
    }
    header('Location: sellers.php'); exit;
}

// All applications
$applications = db()->query("SELECT sa.*, u.firstname, u.lastname, u.email, u.phone
    FROM seller_applications sa JOIN users u ON u.id=sa.user_id
    ORDER BY sa.created_at DESC")->fetchAll();

// All sellers
$sellers = db()->query("SELECT u.*, COUNT(p.id) nb_products,
    COALESCE((SELECT SUM(oi.subtotal) FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.product_id IN (SELECT id FROM products WHERE seller_id=u.id) AND o.status!='cancelled'),0) total_sales
    FROM users u
    LEFT JOIN products p ON p.seller_id=u.id AND p.active=1
    WHERE u.role='seller'
    GROUP BY u.id
    ORDER BY u.created_at DESC")->fetchAll();
?>
<?php require_once 'includes/admin_header.php'; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
  <h1 style="font-size:1.4rem;font-weight:800;margin:0">Vendeurs partenaires</h1>
</div>

<?php
$pending = array_filter($applications, fn($a) => $a['status']==='pending');
?>

<?php if ($pending): ?>
<!-- Demandes en attente -->
<div class="a-card" style="margin-bottom:1.5rem;border:2px solid #f59e0b">
  <div class="a-card-header" style="background:#fef3c7;border-color:#f59e0b;color:#92400e">
    <div style="display:flex;align-items:center;gap:.5rem">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
      Demandes en attente (<?= count($pending) ?>)
    </div>
  </div>
  <div class="a-card-body" style="padding:0">
    <?php foreach ($pending as $app): ?>
    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:grid;grid-template-columns:1fr 1fr auto;gap:1rem;align-items:start">
      <div>
        <div style="font-weight:700;font-size:.95rem"><?= h($app['firstname'].' '.$app['lastname']) ?></div>
        <div style="font-size:.82rem;color:var(--text-2)"><?= h($app['email']) ?> — <?= h($app['phone']??'') ?></div>
        <div style="margin-top:.4rem;font-size:.82rem"><strong>Boutique :</strong> <?= h($app['business_name']) ?></div>
        <div style="font-size:.8rem;color:var(--text-2);margin-top:.2rem"><strong>Modèle :</strong> <?= $app['seller_type']==='managed'?'Plateforme gérée':'Autonome' ?></div>
        <div style="font-size:.8rem;color:var(--text-2);margin-top:.1rem"><strong>Compte :</strong>
          <?php $at = $app['account_type']??'individual'; ?>
          <span style="display:inline-block;padding:.1rem .55rem;border-radius:50px;font-size:.72rem;font-weight:700;background:<?= $at==='enterprise'?'#ede9fe':'#dbeafe' ?>;color:<?= $at==='enterprise'?'#5b21b6':'#1e40af' ?>"><?= $at==='enterprise'?'Entreprise':'Individuel' ?></span>
          — Frais : <strong><?= money((float)setting($at==='enterprise'?'opening_fee_enterprise':'opening_fee_individual','5000')) ?></strong>
        </div>
        <div style="font-size:.8rem;color:var(--text-2);margin-top:.3rem;font-style:italic">"<?= h(mb_substr($app['description'],0,120)).(mb_strlen($app['description'])>120?'…':'') ?>"</div>
        <div style="font-size:.74rem;color:var(--text-3);margin-top:.3rem"><?= date('d/m/Y H:i', strtotime($app['created_at'])) ?></div>
      </div>
      <div>
        <!-- Approuver -->
        <form method="POST" style="margin-bottom:.75rem">
          <?= csrfField() ?>
          <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
          <input type="hidden" name="action" value="approve">
          <textarea name="admin_note" class="form-control" rows="2" placeholder="Note (optionnel)" style="font-size:.8rem;margin-bottom:.4rem"></textarea>
          <button type="submit" class="btn btn-primary btn-sm" style="width:100%;font-size:.82rem">Approuver</button>
        </form>
        <!-- Rejeter -->
        <form method="POST">
          <?= csrfField() ?>
          <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
          <input type="hidden" name="action" value="reject">
          <textarea name="admin_note" class="form-control" rows="2" placeholder="Raison du rejet" style="font-size:.8rem;margin-bottom:.4rem"></textarea>
          <button type="submit" class="btn btn-sm" style="width:100%;font-size:.82rem;background:#fee2e2;color:#dc2626;border:none">Rejeter</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Vendeurs actifs -->
<div class="a-card" style="margin-bottom:1.5rem">
  <div class="a-card-header">Vendeurs actifs (<?= count($sellers) ?>)</div>
  <div class="a-card-body" style="padding:0">
    <?php if ($sellers): ?>
    <table class="a-table" style="margin:0">
      <thead><tr>
        <th>Vendeur</th><th>Boutique</th><th>Compte</th><th>Modèle</th><th>Produits</th><th>Ventes totales</th><th>Depuis</th><th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($sellers as $s): ?>
        <tr>
          <td>
            <div style="font-weight:600;font-size:.85rem"><?= h($s['firstname'].' '.$s['lastname']) ?></div>
            <div style="font-size:.77rem;color:var(--text-3)"><?= h($s['email']) ?></div>
          </td>
          <td style="font-size:.84rem"><?= h($s['business_name']??'—') ?></td>
          <td>
            <?php $sat = $s['account_type']??'individual'; ?>
            <span style="display:inline-block;padding:.15rem .6rem;border-radius:50px;font-size:.72rem;font-weight:700;background:<?= $sat==='enterprise'?'#ede9fe':'#dbeafe' ?>;color:<?= $sat==='enterprise'?'#5b21b6':'#1e40af' ?>">
              <?= $sat==='enterprise'?'Entreprise':'Individuel' ?>
            </span>
          </td>
          <td>
            <span style="display:inline-block;padding:.15rem .6rem;border-radius:50px;font-size:.72rem;font-weight:700;background:<?= $s['seller_type']==='managed'?'#dbeafe':'#d1fae5' ?>;color:<?= $s['seller_type']==='managed'?'#1e40af':'#065f46' ?>">
              <?= $s['seller_type']==='managed'?'Plateforme':'Autonome' ?>
            </span>
          </td>
          <td style="font-size:.83rem;text-align:center"><?= $s['nb_products'] ?></td>
          <td style="font-weight:700;font-size:.84rem"><?= money((float)$s['total_sales']) ?></td>
          <td style="font-size:.8rem;color:var(--text-2)"><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
          <td>
            <a href="../seller/index.php" class="btn btn-sm btn-outline" style="font-size:.77rem;padding:.25rem .6rem" target="_blank">Voir</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div style="padding:2rem;text-align:center;color:var(--text-3)">Aucun vendeur actif.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Historique des demandes traitées -->
<?php $treated = array_filter($applications, fn($a) => $a['status']!=='pending'); ?>
<?php if ($treated): ?>
<div class="a-card">
  <div class="a-card-header">Demandes traitées</div>
  <div class="a-card-body" style="padding:0">
    <table class="a-table" style="margin:0">
      <thead><tr><th>Candidat</th><th>Boutique</th><th>Compte</th><th>Modèle</th><th>Décision</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach ($treated as $app): ?>
        <tr>
          <td style="font-size:.83rem"><?= h($app['firstname'].' '.$app['lastname']) ?><br><span style="font-size:.75rem;color:var(--text-3)"><?= h($app['email']) ?></span></td>
          <td style="font-size:.83rem"><?= h($app['business_name']) ?></td>
          <td><?php $hat = $app['account_type']??'individual'; ?><span style="display:inline-block;padding:.1rem .55rem;border-radius:50px;font-size:.72rem;font-weight:700;background:<?= $hat==='enterprise'?'#ede9fe':'#dbeafe' ?>;color:<?= $hat==='enterprise'?'#5b21b6':'#1e40af' ?>"><?= $hat==='enterprise'?'Entreprise':'Individuel' ?></span></td>
          <td style="font-size:.82rem"><?= $app['seller_type']==='managed'?'Plateforme':'Autonome' ?></td>
          <td>
            <span style="display:inline-block;padding:.15rem .6rem;border-radius:50px;font-size:.72rem;font-weight:700;background:<?= $app['status']==='approved'?'#d1fae5':'#fee2e2' ?>;color:<?= $app['status']==='approved'?'#065f46':'#dc2626' ?>">
              <?= $app['status']==='approved'?'Approuvé':'Rejeté' ?>
            </span>
            <?php if ($app['admin_note']): ?>
              <div style="font-size:.74rem;color:var(--text-3);margin-top:.2rem"><?= h($app['admin_note']) ?></div>
            <?php endif; ?>
          </td>
          <td style="font-size:.8rem;color:var(--text-2)"><?= date('d/m/Y', strtotime($app['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
