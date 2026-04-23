<?php
$adminPage = 'users';
$pageTitle = 'Utilisateurs';
require_once 'includes/auth.php';

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// Toggle active
if ($action === 'toggle' && $id && $id != $_SESSION['user_id']) {
    db()->prepare("UPDATE users SET active=1-active WHERE id=?")->execute([$id]);
    flash('info','Statut utilisateur modifié.'); header('Location: users.php'); exit;
}

$page    = max(1,(int)($_GET['page']??1));
$perPage = 20;
$offset  = ($page-1)*$perPage;
$search  = trim($_GET['q']??'');
$role    = $_GET['role']??'';

$where=['1=1']; $params=[];
if ($search) { $where[]='(firstname LIKE ? OR lastname LIKE ? OR email LIKE ?)'; $params[]="%$search%"; $params[]="%$search%"; $params[]="%$search%"; }
if ($role)   { $where[]='role=?'; $params[]=$role; }
$whereSQL = implode(' AND ',$where);

$total = db()->prepare("SELECT COUNT(*) FROM users WHERE $whereSQL");
$total->execute($params); $total=(int)$total->fetchColumn();

$stmt = db()->prepare("SELECT u.*,(SELECT COUNT(*) FROM orders WHERE user_id=u.id) order_cnt FROM users u WHERE $whereSQL ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<?php require_once 'includes/admin_header.php'; ?>

<div class="flex-between mb-2">
  <h2 style="font-size:1.1rem;font-weight:700">Utilisateurs (<?= $total ?>)</h2>
</div>

<div class="a-card mb-2">
  <div class="a-card-body" style="padding:.75rem 1.25rem">
    <form method="GET" action="users.php" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
      <input class="form-control" name="q" value="<?= h($search) ?>" placeholder="Nom, email…" style="width:220px">
      <select class="form-control" name="role" style="width:auto" onchange="this.form.submit()">
        <option value="">Tous rôles</option>
        <option value="customer" <?= $role==='customer'?'selected':'' ?>>Clients</option>
        <option value="admin"    <?= $role==='admin'?'selected':'' ?>>Admins</option>
      </select>
      <button type="submit" class="btn btn-secondary btn-sm">Rechercher</button>
      <a href="users.php" class="btn btn-outline btn-sm">Reset</a>
    </form>
  </div>
</div>

<div class="a-card">
  <div class="table-wrap">
    <table class="a-table">
      <thead><tr><th>Utilisateur</th><th>Email</th><th>Téléphone</th><th>Commandes</th><th>Rôle</th><th>Statut</th><th>Inscription</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:.5rem">
                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#5469d4,#1e40af);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.82rem;font-weight:700;flex-shrink:0"><?= mb_strtoupper(mb_substr($u['firstname'],0,1)) ?></div>
                <div style="font-weight:600"><?= h($u['firstname'].' '.$u['lastname']) ?></div>
              </div>
            </td>
            <td><?= h($u['email']) ?></td>
            <td><?= h($u['phone']??'—') ?></td>
            <td style="font-weight:600"><?= $u['order_cnt'] ?></td>
            <td><span class="status status-<?= $u['role'] ?>"><?= h($u['role']) ?></span></td>
            <td><span class="status <?= $u['active']?'status-active':'status-inactive' ?>"><?= $u['active']?'Actif':'Inactif' ?></span></td>
            <td><?= date('d/m/Y',strtotime($u['created_at'])) ?></td>
            <td>
              <?php if ($u['id'] != $_SESSION['user_id']): ?>
                <a href="users.php?action=toggle&id=<?= $u['id'] ?>" class="btn btn-sm <?= $u['active']?'btn-outline':'btn-success' ?>" data-confirm="<?= $u['active']?'Désactiver cet utilisateur ?':'Activer cet utilisateur ?' ?>">
                  <?= $u['active']?'Désactiver':'Activer' ?>
                </a>
              <?php else: ?>
                <span class="text-muted">Vous</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$users): ?><tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--a-text3)">Aucun utilisateur.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($total > $perPage): ?>
  <div class="pagination">
    <?php for ($i=1;$i<=ceil($total/$perPage);$i++): ?>
      <a href="users.php?q=<?= urlencode($search) ?>&role=<?= h($role) ?>&page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
