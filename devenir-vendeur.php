<?php
require_once 'includes/functions.php';
$pageTitle = 'Devenir vendeur';
$u = currentUser();
$errors = [];
$success = false;

// Déjà vendeur ?
if ($u && $u['role'] === 'seller') {
    header('Location: seller/index.php'); exit;
}

// Vérifier si une demande est déjà en cours
$pendingApp = null;
if ($u) {
    $s = db()->prepare("SELECT * FROM seller_applications WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
    $s->execute([$u['id']]);
    $pendingApp = $s->fetch() ?: null;
}

// Frais d'ouverture depuis les paramètres
$feeIndividual  = (int)setting('opening_fee_individual', '5000');
$feeEnterprise  = (int)setting('opening_fee_enterprise', '20000');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn()) { flash('info','Connectez-vous pour soumettre une demande.'); header('Location: login.php?redirect='.urlencode($_SERVER['REQUEST_URI'])); exit; }
    verifyCsrf();

    $businessName = trim($_POST['business_name']??'');
    $description  = trim($_POST['description']??'');
    $sellerType   = $_POST['seller_type']??'';
    $accountType  = $_POST['account_type']??'';

    if (!$businessName)                                      $errors[] = 'Nom de boutique requis';
    if (!$description || strlen($description) < 30)          $errors[] = 'Description trop courte (30 caractères minimum)';
    if (!in_array($sellerType, ['managed','autonomous']))     $errors[] = 'Modèle vendeur requis';
    if (!in_array($accountType, ['individual','enterprise'])) $errors[] = 'Type de compte requis';

    if (!$errors) {
        db()->prepare("INSERT INTO seller_applications(user_id,business_name,description,seller_type,account_type) VALUES(?,?,?,?,?)")
            ->execute([(int)$u['id'], $businessName, $description, $sellerType, $accountType]);
        $success = true;
        $s = db()->prepare("SELECT * FROM seller_applications WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
        $s->execute([$u['id']]);
        $pendingApp = $s->fetch() ?: null;
    }
}
?>
<?php require_once 'includes/header.php'; ?>

