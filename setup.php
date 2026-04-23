<?php
//  World Compass – One-click installer 
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'ecommerce';

$message = '';
$success = false;
$log     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {

    // mysqli multi_query handles the full SQL file correctly
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);

    if ($conn->connect_error) {
        $message = 'Connexion MySQL impossible : '.$conn->connect_error;
        $message .= '<br>Vérifiez que MySQL est démarré dans XAMPP.';
    } else {
        $conn->set_charset('utf8mb4');

        $sql = file_get_contents(__DIR__.'/install.sql');
        if (!$sql) {
            $message = 'Fichier install.sql introuvable.';
        } else {
            // Execute entire SQL file
            if ($conn->multi_query($sql)) {
                // Consume all result sets (required after multi_query)
                do {
                    if ($res = $conn->store_result()) {
                        $res->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
            }

            if ($conn->errno) {
                $message = 'Erreur SQL : '.$conn->error;
            } else {
                $success = true;
                $message = ' Installation réussie ! Base de données <strong>ecommerce</strong> créée avec succès.';
            }
        }
        $conn->close();
    }
}

//  Check if DB already exists 
$dbExists = false;
try {
    $test = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $cnt  = $test->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $dbExists = true;
} catch (Throwable) {}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Installation – World Compass</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,sans-serif;background:#f1f5f9;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:1.5rem}
.box{background:#fff;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,.12);padding:2.5rem;width:100%;max-width:520px}
.logo{font-size:2.5rem;margin-bottom:.75rem;display:block;text-align:center}
h1{color:#0f172a;font-size:1.55rem;text-align:center;margin-bottom:.35rem}
.sub{color:#64748b;font-size:.9rem;text-align:center;margin-bottom:1.75rem}
.btn{width:100%;padding:1rem;background:#FF6B2B;color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .2s;margin-top:.5rem}
.btn:hover{background:#e55b1f}
.btn-reinstall{background:#64748b;margin-top:.5rem}
.btn-reinstall:hover{background:#475569}
.alert{padding:.9rem 1.1rem;border-radius:8px;margin-bottom:1.25rem;font-size:.88rem;font-weight:500;line-height:1.5}
.alert-success{background:#d1fae5;color:#065f46;border-left:4px solid #10b981}
.alert-error  {background:#fee2e2;color:#991b1b;border-left:4px solid #ef4444}
.alert-info   {background:#eff6ff;color:#1e40af;border-left:4px solid #5469d4}
.checks{margin:1.25rem 0;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden}
.check-row{display:flex;justify-content:space-between;align-items:center;padding:.65rem 1rem;font-size:.83rem;border-bottom:1px solid #f1f5f9}
.check-row:last-child{border-bottom:none}
.check-label{color:#475569}
.ok {color:#10b981;font-weight:700}
.no {color:#ef4444;font-weight:700}
.warn{color:#f59e0b;font-weight:700}
.links{margin-top:1.5rem;display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap}
.links a{color:#FF6B2B;font-size:.85rem;font-weight:600;text-decoration:none;padding:.4rem .8rem;border:1.5px solid #FF6B2B;border-radius:6px;transition:all .2s}
.links a:hover{background:#FF6B2B;color:#fff}
.demo-box{background:#eff6ff;border-radius:8px;padding:1rem;margin-top:1.25rem;font-size:.82rem;color:#1e40af;line-height:1.8}
code{background:#dbeafe;padding:.1rem .35rem;border-radius:4px;font-size:.8rem}
</style>
</head>
<body>
<div class="box">
  <span class="logo"></span>
  <h1>World Compass – Installation</h1>
  <p class="sub">Assistant de configuration de la base de données</p>

  <!-- System checks -->
  <div class="checks">
    <?php
    $checks = [
      ['PHP ≥ 8.0',        version_compare(PHP_VERSION,'8.0')>=0, PHP_VERSION],
      ['Extension mysqli', extension_loaded('mysqli'),             extension_loaded('mysqli')?'OK':'Activer dans php.ini'],
      ['Extension PDO MySQL', extension_loaded('pdo_mysql'),       extension_loaded('pdo_mysql')?'OK':'Activer dans php.ini'],
      ['Fichier install.sql', file_exists('install.sql'),          file_exists('install.sql')?'Présent':'Absent'],
      ['MySQL démarré',    $dbExists || @(new mysqli($DB_HOST,$DB_USER,$DB_PASS))->connect_errno===0, 'Vérifier XAMPP'],
    ];
    foreach ($checks as [$label, $ok, $detail]):
      $cls = $ok ? 'ok' : 'no';
      $ico = $ok ? '' : '';
    ?>
      <div class="check-row">
        <span class="check-label"><?= $label ?></span>
        <span class="<?= $cls ?>"><?= $ico ?> <?= htmlspecialchars($detail) ?></span>
      </div>
    <?php endforeach; ?>
    <div class="check-row">
      <span class="check-label">Base de données ecommerce</span>
      <span class="<?= $dbExists?'ok':'warn' ?>"><?= $dbExists?' Installée':' Non créée' ?></span>
    </div>
  </div>

  <!-- Message -->
  <?php if ($message): ?>
    <div class="alert <?= $success?'alert-success':'alert-error' ?>"><?= $message ?></div>
  <?php endif; ?>

  <!-- Already installed notice -->
  <?php if ($dbExists && !$success): ?>
    <div class="alert alert-info">
      ℹ La base de données est déjà installée et contient <strong><?= $cnt ?? 0 ?></strong> produit(s).
      Vous pouvez réinstaller pour remettre à zéro.
    </div>
  <?php endif; ?>

  <!-- Action buttons -->
  <form method="POST">
    <?php if (!$dbExists): ?>
      <button type="submit" name="install" class="btn"> Lancer l'installation</button>
    <?php else: ?>
      <button type="submit" name="install" class="btn btn-reinstall"
        onclick="return confirm('Réinstaller efface toutes les données. Continuer ?')">
         Réinstaller (remise à zéro)
      </button>
    <?php endif; ?>
  </form>

  <!-- Links -->
  <div class="links">
    <a href="index.php"> Boutique</a>
    <a href="login.php"> Connexion</a>
    <a href="admin/index.php"> Admin</a>
  </div>

  <!-- Demo credentials -->
  <?php if ($dbExists || $success): ?>
    <div class="demo-box">
      <strong>Comptes de démonstration :</strong><br>
       Admin : <code>admin@shop.com</code> / <code>password</code><br>
       Client : <code>user@shop.com</code> / <code>password</code>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
