<?php
require_once '../includes/functions.php';
requireLogin();
requireSeller();
$pageTitle = 'Mes produits';
$u = currentUser();
$errors  = [];
$action  = $_GET['action'] ?? 'list';
$editId  = (int)($_GET['id'] ?? 0);
$cats    = getCategories();

// AJAX add-to-cart ignoré ici — on inclut les fonctions pour les helpers

// Save/update product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    verifyCsrf();
    $name  = trim($_POST['name']??'');
    $desc  = trim($_POST['description']??'');
    $price = (float)($_POST['price']??0);
    $oldp  = (float)($_POST['old_price']??0);
    $stock = (int)($_POST['stock']??0);
    $catId = (int)($_POST['category_id']??0);
    $color = preg_replace('/[^a-fA-F0-9#]/','',$_POST['image_color']??'#5469d4');
    $featured  = isset($_POST['featured']) ? 1 : 0;
    $flashPrice= (float)($_POST['flash_sale_price']??0);
    $flashEnd  = trim($_POST['flash_sale_end']??'');

    if (!$name)   $errors[] = 'Nom du produit requis';
    if ($price<0) $errors[] = 'Prix invalide';

    // Upload image
    $imageFile = null;
    if (!empty($_FILES['image']['name'])) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($_FILES['image']['tmp_name']);
        if (!in_array($mime,['image/jpeg','image/png','image/webp','image/gif'])) {
            $errors[] = 'Format image invalide (JPG, PNG, WebP)';
        } else {
            $ext  = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageFile = 'p_'.time().'_'.uniqid().'.'.$ext;
            $uploadDir = __DIR__.'/../uploads/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir.$imageFile)) {
                $errors[] = 'Erreur lors du téléversement';
                $imageFile = null;
            }
        }
    }

    if (!$errors) {
        $pSlug = slug($name);
        // Unicité du slug
        $exists = db()->prepare("SELECT id FROM products WHERE slug=? AND id!=?");
        $exists->execute([$pSlug, $editId]);
        if ($exists->fetch()) $pSlug .= '-'.substr(uniqid(),-4);

        $flashPriceVal = $flashPrice > 0 ? $flashPrice : null;
        $flashEndVal   = ($flashEnd && $flashPrice > 0) ? $flashEnd : null;

        if ($editId) {
            // Modifier
            $product = db()->prepare("SELECT * FROM products WHERE id=? AND seller_id=?");
            $product->execute([$editId, $u['id']]);
            $existing = $product->fetch();
            if (!$existing) { flash('error','Produit introuvable.'); header('Location: products.php'); exit; }

            $currentImage = $imageFile ?? $existing['image'];
            db()->prepare("UPDATE products SET name=?,slug=?,description=?,price=?,old_price=?,stock=?,category_id=?,image_color=?,image=?,featured=?,flash_sale_price=?,flash_sale_end=? WHERE id=? AND seller_id=?")
                ->execute([$name,$pSlug,$desc,$price,$oldp?:null,$stock,$catId?:null,$color,$currentImage,$featured,$flashPriceVal,$flashEndVal,$editId,$u['id']]);
            flash('success','Produit mis à jour.');
        } else {
            // Créer
            db()->prepare("INSERT INTO products(seller_id,category_id,name,slug,description,price,old_price,stock,image_color,image,featured,flash_sale_price,flash_sale_end) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([$u['id'],$catId?:null,$name,$pSlug,$desc,$price,$oldp?:null,$stock,$color,$imageFile,$featured,$flashPriceVal,$flashEndVal]);
            flash('success','Produit ajouté avec succès.');
        }
        header('Location: products.php'); exit;
    }
}

// Delete product
if (isset($_GET['delete']) && $_GET['delete']) {
    $pid = (int)$_GET['delete'];
    db()->prepare("DELETE FROM products WHERE id=? AND seller_id=?")->execute([$pid, $u['id']]);
    flash('success','Produit supprimé.');
    header('Location: products.php'); exit;
}

