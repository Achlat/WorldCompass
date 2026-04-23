<?php
require_once 'includes/functions.php';
$items = getCartItems();
if (!$items) { flash('info','Votre panier est vide.'); header('Location: cart.php'); exit; }

$total    = cartTotal();
$shipping = $total < (float)setting('free_shipping_threshold','50000') ? (float)setting('shipping_cost','2000') : 0;
$u        = currentUser();
$pageTitle= 'Commander';
$errors   = [];

// Programme de fidélité
$userPoints       = ($u && isLoggedIn()) ? getLoyaltyPoints((int)$u['id']) : 0;
$pointsToEarn     = orderEarnPoints($total); // pts qui seront gagnés
$redeemPoints     = 0;
$pointsDiscount   = 0.0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $redeemPoints = max(0, min((int)($_POST['redeem_points']??0), $userPoints));
    $pointsDiscount = loyaltyPointsValue($redeemPoints);
}

$grand = max(0, $total + $shipping - $pointsDiscount);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = [
        'firstname'     => trim($_POST['firstname']??''),
        'lastname'      => trim($_POST['lastname']??''),
        'email'         => trim($_POST['email']??''),
        'phone'         => trim($_POST['phone']??''),
        'address'       => trim($_POST['address']??''),
        'city'          => trim($_POST['city']??''),
        'payment'       => $_POST['payment']??'cash',
        'payment_phone' => trim($_POST['payment_phone']??''),
        'notes'         => trim($_POST['notes']??''),
    ];
    if (!$f['firstname']) $errors[] = 'Prénom requis';
    if (!$f['lastname'])  $errors[] = 'Nom requis';
    if (!filter_var($f['email'],FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide';
    if (!$f['phone'])     $errors[] = 'Téléphone requis';
    if (!$f['address'])   $errors[] = 'Adresse requise';
    if (!$f['city'])      $errors[] = 'Ville requise';

    $mobileMethods = ['yas_money','moov_money'];
    if (in_array($f['payment'], $mobileMethods) && !$f['payment_phone']) {
        $errors[] = 'Numéro de téléphone Mobile Money requis';
    }

    if (!$errors) {
        $orderNum = 'ORD-'.date('Y').'-'.str_pad(rand(1,9999),4,'0',STR_PAD_LEFT);
        $ins = db()->prepare("INSERT INTO orders(user_id,order_number,firstname,lastname,email,phone,address,city,subtotal,shipping,total,payment_method,notes)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $notesTxt = $f['notes'] ?: '';
        if ($f['payment_phone']) $notesTxt .= "\nMobile Money: ".$f['payment_phone'];
        if ($redeemPoints > 0)  $notesTxt .= "\nPoints fidélité utilisés: ".$redeemPoints.' pts (-'.money($pointsDiscount).')';

        $ins->execute([
            isLoggedIn()?$_SESSION['user_id']:null,
            $orderNum,$f['firstname'],$f['lastname'],$f['email'],$f['phone'],
            $f['address'],$f['city'],$total,$shipping,$grand,$f['payment'],$notesTxt
        ]);
        $orderId = (int)db()->lastInsertId();

        try { db()->prepare("UPDATE orders SET payment_phone=? WHERE id=?")->execute([$f['payment_phone'],$orderId]); } catch (Throwable) {}

        $insertedItems = [];
        foreach ($items as $item) {
            db()->prepare("INSERT INTO order_items(order_id,product_id,product_name,product_price,quantity,subtotal) VALUES(?,?,?,?,?,?)")
                ->execute([$orderId,$item['product_id'],$item['name'],$item['price'],$item['quantity'],$item['price']*$item['quantity']]);
            db()->prepare("UPDATE products SET stock=stock-? WHERE id=?")->execute([$item['quantity'],$item['product_id']]);
            $insertedItems[] = $item;
        }

        // Fidélité — débiter les points utilisés, créditer les points gagnés
        if (isLoggedIn()) {
            $uid = (int)$_SESSION['user_id'];
            if ($redeemPoints > 0) redeemLoyaltyPoints($uid, $redeemPoints, $orderId);
            if ($pointsToEarn  > 0) addLoyaltyPoints($uid, $pointsToEarn, $orderId, 'Achat #'.$orderNum);
        }

        // Commissions vendeurs
        try { createCommissions($orderId, $insertedItems); } catch (Throwable) {}
        // Commission plateforme World Compass
        try { createPlatformCommissions($orderId, $insertedItems); } catch (Throwable) {}

        clearCart();
        $_SESSION['last_order'] = ['num'=>$orderNum,'total'=>$grand,'points_earned'=>$pointsToEarn];
        flash('success','Commande #'.$orderNum.' passée ! Vous gagnez '.$pointsToEarn.' points de fidélité.');
        header('Location: orders.php'); exit;
    }
}
?>
<?php require_once 'includes/header.php'; ?>

<div class="page-hdr"><div class="page-hdr-inner">
  <h1>Finaliser ma commande</h1>
</div></div>

<div class="container" style="padding-bottom:3rem">
  <?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>',array_map('h',$errors)) ?></div>
  <?php endif; ?>

  <form method="POST" id="checkoutForm">
    <?= csrfField() ?>
    <div class="checkout-layout">

      <div>
        <!-- Livraison -->
        <div class="checkout-section">
          <h3>Informations de livraison</h3>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Prénom *</label>
              <input class="form-control" name="firstname" value="<?= h($u?$u['firstname']:($_POST['firstname']??'')) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nom *</label>
              <input class="form-control" name="lastname" value="<?= h($u?$u['lastname']:($_POST['lastname']??'')) ?>" required>
            </div>
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Email *</label>
              <input class="form-control" type="email" name="email" value="<?= h($u?$u['email']:($_POST['email']??'')) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Téléphone *</label>
              <input class="form-control" type="tel" name="phone" value="<?= h($u?$u['phone']:($_POST['phone']??'')) ?>" required placeholder="+228 90 00 00 00">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Adresse complète *</label>
            <textarea class="form-control" name="address" rows="2" required><?= h($u?$u['address']:($_POST['address']??'')) ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Ville *</label>
            <input class="form-control" name="city" value="<?= h($u?$u['city']:($_POST['city']??'')) ?>" required placeholder="Lomé, Kara, Sokodé…">
          </div>
        </div>

        <!-- Programme fidélité -->
        <?php if (isLoggedIn() && $userPoints >= 100): ?>
        <div class="checkout-section" style="background:linear-gradient(135deg,#fef3c7,#fde68a);border:2px solid #f59e0b;border-radius:12px;padding:1.25rem">
          <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.75rem">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>
            <strong style="color:#92400e">Programme de fidélité</strong>
            <span style="margin-left:auto;background:#d97706;color:#fff;padding:.2rem .7rem;border-radius:50px;font-size:.78rem;font-weight:700"><?= $userPoints ?> pts</span>
          </div>
          <p style="font-size:.84rem;color:#78350f;margin-bottom:.85rem">
            Vous avez <strong><?= $userPoints ?> points</strong> = valeur <strong><?= money(loyaltyPointsValue($userPoints)) ?></strong>.
            Utilisez-les pour réduire votre commande (100 pts min, multiples de 100).
          </p>
          <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
            <label style="font-size:.85rem;font-weight:600;color:#78350f">Points à utiliser :</label>
            <input type="number" name="redeem_points" id="redeemInput"
                   min="0" max="<?= $userPoints ?>" step="100" value="<?= $redeemPoints ?>"
                   class="form-control" style="width:120px"
                   oninput="updateDiscount(this.value)">
            <span style="font-size:.9rem;font-weight:700;color:#78350f" id="redeemVal">
              = <?= $redeemPoints > 0 ? '-'.money($pointsDiscount) : '0 FCFA' ?>
            </span>
          </div>
        </div>
        <?php elseif (isLoggedIn()): ?>
        <div class="checkout-section" style="background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:1rem;display:flex;align-items:center;gap:.75rem">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>
          <div>
            <strong style="font-size:.85rem">Points de fidélité</strong>
            <p style="font-size:.8rem;color:var(--text-3);margin:0">Vous avez <?= $userPoints ?> points (minimum 100 pour utiliser). Cette commande vous rapportera <strong>+<?= $pointsToEarn ?> pts</strong>.</p>
          </div>
        </div>
        <?php endif; ?>

        <!-- Paiement -->
        <div class="checkout-section">
          <h3>Mode de paiement</h3>
          <div class="payment-options">

            <label class="pay-opt <?= (!isset($_POST['payment'])||$_POST['payment']==='cash')?'selected':'' ?>">
              <input type="radio" name="payment" value="cash" <?= (!isset($_POST['payment'])||$_POST['payment']==='cash')?'checked':'' ?>>
              <div class="pay-opt-content">
                <div class="pay-opt-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div>
                  <div style="font-weight:600">Paiement à la livraison</div>
                  <div style="font-size:.76rem;color:var(--text-3)">Payez en espèces à la réception</div>
                </div>
              </div>
            </label>

            <label class="pay-opt <?= (($_POST['payment']??'')==='yas_money')?'selected':'' ?>">
              <input type="radio" name="payment" value="yas_money" <?= (($_POST['payment']??'')==='yas_money')?'checked':'' ?>>
              <div class="pay-opt-content">
                <div class="pay-opt-icon" style="background:#ff6b00;color:#fff;font-weight:800;font-size:.85rem;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center">YAS</div>
                <div>
                  <div style="font-weight:600">YAS Money</div>
                  <div style="font-size:.76rem;color:var(--text-3)">Paiement mobile sécurisé YAS</div>
                </div>
              </div>
            </label>

            <label class="pay-opt <?= (($_POST['payment']??'')==='moov_money')?'selected':'' ?>">
              <input type="radio" name="payment" value="moov_money" <?= (($_POST['payment']??'')==='moov_money')?'checked':'' ?>>
              <div class="pay-opt-content">
                <div class="pay-opt-icon" style="background:#0072bc;color:#fff;font-weight:800;font-size:.85rem;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center">MOV</div>
                <div>
                  <div style="font-weight:600">MOOV Money</div>
                  <div style="font-size:.76rem;color:var(--text-3)">Paiement mobile MOOV Africa</div>
                </div>
              </div>
            </label>

            <label class="pay-opt <?= (($_POST['payment']??'')==='bank')?'selected':'' ?>">
              <input type="radio" name="payment" value="bank" <?= (($_POST['payment']??'')==='bank')?'checked':'' ?>>
              <div class="pay-opt-content">
                <div class="pay-opt-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                </div>
                <div>
                  <div style="font-weight:600">Virement bancaire</div>
                  <div style="font-size:.76rem;color:var(--text-3)">Coordonnées envoyées par email</div>
                </div>
              </div>
            </label>

          </div>

          <div id="mobilePay" style="margin-top:1rem;display:<?= in_array(($_POST['payment']??''),['yas_money','moov_money'])?'block':'none' ?>">
            <div style="background:#f8fafc;border:1.5px solid var(--border);border-radius:10px;padding:1.25rem">
              <div id="mobilePay-instructions" style="font-size:.82rem;margin-bottom:.75rem;color:var(--text-2)">
                <?php
                $selPay = $_POST['payment'] ?? '';
                if ($selPay === 'yas_money')  echo '<strong>YAS Money :</strong> Envoyez le montant au <strong>90 78 28 96</strong>, puis indiquez votre numéro ci-dessous.';
                elseif ($selPay === 'moov_money') echo '<strong>MOOV Money :</strong> Envoyez le montant au <strong>99 00 00 00</strong>, puis indiquez votre numéro ci-dessous.';
                else echo 'Indiquez votre numéro Mobile Money.';
                ?>
              </div>
              <label class="form-label">Votre numéro Mobile Money *</label>
              <input class="form-control" type="tel" name="payment_phone"
                     value="<?= h($_POST['payment_phone']??'') ?>"
                     placeholder="+228 90 00 00 00" id="paymentPhoneInput">
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="checkout-section">
          <h3>Notes (optionnel)</h3>
          <textarea class="form-control" name="notes" rows="3" placeholder="Instructions spéciales pour la livraison…"><?= h($_POST['notes']??'') ?></textarea>
        </div>
      </div>

      <!-- Résumé commande -->
      <div class="order-summary">
        <h3 style="font-size:1.05rem;font-weight:700;margin-bottom:1.25rem;border-bottom:1px solid var(--border);padding-bottom:.75rem">Votre commande</h3>
        <?php foreach ($items as $item): ?>
          <div class="order-item">
            <?php if (!empty($item['image']) && file_exists(__DIR__.'/uploads/products/'.$item['image'])): ?>
              <img src="<?= SITE_URL ?>/uploads/products/<?= h($item['image']) ?>" alt=""
                   class="order-item-img" style="object-fit:cover">
            <?php else: ?>
              <div class="order-item-img" style="background:linear-gradient(135deg,<?= h($item['image_color']??'#5469d4') ?>,<?= h($item['image_color']??'#5469d4') ?>99);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.75)">
                <?= str_replace('width="56" height="56"','width="24" height="24"', categoryIcon($item['cat_name']??'')) ?>
              </div>
            <?php endif; ?>
            <div class="order-item-name"><?= h($item['name']) ?> × <?= $item['quantity'] ?></div>
            <div class="order-item-price"><?= money($item['price']*$item['quantity']) ?></div>
          </div>
        <?php endforeach; ?>
        <div class="summary-row" style="margin-top:.75rem"><span>Sous-total</span><span><?= money($total) ?></span></div>
        <div class="summary-row"><span>Livraison</span><span><?= $shipping > 0 ? money($shipping) : '<span style="color:var(--success)">Gratuite</span>' ?></span></div>
        <?php if ($redeemPoints > 0): ?>
        <div class="summary-row" style="color:#d97706"><span>Points fidélité (<?= $redeemPoints ?> pts)</span><span>-<?= money($pointsDiscount) ?></span></div>
        <?php endif; ?>
        <div class="summary-row total"><span>Total</span><span id="grandTotal"><?= money($grand) ?></span></div>
        <?php if (isLoggedIn()): ?>
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.7rem .9rem;font-size:.8rem;color:#166534;margin:.75rem 0;display:flex;align-items:center;gap:.5rem">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>
          Cette commande vous rapportera <strong>+<?= $pointsToEarn ?> points</strong> de fidélité.
        </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary btn-full mt-2" style="border-radius:var(--radius-sm);font-size:1.05rem;padding:1rem">
          Confirmer la commande
        </button>
        <a href="cart.php" style="display:block;text-align:center;font-size:.82rem;color:var(--text-3);margin-top:.75rem">← Retour au panier</a>
      </div>

    </div>
  </form>
</div>

<style>
.pay-opt { display:block; border:2px solid var(--border); border-radius:10px; padding:.9rem 1rem; cursor:pointer; transition:all .2s; margin-bottom:.6rem }
.pay-opt input[type=radio]{ display:none }
.pay-opt.selected, .pay-opt:has(input:checked){ border-color:var(--accent); background:#fff8f5 }
.pay-opt-content { display:flex; align-items:center; gap:.85rem }
.pay-opt-icon { color:var(--text-2); flex-shrink:0 }
</style>

<script>
const radios  = document.querySelectorAll('input[name=payment]');
const opts    = document.querySelectorAll('.pay-opt');
const mobile  = document.getElementById('mobilePay');
const mobInstr= document.getElementById('mobilePay-instructions');
const phoneFld= document.getElementById('paymentPhoneInput');

const yasInstr  = '<strong>YAS Money :</strong> Envoyez le montant au <strong>90 78 28 96</strong>, puis indiquez votre numéro ci-dessous.';
const moovInstr = '<strong>MOOV Money :</strong> Envoyez le montant au <strong>99 00 00 00</strong>, puis indiquez votre numéro ci-dessous.';

radios.forEach(r => {
  r.addEventListener('change', () => {
    opts.forEach(o => o.classList.remove('selected'));
    r.closest('.pay-opt').classList.add('selected');
    const isMobile = r.value === 'yas_money' || r.value === 'moov_money';
    mobile.style.display = isMobile ? 'block' : 'none';
    if (phoneFld) phoneFld.required = isMobile;
    if (isMobile && mobInstr) mobInstr.innerHTML = r.value === 'yas_money' ? yasInstr : moovInstr;
  });
});

// Fidélité — calcul en temps réel
function updateDiscount(val) {
  const pts = Math.floor(parseInt(val||0)/100)*100;
  const disc = pts * 10;
  const el = document.getElementById('redeemVal');
  if (el) el.textContent = pts > 0 ? '= -' + disc.toLocaleString() + ' FCFA' : '= 0 FCFA';
  document.getElementById('redeemInput').value = pts;
}
</script>

<?php require_once 'includes/footer.php'; ?>
