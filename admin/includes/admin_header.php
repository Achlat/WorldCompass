<?php
$adminSite = setting('site_name', SITE_NAME);
$adminUser = currentUser();
$adminPage = $adminPage ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= isset($pageTitle) ? h($pageTitle).' – Admin' : 'Administration – '.$adminSite ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/*  Admin Design System  */
:root {
  --a-bg:      #0f172a;
  --a-sidebar: #1e293b;
  --a-card:    #ffffff;
  --a-border:  #e2e8f0;
  --a-text:    #0f172a;
  --a-text2:   #475569;
  --a-text3:   #94a3b8;
  --a-accent:  #FF6B2B;
  --a-blue:    #5469d4;
  --a-success: #10b981;
  --a-danger:  #ef4444;
  --a-warning: #f59e0b;
  --a-radius:  10px;
  --a-shadow:  0 2px 12px rgba(0,0,0,.07);
  --sidebar-w: 250px;
  --font: 'Inter',system-ui,sans-serif;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{font-size:14px}
body{font-family:var(--font);background:#f1f5f9;color:var(--a-text);display:flex;min-height:100vh}
a{color:inherit;text-decoration:none}
ul{list-style:none}
button{cursor:pointer;font-family:inherit;border:none}
input,select,textarea{font-family:inherit}

/*  Sidebar  */
.a-sidebar{
  width:var(--sidebar-w);flex-shrink:0;
  background:var(--a-sidebar);
  min-height:100vh;
  display:flex;flex-direction:column;
  position:sticky;top:0;height:100vh;overflow-y:auto;
}
.a-logo{
  padding:1.5rem 1.25rem;
  font-size:1.2rem;font-weight:800;
  color:#fff;
  border-bottom:1px solid rgba(255,255,255,.07);
  display:flex;align-items:center;gap:.6rem;
}
.a-logo span{color:var(--a-accent)}
.a-nav{padding:.75rem 0;flex:1}
.a-nav-section{padding:.4rem 1.25rem .2rem;font-size:.65rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-top:.5rem}
.a-nav a{
  display:flex;align-items:center;gap:.75rem;
  padding:.65rem 1.25rem;
  color:#94a3b8;font-size:.88rem;font-weight:500;
  transition:all .2s;border-left:3px solid transparent;
}
.a-nav a:hover{background:rgba(255,255,255,.05);color:#fff}
.a-nav a.active{background:rgba(255,107,43,.12);color:var(--a-accent);border-left-color:var(--a-accent);font-weight:600}
.a-nav-icon{font-size:1rem;width:20px;text-align:center}
.a-sidebar-footer{padding:1rem 1.25rem;border-top:1px solid rgba(255,255,255,.07)}
.a-sidebar-footer a{display:flex;align-items:center;gap:.5rem;color:#64748b;font-size:.82rem;transition:color .2s}
.a-sidebar-footer a:hover{color:var(--a-accent)}

/*  Main area  */
.a-main{flex:1;display:flex;flex-direction:column;min-width:0}

/*  Topbar  */
.a-topbar{
  background:#fff;
  border-bottom:1px solid var(--a-border);
  padding:0 1.5rem;
  height:60px;
  display:flex;align-items:center;justify-content:space-between;
  position:sticky;top:0;z-index:100;
  box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.a-topbar-title{font-size:1.1rem;font-weight:700;color:var(--a-text)}
.a-topbar-actions{display:flex;align-items:center;gap:.75rem}
.a-topbar-btn{
  display:flex;align-items:center;gap:.4rem;
  padding:.4rem .9rem;border-radius:6px;font-size:.8rem;font-weight:600;
  background:#f8fafc;border:1px solid var(--a-border);color:var(--a-text2);
  transition:all .2s;
}
.a-topbar-btn:hover{background:var(--a-accent);color:#fff;border-color:var(--a-accent)}
.a-content{padding:1.5rem;flex:1}

/*  Utility  */
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem}
.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem}
.grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem}
.flex{display:flex;align-items:center}
.flex-between{display:flex;align-items:center;justify-content:space-between}
.text-muted{color:var(--a-text3);font-size:.82rem}
.mt-1{margin-top:.5rem}.mt-2{margin-top:1rem}.mt-3{margin-top:1.5rem}
.mb-1{margin-bottom:.5rem}.mb-2{margin-bottom:1rem}.mb-3{margin-bottom:1.5rem}
.fw-700{font-weight:700}

/*  Cards  */
.a-card{background:#fff;border-radius:var(--a-radius);box-shadow:var(--a-shadow);overflow:hidden}
.a-card-header{padding:1rem 1.25rem;border-bottom:1px solid var(--a-border);display:flex;align-items:center;justify-content:space-between;font-weight:700;font-size:.95rem}
.a-card-body{padding:1.25rem}

/*  Stat card  */
.stat-card{background:#fff;border-radius:var(--a-radius);box-shadow:var(--a-shadow);padding:1.25rem;display:flex;align-items:center;gap:1.1rem}
.stat-icon{width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0}
.stat-label{font-size:.78rem;color:var(--a-text3);font-weight:600;text-transform:uppercase;letter-spacing:.4px}
.stat-value{font-size:1.6rem;font-weight:800;margin:.1rem 0}
.stat-sub{font-size:.72rem;color:var(--a-text3)}

/*  Buttons  */
.btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;border-radius:6px;font-weight:600;font-size:.82rem;cursor:pointer;border:none;transition:all .2s;white-space:nowrap}
.btn-primary{background:var(--a-accent);color:#fff}
.btn-primary:hover{background:#e55b1f}
.btn-secondary{background:var(--a-blue);color:#fff}
.btn-secondary:hover{background:#4256c0}
.btn-danger{background:var(--a-danger);color:#fff}
.btn-danger:hover{background:#dc2626}
.btn-success{background:var(--a-success);color:#fff}
.btn-outline{background:transparent;border:1.5px solid var(--a-border);color:var(--a-text2)}
.btn-outline:hover{background:#f8fafc}
.btn-sm{padding:.35rem .75rem;font-size:.76rem}
.btn-full{width:100%;justify-content:center}

/*  Table  */
.table-wrap{overflow-x:auto}
table.a-table{width:100%;border-collapse:collapse;font-size:.85rem}
table.a-table th{background:#f8fafc;padding:.75rem 1rem;text-align:left;font-weight:700;font-size:.75rem;color:var(--a-text2);text-transform:uppercase;letter-spacing:.3px;border-bottom:2px solid var(--a-border)}
table.a-table td{padding:.75rem 1rem;border-bottom:1px solid var(--a-border);vertical-align:middle}
table.a-table tr:last-child td{border-bottom:none}
table.a-table tbody tr:hover{background:#fafbfc}

/*  Forms  */
.form-group{margin-bottom:1rem}
.form-label{display:block;font-size:.8rem;font-weight:600;color:var(--a-text2);margin-bottom:.35rem}
.form-control{width:100%;padding:.6rem .85rem;border:1.5px solid var(--a-border);border-radius:6px;font-size:.88rem;color:var(--a-text);background:#fff;outline:none;transition:border-color .2s}
.form-control:focus{border-color:var(--a-blue);box-shadow:0 0 0 3px rgba(84,105,212,.1)}
textarea.form-control{resize:vertical;min-height:90px}
select.form-control{cursor:pointer}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.form-hint{font-size:.72rem;color:var(--a-text3);margin-top:.25rem}

/*  Alerts  */
.alert{padding:.75rem 1rem;border-radius:6px;font-size:.85rem;font-weight:500;margin-bottom:.75rem}
.alert-success{background:#d1fae5;color:#065f46;border-left:4px solid var(--a-success)}
.alert-error{background:#fee2e2;color:#991b1b;border-left:4px solid var(--a-danger)}
.alert-info{background:#e0f2fe;color:#0c4a6e;border-left:4px solid var(--a-blue)}
.alert-warning{background:#fef3c7;color:#92400e;border-left:4px solid var(--a-warning)}

/*  Status  */
.status{display:inline-flex;align-items:center;padding:.18rem .6rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px}
.status-pending{background:#fef3c7;color:#92400e}
.status-processing{background:#dbeafe;color:#1e40af}
.status-shipped{background:#e0e7ff;color:#3730a3}
.status-delivered{background:#d1fae5;color:#065f46}
.status-cancelled{background:#fee2e2;color:#991b1b}
.status-active{background:#d1fae5;color:#065f46}
.status-inactive{background:#f1f5f9;color:#64748b}
.status-admin{background:#ede9fe;color:#5b21b6}
.status-customer{background:#dbeafe;color:#1e40af}

/*  Pagination  */
.pagination{display:flex;justify-content:center;gap:.35rem;margin-top:1.5rem;flex-wrap:wrap}
.page-btn{width:34px;height:34px;display:flex;align-items:center;justify-content:center;border-radius:6px;font-size:.82rem;font-weight:600;color:var(--a-text2);background:#fff;border:1px solid var(--a-border);transition:all .2s}
.page-btn:hover,.page-btn.active{background:var(--a-accent);color:#fff;border-color:var(--a-accent)}

/*  Modal  */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;display:flex;align-items:center;justify-content:center;padding:1.5rem}
.modal{background:#fff;border-radius:var(--a-radius);box-shadow:0 20px 60px rgba(0,0,0,.25);width:100%;max-width:520px;overflow:hidden}
.modal-header{padding:1.1rem 1.25rem;border-bottom:1px solid var(--a-border);display:flex;align-items:center;justify-content:space-between;font-weight:700}
.modal-body{padding:1.25rem}
.modal-footer{padding:1rem 1.25rem;border-top:1px solid var(--a-border);display:flex;justify-content:flex-end;gap:.75rem}
.modal-close{background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--a-text3);line-height:1}

/*  Responsive  */
@media(max-width:900px){
  .a-sidebar{display:none}
  .grid-4{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:600px){
  .grid-4,.grid-3,.grid-2{grid-template-columns:1fr}
  .a-content{padding:1rem}
  .form-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>

<!--  SIDEBAR  -->
<aside class="a-sidebar">
  <div class="a-logo">
    <?= h($adminSite) ?> <span style="font-size:.7rem;color:#64748b;font-weight:400;margin-left:.25rem">Admin</span>
  </div>

  <nav class="a-nav">
    <div class="a-nav-section">Principal</div>
    <a href="index.php" class="<?= $adminPage==='dashboard'?'active':'' ?>">
      <span class="a-nav-icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      </span>
      Tableau de bord
    </a>

    <div class="a-nav-section">Catalogue</div>
    <a href="products.php" class="<?= $adminPage==='products'?'active':'' ?>">
      <span class="a-nav-icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </span>
      Produits
    </a>
    <a href="categories.php" class="<?= $adminPage==='categories'?'active':'' ?>">
      <span class="a-nav-icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      </span>
      Catégories
    </a>

    <div class="a-nav-section">Ventes</div>
    <a href="orders.php" class="<?= $adminPage==='orders'?'active':'' ?>">
      <span class="a-nav-icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/></svg>
      </span>
      Commandes
      <?php
        $pendCnt = db()->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
        if ($pendCnt > 0): ?>
          <span style="margin-left:auto;background:var(--a-accent);color:#fff;font-size:.65rem;padding:.1rem .45rem;border-radius:50px;font-weight:700"><?= $pendCnt ?></span>
      <?php endif; ?>
    </a>
    <a href="users.php" class="<?= $adminPage==='users'?'active':'' ?>">
      <span class="a-nav-icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </span>
      Utilisateurs
    </a>

    <a href="sellers.php" class="<?= $adminPage==='sellers'?'active':'' ?>">
      <span class="a-nav-icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
      </span>
      Vendeurs
      <?php
      try {
          $pendSellers = db()->query("SELECT COUNT(*) FROM seller_applications WHERE status='pending'")->fetchColumn();
          if ($pendSellers > 0) echo '<span style="background:var(--a-warning);color:#fff;border-radius:50px;padding:.05rem .5rem;font-size:.7rem;margin-left:auto">'.$pendSellers.'</span>';
      } catch (Throwable) {}
      ?>
    </a>

    <div class="a-nav-section">Système</div>
    <a href="settings.php" class="<?= $adminPage==='settings'?'active':'' ?>">
      <span class="a-nav-icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
      </span>
      Paramètres
    </a>
  </nav>

  <div class="a-sidebar-footer">
    <a href="<?= SITE_URL ?>" target="_blank">Voir le site</a>
    <a href="<?= SITE_URL ?>/logout.php" style="margin-top:.5rem;color:var(--a-danger)">Déconnexion</a>
  </div>
</aside>

<!--  MAIN  -->
<div class="a-main">
  <div class="a-topbar">
    <span class="a-topbar-title"><?= $pageTitle ?? 'Administration' ?></span>
    <div class="a-topbar-actions">
      <span style="font-size:.82rem;color:var(--a-text3)"><?= h($adminUser['firstname'].' '.$adminUser['lastname']) ?></span>
      <a href="<?= SITE_URL ?>" target="_blank" class="a-topbar-btn">Site</a>
    </div>
  </div>

  <div class="a-content">
    <?php
    $f = getFlash();
    if ($f) {
        echo '<div class="alert alert-'.$f['type'].'">'.h($f['msg']).'</div>';
    }
    ?>
