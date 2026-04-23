<?php
/**
 * Migration – ajoute les colonnes manquantes
 * Exécuter une seule fois : http://localhost/ecommerce/migrate.php
 */
require_once 'includes/config.php';

$steps = [];

// 1. Colonne image sur products
try {
    db()->query("ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT NULL");
    $steps[] = ['ok', 'Colonne products.image ajoutée'];
} catch (PDOException $e) {
    $steps[] = [str_contains($e->getMessage(),'Duplicate column') ? 'skip' : 'err',
                'products.image : '.$e->getMessage()];
}

// 2. Colonne payment_phone sur orders
try {
    db()->query("ALTER TABLE orders ADD COLUMN payment_phone VARCHAR(20) DEFAULT NULL");
    $steps[] = ['ok', 'Colonne orders.payment_phone ajoutée'];
} catch (PDOException $e) {
    $steps[] = [str_contains($e->getMessage(),'Duplicate column') ? 'skip' : 'err',
                'orders.payment_phone : '.$e->getMessage()];
}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
<title>Migration</title>
<style>body{font-family:sans-serif;max-width:600px;margin:3rem auto;padding:0 1rem}
h1{font-size:1.4rem;margin-bottom:1rem}
.ok{color:#065f46;background:#d1fae5;padding:.5rem .75rem;border-radius:6px;margin:.4rem 0}
.skip{color:#92400e;background:#fef3c7;padding:.5rem .75rem;border-radius:6px;margin:.4rem 0}
.err{color:#991b1b;background:#fee2e2;padding:.5rem .75rem;border-radius:6px;margin:.4rem 0}
a{display:inline-block;margin-top:1rem;color:#5469d4}</style></head><body>
<h1>Migration base de données</h1>
<?php foreach ($steps as [$type, $msg]): ?>
  <div class="<?= $type ?>"><?= htmlspecialchars($msg) ?></div>
<?php endforeach; ?>
<p style="margin-top:1.5rem;font-size:.85rem;color:#64748b">
  Migration terminée. Vous pouvez supprimer ce fichier.
</p>
<a href="admin/index.php">Aller au tableau de bord</a>
</body></html>
