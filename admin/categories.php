<?php
$adminPage = 'categories';
$pageTitle = 'Catégories';
require_once 'includes/auth.php';

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name  = trim($_POST['name']??'');
    $desc  = trim($_POST['description']??'');
    $icon  = trim($_POST['icon']??'');
    $color = trim($_POST['color']??'#5469d4');
    $sort  = (int)($_POST['sort_order']??0);
    $active= isset($_POST['active'])?1:0;
    $cSlug = slug($name);

    if (isset($_POST['create']) && $name) {
        db()->prepare("INSERT INTO categories(name,slug,description,icon,color,sort_order,active) VALUES(?,?,?,?,?,?,?)")
            ->execute([$name,$cSlug,$desc,$icon,$color,$sort,$active]);
        flash('success','Catégorie créée.');
    } elseif (isset($_POST['update']) && $id && $name) {
        db()->prepare("UPDATE categories SET name=?,description=?,icon=?,color=?,sort_order=?,active=? WHERE id=?")
            ->execute([$name,$desc,$icon,$color,$sort,$active,$id]);
        flash('success','Catégorie mise à jour.');
    }
    header('Location: categories.php'); exit;
}

if ($action === 'delete' && $id) {
    db()->prepare("UPDATE categories SET active=0 WHERE id=?")->execute([$id]);
    flash('info','Catégorie désactivée.'); header('Location: categories.php'); exit;
}

$categories  = db()->query("SELECT c.*,(SELECT COUNT(*) FROM products WHERE category_id=c.id AND active=1) cnt FROM categories c WHERE c.parent_id IS NULL ORDER BY c.sort_order,c.name")->fetchAll();
$editCat     = ($action==='edit'&&$id) ? getCategoryById($id??0) : null;

function getCategoryById(int $id): ?array {
    $s = db()->prepare("SELECT * FROM categories WHERE id=?");
    $s->execute([$id]);
    return $s->fetch() ?: null;
}
if ($action==='edit'&&$id) $editCat = getCategoryById($id);
?>
<?php require_once 'includes/admin_header.php'; ?>

<?php if ($action === 'add' || ($action === 'edit' && $editCat)): ?>
  <div class="flex-between mb-2">
    <h2 style="font-size:1.1rem;font-weight:700"><?= $action==='add'?'Nouvelle catégorie':'Modifier : '.h($editCat['name']) ?></h2>
    <a href="categories.php" class="btn btn-outline btn-sm">← Retour</a>
  </div>
  <div class="a-card" style="max-width:560px">
    <div class="a-card-body">
      <form method="POST">
        <?= csrfField() ?>
        <div class="form-group">
          <label class="form-label">Nom *</label>
          <input class="form-control" name="name" value="<?= h($editCat['name']??'') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description"><?= h($editCat['description']??'') ?></textarea>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Icône (texte libre)</label>
            <input class="form-control" name="icon" value="<?= h($editCat['icon']??'') ?>" placeholder="ex: Electronique">
          </div>
          <div class="form-group">
            <label class="form-label">Couleur</label>
            <div style="display:flex;gap:.5rem">
              <input class="form-control" type="color" name="color" value="<?= h($editCat['color']??'#5469d4') ?>" style="width:60px;height:40px;padding:2px">
              <input class="form-control" type="text" value="<?= h($editCat['color']??'#5469d4') ?>" id="catColorTxt" style="flex:1">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Ordre d'affichage</label>
          <input class="form-control" type="number" name="sort_order" value="<?= h($editCat['sort_order']??0) ?>" min="0" style="width:100px">
        </div>
        <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;cursor:pointer;font-size:.88rem">
          <input type="checkbox" name="active" <?= ($editCat['active']??1)?'checked':'checked' ?>> Active (visible)
        </label>
        <div style="display:flex;gap:.75rem">
          <button type="submit" name="<?= $action==='add'?'create':'update' ?>" class="btn btn-primary">
            <?= $action==='add'?'Créer':'Enregistrer' ?>
          </button>
          <a href="categories.php" class="btn btn-outline">Annuler</a>
        </div>
      </form>
    </div>
  </div>
  <script>
  const cp2=document.querySelector('input[type=color][name=color]');
  const ct2=document.getElementById('catColorTxt');
  if(cp2&&ct2){cp2.addEventListener('input',()=>ct2.value=cp2.value);ct2.addEventListener('input',()=>cp2.value=ct2.value);}
  </script>

<?php else: ?>
  <div class="flex-between mb-2">
    <h2 style="font-size:1.1rem;font-weight:700">Catégories (<?= count($categories) ?>)</h2>
    <a href="categories.php?action=add" class="btn btn-primary">Nouvelle catégorie</a>
  </div>
  <div class="a-card">
    <div class="table-wrap">
      <table class="a-table">
        <thead><tr><th>Catégorie</th><th>Produits</th><th>Ordre</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($categories as $c): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:.75rem">
                  <div style="width:36px;height:36px;border-radius:8px;background:<?= h($c['color']) ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.7rem;font-weight:700">
                    <?= mb_strtoupper(mb_substr(h($c['name']),0,2)) ?>
                  </div>
                  <div>
                    <div style="font-weight:600"><?= h($c['name']) ?></div>
                    <div class="text-muted">/<?= h($c['slug']) ?></div>
                  </div>
                </div>
              </td>
              <td><span style="font-weight:600"><?= $c['cnt'] ?></span> produit(s)</td>
              <td><?= $c['sort_order'] ?></td>
              <td><span class="status <?= $c['active']?'status-active':'status-inactive' ?>"><?= $c['active']?'Active':'Inactive' ?></span></td>
              <td style="display:flex;gap:.4rem">
                <a href="categories.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">Modifier</a>
                <a href="categories.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-danger btn-sm" data-confirm="Désactiver '<?= h($c['name']) ?>' ?">Désactiver</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
