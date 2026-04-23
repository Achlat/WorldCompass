<?php
$pageTitle = 'Comment fonctionne World Compass';
$pageDesc  = 'Découvrez World Compass : achetez, vendez, livrez. La marketplace complète pour acheteurs et vendeurs en Afrique de l\'Ouest.';
require_once 'includes/functions.php';
$siteName = setting('site_name', SITE_NAME);
?>
<?php require_once 'includes/header.php'; ?>

<style>
/* ════════════════════════════════════
   WORLD COMPASS — PAGE À PROPOS
   Design professionnel, niveau Amazon
   ════════════════════════════════════ */

/* ── Navigation d'ancre fixe ── */
.about-sticky-nav {
  position: sticky;
  top: 68px;
  z-index: 200;
  background: #fff;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 2px 8px rgba(0,0,0,.06);
}
.about-sticky-nav ul {
  display: flex;
  list-style: none;
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 1.25rem;
  overflow-x: auto;
  scrollbar-width: none;
  gap: 0;
}
.about-sticky-nav ul::-webkit-scrollbar { display: none; }
.about-sticky-nav a {
  display: block;
  padding: 1rem 1.25rem;
  font-size: .82rem;
  font-weight: 600;
  color: #64748b;
  white-space: nowrap;
  border-bottom: 3px solid transparent;
  transition: all .2s;
}
.about-sticky-nav a:hover,
.about-sticky-nav a.active { color: var(--accent); border-bottom-color: var(--accent); }

/* ── Hero principal ── */
.ab-hero {
  background: linear-gradient(135deg, #0f1d2e 0%, #1B2A41 45%, #1a3a5c 100%);
  color: #fff;
  padding: 6rem 1.25rem 5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.ab-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle at 20% 50%, rgba(255,107,43,.08) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(84,105,212,.1) 0%, transparent 50%);
}
.ab-hero-inner { position: relative; max-width: 820px; margin: 0 auto; }
.ab-eyebrow {
  display: inline-block;
  background: rgba(255,107,43,.15);
  color: var(--accent);
  border: 1px solid rgba(255,107,43,.3);
  padding: .4rem 1.1rem;
  border-radius: 50px;
  font-size: .78rem;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-bottom: 1.75rem;
}
.ab-hero h1 {
  font-size: clamp(2.2rem, 5.5vw, 3.8rem);
  font-weight: 900;
  line-height: 1.12;
  margin-bottom: 1.25rem;
  letter-spacing: -1px;
}
.ab-hero h1 span { color: var(--accent); }
.ab-hero-sub {
  font-size: 1.15rem;
  color: #94a3b8;
  line-height: 1.75;
  max-width: 640px;
  margin: 0 auto 2.5rem;
}
.ab-hero-cta { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
/* Métriques héro */
.ab-hero-metrics {
  display: flex;
  justify-content: center;
  gap: 3rem;
  margin-top: 4rem;
  padding-top: 3rem;
  border-top: 1px solid rgba(255,255,255,.1);
  flex-wrap: wrap;
}
.ab-metric { text-align: center; }
.ab-metric-val {
  font-size: 2.4rem;
  font-weight: 900;
  color: #fff;
  line-height: 1;
  margin-bottom: .3rem;
}
.ab-metric-val span { color: var(--accent); }
.ab-metric-lbl { font-size: .78rem; color: #64748b; font-weight: 500; text-transform: uppercase; letter-spacing: .5px; }

/* ── Sections générales ── */
.ab-section {
  padding: 5rem 0;
  scroll-margin-top: 120px;
}
.ab-section-inner { max-width: 1100px; margin: 0 auto; padding: 0 1.25rem; }
.ab-section-header { max-width: 680px; margin-bottom: 3rem; }
.ab-overline {
  font-size: .72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: var(--accent);
  margin-bottom: .75rem;
  display: block;
}
.ab-section-title {
  font-size: clamp(1.6rem, 3.5vw, 2.4rem);
  font-weight: 900;
  color: var(--primary);
  line-height: 1.2;
  margin-bottom: 1rem;
  letter-spacing: -.5px;
}
.ab-section-lead {
  font-size: 1.05rem;
  color: var(--text-2);
  line-height: 1.8;
}

/* ── Section acheteur — parcours horizontal ── */
.buyer-steps {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0;
  margin-bottom: 3rem;
  position: relative;
}
.buyer-steps::before {
  content: '';
  position: absolute;
  top: 36px;
  left: calc(10% + 18px);
  right: calc(10% + 18px);
  height: 2px;
  background: linear-gradient(90deg, var(--accent), var(--blue));
}
@media (max-width: 768px) {
  .buyer-steps { grid-template-columns: 1fr 1fr; }
  .buyer-steps::before { display: none; }
}
.buyer-step { text-align: center; padding: 0 .75rem; }
.buyer-step-icon {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
  position: relative;
  z-index: 1;
  transition: transform .3s;
}
.buyer-step:hover .buyer-step-icon { transform: translateY(-4px); }
.buyer-step-num {
  position: absolute;
  top: -4px;
  right: -4px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--accent);
  color: #fff;
  font-size: .65rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #fff;
}
.buyer-step h4 { font-size: .95rem; font-weight: 700; color: var(--primary); margin-bottom: .4rem; }
.buyer-step p  { font-size: .81rem; color: var(--text-2); line-height: 1.6; }

/* Avantages grille */
.advantages-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
}
.advantage-item {
  background: #fff;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 12px rgba(0,0,0,.06);
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  border-left: 4px solid var(--accent);
  transition: box-shadow .2s, transform .2s;
}
.advantage-item:hover { box-shadow: 0 8px 24px rgba(0,0,0,.1); transform: translateY(-2px); }
.adv-icon {
  width: 40px; height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, var(--accent), #ff9e6b);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #fff;
}
.advantage-item h5 { font-size: .9rem; font-weight: 700; color: var(--primary); margin-bottom: .3rem; }
.advantage-item p  { font-size: .8rem; color: var(--text-2); line-height: 1.55; }

