<?php
require_once 'includes/functions.php';
requireLogin();
$pageTitle = 'Mes Commandes';

$orders = db()->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$orders->execute([$_SESSION['user_id']]);
$orders = $orders->fetchAll();

// Detail view
$detail = null;
if (isset($_GET['id'])) {
    $d = db()->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
    $d->execute([(int)$_GET['id'],$_SESSION['user_id']]);
    $detail = $d->fetch();
    if ($detail) {
        $items = db()->prepare("SELECT * FROM order_items WHERE order_id=?");
        $items->execute([$detail['id']]);
        $detail['items'] = $items->fetchAll();
    }
}

$statusLabels = ['pending'=>'En attente','processing'=>'En traitement','shipped'=>'Expédiée','delivered'=>'Livrée','cancelled'=>'Annulée'];
?>
<?php require_once 'includes/header.php'; ?>

<div class="page-hdr"><div class="page-hdr-inner">
  <h1><?= $detail ? 'Commande #'.h($detail['order_number']) : 'Mes Commandes' ?></h1>
</div></div>

<div class="container" style="padding-bottom:3rem">
  <?php if ($detail): ?>
    <a href="orders.php" class="btn btn-outline btn-sm mb-2">← Toutes mes commandes</a>
    <div class="card">
      <div class="card-header">
        <span>Commande #<?= h($detail['order_number']) ?></span>
        <span class="status status-<?= $detail['status'] ?>"><?= $statusLabels[$detail['status']] ?></span>
      </div>
      <div class="card-body">
        <div class="grid-2" style="margin-bottom:1.5rem">
          <div>
            <p class="text-muted fw-600 mb-1">Adresse</p>
            <p><?= h($detail['firstname'].' '.$detail['lastname']) ?></p>
            <p><?= h($detail['address'].', '.$detail['city']) ?></p>
            
          </div>
          <div>
            <p class="text-muted fw-600 mb-1">Paiement</p>
            <p><?= h($detail['payment_method']) ?></p>
            <p class="text-muted" style="margin-top:.35rem">Commandé le <?= date('d/m/Y H:i',strtotime($detail['created_at'])) ?></p>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead><tr><th>Produit</th><th>Prix unitaire</th><th>Qté</th><th>Sous-total</th></tr></thead>
            <tbody>
              <?php foreach ($detail['items'] as $item): ?>
                <tr>
                  <td><?= h($item['product_name']) ?></td>
                  <td><?= money((float)$item['product_price']) ?></td>
                  <td><?= $item['quantity'] ?></td>
                  <td><?= money((float)$item['subtotal']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div style="text-align:right;margin-top:1rem">
          <div class="summary-row"><span>Livraison</span><span><?= $detail['shipping']>0?money((float)$detail['shipping']):'Gratuite' ?></span></div>
          <div class="summary-row total" style="font-size:1.1rem"><span>Total</span><span><?= money((float)$detail['total']) ?></span></div>
        </div>
      </div>
    </div>

  <?php elseif ($orders): ?>
    <div class="card">
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Commande</th><th>Date</th><th>Total</th><th>Statut</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td><strong>#<?= h($o['order_number']) ?></strong></td>
                <td><?= date('d/m/Y',strtotime($o['created_at'])) ?></td>
                <td><?= money((float)$o['total']) ?></td>
                <td><span class="status status-<?= $o['status'] ?>"><?= $statusLabels[$o['status']] ?></span></td>
                <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm">Voir</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div class="empty-state">
      
      <div class="empty-title">Aucune commande</div>
      <p class="empty-text">Vous n'avez pas encore passé de commande.</p>
      <a href="products.php" class="btn btn-primary">Faire mes achats</a>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