<style>
.sv-hero { background:linear-gradient(135deg,#1B2A41,#1a3a5c); color:#fff; padding:4rem 1.25rem; text-align:center }
.sv-hero h1 { font-size:clamp(1.8rem,4vw,2.8rem); font-weight:900; margin-bottom:.75rem }
.sv-hero p  { color:#94a3b8; font-size:1.05rem; max-width:580px; margin:0 auto }
.sv-grid    { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin:1rem 0 0 }
@media(max-width:640px){ .sv-grid{ grid-template-columns:1fr } }
.model-card { border:2px solid var(--border); border-radius:14px; padding:1.75rem; cursor:pointer; transition:all .2s; position:relative }
.model-card.selected { border-color:var(--accent); background:#fff8f5 }
.model-card.selected-blue { border-color:#3b82f6; background:#eff6ff }
.model-card input[type=radio]{ position:absolute; opacity:0 }
.model-card h3  { font-size:1.1rem; font-weight:700; margin-bottom:.5rem }
.model-card p   { font-size:.85rem; color:var(--text-2); line-height:1.65 }
.model-feats    { list-style:none; padding:0; margin:1rem 0 0; display:flex; flex-direction:column; gap:.45rem }
.model-feats li { font-size:.82rem; display:flex; align-items:flex-start; gap:.5rem; color:var(--text) }
.model-feats li svg { flex-shrink:0; margin-top:2px }
.fee-badge   { display:inline-block; border-radius:50px; padding:.3rem .9rem; font-size:.8rem; font-weight:700; margin-top:.9rem }
.fee-badge-ind  { background:#dbeafe; color:#1e40af }
.fee-badge-ent  { background:#ede9fe; color:#5b21b6 }
.commission-badge { display:inline-block; background:var(--accent); color:#fff; border-radius:50px; padding:.2rem .75rem; font-size:.72rem; font-weight:700; margin-top:.75rem }
.status-banner { border-radius:12px; padding:1.25rem 1.5rem; display:flex; align-items:center; gap:1rem; margin:2rem 0 }
.status-pending  { background:#fef3c7; border:2px solid #f59e0b }
.status-approved { background:#d1fae5; border:2px solid #10b981 }
.status-rejected { background:#fee2e2; border:2px solid #ef4444 }
.stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:1.25rem; margin:3rem 0 }
@media(max-width:640px){ .stats-row{ grid-template-columns:1fr } }
.stat-box { text-align:center; padding:1.5rem; background:var(--bg-2); border-radius:12px }
.stat-box-val { font-size:1.8rem; font-weight:900; color:var(--primary) }
.stat-box-lbl { font-size:.78rem; color:var(--text-3); text-transform:uppercase; letter-spacing:.5px; margin-top:.3rem }
.step-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--accent); margin-bottom:.5rem }
.fee-summary { background:#f0f9ff; border:1.5px solid #bae6fd; border-radius:10px; padding:.9rem 1.25rem; margin-top:1.25rem; font-size:.87rem }
.fee-summary strong { color:#0369a1 }
</style>

<!-- Hero -->
<div class="sv-hero">
  <div style="max-width:800px;margin:0 auto">
    <div style="display:inline-block;background:rgba(255,107,43,.15);color:var(--accent);border:1px solid rgba(255,107,43,.3);padding:.35rem 1rem;border-radius:50px;font-size:.75rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:1.25rem">Portail vendeur</div>
    <h1>Vendez sur World Compass,<br>atteignez des milliers d'acheteurs</h1>
    <p>Rejoignez notre réseau de vendeurs partenaires. Choisissez votre type de compte, gérez vos produits depuis votre tableau de bord et développez votre activité.</p>
    <?php if (!isLoggedIn()): ?>
    <div style="display:flex;gap:1rem;justify-content:center;margin-top:1.75rem;flex-wrap:wrap">
      <a href="register.php" class="btn btn-primary btn-lg">Créer un compte gratuit</a>
      <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,.3);color:#e2e8f0">Se connecter</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="container" style="padding-bottom:4rem">

  <!-- Statistiques plateforme -->
  <div class="stats-row">
    <div class="stat-box"><div class="stat-box-val">50K+</div><div class="stat-box-lbl">Acheteurs actifs</div></div>
    <div class="stat-box"><div class="stat-box-val">1 200+</div><div class="stat-box-lbl">Vendeurs partenaires</div></div>
    <div class="stat-box"><div class="stat-box-val">48 h</div><div class="stat-box-lbl">Délai de livraison moyen</div></div>
  </div>

  <?php if ($pendingApp): ?>
    <!-- Statut de la demande -->
    <?php
    $stClass = ['pending'=>'status-pending','approved'=>'status-approved','rejected'=>'status-rejected'][$pendingApp['status']] ?? 'status-pending';
    $stIcons = [
        'pending'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>',
        'approved' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><polyline points="20,6 9,17 4,12"/></svg>',
        'rejected' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    ];
    $stText = [
        'pending'  => 'Votre demande est en cours d\'examen. Nous vous contacterons par email sous 24-48 h.',
        'approved' => 'Votre demande a été approuvée ! Vous pouvez accéder à votre espace vendeur.',
        'rejected' => 'Votre demande n\'a pas été retenue. '.($pendingApp['admin_note'] ? 'Note : '.h($pendingApp['admin_note']) : 'Contactez-nous pour plus d\'informations.'),
    ];
    $accLabel = ($pendingApp['account_type'] ?? 'individual') === 'enterprise' ? 'Compte Entreprise' : 'Compte Individuel';
    ?>
    <div class="status-banner <?= $stClass ?>">
      <?= $stIcons[$pendingApp['status']] ?? '' ?>
      <div>
        <strong><?= $pendingApp['status'] === 'pending' ? 'Demande en cours d\'examen' : ($pendingApp['status'] === 'approved' ? 'Demande approuvée' : 'Demande non retenue') ?></strong>
        <p style="margin:0;font-size:.87rem"><?= $stText[$pendingApp['status']] ?></p>
        <p style="margin:.3rem 0 0;font-size:.8rem;color:var(--text-3)">Type de compte : <strong><?= $accLabel ?></strong></p>
      </div>
      <?php if ($pendingApp['status'] === 'approved'): ?>
        <a href="seller/index.php" class="btn btn-primary" style="margin-left:auto;white-space:nowrap">Accéder à mon espace</a>
      <?php endif; ?>
    </div>

  <?php elseif (!$u || $success): ?>

    <?php if ($success): ?>
    <div class="alert alert-success" style="margin:2rem 0">
      <strong>Demande envoyée !</strong> Notre équipe examine votre dossier et vous contactera sous 24-48 h.
    </div>
    <?php endif; ?>

  <?php else: ?>

    <!-- Formulaire d'inscription vendeur -->
    <div style="max-width:780px;margin:2.5rem auto 0">
      <h2 style="font-size:1.5rem;font-weight:800;margin-bottom:.4rem">Soumettre votre candidature</h2>
      <p style="color:var(--text-2);margin-bottom:2rem">Sélectionnez votre type de compte, puis remplissez votre dossier. Examen sous 24-48 h.</p>

      <?php if ($errors): ?>
        <div class="alert alert-error"><?= implode('<br>',array_map('h',$errors)) ?></div>
      <?php endif; ?>

      <form method="POST" id="sv-form">
        <?= csrfField() ?>

        <!-- ÉTAPE 1 : Type de compte -->
        <div class="form-group">
          <div class="step-label">Étape 1 — Type de compte</div>
          <label class="form-label" style="font-size:1rem;font-weight:700">Choisissez la nature de votre compte *</label>
          <div class="sv-grid">

            <label class="model-card <?= ($_POST['account_type']??'')==='individual'?'selected-blue':'' ?>" id="card-individual" onclick="selectAccount(this,'individual')">
              <input type="radio" name="account_type" value="individual" <?= ($_POST['account_type']??'')==='individual'?'checked':'' ?>>
              <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.6rem">
                <div style="width:38px;height:38px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                </div>
                <h3 style="margin:0">Compte Individuel</h3>
              </div>
              <p>Pour les particuliers, auto-entrepreneurs et micro-vendeurs. Démarrez rapidement avec un abonnement accessible.</p>
              <ul class="model-feats">
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Inscription rapide</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Tableau de bord personnel</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Jusqu'à 50 produits actifs</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Support standard</li>
              </ul>
              <div class="fee-badge fee-badge-ind">Frais d'ouverture : <?= money($feeIndividual) ?></div>
            </label>

            <label class="model-card <?= ($_POST['account_type']??'')==='enterprise'?'selected-blue':'' ?>" id="card-enterprise" onclick="selectAccount(this,'enterprise')">
              <input type="radio" name="account_type" value="enterprise" <?= ($_POST['account_type']??'')==='enterprise'?'checked':'' ?>>
              <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.6rem">
                <div style="width:38px;height:38px;border-radius:50%;background:#ede9fe;display:flex;align-items:center;justify-content:center">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
                </div>
                <h3 style="margin:0">Compte Entreprise</h3>
              </div>
              <p>Pour les sociétés, PME et marques. Accès à toutes les fonctionnalités avancées avec visibilité maximale.</p>
              <ul class="model-feats">
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Produits illimités</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Tableau de bord analytique complet</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Badge "Vendeur certifié"</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Support prioritaire dédié</li>
              </ul>
              <div class="fee-badge fee-badge-ent">Frais d'ouverture : <?= money($feeEnterprise) ?></div>
            </label>

          </div>
          <!-- Récapitulatif frais sélectionnés -->
          <div class="fee-summary" id="fee-summary" style="display:none">
            Frais d'ouverture pour <strong id="fee-account-label">—</strong> : <strong id="fee-amount">—</strong>.
            Ces frais sont à régler lors de la validation de votre dossier par notre équipe.
          </div>
        </div>

        <!-- ÉTAPE 2 : Informations boutique -->
        <div class="form-group" style="margin-top:2rem">
          <div class="step-label">Étape 2 — Informations boutique</div>
          <label class="form-label">Nom de votre boutique / activité *</label>
          <input class="form-control" name="business_name" value="<?= h($_POST['business_name']??'') ?>" required placeholder="ex: Électronique Pro, Boutique Mode Lomé…">
        </div>

        <div class="form-group">
          <label class="form-label">Décrivez votre activité *</label>
          <textarea class="form-control" name="description" rows="4" required placeholder="Quels produits vendez-vous ? Depuis combien de temps ? Quel est votre stock habituel ?"><?= h($_POST['description']??'') ?></textarea>
          <p class="form-hint">Minimum 30 caractères. Soyez précis pour accélérer l'approbation.</p>
        </div>

        <!-- ÉTAPE 3 : Modèle vendeur -->
        <div class="form-group" style="margin-top:2rem">
          <div class="step-label">Étape 3 — Modèle vendeur</div>
          <label class="form-label" style="font-size:1rem;font-weight:700">Choisissez votre modèle de vente *</label>
          <div class="sv-grid">
            <label class="model-card <?= ($_POST['seller_type']??'')==='managed'?'selected':'' ?>" onclick="selectModel(this,'managed')">
              <input type="radio" name="seller_type" value="managed" <?= ($_POST['seller_type']??'')==='managed'?'checked':'' ?>>
              <h3>Plateforme gérée</h3>
              <p>Nous stockons et expédions vos produits depuis nos entrepôts. Idéal si vous débutez ou souhaitez vous concentrer sur vos ventes.</p>
              <ul class="model-feats">
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Stockage entrepôt pris en charge</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Livraison gérée par World Compass</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>SAV et retours pris en charge</li>
              </ul>
              <div class="commission-badge">Commission vendeur : 12% / vente</div>
            </label>

            <label class="model-card <?= ($_POST['seller_type']??'')==='autonomous'?'selected':'' ?>" onclick="selectModel(this,'autonomous')">
              <input type="radio" name="seller_type" value="autonomous" <?= ($_POST['seller_type']??'')==='autonomous'?'checked':'' ?>>
              <h3>Vendeur autonome</h3>
              <p>Vous gérez votre stock et vos expéditions. Vous avez votre propre logistique et souhaitez garder le contrôle total.</p>
              <ul class="model-feats">
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Stock dans vos propres locaux</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Choix de votre transporteur</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>Marge maximale préservée</li>
              </ul>
              <div class="commission-badge" style="background:#10b981">Commission vendeur : 6% / vente</div>
            </label>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="padding:.85rem 2.5rem;font-size:1rem;margin-top:.5rem">
          Envoyer ma candidature
        </button>
        <p style="font-size:.78rem;color:var(--text-3);margin-top:.75rem">En soumettant, vous acceptez les conditions de vente de la plateforme.</p>
      </form>
    </div>

  <?php endif; ?>

</div>

<script>
const FEES = {
    individual: { label: 'Compte Individuel', amount: '<?= money($feeIndividual) ?>' },
    enterprise: { label: 'Compte Entreprise', amount: '<?= money($feeEnterprise) ?>' }
};

function selectAccount(card, val) {
    document.querySelectorAll('[id^="card-individual"],[id^="card-enterprise"]').forEach(c => {
        c.classList.remove('selected-blue');
    });
    card.classList.add('selected-blue');
    card.querySelector('input[type=radio]').checked = true;
    // Mettre à jour le récapitulatif
    const summary = document.getElementById('fee-summary');
    document.getElementById('fee-account-label').textContent = FEES[val].label;
    document.getElementById('fee-amount').textContent = FEES[val].amount;
    summary.style.display = 'block';
}

function selectModel(card, val) {
    document.querySelectorAll('.sv-grid:last-of-type .model-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    card.querySelector('input[type=radio]').checked = true;
}

// Restaurer l'état si POST échoue
(function() {
    const at = '<?= h($_POST['account_type']??'') ?>';
    if (at && FEES[at]) {
        const card = document.getElementById('card-' + at);
        if (card) {
            card.classList.add('selected-blue');
            document.getElementById('fee-account-label').textContent = FEES[at].label;
            document.getElementById('fee-amount').textContent = FEES[at].amount;
            document.getElementById('fee-summary').style.display = 'block';
        }
    }
})();
</script>

<?php require_once 'includes/footer.php'; ?>