/* ── Section vendeur — comparaison ── */
.seller-compare {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2.5rem;
}
@media (max-width: 700px) { .seller-compare { grid-template-columns: 1fr; } }
.seller-model {
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(0,0,0,.08);
}
.seller-model-header {
  padding: 1.75rem 1.75rem 1.25rem;
  color: #fff;
}
.seller-model.managed .seller-model-header { background: linear-gradient(135deg, #1B2A41, #334155); }
.seller-model.autonomous .seller-model-header { background: linear-gradient(135deg, #FF6B2B, #e55b1f); }
.seller-model-header h3 { font-size: 1.15rem; font-weight: 800; margin-bottom: .4rem; }
.seller-model-header p  { font-size: .85rem; opacity: .82; line-height: 1.6; }
.seller-model-badge {
  display: inline-block;
  background: rgba(255,255,255,.15);
  border: 1px solid rgba(255,255,255,.25);
  padding: .2rem .7rem;
  border-radius: 50px;
  font-size: .7rem;
  font-weight: 700;
  margin-bottom: .75rem;
}
.seller-model-body { background: #fff; padding: 1.75rem; }
.seller-feat-list { list-style: none; padding: 0; margin: 0 0 1.5rem; }
.seller-feat-list li {
  display: flex; gap: .7rem; align-items: flex-start;
  padding: .55rem 0;
  border-bottom: 1px solid #f1f5f9;
  font-size: .88rem; color: var(--text-2); line-height: 1.5;
}
.seller-feat-list li:last-child { border-bottom: none; }
.seller-feat-icon { width: 20px; height: 20px; flex-shrink: 0; margin-top: 1px; }
.seller-proscons { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-top: 1rem; }
.seller-pro, .seller-con {
  border-radius: 8px;
  padding: .85rem 1rem;
  font-size: .8rem;
  line-height: 1.55;
}
.seller-pro { background: #d1fae5; color: #065f46; }
.seller-pro strong, .seller-con strong { display: block; font-weight: 700; margin-bottom: .25rem; }
.seller-con { background: #fef3c7; color: #92400e; }

/* ── Section logistique ── */
.logistics-timeline {
  position: relative;
  margin: 2rem 0 3rem;
}
.logistics-timeline::before {
  content: '';
  position: absolute;
  left: 35px;
  top: 0; bottom: 0;
  width: 2px;
  background: linear-gradient(to bottom, var(--accent), var(--blue));
}
@media (max-width: 600px) { .logistics-timeline::before { display: none; } }
.log-item {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 2.5rem;
  position: relative;
}
.log-icon {
  width: 72px; height: 72px;
  border-radius: 50%;
  background: #fff;
  border: 3px solid var(--accent);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  color: var(--accent);
  position: relative;
  z-index: 1;
  box-shadow: 0 4px 16px rgba(255,107,43,.2);
}
.log-item:nth-child(2) .log-icon { border-color: #5469d4; color: #5469d4; box-shadow: 0 4px 16px rgba(84,105,212,.2); }
.log-item:nth-child(3) .log-icon { border-color: var(--success); color: var(--success); box-shadow: 0 4px 16px rgba(16,185,129,.2); }
.log-item:nth-child(4) .log-icon { border-color: var(--warning); color: var(--warning); box-shadow: 0 4px 16px rgba(245,158,11,.2); }
.log-content { padding-top: .75rem; }
.log-content h4 { font-size: 1.05rem; font-weight: 800; color: var(--primary); margin-bottom: .5rem; }
.log-content p  { font-size: .9rem; color: var(--text-2); line-height: 1.75; max-width: 600px; }
.log-content .log-tag {
  display: inline-block;
  background: #f1f5f9; color: var(--text-2);
  padding: .2rem .65rem; border-radius: 50px;
  font-size: .72rem; font-weight: 600;
  margin-top: .65rem;
}
/* Statistiques logistique */
.log-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  background: linear-gradient(135deg, var(--primary), #263550);
  border-radius: 16px;
  padding: 2rem;
  color: #fff;
}
@media (max-width: 700px) { .log-stats { grid-template-columns: repeat(2, 1fr); } }
.log-stat { text-align: center; }
.log-stat-val { font-size: 2rem; font-weight: 900; color: #fff; line-height: 1; }
.log-stat-val span { color: var(--accent); }
.log-stat-lbl { font-size: .75rem; color: #94a3b8; margin-top: .35rem; }

/* ── Section revenus ── */
.revenue-tabs { margin-top: 2.5rem; }
.revenue-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.25rem;
}
.rev-card {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,.07);
  transition: transform .2s, box-shadow .2s;
}
.rev-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.12); }
.rev-card-top {
  padding: 1.75rem 1.75rem 1.25rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}
.rev-card-icon {
  width: 48px; height: 48px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #fff;
}
.rev-card-top h4 { font-size: 1rem; font-weight: 800; color: var(--primary); margin-bottom: .25rem; }
.rev-card-top p  { font-size: .78rem; color: var(--text-3); }
.rev-card-body { padding: 0 1.75rem 1.75rem; }
.rev-card-body p { font-size: .87rem; color: var(--text-2); line-height: 1.7; }
.rev-pct {
  display: inline-block;
  font-size: .75rem;
  font-weight: 700;
  padding: .3rem .75rem;
  border-radius: 50px;
  margin-top: .75rem;
}

/* ── Section services ── */
.services-bento {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-template-rows: auto;
  gap: 1.25rem;
}
@media (max-width: 900px) { .services-bento { grid-template-columns: 1fr 1fr; } }
@media (max-width: 600px) { .services-bento { grid-template-columns: 1fr; } }
.svc-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,.06);
  padding: 1.75rem;
  transition: transform .2s, box-shadow .2s;
  display: flex;
  flex-direction: column;
  gap: .75rem;
}
.svc-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.1); }
.svc-card.featured {
  grid-column: span 2;
  background: linear-gradient(135deg, #1B2A41 0%, #263550 100%);
  color: #fff;
}
@media (max-width: 900px) { .svc-card.featured { grid-column: span 2; } }
@media (max-width: 600px) { .svc-card.featured { grid-column: span 1; } }
.svc-icon {
  width: 52px; height: 52px;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #fff;
}
.svc-card h4 { font-size: 1rem; font-weight: 800; color: var(--primary); }
.svc-card.featured h4 { color: #fff; }
.svc-card p  { font-size: .87rem; color: var(--text-2); line-height: 1.65; flex: 1; }
.svc-card.featured p { color: #94a3b8; }

/* ── Résumé final ── */
.ab-summary {
  background: linear-gradient(135deg, #0f1d2e 0%, #1B2A41 100%);
  color: #fff;
  padding: 6rem 1.25rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.ab-summary::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(ellipse at 30% 60%, rgba(255,107,43,.07), transparent 60%),
                    radial-gradient(ellipse at 70% 20%, rgba(84,105,212,.08), transparent 60%);
}
.ab-summary-inner { position: relative; max-width: 900px; margin: 0 auto; }
.ab-summary h2 {
  font-size: clamp(1.8rem, 4.5vw, 3rem);
  font-weight: 900;
  line-height: 1.15;
  margin-bottom: 1.25rem;
  letter-spacing: -.5px;
}
.ab-summary h2 span { color: var(--accent); }
.ab-summary-lead {
  font-size: 1.05rem;
  color: #94a3b8;
  max-width: 640px;
  margin: 0 auto 3rem;
  line-height: 1.8;
}
.pillars {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.25rem;
  margin: 0 auto 3.5rem;
}
@media (max-width: 700px) { .pillars { grid-template-columns: 1fr; } }
.pillar {
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 16px;
  padding: 2rem 1.5rem;
  text-align: center;
  transition: background .2s, border-color .2s;
}
.pillar:hover { background: rgba(255,255,255,.1); border-color: rgba(255,107,43,.4); }
.pillar-num {
  font-size: 3rem;
  font-weight: 900;
  color: var(--accent);
  opacity: .25;
  line-height: 1;
  margin-bottom: .5rem;
}
.pillar h3 { font-size: 1rem; font-weight: 800; color: #fff; margin-bottom: .6rem; }
.pillar p  { font-size: .83rem; color: #94a3b8; line-height: 1.6; }
.ab-summary-cta { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }

/* ── Alternance fond de section ── */
.bg-white  { background: #fff; }
.bg-light  { background: #f8fafc; }
.bg-dark   { background: var(--primary); }
</style>

<!-- ── Navigation d'ancre ── -->
<div class="about-sticky-nav">
  <ul>
    <li><a href="#acheteur">Acheter</a></li>
    <li><a href="#vendeur">Vendre</a></li>
    <li><a href="#logistique">Logistique</a></li>
    <li><a href="#revenus">Modele economique</a></li>
    <li><a href="#services">Services</a></li>
    <li><a href="#resume">En resume</a></li>
  </ul>
</div>

<!-- ════════════════════════════════════
     HERO
     ════════════════════════════════════ -->
<div class="ab-hero">
  <div class="ab-hero-inner">
    <div class="ab-eyebrow">Plateforme e-commerce</div>
    <h1>Bienvenue sur <span><?= h($siteName) ?></span></h1>
    <p class="ab-hero-sub">
      Une marketplace complète qui connecte des millions d'acheteurs
      à des milliers de vendeurs, avec une logistique intégrée,
      des paiements sécurisés et un ecosystème pensé pour la croissance.
    </p>
    <div class="ab-hero-cta">
      <a href="products.php" class="btn btn-primary btn-lg">Explorer les produits</a>
      <a href="register.php" class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,.3);color:#e2e8f0">Créer un compte</a>
    </div>
    <div class="ab-hero-metrics">
      <div class="ab-metric">
        <div class="ab-metric-val">50<span>K+</span></div>
        <div class="ab-metric-lbl">Produits disponibles</div>
      </div>
      <div class="ab-metric">
        <div class="ab-metric-val">12<span>K+</span></div>
        <div class="ab-metric-lbl">Clients actifs</div>
      </div>
      <div class="ab-metric">
        <div class="ab-metric-val">1<span>000+</span></div>
        <div class="ab-metric-lbl">Vendeurs partenaires</div>
      </div>
      <div class="ab-metric">
        <div class="ab-metric-val">48<span>h</span></div>
        <div class="ab-metric-lbl">Délai livraison moyen</div>
      </div>
    </div>
  </div>
</div>


<!-- ════════════════════════════════════
     SECTION 1 — ACHETEUR
     ════════════════════════════════════ -->
<section id="acheteur" class="ab-section bg-white">
  <div class="ab-section-inner">
    <div class="ab-section-header">
      <span class="ab-overline">Pour les acheteurs</span>
      <h2 class="ab-section-title">Acheter sur <?= h($siteName) ?> en 5 étapes</h2>
      <p class="ab-section-lead">
        De la création de votre compte jusqu'à la réception de votre colis,
        chaque étape est conçue pour être simple, rapide et sécurisée.
        Pas besoin d'expérience préalable.
      </p>
    </div>

    <!-- Parcours 5 étapes -->
    <div class="buyer-steps">
      <!-- Étape 1 -->
      <div class="buyer-step">
        <div class="buyer-step-icon" style="background:linear-gradient(135deg,#1B2A41,#334155)">
          <div class="buyer-step-num">1</div>
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <h4>Créer un compte</h4>
        <p>Inscrivez-vous en 60 secondes avec votre email et un mot de passe. Votre compte centralise vos commandes, adresses et historique.</p>
      </div>
      <!-- Étape 2 -->
      <div class="buyer-step">
        <div class="buyer-step-icon" style="background:linear-gradient(135deg,#5469d4,#3b52c0)">
          <div class="buyer-step-num">2</div>
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <h4>Rechercher des produits</h4>
        <p>Utilisez la barre de recherche ou naviguez par catégorie. Filtrez par prix, popularité et disponibilité pour trouver exactement ce qu'il vous faut.</p>
      </div>
      <!-- Étape 3 -->
      <div class="buyer-step">
        <div class="buyer-step-icon" style="background:linear-gradient(135deg,#FF6B2B,#e55b1f)">
          <div class="buyer-step-num">3</div>
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <h4>Ajouter au panier</h4>
        <p>Choisissez vos articles, ajustez les quantités et ajoutez-les à votre panier. Comparez les offres et achetez tout en une seule commande.</p>
      </div>
      <!-- Étape 4 -->
      <div class="buyer-step">
        <div class="buyer-step-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
          <div class="buyer-step-num">4</div>
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <h4>Payer en toute sécurité</h4>
        <p>Paiement à la livraison, YAS Money, MOOV Money ou virement bancaire. Toutes les transactions sont chiffrées et protégées.</p>
      </div>
      <!-- Étape 5 -->
      <div class="buyer-step">
        <div class="buyer-step-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
          <div class="buyer-step-num">5</div>
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
        </div>
        <h4>Recevoir sa commande</h4>
        <p>Suivez votre colis en temps réel depuis votre compte. Notifié à chaque étape — préparation, expédition, livraison à domicile.</p>
      </div>
    </div>

    <!-- Avantages acheteurs -->
    <div class="advantages-grid">
      <div class="advantage-item">
        <div class="adv-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div>
          <h5>Paiements sécurisés</h5>
          <p>Chaque transaction est protégée. Paiement à la livraison disponible partout.</p>
        </div>
      </div>
      <div class="advantage-item" style="border-left-color:var(--blue)">
        <div class="adv-icon" style="background:linear-gradient(135deg,var(--blue),#3b52c0)">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16,8 20,8 23,11 23,16 16,16 16,8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        </div>
        <div>
          <h5>Livraison rapide</h5>
          <p>Réseau logistique optimisé. Livraison en 24 à 72h selon votre zone.</p>
        </div>
      </div>
      <div class="advantage-item" style="border-left-color:var(--success)">
        <div class="adv-icon" style="background:linear-gradient(135deg,var(--success),#059669)">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9,11 12,14 22,4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div>
          <h5>Retours sous 7 jours</h5>
          <p>Produit non conforme ? Retour simple et remboursement garanti.</p>
        </div>
      </div>
      <div class="advantage-item" style="border-left-color:var(--warning)">
        <div class="adv-icon" style="background:linear-gradient(135deg,var(--warning),#d97706)">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
        </div>
        <div>
          <h5>Support 24h/7j</h5>
          <p>Une équipe disponible pour répondre à toutes vos questions sans délai.</p>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ════════════════════════════════════
     SECTION 2 — VENDEUR
     ════════════════════════════════════ -->
<section id="vendeur" class="ab-section bg-light">
  <div class="ab-section-inner">
    <div class="ab-section-header">
      <span class="ab-overline">Pour les vendeurs</span>
      <h2 class="ab-section-title">Deux façons de vendre sur <?= h($siteName) ?></h2>
      <p class="ab-section-lead">
        <?= h($siteName) ?> s'adapte à votre modèle d'entreprise. Que vous souhaitiez
        confier entièrement votre logistique à la plateforme ou gérer votre stock
        de façon autonome, nous avons la solution adaptée à votre situation.
      </p>
    </div>

    <div class="seller-compare">

      <!-- Modèle 1 : Plateforme gérée -->
      <div class="seller-model managed">
        <div class="seller-model-header">
          <div class="seller-model-badge">Recommandé pour débutants</div>
          <h3>Modèle géré par la plateforme</h3>
          <p>Vous envoyez vos produits dans nos entrepôts. Nous gérons le reste — stockage, préparation, expédition, retours.</p>
        </div>
        <div class="seller-model-body">
          <ul class="seller-feat-list">
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#5469d4" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Stockage de vos produits dans nos entrepôts régionaux
            </li>
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#5469d4" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Préparation automatique des commandes dès la validation
            </li>
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#5469d4" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Expédition via notre réseau de transporteurs partenaires
            </li>
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#5469d4" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Gestion des retours et remboursements prise en charge
            </li>
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#5469d4" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Badge "Livraison garantie" affiché sur vos produits
            </li>
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#5469d4" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Tableau de bord avec statistiques en temps réel
            </li>
          </ul>
          <div class="seller-proscons">
            <div class="seller-pro">
              <strong>Avantage principal</strong>
              Zéro gestion logistique. Concentrez-vous uniquement sur votre catalogue et votre marketing.
            </div>
            <div class="seller-con">
              <strong>Point de vigilance</strong>
              Commission plus élevée (8 à 15%) pour couvrir les frais logistiques de la plateforme.
            </div>
          </div>
        </div>
      </div>

      <!-- Modèle 2 : Autonome -->
      <div class="seller-model autonomous">
        <div class="seller-model-header">
          <div class="seller-model-badge">Idéal pour vendeurs établis</div>
          <h3>Modèle autonome (vendeur indépendant)</h3>
          <p>Vous restez maître de votre stock et de vos expéditions. <?= h($siteName) ?> vous fournit la vitrine et la clientèle.</p>
        </div>
        <div class="seller-model-body">
          <ul class="seller-feat-list">
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#FF6B2B" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Vos stocks restent dans vos locaux ou votre propre entrepôt
            </li>
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#FF6B2B" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Vous choisissez votre propre transporteur et vos tarifs
            </li>
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#FF6B2B" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Flexibilité totale sur les délais et conditions de livraison
            </li>
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#FF6B2B" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Gestion directe de vos retours et de votre relation client
            </li>
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#FF6B2B" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Accès aux mêmes outils marketing et promotionnels
            </li>
            <li>
              <svg class="seller-feat-icon" viewBox="0 0 24 24" fill="none" stroke="#FF6B2B" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
              Commission réduite (3 à 8%) sur chaque vente
            </li>
          </ul>
          <div class="seller-proscons">
            <div class="seller-pro">
              <strong>Avantage principal</strong>
              Marges préservées et contrôle total sur votre chaîne d'approvisionnement.
            </div>
            <div class="seller-con">
              <strong>Point de vigilance</strong>
              Vous êtes responsable de la fiabilité des livraisons, ce qui engage votre réputation.
            </div>
          </div>
        </div>
      </div>

    </div>

    <div style="text-align:center;padding:2rem;background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06)">
      <p style="font-size:1rem;color:var(--text-2);margin-bottom:1.25rem;max-width:540px;margin-left:auto;margin-right:auto;line-height:1.7">
        Prêt à rejoindre <?= h($siteName) ?> en tant que vendeur ? L'inscription est gratuite et vous pouvez commencer à vendre dès aujourd'hui.
      </p>
      <a href="register.php" class="btn btn-primary btn-lg">Devenir vendeur partenaire</a>
    </div>
  </div>
</section>


<!-- ════════════════════════════════════
     SECTION 3 — LOGISTIQUE
     ════════════════════════════════════ -->
<section id="logistique" class="ab-section bg-white">
  <div class="ab-section-inner">
    <div class="ab-section-header">
      <span class="ab-overline">Infrastructure</span>
      <h2 class="ab-section-title">Le système logistique <?= h($siteName) ?></h2>
      <p class="ab-section-lead">
        La logistique est le cœur de notre modèle. Nous avons bâti
        un réseau d'entrepôts et de partenaires de livraison pour garantir
        que chaque colis arrive à destination dans les meilleurs délais,
        en parfait état.
      </p>
    </div>

    <div class="logistics-timeline">

      <div class="log-item">
        <div class="log-icon">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
        </div>
        <div class="log-content">
          <h4>1. Stockage dans nos entrepôts régionaux</h4>
          <p>
            Les vendeurs partenaires envoient leurs produits dans l'un de nos centres logistiques régionaux.
            À réception, chaque article est vérifié, référencé et stocké dans des conditions optimales.
            Notre système d'inventaire en temps réel garantit une traçabilité parfaite de chaque unité.
          </p>
          <span class="log-tag">Entrepôts climatisés — Sécurité 24h/24 — Inventaire numérique</span>
        </div>
      </div>

      <div class="log-item">
        <div class="log-icon" style="border-color:#5469d4;color:#5469d4">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div class="log-content">
          <h4>2. Traitement automatique des commandes</h4>
          <p>
            Dès qu'un client valide son paiement, notre système génère automatiquement un bon de préparation
            et l'achemine vers l'entrepôt le plus proche du point de livraison.
            Un agent de préparation prend en charge le picking, le contrôle qualité et l'emballage
            dans les 2 heures ouvrables suivant la commande.
          </p>
          <span class="log-tag">Traitement sous 2h — Contrôle qualité — Emballage soigné</span>
        </div>
      </div>

      <div class="log-item">
        <div class="log-icon" style="border-color:var(--success);color:var(--success)">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16,8 20,8 23,11 23,16 16,16 16,8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        </div>
        <div class="log-content">
          <h4>3. Expédition via nos partenaires logistiques</h4>
          <p>
            Le colis est confié au transporteur partenaire offrant le meilleur compromis
            délai/coût pour la destination concernée. Notre réseau couvre l'ensemble
            du territoire et s'étend à plusieurs pays de la sous-région.
            Un code de suivi est automatiquement envoyé au client par SMS et par email.
          </p>
          <span class="log-tag">12+ partenaires transporteurs — Suivi SMS — Couverture nationale</span>
        </div>
      </div>

      <div class="log-item">
        <div class="log-icon" style="border-color:var(--warning);color:var(--warning)">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
        </div>
        <div class="log-content">
          <h4>4. Livraison au domicile du client</h4>
          <p>
            Le livreur prend contact avec le client avant de se présenter à son adresse.
            En cas d'absence, une nouvelle tentative de livraison est planifiée
            ou le colis est déposé dans un point relais proche du domicile.
            Le client confirme la réception et peut laisser un avis sur sa commande.
          </p>
          <span class="log-tag">Livraison à domicile — Points relais — Confirmation réception</span>
        </div>
      </div>
    </div>

    <!-- Métriques logistique -->
    <div class="log-stats">
      <div class="log-stat">
        <div class="log-stat-val">4<span>+</span></div>
        <div class="log-stat-lbl">Entrepôts régionaux</div>
      </div>
      <div class="log-stat">
        <div class="log-stat-val">12<span>+</span></div>
        <div class="log-stat-lbl">Partenaires livraison</div>
      </div>
      <div class="log-stat">
        <div class="log-stat-val">48<span>h</span></div>
        <div class="log-stat-lbl">Délai moyen en ville</div>
      </div>
      <div class="log-stat">
        <div class="log-stat-val">98<span>%</span></div>
        <div class="log-stat-lbl">Taux de livraison réussie</div>
      </div>
    </div>
  </div>
</section>


<!-- ════════════════════════════════════
     SECTION 4 — MODÈLE ÉCONOMIQUE
     ════════════════════════════════════ -->
<section id="revenus" class="ab-section bg-light">
  <div class="ab-section-inner">
    <div class="ab-section-header">
      <span class="ab-overline">Modèle économique</span>
      <h2 class="ab-section-title">Comment <?= h($siteName) ?> génère ses revenus</h2>
      <p class="ab-section-lead">
        Notre modèle repose sur quatre sources de revenus complémentaires
        qui permettent à la plateforme d'investir continuellement dans
        l'amélioration du service, l'extension du réseau logistique
        et le développement de nouvelles fonctionnalités.
      </p>
    </div>

    <div class="revenue-grid">

      <div class="rev-card">
        <div class="rev-card-top">
          <div class="rev-card-icon" style="background:linear-gradient(135deg,#FF6B2B,#e55b1f)">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div>
            <h4>Commissions sur ventes</h4>
            <p>Source principale de revenus</p>
          </div>
        </div>
        <div class="rev-card-body">
          <p>
            Pour chaque transaction réalisée sur la plateforme, <?= h($siteName) ?> prélève
            un pourcentage du montant de la vente, variant entre 3% et 15%
            selon la catégorie de produit et le modèle logistique choisi.
            Ce système aligne les intérêts de la plateforme avec ceux du vendeur.
          </p>
          <span class="rev-pct" style="background:#fff0e8;color:#FF6B2B">3% à 15% par transaction</span>
        </div>
      </div>

      <div class="rev-card">
        <div class="rev-card-top">
          <div class="rev-card-icon" style="background:linear-gradient(135deg,#5469d4,#3b52c0)">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
          </div>
          <div>
            <h4>Abonnements vendeurs</h4>
            <p>Plans mensuels et annuels</p>
          </div>
        </div>
        <div class="rev-card-body">
          <p>
            Les vendeurs professionnels souscrivent à des plans d'abonnement
            qui leur donnent accès à des avantages exclusifs :
            mise en avant de catalogue, quota d'images étendu,
            outils analytiques avancés, support prioritaire et accès
            aux campagnes promotionnelles saisonnières.
          </p>
          <span class="rev-pct" style="background:#dbeafe;color:#1e40af">Plans à partir de 5 000 FCFA/mois</span>
        </div>
      </div>

      <div class="rev-card">
        <div class="rev-card-top">
          <div class="rev-card-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
          </div>
          <div>
            <h4>Publicité et sponsorisation</h4>
            <p>Visibilité ciblée pour les vendeurs</p>
          </div>
        </div>
        <div class="rev-card-body">
          <p>
            Les vendeurs peuvent sponsoriser leurs produits pour
            apparaître en tête des résultats de recherche, sur la page d'accueil
            ou dans les pages de catégories. Ce système de publicité au coût
            par clic (CPC) garantit un retour sur investissement mesurable
            et une visibilité au moment où l'acheteur est prêt à acheter.
          </p>
          <span class="rev-pct" style="background:#d1fae5;color:#065f46">Paiement au clic ou à l'impression</span>
        </div>
      </div>

      <div class="rev-card">
        <div class="rev-card-top">
          <div class="rev-card-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16,8 20,8 23,11 23,16 16,16 16,8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          </div>
          <div>
            <h4>Services logistiques annexes</h4>
            <p>Frais d'entrepôt et d'expédition</p>
          </div>
        </div>
        <div class="rev-card-body">
          <p>
            Les vendeurs qui utilisent nos entrepôts paient des frais
            de stockage (au m³ ou à l'unité) et de préparation de commandes.
            Ces services permettent à <?= h($siteName) ?> d'autofinancer
            son infrastructure logistique tout en offrant aux vendeurs
            une solution clé en main à un tarif compétitif par rapport
            à une gestion logistique en propre.
          </p>
          <span class="rev-pct" style="background:#fef3c7;color:#92400e">Tarification transparente à l'unité</span>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ════════════════════════════════════
     SECTION 5 — SERVICES SUPPLÉMENTAIRES
     ════════════════════════════════════ -->
<section id="services" class="ab-section bg-white">
  <div class="ab-section-inner">
    <div class="ab-section-header">
      <span class="ab-overline">Ecosystème complet</span>
      <h2 class="ab-section-title">Les services supplémentaires <?= h($siteName) ?></h2>
      <p class="ab-section-lead">
        Au-delà de la marketplace, <?= h($siteName) ?> construit un écosystème
        de services pensés pour améliorer chaque aspect de l'expérience
        d'achat et de vente. Ces fonctionnalités font la différence
        entre une simple boutique en ligne et une plateforme de référence.
      </p>
    </div>

    <div class="services-bento">

      <!-- Application mobile — featured -->
      <div class="svc-card featured">
        <div class="svc-icon" style="background:rgba(255,107,43,.25)">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FF6B2B" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18" stroke-width="3" stroke-linecap="round"/></svg>
        </div>
        <h4>Application mobile World Compass</h4>
        <p>
          Achetez, vendez et gérez vos commandes depuis votre smartphone.
          Notifications push pour le suivi de livraison, paiement en un tap,
          gestion de catalogue simplifié et accès aux offres exclusives
          réservées aux utilisateurs de l'application.
          Disponible sur Android et iOS.
        </p>
        <div style="display:flex;gap:.65rem;margin-top:.25rem;flex-wrap:wrap">
          <span style="display:inline-block;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#e2e8f0;padding:.3rem .85rem;border-radius:50px;font-size:.75rem;font-weight:600">Android</span>
          <span style="display:inline-block;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#e2e8f0;padding:.3rem .85rem;border-radius:50px;font-size:.75rem;font-weight:600">iOS</span>
          <span style="display:inline-block;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#e2e8f0;padding:.3rem .85rem;border-radius:50px;font-size:.75rem;font-weight:600">Bientôt disponible</span>
        </div>
      </div>

      <!-- Compte Premium -->
      <div class="svc-card">
        <div class="svc-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>
        </div>
        <h4>Compte Premium acheteur</h4>
        <p>
          Livraison prioritaire sous 24h, accès anticipé aux ventes flash,
          retours gratuits illimités, support client dédié et réductions
          exclusives sur les catégories premium.
        </p>
      </div>

      <!-- Outils analytiques -->
      <div class="svc-card">
        <div class="svc-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <h4>Outils analytiques vendeurs</h4>
        <p>
          Tableau de bord complet : évolution des ventes, taux de conversion,
          produits les plus consultés, avis clients, comparaison concurrentielle
          et prévisions de stock.
        </p>
      </div>

      <!-- Programme fidélité -->
      <div class="svc-card">
        <div class="svc-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <h4>Programme de fidélité</h4>
        <p>
          Accumulez des points à chaque achat. Échangez-les contre des
          réductions, des crédits de livraison ou des accès prioritaires
          aux ventes privées et aux nouvelles collections.
        </p>
      </div>

      <!-- SAV intégré -->
      <div class="svc-card">
        <div class="svc-icon" style="background:linear-gradient(135deg,#0891b2,#0e7490)">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <h4>Service après-vente intégré</h4>
        <p>
          Ouverture de litige, demande de remboursement, échange de produit —
          tout se gère directement depuis votre espace client,
          sans appel téléphonique ni démarche complexe.
        </p>
      </div>

      <!-- Ventes flash -->
      <div class="svc-card">
        <div class="svc-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626)">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13,2 3,14 12,14 11,22 21,10 12,10"/></svg>
        </div>
        <h4>Ventes flash et promotions</h4>
        <p>
          Des offres limitées dans le temps avec des réductions jusqu'à 70%.
          Les vendeurs peuvent programmer leurs propres ventes flash
          pour écouler les stocks et attirer de nouveaux clients.
        </p>
      </div>

    </div>
  </div>
</section>


<!-- ════════════════════════════════════
     RÉSUMÉ FINAL
     ════════════════════════════════════ -->
<div id="resume" class="ab-summary" style="scroll-margin-top:120px">
  <div class="ab-summary-inner">
    <div class="ab-eyebrow">En résumé</div>
    <h2>
      <?= h($siteName) ?>, bien plus qu'une boutique en ligne.<br>
      <span>Une plateforme qui crée de la valeur à chaque étape.</span>
    </h2>
    <p class="ab-summary-lead">
      Que vous soyez acheteur à la recherche du meilleur prix,
      vendeur souhaitant développer son activité commerciale en ligne
      ou entrepreneur cherchant une opportunité concrète,
      <?= h($siteName) ?> est la plateforme qui grandit avec vous.
    </p>

    <div class="pillars">
      <div class="pillar">
        <div class="pillar-num">01</div>
        <h3>Une marketplace complète</h3>
        <p>Des milliers de produits dans toutes les catégories, des prix compétitifs, des avis clients vérifiés et une expérience d'achat fluide du premier clic à la livraison.</p>
      </div>
      <div class="pillar">
        <div class="pillar-num">02</div>
        <h3>Une solution logistique intégrée</h3>
        <p>Entrepôts régionaux, partenaires transporteurs, suivi en temps réel et gestion des retours. Une infrastructure logistique professionnelle accessible à tous les vendeurs.</p>
      </div>
      <div class="pillar">
        <div class="pillar-num">03</div>
        <h3>Une opportunité business réelle</h3>
        <p>Deux modèles de vente adaptés à votre situation, des outils de croissance intégrés et un accès immédiat à une base de clients active et en expansion continue.</p>
      </div>
    </div>

    <div class="ab-summary-cta">
      <a href="products.php" class="btn btn-primary btn-lg">Commencer mes achats</a>
      <a href="register.php" class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,.25);color:#e2e8f0">Devenir vendeur</a>
    </div>
  </div>
</div>

<script>
// Navigation d'ancre active
(function(){
  const links = document.querySelectorAll('.about-sticky-nav a');
  const sections = [];
  links.forEach(l => {
    const id = l.getAttribute('href').replace('#','');
    const el = document.getElementById(id);
    if (el) sections.push({ el, link: l });
  });
  function update(){
    const scrollY = window.scrollY + 160;
    let active = null;
    sections.forEach(({ el, link }) => {
      if (el.offsetTop <= scrollY) active = link;
    });
    links.forEach(l => l.classList.remove('active'));
    if (active) active.classList.add('active');
  }
  window.addEventListener('scroll', update, { passive: true });
  update();
})();
</script>

<?php require_once 'includes/footer.php'; ?>
