/* ══════════════════════════════════════════════════════════
   NEXASTORE – Main JavaScript
   ══════════════════════════════════════════════════════════ */

'use strict';

const SITE_URL = document.currentScript?.dataset?.url || window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '');

// ── Toast ──────────────────────────────────────────────────
function showToast(msg, duration = 3000) {
  const t = document.getElementById('cartToast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), duration);
}

// ── Update cart badge ──────────────────────────────────────
function updateCartBadge(count) {
  const badge = document.getElementById('cart-badge');
  if (!badge) return;
  badge.textContent = count > 0 ? count : '';
}

// ── Add to cart via AJAX ───────────────────────────────────
async function addToCart(productId, qty = 1, btn = null) {
  if (btn) { btn.classList.add('loading'); btn.textContent = '⏳ Ajout…'; }
  try {
    const resp = await fetch('cart.php?action=add', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: `product_id=${productId}&qty=${qty}`
    });
    const data = await resp.json();
    if (data.success) {
      updateCartBadge(data.count);
      showToast('🛒 ' + data.message);
      if (btn) { btn.classList.remove('loading'); btn.classList.add('added'); btn.innerHTML = '<span>✓</span> Ajouté !'; setTimeout(() => { btn.classList.remove('added'); btn.innerHTML = '<span>🛒</span> Ajouter au panier'; }, 2500); }
    } else {
      showToast('❌ ' + (data.message || 'Erreur'));
      if (btn) { btn.classList.remove('loading'); btn.textContent = '🛒 Ajouter au panier'; }
    }
  } catch(e) {
    showToast('❌ Erreur réseau');
    if (btn) { btn.classList.remove('loading'); btn.textContent = '🛒 Ajouter au panier'; }
  }
}

// ── Delegate add-to-cart buttons ──────────────────────────
document.addEventListener('click', e => {
  const btn = e.target.closest('.btn-add-cart');
  if (!btn) return;
  e.preventDefault();
  const id = btn.dataset.id;
  const qtyInput = document.getElementById('detailQty');
  const qty = qtyInput ? parseInt(qtyInput.value) : 1;
  if (id) addToCart(id, qty, btn);
});

// ── Qty controls (product detail) ─────────────────────────
document.addEventListener('click', e => {
  const btn = e.target.closest('[data-qty]');
  if (!btn) return;
  const input = document.getElementById('detailQty');
  if (!input) return;
  let v = parseInt(input.value) || 1;
  const max = parseInt(input.max) || 99;
  btn.dataset.qty === '+' ? (v = Math.min(v+1, max)) : (v = Math.max(v-1, 1));
  input.value = v;
});

// ── Cart qty update ────────────────────────────────────────
document.addEventListener('change', async e => {
  const input = e.target.closest('.cart-qty-input');
  if (!input) return;
  const cartId = input.dataset.cartId;
  const qty = parseInt(input.value);
  if (!cartId || isNaN(qty)) return;
  const resp = await fetch('cart.php?action=update', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: `cart_id=${cartId}&qty=${qty}`
  });
  const data = await resp.json();
  if (data.success) { updateCartBadge(data.count); location.reload(); }
});

// ── Cart item remove ───────────────────────────────────────
document.addEventListener('click', async e => {
  const btn = e.target.closest('.cart-remove');
  if (!btn) return;
  const cartId = btn.dataset.cartId;
  if (!cartId) return;
  if (!confirm('Retirer cet article ?')) return;
  const resp = await fetch('cart.php?action=remove', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: `cart_id=${cartId}`
  });
  const data = await resp.json();
  if (data.success) {
    updateCartBadge(data.count);
    const row = btn.closest('.cart-item');
    if (row) { row.style.transition = 'all .3s'; row.style.opacity = '0'; row.style.height = '0'; setTimeout(() => location.reload(), 300); }
  }
});

// ── Product tabs ───────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = btn.dataset.tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const pane = document.getElementById(target);
    if (pane) pane.classList.add('active');
  });
});

// ── Payment option selection ───────────────────────────────
document.querySelectorAll('.pay-opt').forEach(opt => {
  opt.addEventListener('click', () => {
    document.querySelectorAll('.pay-opt').forEach(o => o.classList.remove('selected'));
    opt.classList.add('selected');
    const radio = opt.querySelector('input[type=radio]');
    if (radio) radio.checked = true;
  });
});

// ── Back to top ────────────────────────────────────────────
const backBtn = document.getElementById('backToTop');
if (backBtn) {
  window.addEventListener('scroll', () => { backBtn.classList.toggle('visible', window.scrollY > 400); }, { passive: true });
  backBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

// ── Mobile menu ────────────────────────────────────────────
const mobBtn       = document.getElementById('mobMenuBtn');
const mobileNav    = document.getElementById('mobileNav');
const mobIcoMenu   = document.getElementById('mobIcoMenu');
const mobIcoClose  = document.getElementById('mobIcoClose');
const mobMenuLabel = document.getElementById('mobMenuLabel');
if (mobBtn && mobileNav) {
  mobBtn.addEventListener('click', e => {
    e.stopPropagation();
    const open = mobileNav.classList.toggle('open');
    if (mobIcoMenu)  mobIcoMenu.style.display  = open ? 'none'  : '';
    if (mobIcoClose) mobIcoClose.style.display = open ? ''      : 'none';
    if (mobMenuLabel) mobMenuLabel.textContent  = open ? 'Fermer' : 'Menu';
  });
  document.addEventListener('click', e => {
    if (!mobileNav.contains(e.target) && !mobBtn.contains(e.target)) {
      mobileNav.classList.remove('open');
      if (mobIcoMenu)  mobIcoMenu.style.display  = '';
      if (mobIcoClose) mobIcoClose.style.display = 'none';
      if (mobMenuLabel) mobMenuLabel.textContent  = 'Menu';
    }
  });
}

// ── Animate numbers on scroll ──────────────────────────────
function animateNumber(el) {
  const target = parseInt(el.dataset.target) || 0;
  const duration = 1400;
  const step = target / (duration / 16);
  let current = 0;
  const timer = setInterval(() => {
    current = Math.min(current + step, target);
    el.textContent = Math.floor(current).toLocaleString('fr-FR') + (el.dataset.suffix || '');
    if (current >= target) clearInterval(timer);
  }, 16);
}
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { animateNumber(e.target); observer.unobserve(e.target); } });
}, { threshold: .5 });
document.querySelectorAll('[data-animate-number]').forEach(el => observer.observe(el));

// ── Search suggestions (debounce) ─────────────────────────
let searchTimer;
const searchInput = document.querySelector('.search-input');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { /* could add live search */ }, 300);
  });
}

// ── Form validation helper ─────────────────────────────────
function validateForm(form) {
  let valid = true;
  form.querySelectorAll('[required]').forEach(field => {
    if (!field.value.trim()) {
      field.classList.add('error');
      valid = false;
    } else {
      field.classList.remove('error');
    }
  });
  return valid;
}

// ── Auto-hide alerts ───────────────────────────────────────
document.querySelectorAll('.alert').forEach(a => {
  setTimeout(() => { a.style.transition='all .5s'; a.style.opacity='0'; a.style.maxHeight='0'; a.style.overflow='hidden'; }, 5000);
});
