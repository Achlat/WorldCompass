<?php
$adminPage = 'products';
$pageTitle = 'Gestion des Produits';
require_once 'includes/auth.php';

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// Répertoire uploads
$uploadDir = __DIR__.'/../uploads/products/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Gestion du formulaire (création / modification)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $flashPricePost = (float)($_POST['flash_sale_price']??0);
    $flashEndPost   = trim($_POST['flash_sale_end']??'');
    $f = [
        'category_id'      => (int)$_POST['category_id'],
        'name'             => trim($_POST['name']??''),
        'description'      => trim($_POST['description']??''),
        'price'            => (float)$_POST['price'],
        'old_price'        => ($_POST['old_price']!=='' && $_POST['old_price']!==null) ? (float)$_POST['old_price'] : null,
        'stock'            => (int)$_POST['stock'],
        'image_color'      => trim($_POST['image_color']??'#5469d4'),
        'featured'         => isset($_POST['featured']) ? 1 : 0,
        'active'           => isset($_POST['active']) ? 1 : 0,
        'image'            => null,
        'flash_sale_price' => $flashPricePost > 0 ? $flashPricePost : null,
        'flash_sale_end'   => ($flashEndPost && $flashPricePost > 0) ? $flashEndPost : null,
    ];

    // Traitement de l'image uploadée
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5*1024*1024) {
            $fname = 'prod_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir.$fname)) {
                $f['image'] = $fname;
            }
        }
    }

    $pSlug = slug($f['name']);

    if (isset($_POST['create'])) {
        // Vérifier unicité du slug (avec prepared statement)
        $base = $pSlug; $i = 1;
        while (true) {
            $chk = db()->prepare("SELECT id FROM products WHERE slug=?");
            $chk->execute([$pSlug]);
            if (!$chk->fetch()) break;
            $pSlug = $base.'-'.$i++;
        }
        db()->prepare("INSERT INTO products(category_id,name,slug,description,price,old_price,stock,image_color,image,featured,active,flash_sale_price,flash_sale_end)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$f['category_id'],$f['name'],$pSlug,$f['description'],$f['price'],$f['old_price'],
                       $f['stock'],$f['image_color'],$f['image'],$f['featured'],$f['active'],
                       $f['flash_sale_price'],$f['flash_sale_end']]);
        flash('success','Produit créé avec succès.');
    } elseif (isset($_POST['update']) && $id) {
        // Si nouvelle image, supprimer l'ancienne
        if ($f['image']) {
            $old = db()->prepare("SELECT image FROM products WHERE id=?");
            $old->execute([$id]);
            $oldImg = $old->fetchColumn();
            if ($oldImg && file_exists($uploadDir.$oldImg)) unlink($uploadDir.$oldImg);
            db()->prepare("UPDATE products SET category_id=?,name=?,description=?,price=?,old_price=?,stock=?,image_color=?,image=?,featured=?,active=?,flash_sale_price=?,flash_sale_end=? WHERE id=?")
                ->execute([$f['category_id'],$f['name'],$f['description'],$f['price'],$f['old_price'],
                           $f['stock'],$f['image_color'],$f['image'],$f['featured'],$f['active'],
                           $f['flash_sale_price'],$f['flash_sale_end'],$id]);
        } else {
            db()->prepare("UPDATE products SET category_id=?,name=?,description=?,price=?,old_price=?,stock=?,image_color=?,featured=?,active=?,flash_sale_price=?,flash_sale_end=? WHERE id=?")
                ->execute([$f['category_id'],$f['name'],$f['description'],$f['price'],$f['old_price'],
                           $f['stock'],$f['image_color'],$f['featured'],$f['active'],
                           $f['flash_sale_price'],$f['flash_sale_end'],$id]);
        }
        flash('success','Produit mis à jour.');
    }
    header('Location: products.php'); exit;
}

// Suppression (désactivation)
if ($action === 'delete' && $id) {
    db()->prepare("UPDATE products SET active=0 WHERE id=?")->execute([$id]);
    flash('info','Produit désactivé.'); header('Location: products.php'); exit;
}

// Filtres liste
$page    = max(1,(int)($_GET['page']??1));
$perPage = 15;
$offset  = ($page-1)*$perPage;
$catFilter  = (int)($_GET['cat']??0);
$lowStock   = isset($_GET['low_stock']);
$search     = trim($_GET['q']??'');

