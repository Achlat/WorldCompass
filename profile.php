<?php
require_once 'includes/functions.php';
requireLogin();
$u = currentUser();
$pageTitle = 'Mon Profil';
$tab = $_GET['tab'] ?? 'info';
$loyaltyPoints = getLoyaltyPoints((int)$u['id']);
$loyaltyHistory = getLoyaltyHistory((int)$u['id'], 10);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (isset($_POST['update_info'])) {
        $firstname = trim($_POST['firstname']??'');
        $lastname  = trim($_POST['lastname']??'');
        $phone     = trim($_POST['phone']??'');
        $address   = trim($_POST['address']??'');
        $city      = trim($_POST['city']??'');
        if ($firstname && $lastname) {
            db()->prepare("UPDATE users SET firstname=?,lastname=?,phone=?,address=?,city=? WHERE id=?")
                ->execute([$firstname,$lastname,$phone,$address,$city,$_SESSION['user_id']]);
            $_SESSION['user_name'] = $firstname;
            flash('success','Profil mis à jour.');
        } else { flash('error','Prénom et nom requis.'); }
        header('Location: profile.php'); exit;
    }
    if (isset($_POST['update_password'])) {
        $old  = $_POST['old_password']??'';
        $new  = $_POST['new_password']??'';
        $new2 = $_POST['new_password2']??'';
        if (!password_verify($old,$u['password'])) $errors[] = 'Ancien mot de passe incorrect';
        elseif (strlen($new)<6) $errors[] = 'Nouveau mot de passe : 6 caractères minimum';
        elseif ($new!==$new2) $errors[] = 'Les mots de passe ne correspondent pas';
        if (!$errors) {
            db()->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new,PASSWORD_DEFAULT),$_SESSION['user_id']]);
            flash('success','Mot de passe modifié.'); header('Location: profile.php'); exit;
        }
        $tab = 'password';
    }
}
// Refresh user
$u = currentUser();
?>
<?php require_once 'includes/header.php'; ?>

<div class="page-hdr"><div class="page-hdr-inner">
  <h1>Mon Compte</h1>
</div></div>

