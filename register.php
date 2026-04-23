<?php
require_once 'includes/functions.php';
if (isLoggedIn()) { header('Location: '.SITE_URL); exit; }
$pageTitle = 'Inscription';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $f = [
        'firstname' => trim($_POST['firstname']??''),
        'lastname'  => trim($_POST['lastname']??''),
        'email'     => trim($_POST['email']??''),
        'phone'     => trim($_POST['phone']??''),
        'password'  => $_POST['password']??'',
        'password2' => $_POST['password2']??'',
    ];
    if (!$f['firstname'])    $errors[] = 'Prénom requis';
    if (!$f['lastname'])     $errors[] = 'Nom requis';
    if (!filter_var($f['email'],FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide';
    if (strlen($f['password']) < 6) $errors[] = 'Mot de passe : 6 caractères minimum';
    if ($f['password'] !== $f['password2']) $errors[] = 'Les mots de passe ne correspondent pas';

    if (!$errors) {
        $check = db()->prepare("SELECT id FROM users WHERE email=?");
        $check->execute([$f['email']]);
        if ($check->fetch()) {
            $errors[] = 'Cet email est déjà utilisé.';
        } else {
            $hash = password_hash($f['password'], PASSWORD_DEFAULT);
            db()->prepare("INSERT INTO users(firstname,lastname,email,phone,password) VALUES(?,?,?,?,?)")
                ->execute([$f['firstname'],$f['lastname'],$f['email'],$f['phone'],$hash]);
            $uid = db()->lastInsertId();
            $_SESSION['user_id']   = $uid;
            $_SESSION['user_role'] = 'customer';
            $_SESSION['user_name'] = $f['firstname'];
            flash('success','Bienvenue, '.h($f['firstname']).' ! Votre compte est créé.');
            header('Location: '.SITE_URL); exit;
        }
    }
}
?>
<?php require_once 'includes/header.php'; ?>

<div class="auth-wrap">
  <div class="auth-card" style="max-width:520px">
    <div class="auth-logo"><?= h(setting('site_name',SITE_NAME)) ?></div>
    <p class="auth-subtitle">Créer votre compte gratuitement</p>

    <?php if ($errors): ?>
      <div class="alert alert-error"><?= implode('<br>',array_map('h',$errors)) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?= csrfField() ?>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Prénom *</label>
          <input class="form-control" name="firstname" value="<?= h($_POST['firstname']??'') ?>" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label">Nom *</label>
          <input class="form-control" name="lastname" value="<?= h($_POST['lastname']??'') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Email *</label>
        <input class="form-control" type="email" name="email" value="<?= h($_POST['email']??'') ?>" required placeholder="vous@email.com">
      </div>
      <div class="form-group">
        <label class="form-label">Téléphone</label>
        <input class="form-control" type="tel" name="phone" value="<?= h($_POST['phone']??'') ?>" placeholder="+228 90 00 00 00">
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Mot de passe *</label>
          <input class="form-control" type="password" name="password" required minlength="6" placeholder="Min. 6 caractères">
        </div>
        <div class="form-group">
          <label class="form-label">Confirmer *</label>
          <input class="form-control" type="password" name="password2" required placeholder="Répéter">
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full mt-2">Créer mon compte</button>
    </form>

    <p class="auth-footer">Déjà un compte ? <a href="login.php">Se connecter</a></p>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
