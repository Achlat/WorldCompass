<?php
require_once 'includes/functions.php';
if (isLoggedIn()) { header('Location: '.SITE_URL); exit; }
$pageTitle = 'Connexion';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = trim($_POST['email']??'');
    $pass  = $_POST['password']??'';
    if (!$email || !$pass) {
        $errors[] = 'Veuillez remplir tous les champs.';
    } else {
        $s = db()->prepare("SELECT * FROM users WHERE email=? AND active=1");
        $s->execute([$email]);
        $user = $s->fetch();
        if ($user && password_verify($pass,$user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['firstname'];
            flash('success','Bienvenue, '.h($user['firstname']).' !');
            $redirect = $_GET['redirect'] ?? ($_POST['redirect'] ?? SITE_URL.'/index.php');
            header('Location: '.$redirect); exit;
        } else {
            $errors[] = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<?php require_once 'includes/header.php'; ?>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><?= h(setting('site_name',SITE_NAME)) ?></div>
    <p class="auth-subtitle">Connexion à votre compte</p>

    <?php if ($errors): ?>
      <div class="alert alert-error"><?= implode('<br>',array_map('h',$errors)) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?= csrfField() ?>
      <input type="hidden" name="redirect" value="<?= h($_GET['redirect']??SITE_URL) ?>">
      <div class="form-group">
        <label class="form-label">Adresse email</label>
        <input class="form-control" type="email" name="email" value="<?= h($_POST['email']??'') ?>" required autofocus placeholder="vous@email.com">
      </div>
      <div class="form-group">
        <label class="form-label">Mot de passe</label>
        <input class="form-control" type="password" name="password" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary btn-full mt-2">Se connecter</button>
    </form>

    <p class="auth-footer">Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
