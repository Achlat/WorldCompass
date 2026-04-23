<?php
require_once __DIR__.'/config.php';

//  Auth helpers
function isLoggedIn(): bool    { return !empty($_SESSION['user_id']); }
function isAdmin(): bool       { return ($_SESSION['user_role'] ?? '') === 'admin'; }
function isSeller(): bool      { return in_array($_SESSION['user_role'] ?? '', ['seller','admin']); }
function requireLogin(): void  { if (!isLoggedIn()) { header('Location:'.SITE_URL.'/login.php?redirect='.urlencode($_SERVER['REQUEST_URI'])); exit; } }
function requireAdmin(): void  { if (!isAdmin()) { header('Location:'.SITE_URL.'/index.php'); exit; } }
function requireSeller(): void { if (!isSeller()) { flash('info','Accès réservé aux vendeurs partenaires.'); header('Location:'.SITE_URL.'/devenir-vendeur.php'); exit; } }

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    $s = db()->prepare("SELECT * FROM users WHERE id=?");
    $s->execute([$_SESSION['user_id']]);
    return $s->fetch() ?: null;
}

//  Output helpers
function h(string $s): string  { return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function money(float $n): string { return number_format($n,0,',',' ').' '.CURRENCY; }
function slug(string $s): string { return strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',iconv('UTF-8','ASCII//TRANSLIT',$s)),'-')); }
function ago(string $dt): string {
    $diff = time() - strtotime($dt);
    if ($diff < 60)    return 'A l\'instant';
    if ($diff < 3600)  return intval($diff/60).' min';
    if ($diff < 86400) return intval($diff/3600).' h';
    return date('d/m/Y', strtotime($dt));
}
function stars(float $r): string {
    $out = '';
    for ($i=1;$i<=5;$i++) {
        $color = $i<=$r ? '#f59e0b' : '#d1d5db';
        $out  .= '<span style="color:'.$color.';font-size:1em">&#9733;</span>';
    }
    return $out;
}
function discount(float $old, float $new): int {
    return $old > 0 ? intval(round(($old-$new)/$old*100)) : 0;
}

//  Flash messages
function flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type'=>$type,'msg'=>$msg];
}
function getFlash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
function renderFlash(): void {
    $f = getFlash();
    if (!$f) return;
    echo '<div class="alert alert-'.$f['type'].'">'.h($f['msg']).'</div>';
}

//  Categories
function getCategories(): array {
    return db()->query("SELECT * FROM categories WHERE active=1 AND parent_id IS NULL ORDER BY sort_order")->fetchAll();
}
function getCategoryBySlug(string $slug): ?array {
    $s = db()->prepare("SELECT * FROM categories WHERE slug=? AND active=1");
    $s->execute([$slug]);
    return $s->fetch() ?: null;
}

//  Products
function getFeaturedProducts(int $limit=8): array {
    return db()->query("SELECT p.*,c.name cat_name,c.slug cat_slug
        FROM products p LEFT JOIN categories c ON c.id=p.category_id
        WHERE p.featured=1 AND p.active=1 ORDER BY p.created_at DESC LIMIT $limit")->fetchAll();
}
function getProductBySlug(string $slug): ?array {
    $s = db()->prepare("SELECT p.*,c.name cat_name,c.slug cat_slug
        FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.slug=?");
    $s->execute([$slug]);
    return $s->fetch() ?: null;
}
function getProductById(int $id): ?array {
    $s = db()->prepare("SELECT p.*,c.name cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.id=?");
    $s->execute([$id]);
    return $s->fetch() ?: null;
}
function getRelatedProducts(int $categoryId, int $excludeId, int $limit=4): array {
    $s = db()->prepare("SELECT * FROM products WHERE category_id=? AND id!=? AND active=1 ORDER BY RAND() LIMIT $limit");
    $s->execute([$categoryId,$excludeId]);
    return $s->fetchAll();
}
function productAvgRating(int $pid): float {
    $s = db()->prepare("SELECT AVG(rating) FROM reviews WHERE product_id=?");
    $s->execute([$pid]);
    return round((float)$s->fetchColumn(),1);
}
function productReviewCount(int $pid): int {
    $s = db()->prepare("SELECT COUNT(*) FROM reviews WHERE product_id=?");
    $s->execute([$pid]);
    return (int)$s->fetchColumn();
}

// ── Ventes flash ──
function getFlashProducts(int $limit = 20): array {
    return db()->query("SELECT p.*,c.name cat_name,c.slug cat_slug
        FROM products p LEFT JOIN categories c ON c.id=p.category_id
        WHERE p.flash_sale_price IS NOT NULL AND p.flash_sale_end > NOW() AND p.active=1
        ORDER BY p.flash_sale_end ASC LIMIT $limit")->fetchAll();
}
function hasActiveFlash(): bool {
    return (bool)db()->query("SELECT 1 FROM products WHERE flash_sale_price IS NOT NULL AND flash_sale_end > NOW() AND active=1 LIMIT 1")->fetchColumn();
}

// ── Programme de fidélité ──
function getLoyaltyPoints(int $userId): int {
    $s = db()->prepare("SELECT loyalty_points FROM users WHERE id=?");
    $s->execute([$userId]);
    return (int)($s->fetchColumn() ?: 0);
}
function addLoyaltyPoints(int $userId, int $points, int $orderId = 0, string $note = ''): void {
    db()->prepare("UPDATE users SET loyalty_points=loyalty_points+? WHERE id=?")->execute([$points,$userId]);
    db()->prepare("INSERT INTO loyalty_transactions(user_id,order_id,points,type,note) VALUES(?,?,?,?,?)")
        ->execute([$userId, $orderId ?: null, $points, 'earn', $note]);
}
function redeemLoyaltyPoints(int $userId, int $points, int $orderId = 0): bool {
    $avail = getLoyaltyPoints($userId);
    if ($avail < $points) return false;
    db()->prepare("UPDATE users SET loyalty_points=loyalty_points-? WHERE id=?")->execute([$points,$userId]);
    db()->prepare("INSERT INTO loyalty_transactions(user_id,order_id,points,type,note) VALUES(?,?,?,?,?)")
        ->execute([$userId, $orderId ?: null, $points, 'redeem', 'Réduction commande']);
    return true;
}
function loyaltyPointsValue(int $points): float { return $points * 10.0; } // 1 pt = 10 FCFA
function orderEarnPoints(float $total): int { return (int)floor($total / 100); } // 100 FCFA = 1 pt
function getLoyaltyHistory(int $userId, int $limit = 20): array {
    $s = db()->prepare("SELECT lt.*,o.order_number FROM loyalty_transactions lt LEFT JOIN orders o ON o.id=lt.order_id WHERE lt.user_id=? ORDER BY lt.created_at DESC LIMIT $limit");
    $s->execute([$userId]);
    return $s->fetchAll();
}

// ── Portail vendeur ──
function getSellerStats(int $sellerId): array {
    $db = db();
    $totalSales = $db->prepare("SELECT COALESCE(SUM(oi.subtotal),0)
        FROM order_items oi JOIN orders o ON o.id=oi.order_id
        WHERE oi.product_id IN (SELECT id FROM products WHERE seller_id=?) AND o.status!='cancelled'");
    $totalSales->execute([$sellerId]);

    $totalOrders = $db->prepare("SELECT COUNT(DISTINCT o.id)
        FROM orders o JOIN order_items oi ON oi.order_id=o.id
        WHERE oi.product_id IN (SELECT id FROM products WHERE seller_id=?) AND o.status!='cancelled'");
    $totalOrders->execute([$sellerId]);

    $totalProducts = $db->prepare("SELECT COUNT(*) FROM products WHERE seller_id=? AND active=1");
    $totalProducts->execute([$sellerId]);

    $pendingComm = $db->prepare("SELECT COALESCE(SUM(commission_amount),0) FROM commissions WHERE seller_id=? AND status='pending'");
    $pendingComm->execute([$sellerId]);

    return [
        'total_sales'    => (float)$totalSales->fetchColumn(),
        'total_orders'   => (int)$totalOrders->fetchColumn(),
        'total_products' => (int)$totalProducts->fetchColumn(),
        'pending_comm'   => (float)$pendingComm->fetchColumn(),
    ];
}

function getSellerProducts(int $sellerId): array {
    $s = db()->prepare("SELECT p.*,c.name cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.seller_id=? ORDER BY p.created_at DESC");
    $s->execute([$sellerId]);
    return $s->fetchAll();
}

function getSellerOrders(int $sellerId, int $limit = 50): array {
    $s = db()->prepare("SELECT o.*,oi.product_name,oi.quantity,oi.subtotal item_subtotal,
        CONCAT(o.firstname,' ',o.lastname) full_name
        FROM orders o
        JOIN order_items oi ON oi.order_id=o.id
        WHERE oi.product_id IN (SELECT id FROM products WHERE seller_id=?)
        AND o.status!='cancelled'
        ORDER BY o.created_at DESC LIMIT $limit");
    $s->execute([$sellerId]);
    return $s->fetchAll();
}

function createCommissions(int $orderId, array $items): void {
    foreach ($items as $item) {
        $pid = (int)($item['product_id'] ?? $item['id'] ?? 0);
        if (!$pid) continue;
        $product = getProductById($pid);
        if (!$product || !$product['seller_id']) continue;
        $sellerId = (int)$product['seller_id'];
        $sellerRow = db()->prepare("SELECT seller_type FROM users WHERE id=?");
        $sellerRow->execute([$sellerId]);
        $sv = $sellerRow->fetch();
        $rate = ($sv && $sv['seller_type'] === 'autonomous') ? 6.0 : 12.0;
        $saleAmt = (float)$item['price'] * (int)$item['quantity'];
        $commAmount = round($saleAmt * $rate / 100, 2);
        db()->prepare("INSERT INTO commissions(order_id,order_item_id,seller_id,product_name,sale_amount,commission_rate,commission_amount)
            VALUES(?,?,?,?,?,?,?)")
            ->execute([$orderId, 0, $sellerId, $item['name'], $saleAmt, $rate, $commAmount]);
    }
}

// ── Commission plateforme ──
function isPlatformCommissionEnabled(): bool {
    return setting('platform_commission_enabled', '1') === '1';
}
function getPlatformCommissionRate(): float {
    return (float)setting('platform_commission_rate', '3.00');
}
function createPlatformCommissions(int $orderId, array $items): void {
    if (!isPlatformCommissionEnabled()) return;
    $rate = getPlatformCommissionRate();
    if ($rate <= 0) return;
    // Regrouper les ventes par vendeur
    $sellerSales = [];
    foreach ($items as $item) {
        $pid = (int)($item['product_id'] ?? $item['id'] ?? 0);
        if (!$pid) continue;
        $product = getProductById($pid);
        if (!$product || !$product['seller_id']) continue;
        $sid = (int)$product['seller_id'];
        $sellerSales[$sid] = ($sellerSales[$sid] ?? 0.0) + (float)$item['price'] * (int)$item['quantity'];
    }
    foreach ($sellerSales as $sid => $saleAmt) {
        $commAmount = round($saleAmt * $rate / 100, 2);
        db()->prepare("INSERT INTO platform_commissions(order_id,seller_id,sale_amount,commission_rate,commission_amount) VALUES(?,?,?,?,?)")
            ->execute([$orderId, $sid, $saleAmt, $rate, $commAmount]);
    }
}
function getPlatformCommissionTotals(): array {
    try {
        $pending = (float)db()->query("SELECT COALESCE(SUM(commission_amount),0) FROM platform_commissions WHERE status='pending'")->fetchColumn();
        $paid    = (float)db()->query("SELECT COALESCE(SUM(commission_amount),0) FROM platform_commissions WHERE status='paid'")->fetchColumn();
        return ['pending' => $pending, 'paid' => $paid, 'total' => $pending + $paid];
    } catch (Throwable) {
        return ['pending' => 0.0, 'paid' => 0.0, 'total' => 0.0];
    }
}

// Icônes SVG par catégorie (placeholder réaliste)
function categoryIcon(string $catName): string {
    $name = mb_strtolower($catName);
    if (str_contains($name,'électron') || str_contains($name,'electron') || str_contains($name,'tech')) {
        return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" width="56" height="56"><rect x="8" y="12" width="48" height="32" rx="3" stroke="currentColor" stroke-width="3"/><line x1="20" y1="48" x2="44" y2="48" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><line x1="32" y1="44" x2="32" y2="52" stroke="currentColor" stroke-width="3"/></svg>';
    }
    if (str_contains($name,'mode') || str_contains($name,'vêtement') || str_contains($name,'vetement')) {
        return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" width="56" height="56"><path d="M24 8l-14 12h10v28h24V20h10L40 8c0 0-2 6-8 6s-8-6-8-6z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/></svg>';
    }
    if (str_contains($name,'maison') || str_contains($name,'déco') || str_contains($name,'deco')) {
        return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" width="56" height="56"><path d="M8 28L32 8l24 20" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/><rect x="14" y="28" width="36" height="24" stroke="currentColor" stroke-width="3"/><rect x="26" y="36" width="12" height="16" stroke="currentColor" stroke-width="2.5"/></svg>';
    }
    if (str_contains($name,'sport') || str_contains($name,'loisir')) {
        return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" width="56" height="56"><circle cx="32" cy="32" r="22" stroke="currentColor" stroke-width="3"/><path d="M16 20c4 4 4 20 16 20s12-16 16-20" stroke="currentColor" stroke-width="2.5"/><path d="M48 44c-4-4-4-20-16-20S20 40 16 44" stroke="currentColor" stroke-width="2.5"/></svg>';
    }
    if (str_contains($name,'beauté') || str_contains($name,'beaute') || str_contains($name,'santé') || str_contains($name,'sante')) {
        return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" width="56" height="56"><path d="M32 8c0 0-18 14-18 30a18 18 0 0 0 36 0C50 22 32 8 32 8z" stroke="currentColor" stroke-width="3"/><path d="M32 30v8M28 34h8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>';
    }
    if (str_contains($name,'aliment') || str_contains($name,'épicerie') || str_contains($name,'nourriture')) {
        return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" width="56" height="56"><path d="M20 12v16c0 6 4 10 12 12 8-2 12-6 12-12V12" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><line x1="32" y1="40" x2="32" y2="56" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><line x1="20" y1="56" x2="44" y2="56" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>';
    }
    // Défaut
    return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" width="56" height="56"><rect x="10" y="16" width="44" height="36" rx="4" stroke="currentColor" stroke-width="3"/><path d="M10 26h44" stroke="currentColor" stroke-width="2.5"/><circle cx="20" cy="21" r="2" fill="currentColor"/><circle cx="28" cy="21" r="2" fill="currentColor"/></svg>';
}

// Helper image produit
function productImageHtml(array $p, string $classes='', string $style=''): string {
    $img = $p['image'] ?? '';
    $imgPath = __DIR__.'/../uploads/products/'.$img;
    if ($img && file_exists($imgPath)) {
        return '<img src="'.SITE_URL.'/uploads/products/'.h($img).'" alt="'.h($p['name']).'"'.
               ($classes ? ' class="'.h($classes).'"' : '').
               ($style ? ' style="'.h($style).'"' : '').
               ' loading="lazy">';
    }
    return '';
}

//  Cart
function cartKey(): string {
    return isLoggedIn() ? 'uid_'.$_SESSION['user_id'] : (session_id());
}

function getCartItems(): array {
    if (isLoggedIn()) {
        $s = db()->prepare("SELECT ci.*,p.name,p.price,p.stock,p.image_color,p.slug,p.image,c.name cat_name
            FROM cart ci
            JOIN products p ON p.id=ci.product_id
            LEFT JOIN categories c ON c.id=p.category_id
            WHERE ci.user_id=? ORDER BY ci.id DESC");
        $s->execute([$_SESSION['user_id']]);
    } else {
        $s = db()->prepare("SELECT ci.*,p.name,p.price,p.stock,p.image_color,p.slug,p.image,c.name cat_name
            FROM cart ci
            JOIN products p ON p.id=ci.product_id
            LEFT JOIN categories c ON c.id=p.category_id
            WHERE ci.session_id=? ORDER BY ci.id DESC");
        $s->execute([session_id()]);
    }
    return $s->fetchAll();
}

function cartCount(): int {
    if (isLoggedIn()) {
        $s = db()->prepare("SELECT SUM(quantity) FROM cart WHERE user_id=?");
        $s->execute([$_SESSION['user_id']]);
    } else {
        $s = db()->prepare("SELECT SUM(quantity) FROM cart WHERE session_id=?");
        $s->execute([session_id()]);
    }
    return (int)$s->fetchColumn();
}

function cartTotal(): float {
    $total = 0;
    foreach (getCartItems() as $item) $total += $item['price'] * $item['quantity'];
    return $total;
}

function addToCart(int $pid, int $qty=1): bool {
    $p = getProductById($pid);
    if (!$p || $p['stock'] < 1) return false;

    if (isLoggedIn()) {
        $s = db()->prepare("SELECT id,quantity FROM cart WHERE user_id=? AND product_id=?");
        $s->execute([$_SESSION['user_id'],$pid]);
        $row = $s->fetch();
        if ($row) {
            db()->prepare("UPDATE cart SET quantity=quantity+? WHERE id=?")->execute([$qty,$row['id']]);
        } else {
            db()->prepare("INSERT INTO cart(user_id,product_id,quantity) VALUES(?,?,?)")->execute([$_SESSION['user_id'],$pid,$qty]);
        }
    } else {
        $s = db()->prepare("SELECT id,quantity FROM cart WHERE session_id=? AND product_id=?");
        $s->execute([session_id(),$pid]);
        $row = $s->fetch();
        if ($row) {
            db()->prepare("UPDATE cart SET quantity=quantity+? WHERE id=?")->execute([$qty,$row['id']]);
        } else {
            db()->prepare("INSERT INTO cart(session_id,product_id,quantity) VALUES(?,?,?)")->execute([session_id(),$pid,$qty]);
        }
    }
    return true;
}

function updateCartQty(int $cartId, int $qty): void {
    if ($qty < 1) {
        db()->prepare("DELETE FROM cart WHERE id=?")->execute([$cartId]);
    } else {
        db()->prepare("UPDATE cart SET quantity=? WHERE id=?")->execute([$qty,$cartId]);
    }
}

function removeFromCart(int $cartId): void {
    db()->prepare("DELETE FROM cart WHERE id=?")->execute([$cartId]);
}

function clearCart(): void {
    if (isLoggedIn()) {
        db()->prepare("DELETE FROM cart WHERE user_id=?")->execute([$_SESSION['user_id']]);
    } else {
        db()->prepare("DELETE FROM cart WHERE session_id=?")->execute([session_id()]);
    }
}

//  Pagination
function paginate(int $total, int $perPage, int $current, string $url): string {
    $pages = ceil($total/$perPage);
    if ($pages <= 1) return '';
    $sep = strpos($url,'?') !== false ? '&' : '?';
    $html = '<div class="pagination">';
    if ($current > 1)    $html .= '<a href="'.$url.$sep.'page='.($current-1).'" class="page-btn">&#8249;</a>';
    for ($i=1;$i<=$pages;$i++) {
        $active = $i===$current ? ' active' : '';
        $html .= '<a href="'.$url.$sep.'page='.$i.'" class="page-btn'.$active.'">'.$i.'</a>';
    }
    if ($current < $pages) $html .= '<a href="'.$url.$sep.'page='.($current+1).'" class="page-btn">&#8250;</a>';
    $html .= '</div>';
    return $html;
}

//  CSRF
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrfField(): string { return '<input type="hidden" name="csrf" value="'.csrfToken().'">'; }
function verifyCsrf(): void {
    if (($_POST['csrf'] ?? '') !== csrfToken()) { flash('error','Requete invalide.'); header('Location: '.$_SERVER['HTTP_REFERER']); exit; }
}

//  Carte produit HTML
function productCard(array $p, bool $wishlist=false): string {
    // Vente flash : utilise flash_sale_price si active
    $isFlash  = !empty($p['flash_sale_price']) && !empty($p['flash_sale_end']) && strtotime($p['flash_sale_end']) > time();
    $dispPrice = $isFlash ? (float)$p['flash_sale_price'] : (float)$p['price'];
    $refPrice  = $isFlash ? (float)$p['price'] : (float)($p['old_price'] ?? 0);
    $pct       = $refPrice > 0 ? discount($refPrice, $dispPrice) : 0;

    $rating = productAvgRating($p['id']);
    $rcount = productReviewCount($p['id']);
    $badge  = $isFlash
        ? '<span class="badge badge-flash">FLASH -'.$pct.'%</span>'
        : ($pct>0 ? '<span class="badge badge-sale">-'.$pct.'%</span>' : '');
    $feat   = $p['featured'] ? '<span class="badge badge-hot">Populaire</span>' : '';
    $stock  = $p['stock']<1 ? '<span class="out-of-stock">Rupture de stock</span>' : '';
    $color  = h($p['image_color']??'#5469d4');
    $imgFile= $p['image'] ?? '';
    $imgPath= __DIR__.'/../uploads/products/'.$imgFile;

    $flashCountdown = '';
    if ($isFlash) {
        $end = h($p['flash_sale_end']);
        $flashCountdown = '<div class="flash-countdown" data-end="'.$end.'">Fin dans <span class="flash-timer">...</span></div>';
    }

    // Image : photo réelle si disponible, sinon icône SVG sur fond de couleur
    if ($imgFile && file_exists($imgPath)) {
        $imgHtml = '<img src="'.SITE_URL.'/uploads/products/'.h($imgFile).'" alt="'.h($p['name']).'"
                        style="width:100%;height:100%;object-fit:cover;position:absolute;top:0;left:0;border-radius:inherit" loading="lazy">';
        $iconHtml = '';
    } else {
        $imgHtml  = '';
        $iconHtml = '<div style="color:rgba(255,255,255,.7);display:flex;align-items:center;justify-content:center;width:100%;height:100%;position:absolute;top:0;left:0">'.categoryIcon($p['cat_name']??'').'</div>';
    }

    return '<div class="product-card'.($isFlash?' product-card--flash':'').'" data-id="'.h((string)$p['id']).'">
        <a href="'.SITE_URL.'/product.php?slug='.h($p['slug']).'" class="product-img-wrap">
            <div class="product-img-placeholder" style="background:linear-gradient(135deg,'.h($color).','.h($color).'cc);position:relative;overflow:hidden">
                '.$imgHtml.$iconHtml.'
            </div>
            '.$badge.$feat.'
        </a>
        <div class="product-info">
            '.($p['cat_name']??'' ? '<span class="product-cat">'.h($p['cat_name']).'</span>' : '').'
            <a href="'.SITE_URL.'/product.php?slug='.h($p['slug']).'" class="product-name">'.h($p['name']).'</a>
            <div class="product-rating">
                <span class="stars">'.stars($rating).'</span>
                <span class="rating-count">('.$rcount.')</span>
            </div>
            <div class="product-price">
                <span class="price-current">'.money($dispPrice).'</span>
                '.($refPrice > 0 ? '<span class="price-old">'.money($refPrice).'</span>' : '').'
            </div>
            '.$flashCountdown.$stock.'
            '.($p['stock']>0 ? '<button class="btn btn-cart btn-add-cart" data-id="'.h((string)$p['id']).'">Ajouter au panier</button>' : '').'
        </div>
    </div>';
}
