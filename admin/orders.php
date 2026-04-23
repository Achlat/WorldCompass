<?php
$adminPage = 'orders';
$pageTitle = 'Commandes';
require_once 'includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$statusFilter = $_GET['status'] ?? '';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    verifyCsrf();
    $oid  = (int)$_POST['order_id'];
    $stat = $_POST['status'];
    $allowed = ['pending','processing','shipped','delivered','cancelled'];
    if (in_array($stat,$allowed)) {
        db()->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$stat,$oid]);
        flash('success','Statut mis à jour.');
    }
    header('Location: orders.php'.($id?"?id=$id":'')); exit;
}

$statusLabels = ['pending'=>'En attente','processing'=>'En cours','shipped'=>'Expédiée','delivered'=>'Livrée','cancelled'=>'Annulée'];

// Detail view
if ($id) {
    $order = db()->prepare("SELECT * FROM orders WHERE id=?");
    $order->execute([$id]);
    $order = $order->fetch();
    if ($order) {
        $items = db()->prepare("SELECT * FROM order_items WHERE order_id=?");
        $items->execute([$id]);
        $order['items'] = $items->fetchAll();
    }
    if (!$order) { flash('error','Commande introuvable.'); header('Location: orders.php'); exit; }
} else {
    // List
    $page    = max(1,(int)($_GET['page']??1));
    $perPage = 20;
    $offset  = ($page-1)*$perPage;
    $search  = trim($_GET['q']??'');

    $where = ['1=1']; $params=[];
    if ($statusFilter) { $where[]='status=?'; $params[]=$statusFilter; }
    if ($search) { $where[]='(order_number LIKE ? OR firstname LIKE ? OR email LIKE ?)'; $params[]="%$search%"; $params[]="%$search%"; $params[]="%$search%"; }
    $whereSQL = implode(' AND ',$where);

    $total = db()->prepare("SELECT COUNT(*) FROM orders WHERE $whereSQL");
    $total->execute($params); $total=(int)$total->fetchColumn();

    $stmt = db()->prepare("SELECT * FROM orders WHERE $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
}
?>
<?php require_once 'includes/admin_header.php'; ?>

<?php if ($id && isset($order)): ?>
  <!--  ORDER DETAIL  -->
  <div class="flex-between mb-2">
    <h2 style="font-size:1.1rem;font-weight:700">Commande #<?= h($order['order_number']) ?></h2>
    <a href="orders.php" class="btn btn-outline btn-sm">← Retour</a>
  </div>

  <div class="grid-2" style="align-items:start">
    <div>
      <div class="a-card mb-2">
        <div class="a-card-header">
          <span>Informations</span>
          <span class="status status-<?= $order['status'] ?>"><?= $statusLabels[$order['status']] ?></span>
        </div>
        <div class="a-card-body">
          <table style="width:100%;font-size:.88rem;border-collapse:collapse">
            <?php foreach ([
              ['Client', h($order['firstname'].' '.$order['lastname'])],
              ['Email',  h($order['email'])],
              ['Tél.',   h($order['phone'])],
              ['Adresse',h($order['address'].', '.$order['city'])],
              ['Paiement',h($order['payment_method'])],
              ['Date',   date('d/m/Y H:i',strtotime($order['created_at']))],
              ['Notes',  h($order['notes']??'—')],
            ] as [$l,$v]): ?>
              <tr><td style="padding:.4rem .5rem;font-weight:600;color:var(--a-text3);width:90px"><?= $l ?></td><td style="padding:.4rem .5rem"><?= $v ?></td></tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>

      <!-- Update status -->
      <div class="a-card">
        <div class="a-card-header">Changer le statut</div>
        <div class="a-card-body">
          <form method="POST" style="display:flex;gap:.75rem;align-items:center">
            <?= csrfField() ?>
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <select class="form-control" name="status" style="width:auto">
              <?php foreach ($statusLabels as $k=>$v): ?>
                <option value="<?= $k ?>" <?= $order['status']===$k?'selected':'' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" name="update_status" class="btn btn-primary">Enregistrer</button>
          </form>
        </div>
      </div>
    </div>

    <div class="a-card">
      <div class="a-card-header">Articles commandés</div>
      <div class="table-wrap">
        <table class="a-table">
          <thead><tr><th>Produit</th><th>Prix</th><th>Qté</th><th>Total</th></tr></thead>
          <tbody>
            <?php foreach ($order['items'] as $item): ?>
              <tr>
                <td><?= h($item['product_name']) ?></td>
                <td><?= money((float)$item['product_price']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td style="font-weight:700"><?= money((float)$item['subtotal']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div style="padding:1rem;text-align:right;border-top:1px solid var(--a-border)">
        <div style="font-size:.88rem;color:var(--a-text3);margin-bottom:.35rem">
          Livraison : <?= $order['shipping']>0?money((float)$order['shipping']):'Gratuite' ?>
        </div>
        <div style="font-size:1.1rem;font-weight:800">Total : <?= money((float)$order['total']) ?></div>
      </div>
    </div>
  </div>

<?php else: ?>
  <!--  ORDER LIST  -->
  <div class="flex-between mb-2">
    <h2 style="font-size:1.1rem;font-weight:700">Commandes (<?= $total ?>)</h2>
  </div>

  <!-- Filters -->
  <div class="a-card mb-2">
    <div class="a-card-body" style="padding:.75rem 1.25rem">
      <form method="GET" action="orders.php" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
        <input class="form-control" name="q" value="<?= h($search) ?>" placeholder="N° commande, client, email…" style="width:220px">
        <div style="display:flex;gap:.4rem;flex-wrap:wrap">
          <?php foreach (array_merge([''=>'Tous'],$statusLabels) as $k=>$v): ?>
            <a href="orders.php?status=<?= $k ?>" class="btn btn-sm <?= $statusFilter===$k?'btn-secondary':'btn-outline' ?>"><?= $v ?></a>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-secondary btn-sm">Rechercher</button>
      </form>
    </div>
  </div>

  <div class="a-card">
    <div class="table-wrap">
      <table class="a-table">
        <thead><tr><th>N°</th><th>Client</th><th>Date</th><th>Total</th><th>Paiement</th><th>Statut</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><a href="orders.php?id=<?= $o['id'] ?>" style="font-weight:700;color:var(--a-blue)">#<?= h($o['order_number']) ?></a></td>
              <td>
                <div style="font-weight:600"><?= h($o['firstname'].' '.$o['lastname']) ?></div>
                <div class="text-muted"><?= h($o['email']) ?></div>
              </td>
              <td><?= date('d/m/Y',strtotime($o['created_at'])) ?><br><span class="text-muted"><?= date('H:i',strtotime($o['created_at'])) ?></span></td>
              <td style="font-weight:700"><?= money((float)$o['total']) ?></td>
              <td><?= h($o['payment_method']) ?></td>
              <td><span class="status status-<?= $o['status'] ?>"><?= $statusLabels[$o['status']] ?></span></td>
              <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm">Voir</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$orders): ?>
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--a-text3)">Aucune commande.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($total > $perPage): ?>
    <div class="pagination">
      <?php for ($i=1;$i<=ceil($total/$perPage);$i++): ?>
        <a href="orders.php?status=<?= h($statusFilter) ?>&q=<?= urlencode($search) ?>&page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