<div class="container" style="padding-bottom:3rem">
  <div class="profile-layout">
    <!-- Sidebar -->
    <div class="profile-sidebar">
      <div class="profile-avatar">
        <div class="avatar-initials"><?= h(mb_substr($u["firstname"],0,1).mb_substr($u["lastname"],0,1)) ?></div>
        <div class="avatar-name"><?= h($u['firstname'].' '.$u['lastname']) ?></div>
        <div class="avatar-email"><?= h($u['email']) ?></div>
      </div>
      <!-- Points fidélité -->
      <div style="background:linear-gradient(135deg,#fef3c7,#fde68a);border-radius:10px;padding:.85rem 1rem;margin:1rem 0;display:flex;align-items:center;gap:.6rem">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>
        <div>
          <div style="font-size:.78rem;font-weight:700;color:#92400e">Points de fidélité</div>
          <div style="font-size:1.15rem;font-weight:900;color:#78350f"><?= $loyaltyPoints ?> pts</div>
          <div style="font-size:.7rem;color:#a16207">= <?= money(loyaltyPointsValue($loyaltyPoints)) ?></div>
        </div>
      </div>
      <nav class="profile-nav">
        <a href="profile.php?tab=info" class="<?= $tab==='info'?'active':'' ?>">Mes informations</a>
        <a href="orders.php">Mes commandes</a>
        <a href="profile.php?tab=fidelite" class="<?= $tab==='fidelite'?'active':'' ?>">Fidélité</a>
        <a href="profile.php?tab=password" class="<?= $tab==='password'?'active':'' ?>">Mot de passe</a>
        <?php if (isSeller()): ?>
        <a href="seller/index.php" style="color:var(--accent)">Ma boutique</a>
        <?php else: ?>
        <a href="devenir-vendeur.php" style="color:var(--text-2)">Devenir vendeur</a>
        <?php endif; ?>
        <a href="logout.php" style="color:var(--danger)">Déconnexion</a>
      </nav>
    </div>

    <!-- Content -->
    <div>
      <?php if ($errors): ?>
        <div class="alert alert-error"><?= implode('<br>',array_map('h',$errors)) ?></div>
      <?php endif; ?>

      <?php if ($tab === 'info'): ?>
        <div class="card">
          <div class="card-header">Mes informations personnelles</div>
          <div class="card-body">
            <form method="POST">
              <?= csrfField() ?>
              <input type="hidden" name="update_info" value="1">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Prénom</label>
                  <input class="form-control" name="firstname" value="<?= h($u['firstname']) ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Nom</label>
                  <input class="form-control" name="lastname" value="<?= h($u['lastname']) ?>" required>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" value="<?= h($u['email']) ?>" disabled>
                <p class="form-hint">L'email ne peut pas être modifié.</p>
              </div>
              <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input class="form-control" name="phone" value="<?= h($u['phone']??'') ?>" placeholder="+228 90 00 00 00">
              </div>
              <div class="form-group">
                <label class="form-label">Adresse</label>
                <textarea class="form-control" name="address" rows="2"><?= h($u['address']??'') ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Ville</label>
                <input class="form-control" name="city" value="<?= h($u['city']??'') ?>">
              </div>
              <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
          </div>
        </div>

      <?php elseif ($tab === 'fidelite'): ?>
        <div class="card">
          <div class="card-header">Programme de fidélité</div>
          <div class="card-body">
            <div style="background:linear-gradient(135deg,#fef3c7,#fde68a);border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
              <div>
                <div style="font-size:.8rem;color:#92400e;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Votre solde</div>
                <div style="font-size:2.4rem;font-weight:900;color:#78350f;line-height:1"><?= $loyaltyPoints ?> <span style="font-size:1.2rem">pts</span></div>
                <div style="font-size:.85rem;color:#a16207;margin-top:.2rem">= <?= money(loyaltyPointsValue($loyaltyPoints)) ?> de réduction disponibles</div>
              </div>
              <a href="checkout.php" class="btn btn-primary" style="white-space:nowrap">Utiliser mes points</a>
            </div>
            <div style="background:#f8fafc;border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.5rem">
              <h4 style="font-size:.88rem;font-weight:700;margin:0 0 .6rem">Comment fonctionnent les points ?</h4>
              <ul style="font-size:.83rem;color:var(--text-2);line-height:1.8;padding-left:1.2rem;margin:0">
                <li>Gagnez <strong>1 point</strong> pour chaque <strong>100 FCFA</strong> dépensé</li>
                <li>1 point = <strong>10 FCFA</strong> de réduction</li>
                <li>Minimum <strong>100 points</strong> pour utiliser (multiples de 100)</li>
                <li>Points utilisables à la prochaine commande</li>
              </ul>
            </div>
            <h4 style="font-size:.88rem;font-weight:700;margin-bottom:.75rem">Historique</h4>
            <?php if ($loyaltyHistory): ?>
            <table class="a-table" style="font-size:.83rem">
              <thead><tr><th>Date</th><th>Commande</th><th>Points</th><th>Type</th></tr></thead>
              <tbody>
              <?php foreach ($loyaltyHistory as $lt): ?>
                <tr>
                  <td><?= date('d/m/Y', strtotime($lt['created_at'])) ?></td>
                  <td><?= $lt['order_number'] ? '#'.h($lt['order_number']) : '—' ?></td>
                  <td style="font-weight:700;color:<?= $lt['type']==='earn'?'var(--success)':'var(--danger)' ?>">
                    <?= $lt['type']==='earn'?'+':'-' ?><?= $lt['points'] ?> pts
                  </td>
                  <td><?= $lt['type']==='earn'?'Gagné':'Utilisé' ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            <?php else: ?>
              <p style="color:var(--text-3);font-size:.85rem">Aucun historique. Passez votre première commande pour gagner des points !</p>
            <?php endif; ?>
          </div>
        </div>

      <?php elseif ($tab === 'password'): ?>
        <div class="card">
          <div class="card-header">Changer le mot de passe</div>
          <div class="card-body">
            <form method="POST" style="max-width:400px">
              <?= csrfField() ?>
              <input type="hidden" name="update_password" value="1">
              <div class="form-group">
                <label class="form-label">Mot de passe actuel</label>
                <input class="form-control" type="password" name="old_password" required>
              </div>
              <div class="form-group">
                <label class="form-label">Nouveau mot de passe</label>
                <input class="form-control" type="password" name="new_password" required minlength="6">
              </div>
              <div class="form-group">
                <label class="form-label">Confirmer le nouveau</label>
                <input class="form-control" type="password" name="new_password2" required>
              </div>
              <button type="submit" class="btn btn-primary">Modifier</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