// Load for edit
$editProduct = null;
if ($action === 'edit' && $editId) {
    $s = db()->prepare("SELECT * FROM products WHERE id=? AND seller_id=?");
    $s->execute([$editId, $u['id']]);
    $editProduct = $s->fetch() ?: null;
    if (!$editProduct) { flash('error','Produit introuvable.'); header('Location: products.php'); exit; }
}

$products = getSellerProducts((int)$u['id']);
?>
<?php require_once '../includes/header.php'; ?>

<style>
.seller-layout{display:grid;grid-template-columns:220px 1fr;gap:0;min-height:70vh}
@media(max-width:768px){.seller-layout{grid-template-columns:1fr}}
.seller-sidebar{background:#1B2A41;color:#e2e8f0;padding:1.75rem 0;min-height:70vh}
.seller-sidebar-brand{padding:.5rem 1.5rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:1rem}
.seller-sidebar-brand h3{font-size:.9rem;font-weight:800;color:#fff;margin:0}
.seller-sidebar-brand p{font-size:.72rem;color:#64748b;margin:.2rem 0 0}
.seller-nav a{display:flex;align-items:center;gap:.7rem;padding:.72rem 1.5rem;color:#94a3b8;font-size:.85rem;font-weight:500;transition:all .15s}
.seller-nav a:hover,.seller-nav a.active{background:rgba(255,255,255,.07);color:#fff}
.seller-nav a.active{border-right:3px solid var(--accent)}
.seller-main{padding:2rem 2rem 3rem;background:var(--bg-2)}
.scard{background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.06);overflow:hidden;margin-bottom:1.5rem}
.scard-hdr{padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.scard-hdr h3{font-size:.95rem;font-weight:700;margin:0}
</style>

<div class="seller-layout">
  <aside class="seller-sidebar">
    <div class="seller-sidebar-brand">
      <h3><?= h($u['business_name'] ?: $u['firstname'].' '.$u['lastname']) ?></h3>
      <p>Vendeur <?= $u['seller_type'] === 'managed' ? 'plateforme' : ($u['seller_type'] === 'autonomous' ? 'autonome' : 'partenaire') ?></p>
    </div>
    <nav class="seller-nav">
      <a href="index.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Tableau de bord
      </a>
      <a href="products.php" class="active">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8l5 5-5 5"/></svg>
        Mes produits
      </a>
      <a href="orders.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
        Commandes
      </a>
      <a href="commissions.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Commissions
      </a>
    </nav>
  </aside>

  <main class="seller-main">

    <?php if ($action === 'list'): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
      <h1 style="font-size:1.3rem;font-weight:800;margin:0">Mes produits (<?= count($products) ?>)</h1>
      <a href="products.php?action=add" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:-.1em;margin-right:4px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Ajouter un produit
      </a>
    </div>

    <?php if ($products): ?>
    <div class="scard">
      <div class="scard-body" style="padding:0">
        <table class="a-table" style="margin:0">
          <thead><tr>
            <th>Produit</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Flash</th><th>Actions</th>
          </tr></thead>
          <tbody>
          <?php foreach ($products as $p): ?>
            <?php
            $isFlash = !empty($p['flash_sale_price']) && !empty($p['flash_sale_end']) && strtotime($p['flash_sale_end']) > time();
            ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:.75rem">
                  <div style="width:40px;height:40px;border-radius:8px;background:<?= h($p['image_color']??'#5469d4') ?>;flex-shrink:0;overflow:hidden">
                    <?php if (!empty($p['image']) && file_exists('../uploads/products/'.$p['image'])): ?>
                      <img src="<?= SITE_URL ?>/uploads/products/<?= h($p['image']) ?>" style="width:100%;height:100%;object-fit:cover">
                    <?php endif; ?>
                  </div>
                  <div>
                    <div style="font-weight:600;font-size:.85rem"><?= h($p['name']) ?></div>
                    <?php if (!$p['active']): ?><span style="font-size:.7rem;color:var(--danger)">Désactivé</span><?php endif; ?>
                  </div>
                </div>
              </td>
              <td style="font-size:.82rem;color:var(--text-2)"><?= h($p['cat_name']??'—') ?></td>
              <td style="font-size:.84rem;font-weight:600">
                <?= money((float)$p['price']) ?>
                <?php if ($p['old_price']): ?><br><span style="font-size:.74rem;color:var(--text-3);text-decoration:line-through"><?= money((float)$p['old_price']) ?></span><?php endif; ?>
              </td>
              <td>
                <span style="color:<?= $p['stock']<5?'var(--danger)':'inherit' ?>;font-weight:<?= $p['stock']<5?'700':'400' ?>">
                  <?= $p['stock'] ?>
                </span>
              </td>
              <td>
                <?php if ($isFlash): ?>
                  <span style="display:inline-block;background:#fee2e2;color:#dc2626;border-radius:50px;padding:.15rem .6rem;font-size:.7rem;font-weight:700">FLASH</span>
                <?php else: ?>
                  <span style="font-size:.76rem;color:var(--text-3)">—</span>
                <?php endif; ?>
              </td>
              <td>
                <div style="display:flex;gap:.4rem">
                  <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm" style="padding:.3rem .65rem;font-size:.77rem">Modifier</a>
                  <a href="<?= SITE_URL ?>/product.php?slug=<?= h($p['slug']) ?>" target="_blank" class="btn btn-sm btn-outline" style="padding:.3rem .65rem;font-size:.77rem">Voir</a>
                  <a href="products.php?delete=<?= $p['id'] ?>" onclick="return confirm('Supprimer ce produit ?')" class="btn btn-sm" style="padding:.3rem .65rem;font-size:.77rem;background:#fee2e2;color:#dc2626;border:none">Suppr.</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php else: ?>
      <div class="scard"><div style="padding:3rem;text-align:center;color:var(--text-3)">
        Aucun produit. <a href="products.php?action=add">Créez votre premier produit</a>.
      </div></div>
    <?php endif; ?>

    <?php else: // Formulaire add/edit ?>

    <div style="margin-bottom:1.5rem">
      <a href="products.php" style="font-size:.85rem;color:var(--text-2)">← Mes produits</a>
      <h1 style="font-size:1.3rem;font-weight:800;margin:.5rem 0 0"><?= $editId ? 'Modifier le produit' : 'Ajouter un produit' ?></h1>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-error"><?= implode('<br>',array_map('h',$errors)) ?></div>
    <?php endif; ?>

    <div class="scard">
      <div class="scard-body">
        <form method="POST" enctype="multipart/form-data">
          <?= csrfField() ?>
          <input type="hidden" name="save_product" value="1">
          <?php $ep = $editProduct; ?>

          <div class="form-grid">
            <div class="form-group" style="grid-column:span 2">
              <label class="form-label">Nom du produit *</label>
              <input class="form-control" name="name" value="<?= h($ep?$ep['name']:($_POST['name']??'')) ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4" placeholder="Décrivez votre produit : caractéristiques, matériaux, dimensions…"><?= h($ep?$ep['description']:($_POST['description']??'')) ?></textarea>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Prix de vente (FCFA) *</label>
              <input class="form-control" type="number" name="price" value="<?= h((string)($ep?$ep['price']:($_POST['price']??''))) ?>" required min="0" step="0.01">
            </div>
            <div class="form-group">
              <label class="form-label">Ancien prix (barré, optionnel)</label>
              <input class="form-control" type="number" name="old_price" value="<?= h((string)($ep?$ep['old_price']:($_POST['old_price']??''))) ?>" min="0" step="0.01">
            </div>
            <div class="form-group">
              <label class="form-label">Stock disponible</label>
              <input class="form-control" type="number" name="stock" value="<?= h((string)($ep?$ep['stock']:($_POST['stock']??'0'))) ?>" min="0">
            </div>
            <div class="form-group">
              <label class="form-label">Catégorie</label>
              <select class="form-control" name="category_id">
                <option value="">— Choisir —</option>
                <?php foreach ($cats as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= (($ep?$ep['category_id']:($_POST['category_id']??''))==$c['id'])?'selected':'' ?>><?= h($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Image -->
          <div class="form-group">
            <label class="form-label">Photo du produit</label>
            <?php if ($ep && $ep['image'] && file_exists('../uploads/products/'.$ep['image'])): ?>
              <div style="margin-bottom:.75rem">
                <img src="<?= SITE_URL ?>/uploads/products/<?= h($ep['image']) ?>" style="height:80px;border-radius:8px;object-fit:cover">
              </div>
            <?php endif; ?>
            <div class="upload-zone" id="uploadZone" onclick="document.getElementById('imgFile').click()">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
              <p style="margin:.5rem 0 0;font-size:.84rem;color:var(--text-3)">Cliquez ou glissez une photo<br><span style="font-size:.75rem">JPG, PNG, WebP – max 5 Mo</span></p>
              <img id="imgPreview" style="max-height:100px;border-radius:8px;margin-top:.75rem;display:none">
            </div>
            <input type="file" id="imgFile" name="image" accept="image/*" style="display:none" onchange="previewImg(this)">
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Couleur de fond (si pas de photo)</label>
              <div style="display:flex;align-items:center;gap:.75rem">
                <input type="color" name="image_color" value="<?= h($ep?$ep['image_color']:($_POST['image_color']??'#5469d4')) ?>" style="width:50px;height:38px;border:none;border-radius:8px;cursor:pointer">
                <input class="form-control" type="text" id="colorHex" value="<?= h($ep?$ep['image_color']:($_POST['image_color']??'#5469d4')) ?>" style="max-width:100px" readonly>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                <input type="checkbox" name="featured" value="1" <?= ($ep?$ep['featured']:($_POST['featured']??0))?'checked':'' ?>>
                Mettre en avant (produit populaire)
              </label>
            </div>
          </div>

          <!-- Vente flash -->
          <div style="border:1.5px solid #fee2e2;border-radius:10px;padding:1.25rem;margin-top:.5rem">
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#dc2626"><polygon points="13,2 3,14 12,14 11,22 21,10 12,10"/></svg>
              <strong style="font-size:.9rem;color:#dc2626">Vente Flash (optionnel)</strong>
            </div>
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Prix flash (FCFA)</label>
                <input class="form-control" type="number" name="flash_sale_price" value="<?= h((string)($ep?$ep['flash_sale_price']:($_POST['flash_sale_price']??''))) ?>" min="0" step="0.01" placeholder="Prix soldé">
              </div>
              <div class="form-group">
                <label class="form-label">Date de fin de la vente flash</label>
                <input class="form-control" type="datetime-local" name="flash_sale_end" value="<?= h($ep&&$ep['flash_sale_end']?date('Y-m-d\TH:i',strtotime($ep['flash_sale_end'])):($_POST['flash_sale_end']??'')) ?>">
              </div>
            </div>
          </div>

          <div style="display:flex;gap:1rem;margin-top:1.5rem">
            <button type="submit" class="btn btn-primary"><?= $editId ? 'Enregistrer les modifications' : 'Publier le produit' ?></button>
            <a href="products.php" class="btn btn-outline">Annuler</a>
          </div>
        </form>
      </div>
    </div>

    <?php endif; ?>
  </main>
</div>

<style>
.upload-zone{border:2px dashed var(--border);border-radius:10px;padding:2rem;text-align:center;cursor:pointer;transition:border-color .2s}
.upload-zone:hover{border-color:var(--accent)}
input[type=color]{padding:2px}
</style>
<script>
function previewImg(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById('imgPreview');
    img.src = e.target.result;
    img.style.display = 'block';
  };
  reader.readAsDataURL(file);
}
document.querySelector('input[type=color]')?.addEventListener('input', function(){
  document.getElementById('colorHex').value = this.value;
});
const zone = document.getElementById('uploadZone');
if (zone) {
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor='var(--accent)' });
  zone.addEventListener('dragleave', () => { zone.style.borderColor='' });
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.style.borderColor='';
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
      document.getElementById('imgFile').files = e.dataTransfer.files;
      const reader = new FileReader();
      reader.onload = ev => {
        const img = document.getElementById('imgPreview');
        img.src = ev.target.result; img.style.display = 'block';
      };
      reader.readAsDataURL(file);
    }
  });
}
</script>

<?php require_once '../includes/footer.php'; ?>