$where = ['p.active IN (0,1)'];
$params = [];
if ($catFilter) { $where[] = 'p.category_id=?'; $params[] = $catFilter; }
if ($lowStock)  { $where[] = 'p.stock<=5 AND p.active=1'; }
else            { $where[] = 'p.active=1'; }
if ($search)    { $where[] = 'p.name LIKE ?'; $params[] = "%$search%"; }

// Reconstruit where proprement
$where = ['1=1'];
$params = [];
if ($catFilter) { $where[] = 'p.category_id=?'; $params[] = $catFilter; }
if ($lowStock)  { $where[] = 'p.stock<=5'; }
if ($search)    { $where[] = 'p.name LIKE ?'; $params[] = "%$search%"; }
$where[] = 'p.active=1';
$whereSQL = implode(' AND ',$where);

$countStmt = db()->prepare("SELECT COUNT(*) FROM products p WHERE $whereSQL");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$stmt = db()->prepare("SELECT p.*,c.name cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE $whereSQL ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = db()->query("SELECT * FROM categories WHERE parent_id IS NULL AND active=1 ORDER BY sort_order")->fetchAll();

// Données formulaire édition
$editProduct = null;
if ($action === 'edit' && $id) {
    $editProduct = getProductById($id);
}
?>
<?php require_once 'includes/admin_header.php'; ?>

<?php if ($action === 'add' || ($action === 'edit' && $editProduct)): ?>
  <!-- ═══ FORMULAIRE AJOUT / ÉDITION ═══ -->
  <div class="flex-between mb-2">
    <h2 style="font-size:1.1rem;font-weight:700"><?= $action==='add'?'Nouveau produit':'Modifier : '.h($editProduct['name']) ?></h2>
    <a href="products.php" class="btn btn-outline btn-sm">← Retour</a>
  </div>

  <div class="a-card">
    <div class="a-card-body">
      <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Nom du produit *</label>
            <input class="form-control" name="name" value="<?= h($editProduct['name']??'') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Catégorie</label>
            <select class="form-control" name="category_id">
              <option value="0">-- Aucune --</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($editProduct['category_id']??0)==$c['id']?'selected':'' ?>><?= h($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description" rows="4"><?= h($editProduct['description']??'') ?></textarea>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Prix (<?= CURRENCY ?>) *</label>
            <input class="form-control" type="number" name="price" value="<?= h((string)($editProduct['price']??'')) ?>" min="0" step="100" required>
          </div>
          <div class="form-group">
            <label class="form-label">Ancien prix (optionnel)</label>
            <input class="form-control" type="number" name="old_price" value="<?= h((string)($editProduct['old_price']??'')) ?>" min="0" step="100" placeholder="Vide si aucun">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Stock *</label>
            <input class="form-control" type="number" name="stock" value="<?= h((string)($editProduct['stock']??0)) ?>" min="0" required>
          </div>
          <div class="form-group">
            <label class="form-label">Couleur de fond (si pas d'image)</label>
            <div style="display:flex;gap:.5rem;align-items:center">
              <input class="form-control" type="color" name="image_color" value="<?= h($editProduct['image_color']??'#5469d4') ?>" style="width:60px;height:40px;padding:2px">
              <input class="form-control" type="text" id="colorText" value="<?= h($editProduct['image_color']??'#5469d4') ?>" style="flex:1">
            </div>
          </div>
        </div>

        <!-- ─── UPLOAD IMAGE ─── -->
        <div class="form-group">
          <label class="form-label">Photo du produit (JPG, PNG, WEBP – max 5 Mo)</label>
          <?php if (!empty($editProduct['image']) && file_exists($uploadDir.$editProduct['image'])): ?>
            <div style="margin-bottom:.75rem;display:flex;align-items:center;gap:1rem">
              <img src="<?= SITE_URL ?>/uploads/products/<?= h($editProduct['image']) ?>" alt="Image actuelle"
                   style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--a-border)">
              <span style="font-size:.8rem;color:var(--a-text3)">Image actuelle – choisissez-en une nouvelle pour la remplacer</span>
            </div>
          <?php endif; ?>
          <div style="border:2px dashed var(--a-border);border-radius:8px;padding:1.25rem;text-align:center;background:#fafbfc;cursor:pointer;transition:border-color .2s" id="dropZone">
            <div style="font-size:.85rem;color:var(--a-text2);margin-bottom:.5rem">Cliquez pour choisir une photo depuis votre ordinateur</div>
            <div style="font-size:.75rem;color:var(--a-text3)">ou glissez-déposez ici</div>
            <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp,image/gif"
                   style="position:absolute;opacity:0;width:100%;height:100%;top:0;left:0;cursor:pointer">
          </div>
          <div id="imagePreview" style="display:none;margin-top:.75rem">
            <img id="previewImg" style="max-width:200px;max-height:200px;border-radius:8px;object-fit:cover;border:1px solid var(--a-border)">
            <div id="previewName" style="font-size:.78rem;color:var(--a-text3);margin-top:.35rem"></div>
          </div>
        </div>

        <div style="display:flex;gap:2rem;margin-bottom:1.25rem">
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.88rem">
            <input type="checkbox" name="featured" <?= ($editProduct['featured']??0)?'checked':'' ?>>
            Produit populaire
          </label>
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.88rem">
            <input type="checkbox" name="active" <?= ($editProduct['active']??1)?'checked':'checked' ?>>
            Actif (visible)
          </label>
        </div>

        <!-- Vente flash -->
        <div style="border:1.5px solid #fecaca;border-radius:10px;padding:1.15rem;margin-bottom:1.25rem;background:#fff5f5">
          <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.9rem;font-weight:700;font-size:.88rem;color:#dc2626">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="#dc2626"><polygon points="13,2 3,14 12,14 11,22 21,10 12,10"/></svg>
            Vente Flash (optionnel)
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div>
              <label class="form-label">Prix flash (FCFA)</label>
              <input class="form-control" type="number" name="flash_sale_price" min="0" step="0.01"
                     value="<?= h((string)($editProduct['flash_sale_price']??'')) ?>" placeholder="Ex: 25000">
            </div>
            <div>
              <label class="form-label">Date de fin</label>
              <input class="form-control" type="datetime-local" name="flash_sale_end"
                     value="<?= h($editProduct['flash_sale_end'] ? date('Y-m-d\TH:i', strtotime($editProduct['flash_sale_end'])) : '') ?>">
            </div>
          </div>
          <p style="font-size:.76rem;color:#dc2626;margin-top:.5rem">Laissez vide pour désactiver la vente flash. Le prix flash sera affiché à la place du prix normal tant que la date n'est pas dépassée.</p>
        </div>

        <div style="display:flex;gap:.75rem">
          <button type="submit" name="<?= $action==='add'?'create':'update' ?>" class="btn btn-primary">
            <?= $action==='add'?'Créer le produit':'Enregistrer les modifications' ?>
          </button>
          <a href="products.php" class="btn btn-outline">Annuler</a>
        </div>
      </form>
    </div>
  </div>

  <style>
    #dropZone { position:relative }
    #dropZone:hover, #dropZone.drag-over { border-color:var(--a-accent); background:#fff8f5 }
  </style>
  <script>
  const dropZone   = document.getElementById('dropZone');
  const imageInput = document.getElementById('imageInput');
  const preview    = document.getElementById('imagePreview');
  const previewImg = document.getElementById('previewImg');
  const previewName= document.getElementById('previewName');
  const colorPicker= document.querySelector('input[type=color][name=image_color]');
  const colorText  = document.getElementById('colorText');

  // Sync color inputs
  if (colorPicker && colorText) {
    colorPicker.addEventListener('input', ()=> colorText.value = colorPicker.value);
    colorText.addEventListener('input',   ()=> colorPicker.value = colorText.value);
  }

  // Image preview
  imageInput.addEventListener('change', handleFile);
  dropZone.addEventListener('dragover',  e=>{ e.preventDefault(); dropZone.classList.add('drag-over'); });
  dropZone.addEventListener('dragleave', ()=> dropZone.classList.remove('drag-over'));
  dropZone.addEventListener('drop', e=>{ e.preventDefault(); dropZone.classList.remove('drag-over'); imageInput.files = e.dataTransfer.files; handleFile(); });

  function handleFile() {
    const f = imageInput.files[0];
    if (!f) return;
    const reader = new FileReader();
    reader.onload = e => {
      previewImg.src = e.target.result;
      previewName.textContent = f.name + ' (' + (f.size/1024).toFixed(0) + ' ko)';
      preview.style.display = 'block';
    };
    reader.readAsDataURL(f);
  }
  </script>

<?php else: ?>
  <!-- ═══ LISTE DES PRODUITS ═══ -->
  <div class="flex-between mb-2">
    <h2 style="font-size:1.1rem;font-weight:700">Produits (<?= $total ?>)</h2>
    <a href="products.php?action=add" class="btn btn-primary">+ Nouveau produit</a>
  </div>

  <!-- Filtres -->
  <div class="a-card mb-2">
    <div class="a-card-body" style="padding:.75rem 1.25rem">
      <form method="GET" action="products.php" style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
        <input class="form-control" name="q" value="<?= h($search) ?>" placeholder="Rechercher…" style="width:200px">
        <select class="form-control" name="cat" style="width:auto" onchange="this.form.submit()">
          <option value="0">Toutes catégories</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $catFilter===$c['id']?'selected':'' ?>><?= h($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;cursor:pointer">
          <input type="checkbox" name="low_stock" <?= $lowStock?'checked':'' ?> onchange="this.form.submit()">
          Stock faible
        </label>
        <button type="submit" class="btn btn-secondary btn-sm">Rechercher</button>
        <a href="products.php" class="btn btn-outline btn-sm">Réinitialiser</a>
      </form>
    </div>
  </div>

  <div class="a-card">
    <div class="table-wrap">
      <table class="a-table">
        <thead>
          <tr><th>#</th><th>Produit</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td style="color:var(--a-text3)"><?= $p['id'] ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:.75rem">
                  <?php if (!empty($p['image']) && file_exists($uploadDir.$p['image'])): ?>
                    <img src="<?= SITE_URL ?>/uploads/products/<?= h($p['image']) ?>" alt=""
                         style="width:40px;height:40px;object-fit:cover;border-radius:8px;flex-shrink:0">
                  <?php else: ?>
                    <div style="width:40px;height:40px;border-radius:8px;background:linear-gradient(135deg,<?= h($p['image_color']) ?>,<?= h($p['image_color']) ?>99);flex-shrink:0;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.8)">
                      <?= str_replace('width="56" height="56"','width="22" height="22"', categoryIcon($p['cat_name']??'')) ?>
                    </div>
                  <?php endif; ?>
                  <div>
                    <div style="font-weight:600;font-size:.85rem"><?= h($p['name']) ?></div>
                    <?php if ($p['featured']): ?><span style="font-size:.7rem;color:var(--a-warning)">Populaire</span><?php endif; ?>
                  </div>
                </div>
              </td>
              <td><span style="font-size:.8rem;color:var(--a-text3)"><?= h($p['cat_name']??'—') ?></span></td>
              <td>
                <span style="font-weight:700"><?= money((float)$p['price']) ?></span>
                <?php if ($p['old_price']): ?><br><span style="font-size:.72rem;text-decoration:line-through;color:var(--a-text3)"><?= money((float)$p['old_price']) ?></span><?php endif; ?>
              </td>
              <td>
                <span style="font-weight:700;color:<?= $p['stock']<=5?'var(--a-danger)':'var(--a-success)' ?>"><?= $p['stock'] ?></span>
                <?php if ($p['stock']<=5): ?><br><span style="font-size:.7rem;color:var(--a-danger)">Faible</span><?php endif; ?>
              </td>
              <td><span class="status <?= $p['active']?'status-active':'status-inactive' ?>"><?= $p['active']?'Actif':'Inactif' ?></span></td>
              <td>
                <div style="display:flex;gap:.4rem">
                  <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">Modifier</a>
                  <a href="<?= SITE_URL ?>/product.php?slug=<?= h($p['slug']) ?>" target="_blank" class="btn btn-outline btn-sm">Voir</a>
                  <a href="products.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" data-confirm="Désactiver ce produit ?">Désactiver</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$products): ?>
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--a-text3)">Aucun produit trouvé.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  <?php if ($total > $perPage): ?>
    <div class="pagination">
      <?php $url = 'products.php?q='.urlencode($search).'&cat='.$catFilter; ?>
      <?php for ($i=1;$i<=ceil($total/$perPage);$i++): ?>
        <a href="<?= $url ?>&page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
