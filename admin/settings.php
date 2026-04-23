<?php
$adminPage = 'settings';
$pageTitle = 'Paramètres';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $keys = ['site_name','site_tagline','site_email','site_phone','site_address','currency','shipping_cost','free_shipping_threshold','items_per_page',
             'opening_fee_individual','opening_fee_enterprise','platform_commission_rate'];
    foreach ($keys as $k) {
        $val = trim($_POST[$k] ?? '');
        db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$k,$val,$val]);
    }
    // Toggle commission plateforme (checkbox — absent = 0)
    $commEnabled = isset($_POST['platform_commission_enabled']) ? '1' : '0';
    db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('platform_commission_enabled',?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$commEnabled,$commEnabled]);
    flash('success','Paramètres enregistrés avec succès.'); header('Location: settings.php'); exit;
}

// Load current settings
$s = [];
$rows = db()->query("SELECT * FROM settings")->fetchAll();
foreach ($rows as $r) $s[$r['setting_key']] = $r['setting_value'];
$sv = fn(string $k, string $d='') => h($s[$k] ?? $d);
?>
<?php require_once 'includes/admin_header.php'; ?>

<form method="POST">
  <?= csrfField() ?>
  <div class="grid-2" style="align-items:start">

    <div>
      <div class="a-card mb-2">
        <div class="a-card-header">Informations du site</div>
        <div class="a-card-body">
          <div class="form-group">
            <label class="form-label">Nom du site</label>
            <input class="form-control" name="site_name" value="<?= $sv('site_name','World Compass') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Slogan</label>
            <input class="form-control" name="site_tagline" value="<?= $sv('site_tagline','Votre Shopping en Toute Confiance') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Email de contact</label>
            <input class="form-control" type="email" name="site_email" value="<?= $sv('site_email') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input class="form-control" name="site_phone" value="<?= $sv('site_phone','+228 90 78 28 96') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Adresse</label>
            <textarea class="form-control" name="site_address" rows="2"><?= $sv('site_address') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="a-card mb-2">
        <div class="a-card-header">Paramètres boutique</div>
        <div class="a-card-body">
          <div class="form-group">
            <label class="form-label">Devise</label>
            <input class="form-control" name="currency" value="<?= $sv('currency','FCFA') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Frais de livraison</label>
            <input class="form-control" type="number" name="shipping_cost" value="<?= $sv('shipping_cost','2000') ?>" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Seuil livraison gratuite</label>
            <input class="form-control" type="number" name="free_shipping_threshold" value="<?= $sv('free_shipping_threshold','50000') ?>" min="0">
            <p class="form-hint">Montant à partir duquel la livraison est offerte</p>
          </div>
          <div class="form-group">
            <label class="form-label">Produits par page</label>
            <input class="form-control" type="number" name="items_per_page" value="<?= $sv('items_per_page','12') ?>" min="4" max="48">
          </div>
        </div>
      </div>

      <div class="a-card">
        <div class="a-card-header">Statistiques rapides</div>
        <div class="a-card-body">
          <?php
          $stats = [
            ['Produits actifs',    db()->query("SELECT COUNT(*) FROM products WHERE active=1")->fetchColumn()],
            ['Commandes totales',  db()->query("SELECT COUNT(*) FROM orders")->fetchColumn()],
            ['Clients inscrits',   db()->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn()],
            ['CA total (livrées)', money((float)(db()->query("SELECT SUM(total) FROM orders WHERE status='delivered'")->fetchColumn()?:0))],
          ];
          foreach ($stats as [$l,$v]):
          ?>
            <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--a-border);font-size:.88rem">
              <span><?= $l ?></span>
              <strong><?= $v ?></strong>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>

  <!-- Frais d'ouverture & Commission plateforme -->
  <div style="margin-top:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start">

    <div class="a-card">
      <div class="a-card-header">Frais d'ouverture de compte vendeur</div>
      <div class="a-card-body">
        <p style="font-size:.83rem;color:var(--text-2);margin-bottom:1.25rem">Montants facturés lors de l'approbation d'un nouveau vendeur, selon le type de compte.</p>
        <div class="form-group">
          <label class="form-label">Compte Individuel (FCFA)</label>
          <input class="form-control" type="number" name="opening_fee_individual" value="<?= $sv('opening_fee_individual','5000') ?>" min="0">
        </div>
        <div class="form-group">
          <label class="form-label">Compte Entreprise (FCFA)</label>
          <input class="form-control" type="number" name="opening_fee_enterprise" value="<?= $sv('opening_fee_enterprise','20000') ?>" min="0">
        </div>
      </div>
    </div>

    <div class="a-card">
      <div class="a-card-header">Commission plateforme World Compass</div>
      <div class="a-card-body">
        <p style="font-size:.83rem;color:var(--text-2);margin-bottom:1.25rem">Commission prélevée par World Compass sur chaque vente réalisée par un vendeur partenaire.</p>
        <div class="form-group">
          <label class="form-label">Taux de commission (%)</label>
          <input class="form-control" type="number" step="0.01" name="platform_commission_rate" value="<?= $sv('platform_commission_rate','3.00') ?>" min="0" max="50">
          <p class="form-hint">Appliqué sur le montant HT de chaque vente vendeur.</p>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label style="display:flex;align-items:center;gap:.75rem;cursor:pointer;padding:.75rem 1rem;border:2px solid var(--a-border);border-radius:8px;transition:border-color .15s"
                 id="comm-toggle-label">
            <div style="position:relative;width:42px;height:24px;flex-shrink:0">
              <input type="checkbox" name="platform_commission_enabled" id="comm-toggle" value="1"
                     <?= $sv('platform_commission_enabled','1')==='1'?'checked':'' ?>
                     style="opacity:0;width:0;height:0"
                     onchange="updateToggle(this)">
              <span id="comm-track" style="position:absolute;inset:0;border-radius:12px;background:<?= $sv('platform_commission_enabled','1')==='1'?'#10b981':'#d1d5db' ?>;transition:background .2s">
                <span id="comm-thumb" style="position:absolute;top:3px;left:<?= $sv('platform_commission_enabled','1')==='1'?'21px':'3px' ?>;width:18px;height:18px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.2)"></span>
              </span>
            </div>
            <span id="comm-toggle-text" style="font-size:.88rem;font-weight:600;color:<?= $sv('platform_commission_enabled','1')==='1'?'#059669':'#9ca3af' ?>">
              Commission <?= $sv('platform_commission_enabled','1')==='1'?'activée':'désactivée' ?>
            </span>
          </label>
        </div>
      </div>
    </div>

  </div>

  <div style="margin-top:1.5rem">
    <button type="submit" class="btn btn-primary btn-lg">Enregistrer les paramètres</button>
  </div>
</form>

<script>
function updateToggle(cb) {
    const track = document.getElementById('comm-track');
    const thumb = document.getElementById('comm-thumb');
    const text  = document.getElementById('comm-toggle-text');
    if (cb.checked) {
        track.style.background = '#10b981';
        thumb.style.left = '21px';
        text.textContent = 'Commission activée';
        text.style.color = '#059669';
    } else {
        track.style.background = '#d1d5db';
        thumb.style.left = '3px';
        text.textContent = 'Commission désactivée';
        text.style.color = '#9ca3af';
    }
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
