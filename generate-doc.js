const fs = require('fs');
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, HeadingLevel, BorderStyle, WidthType,
  ShadingType, VerticalAlign, PageNumber, PageBreak, LevelFormat,
  ExternalHyperlink, TableOfContents, UnderlineType
} = require('docx');

// ─── COLOURS ────────────────────────────────────────────────────────────────
const NAVY   = '1B2A41';
const ORANGE = 'FF6B2B';
const GREY1  = '334155';
const GREY2  = '64748B';
const GREY3  = 'CBD5E1';
const LIGHT  = 'F8FAFC';
const WHITE  = 'FFFFFF';
const GREEN  = 'D1FAE5';
const GREEN_T= '065F46';
const BLUE   = 'DBEAFE';
const BLUE_T = '1E40AF';
const AMBER  = 'FEF3C7';
const AMBER_T= '92400E';

// ─── PAGE GEOMETRY (A4) ─────────────────────────────────────────────────────
const PAGE_W   = 11906;   // A4 width in DXA
const PAGE_H   = 16838;
const MARGIN   = 1080;    // ~1.9 cm margins
const CONTENT_W = PAGE_W - MARGIN * 2;   // 9746 DXA

// ─── BORDERS ────────────────────────────────────────────────────────────────
const noBorder = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };
const noBorders = { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder };
const cellBorder = { style: BorderStyle.SINGLE, size: 1, color: GREY3 };
const cellBorders = { top: cellBorder, bottom: cellBorder, left: cellBorder, right: cellBorder };

// ─── HELPERS ────────────────────────────────────────────────────────────────
function sp(before = 0, after = 0) {
  return { spacing: { before, after } };
}

function run(text, opts = {}) {
  return new TextRun({
    text,
    font: 'Calibri',
    size: opts.size || 22,
    bold: opts.bold || false,
    italics: opts.italics || false,
    color: opts.color || GREY1,
    underline: opts.underline ? { type: UnderlineType.SINGLE } : undefined,
  });
}

function para(children, opts = {}) {
  return new Paragraph({
    alignment: opts.align || AlignmentType.LEFT,
    spacing: { before: opts.before || 0, after: opts.after || 120 },
    indent: opts.indent ? { left: opts.indent } : undefined,
    numbering: opts.numbering,
    heading: opts.heading,
    pageBreakBefore: opts.pageBreak || false,
    children: Array.isArray(children) ? children : [children],
  });
}

function heading1(text, pageBreak = false) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_1,
    pageBreakBefore: pageBreak,
    spacing: { before: 200, after: 160 },
    children: [new TextRun({ text, font: 'Calibri', size: 36, bold: true, color: WHITE })],
    shading: { fill: NAVY, type: ShadingType.CLEAR },
    indent: { left: 240, right: 240 },
    border: {
      bottom: { style: BorderStyle.SINGLE, size: 3, color: ORANGE }
    }
  });
}

function heading2(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_2,
    spacing: { before: 280, after: 100 },
    border: { left: { style: BorderStyle.SINGLE, size: 16, color: ORANGE } },
    indent: { left: 200 },
    children: [new TextRun({ text, font: 'Calibri', size: 26, bold: true, color: NAVY })],
  });
}

function heading3(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_3,
    spacing: { before: 200, after: 80 },
    children: [new TextRun({ text, font: 'Calibri', size: 24, bold: true, color: GREY1 })],
  });
}

function bodyText(text, opts = {}) {
  return new Paragraph({
    spacing: { before: 40, after: 120 },
    alignment: opts.justify ? AlignmentType.JUSTIFIED : AlignmentType.LEFT,
    children: [new TextRun({ text, font: 'Calibri', size: 22, color: GREY1, italics: opts.italics || false })],
  });
}

function bulletItem(text, bold_prefix = '') {
  return new Paragraph({
    numbering: { reference: 'bullets', level: 0 },
    spacing: { before: 40, after: 60 },
    children: bold_prefix
      ? [new TextRun({ text: bold_prefix, font: 'Calibri', size: 22, bold: true, color: NAVY }),
         new TextRun({ text, font: 'Calibri', size: 22, color: GREY1 })]
      : [new TextRun({ text, font: 'Calibri', size: 22, color: GREY1 })],
  });
}

function infoBox(title, text, fill = BLUE, textColor = BLUE_T) {
  const col1 = Math.round(CONTENT_W * 0.04);
  const col2 = CONTENT_W - col1;
  return new Table({
    width: { size: CONTENT_W, type: WidthType.DXA },
    columnWidths: [col1, col2],
    rows: [
      new TableRow({
        children: [
          new TableCell({
            width: { size: col1, type: WidthType.DXA },
            borders: noBorders,
            shading: { fill: ORANGE, type: ShadingType.CLEAR },
            children: [para([])],
          }),
          new TableCell({
            width: { size: col2, type: WidthType.DXA },
            borders: noBorders,
            shading: { fill, type: ShadingType.CLEAR },
            margins: { top: 120, bottom: 120, left: 180, right: 180 },
            children: [
              new Paragraph({
                spacing: { before: 0, after: 80 },
                children: [new TextRun({ text: title, font: 'Calibri', size: 22, bold: true, color: textColor })]
              }),
              new Paragraph({
                spacing: { before: 0, after: 0 },
                children: [new TextRun({ text, font: 'Calibri', size: 21, color: textColor })]
              }),
            ],
          }),
        ],
      }),
    ],
  });
}

function blankLine() {
  return new Paragraph({ spacing: { before: 0, after: 100 }, children: [] });
}

// ─── STANDARD TABLE ROWS ────────────────────────────────────────────────────
function headerRow(labels, widths) {
  return new TableRow({
    tableHeader: true,
    children: labels.map((label, i) =>
      new TableCell({
        width: { size: widths[i], type: WidthType.DXA },
        borders: cellBorders,
        shading: { fill: NAVY, type: ShadingType.CLEAR },
        margins: { top: 80, bottom: 80, left: 120, right: 120 },
        verticalAlign: VerticalAlign.CENTER,
        children: [new Paragraph({
          spacing: { before: 0, after: 0 },
          children: [new TextRun({ text: label, font: 'Calibri', size: 20, bold: true, color: WHITE })]
        })],
      })
    ),
  });
}

function dataRow(cells, widths, shade = false) {
  return new TableRow({
    children: cells.map((cell, i) => {
      const parts = Array.isArray(cell)
        ? cell.map(({ text, bold }) => new TextRun({ text, font: 'Calibri', size: 20, color: bold ? NAVY : GREY1, bold: !!bold }))
        : [new TextRun({ text: String(cell), font: 'Calibri', size: 20, color: GREY1 })];
      return new TableCell({
        width: { size: widths[i], type: WidthType.DXA },
        borders: cellBorders,
        shading: { fill: shade ? LIGHT : WHITE, type: ShadingType.CLEAR },
        margins: { top: 80, bottom: 80, left: 120, right: 120 },
        children: [new Paragraph({ spacing: { before: 0, after: 0 }, children: parts })],
      });
    }),
  });
}

// ─── COVER PAGE ─────────────────────────────────────────────────────────────
function buildCoverPage() {
  const dividerW = Math.round(CONTENT_W * 0.1);
  return [
    // Top spacer
    new Paragraph({ spacing: { before: 1800, after: 0 }, children: [] }),

    // Brand name
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 80 },
      children: [
        new TextRun({ text: 'WORLD ', font: 'Calibri', size: 80, bold: true, color: NAVY }),
        new TextRun({ text: 'COMPASS', font: 'Calibri', size: 80, bold: true, color: ORANGE }),
      ],
    }),

    // Tagline
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 600 },
      children: [new TextRun({ text: 'Plateforme de commerce en ligne — Afrique de l\'Ouest', font: 'Calibri', size: 24, color: GREY2, italics: true })],
    }),

    // Orange bar
    new Table({
      width: { size: dividerW, type: WidthType.DXA },
      columnWidths: [dividerW],
      alignment: AlignmentType.CENTER,
      rows: [new TableRow({
        children: [new TableCell({
          width: { size: dividerW, type: WidthType.DXA },
          borders: noBorders,
          shading: { fill: ORANGE, type: ShadingType.CLEAR },
          children: [new Paragraph({ spacing: { before: 40, after: 40 }, children: [] })],
        })],
      })],
    }),

    new Paragraph({ spacing: { before: 600, after: 0 }, children: [] }),

    // Document title
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 120 },
      children: [new TextRun({ text: 'Documentation Technique', font: 'Calibri', size: 48, bold: true, color: NAVY })],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 600 },
      children: [new TextRun({ text: 'Nouvelles Fonctionnalites — Version 2.1', font: 'Calibri', size: 32, color: ORANGE })],
    }),

    // Subtitle
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 1600 },
      children: [new TextRun({ text: 'Ce document presente l\'ensemble des fonctionnalites developpees et integrees dans la plateforme World Compass. Il constitue la reference officielle des livraisons.', font: 'Calibri', size: 22, color: GREY2 })],
    }),

    // Meta table
    new Table({
      width: { size: Math.round(CONTENT_W * 0.5), type: WidthType.DXA },
      columnWidths: [Math.round(CONTENT_W * 0.2), Math.round(CONTENT_W * 0.3)],
      alignment: AlignmentType.CENTER,
      rows: [
        ['Document prepare par', 'Equipe Developpement'],
        ['Date', 'Avril 2026'],
        ['Version', '2.1 — Types de comptes & Commission plateforme'],
        ['Statut', 'Livrable final'],
      ].map(([k, v]) => new TableRow({
        children: [
          new TableCell({
            width: { size: Math.round(CONTENT_W * 0.2), type: WidthType.DXA },
            borders: noBorders,
            shading: { fill: LIGHT, type: ShadingType.CLEAR },
            margins: { top: 60, bottom: 60, left: 120, right: 80 },
            children: [new Paragraph({ spacing: { before: 0, after: 0 }, children: [new TextRun({ text: k, font: 'Calibri', size: 20, bold: true, color: GREY2 })] })],
          }),
          new TableCell({
            width: { size: Math.round(CONTENT_W * 0.3), type: WidthType.DXA },
            borders: noBorders,
            shading: { fill: WHITE, type: ShadingType.CLEAR },
            margins: { top: 60, bottom: 60, left: 120, right: 80 },
            children: [new Paragraph({ spacing: { before: 0, after: 0 }, children: [new TextRun({ text: v, font: 'Calibri', size: 20, color: GREY1 })] })],
          }),
        ],
      })),
    }),

    // Page break to next section
    new Paragraph({ spacing: { before: 0, after: 0 }, pageBreakBefore: false, children: [new PageBreak()] }),
  ];
}

// ─── EXEC SUMMARY ────────────────────────────────────────────────────────────
function buildExecSummary() {
  const cw = CONTENT_W;
  const col4 = Math.round(cw / 4);
  return [
    heading1('Resume executif et perimetre des livraisons'),
    blankLine(),

    // Stats banner
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: [col4, col4, col4, col4],
      rows: [
        new TableRow({
          children: [
            ['8', 'Fonctionnalites majeures livrees'],
            ['10', 'Nouveaux fichiers PHP'],
            ['8', 'Fichiers existants ameliores'],
            ['7', 'Nouvelles tables / entrees SQL'],
          ].map(([val, lbl], i) => new TableCell({
            width: { size: col4, type: WidthType.DXA },
            borders: noBorders,
            shading: { fill: NAVY, type: ShadingType.CLEAR },
            margins: { top: 160, bottom: 160, left: 120, right: 120 },
            verticalAlign: VerticalAlign.CENTER,
            children: [
              new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 0, after: 60 }, children: [new TextRun({ text: val, font: 'Calibri', size: 52, bold: true, color: ORANGE })] }),
              new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 0, after: 0 }, children: [new TextRun({ text: lbl, font: 'Calibri', size: 18, color: GREY3 })] }),
            ],
          })),
        }),
      ],
    }),

    blankLine(),
    bodyText('Cette version 2.1 de World Compass transforme la plateforme d\'une boutique en ligne classique en une marketplace multi-vendeurs complete, dotee de mecanismes de fidelisation des acheteurs, d\'outils promotionnels avances et d\'un systeme de revenus plateforme entierement configurable. Les developpements livres couvrent l\'integralite du parcours vendeur, la segmentation des comptes vendeurs, les ventes flash en temps reel, un programme de points de fidelite entierement operationnel et une commission plateforme activable/desactivable.', { justify: true }),

    heading2('Perimetre des livraisons'),

    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: [Math.round(cw * 0.28), Math.round(cw * 0.14), Math.round(cw * 0.58)],
      rows: [
        headerRow(['Module', 'Statut', 'Description'], [Math.round(cw * 0.28), Math.round(cw * 0.14), Math.round(cw * 0.58)]),
        ...[
          ['Portail vendeur',               'Nouveau',   'Inscription, tableau de bord, gestion produits et commissions'],
          ['Types de comptes vendeur',       'Nouveau',   'Subdivision Compte Individuel / Compte Entreprise avec frais d\'ouverture differencies et configurables'],
          ['Commission plateforme',          'Nouveau',   'Taux configurable preleve par World Compass sur chaque vente vendeur, activable/desactivable'],
          ['Ventes flash',                   'Nouveau',   'Page dediee, countdown en temps reel, badges sur les produits concernes'],
          ['Programme de fidelite',          'Nouveau',   'Accumulation de points, historique, reduction applicable a la caisse'],
          ['Commissions vendeurs',           'Nouveau',   'Calcul automatique et suivi par le vendeur et l\'administrateur'],
          ['Administration vendeurs',        'Nouveau',   'Approbation/rejet des candidatures avec affichage du type de compte et des frais dus'],
          ['Caisse (checkout)',              'Ameliore',  'Points fidelite, commissions vendeurs et commission plateforme generes automatiquement'],
          ['En-tete et navigation',          'Ameliore',  'Liens vers Ventes Flash, Vendre, Ma Boutique selon le role utilisateur'],
          ['Parametres administrateur',      'Ameliore',  'Nouveaux champs : frais d\'ouverture Individuel/Entreprise, taux et toggle commission plateforme'],
        ].map(([m, s, d], i) => dataRow(
          [[{text: m, bold: true}], [{text: s}], [{text: d}]],
          [Math.round(cw * 0.28), Math.round(cw * 0.14), Math.round(cw * 0.58)],
          i % 2 === 0
        )),
      ],
    }),

    new Paragraph({ spacing: { before: 0, after: 0 }, children: [new PageBreak()] }),
  ];
}

// ─── SECTION 1 — PORTAIL VENDEUR ─────────────────────────────────────────────
function buildSection1() {
  const cw = CONTENT_W;
  const c2 = Math.round(cw / 2) - 100;
  return [
    heading1('Section 1 — Portail vendeur : Inscription et candidature', true),
    new Paragraph({ spacing: { before: 0, after: 40 }, children: [new TextRun({ text: 'Fichier : devenir-vendeur.php', font: 'Calibri', size: 20, italics: true, color: GREY2 })] }),

    bodyText('La page Devenir vendeur permet a tout utilisateur inscrit de soumettre une candidature pour vendre ses produits sur la plateforme World Compass. Elle est accessible depuis la barre de navigation principale via le lien "Vendre". La page est organisee en trois etapes sequentielles : choix du type de compte, informations de la boutique, puis choix du modele logistique.', { justify: true }),

    heading2('Etape 1 — Types de comptes vendeur'),
    bodyText('La premiere etape du formulaire de candidature presente deux types de comptes distincts. Le type de compte determine les frais d\'ouverture appliques lors de l\'approbation du dossier.'),
    blankLine(),

    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: [c2, c2],
      rows: [
        new TableRow({
          children: [
            new TableCell({
              width: { size: c2, type: WidthType.DXA },
              borders: cellBorders,
              shading: { fill: BLUE, type: ShadingType.CLEAR },
              margins: { top: 160, bottom: 160, left: 200, right: 200 },
              children: [
                new Paragraph({ spacing: { before: 0, after: 80 }, children: [new TextRun({ text: 'Compte Individuel', font: 'Calibri', size: 24, bold: true, color: BLUE_T })] }),
                new Paragraph({ spacing: { before: 0, after: 80 }, children: [new TextRun({ text: 'Destine aux particuliers, auto-entrepreneurs et micro-vendeurs. Permet de demarrer rapidement avec un abonnement accessible. Limite a 50 produits actifs.', font: 'Calibri', size: 21, color: BLUE_T })] }),
                new Paragraph({ spacing: { before: 0, after: 0 }, children: [new TextRun({ text: 'Frais d\'ouverture : 5 000 FCFA (configurable)', font: 'Calibri', size: 22, bold: true, color: NAVY })] }),
              ],
            }),
            new TableCell({
              width: { size: c2, type: WidthType.DXA },
              borders: cellBorders,
              shading: { fill: 'EDE9FE', type: ShadingType.CLEAR },
              margins: { top: 160, bottom: 160, left: 200, right: 200 },
              children: [
                new Paragraph({ spacing: { before: 0, after: 80 }, children: [new TextRun({ text: 'Compte Entreprise', font: 'Calibri', size: 24, bold: true, color: '5B21B6' })] }),
                new Paragraph({ spacing: { before: 0, after: 80 }, children: [new TextRun({ text: 'Destine aux societes, PME et marques. Acces a toutes les fonctionnalites avancees : produits illimites, analyses completes, badge Vendeur certifie, support prioritaire.', font: 'Calibri', size: 21, color: '5B21B6' })] }),
                new Paragraph({ spacing: { before: 0, after: 0 }, children: [new TextRun({ text: 'Frais d\'ouverture : 20 000 FCFA (configurable)', font: 'Calibri', size: 22, bold: true, color: '5B21B6' })] }),
              ],
            }),
          ],
        }),
      ],
    }),

    blankLine(),
    infoBox('Frais configurables par l\'administrateur', 'Les montants des frais d\'ouverture (Compte Individuel et Compte Entreprise) sont entierement configurables depuis la page Parametres de l\'administration, sans modifier le code. La mise a jour prend effet instantanement sur le formulaire de candidature.', BLUE, BLUE_T),
    blankLine(),

    heading2('Etape 2 — Informations boutique'),
    bodyText('Le vendeur renseigne le nom de sa boutique et une description de son activite (minimum 30 caracteres). Ces informations sont enregistrees dans la demande et reportees automatiquement sur le profil lors de l\'approbation.'),
    blankLine(),

    heading2('Etape 3 — Modele de vente'),
    bodyText('Independamment du type de compte, le vendeur choisit son modele logistique :'),
    blankLine(),

    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: [c2, c2],
      rows: [
        new TableRow({
          children: [
            new TableCell({
              width: { size: c2, type: WidthType.DXA },
              borders: cellBorders,
              shading: { fill: BLUE, type: ShadingType.CLEAR },
              margins: { top: 160, bottom: 160, left: 200, right: 200 },
              children: [
                new Paragraph({ spacing: { before: 0, after: 80 }, children: [new TextRun({ text: 'Plateforme geree', font: 'Calibri', size: 24, bold: true, color: BLUE_T })] }),
                new Paragraph({ spacing: { before: 0, after: 80 }, children: [new TextRun({ text: 'World Compass prend en charge le stockage et l\'expedition des produits. Ideal pour les vendeurs debutants ou souhaitant externaliser la logistique.', font: 'Calibri', size: 21, color: BLUE_T })] }),
                new Paragraph({ spacing: { before: 0, after: 0 }, children: [new TextRun({ text: 'Commission vendeur : 12% par vente', font: 'Calibri', size: 22, bold: true, color: NAVY })] }),
              ],
            }),
            new TableCell({
              width: { size: c2, type: WidthType.DXA },
              borders: cellBorders,
              shading: { fill: GREEN, type: ShadingType.CLEAR },
              margins: { top: 160, bottom: 160, left: 200, right: 200 },
              children: [
                new Paragraph({ spacing: { before: 0, after: 80 }, children: [new TextRun({ text: 'Vendeur autonome', font: 'Calibri', size: 24, bold: true, color: GREEN_T })] }),
                new Paragraph({ spacing: { before: 0, after: 80 }, children: [new TextRun({ text: 'Le vendeur conserve son propre stock et gere lui-meme les expeditions. Offre une marge preservee et un controle total sur la chaine d\'approvisionnement.', font: 'Calibri', size: 21, color: GREEN_T })] }),
                new Paragraph({ spacing: { before: 0, after: 0 }, children: [new TextRun({ text: 'Commission vendeur : 6% par vente', font: 'Calibri', size: 22, bold: true, color: GREEN_T })] }),
              ],
            }),
          ],
        }),
      ],
    }),

    blankLine(),
    infoBox('Securite technique', 'Le formulaire est protege par un jeton CSRF. Les demandes sont stockees dans la table seller_applications avec le type de compte et le modele de vente. Un utilisateur ne peut soumettre qu\'une seule demande active a la fois. Le statut de sa demande est affiche en temps reel sur la page (en attente, approuve, rejete).', BLUE, BLUE_T),
    blankLine(),

    heading2('Affichage conditionnel selon le statut'),
    bulletItem('Demande en attente : bandeau jaune informant l\'utilisateur que sa candidature est en cours d\'examen.'),
    bulletItem('Demande approuvee : bandeau vert avec lien direct vers l\'espace vendeur.'),
    bulletItem('Demande rejetee : bandeau rouge avec la note de l\'administrateur.'),
    bulletItem('Utilisateur deja vendeur : redirection automatique vers le tableau de bord vendeur.'),

    new Paragraph({ spacing: { before: 0, after: 0 }, children: [new PageBreak()] }),
  ];
}

// ─── SECTIONS 2 & 3 — DASHBOARD + PRODUITS ──────────────────────────────────
function buildSection2_3() {
  const cw = CONTENT_W;
  const col2 = Math.round(cw / 2);
  const col3 = Math.round(cw / 3);

  return [
    heading1('Section 2 — Tableau de bord vendeur', true),
    new Paragraph({ spacing: { before: 0, after: 40 }, children: [new TextRun({ text: 'Fichier : seller/index.php', font: 'Calibri', size: 20, italics: true, color: GREY2 })] }),

    bodyText('L\'espace vendeur est accessible via le menu principal ("Ma Boutique") pour tout utilisateur possedant le role vendeur ou administrateur. Il dispose d\'une barre de navigation laterale propre, independante de l\'interface d\'administration generale.', { justify: true }),

    heading2('Indicateurs affiches'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: [col2, col2],
      rows: [
        headerRow(['Indicateur', 'Description'], [col2, col2]),
        dataRow([['Ventes totales'], ['Somme des montants vendus via les produits du vendeur, commandes non annulees']], [col2, col2], false),
        dataRow([['Nombre de commandes'], ['Compte des commandes distinctes incluant au moins un produit du vendeur']], [col2, col2], true),
        dataRow([['Produits actifs'], ['Nombre de produits publies et visibles dans le catalogue']], [col2, col2], false),
        dataRow([['Commissions en attente'], ['Montant total des commissions non encore versees par la plateforme']], [col2, col2], true),
      ],
    }),

    bodyText('Le tableau de bord affiche egalement un apercu des 8 commandes les plus recentes et un resume des 6 premiers produits avec leur niveau de stock. Un avertissement "Stock faible" est affiche si un produit descend en dessous de 5 unites.'),
    blankLine(),

    heading1('Section 3 — Gestion des produits par le vendeur'),
    new Paragraph({ spacing: { before: 0, after: 40 }, children: [new TextRun({ text: 'Fichier : seller/products.php', font: 'Calibri', size: 20, italics: true, color: GREY2 })] }),

    bodyText('Chaque vendeur dispose d\'une interface complete de gestion de ses propres produits. Seuls les produits lui appartenant sont visibles et modifiables. Il n\'a aucun acces aux produits des autres vendeurs.', { justify: true }),

    heading2('Fonctionnalites disponibles'),
    bulletItem('Ajout de produit : formulaire complet avec nom, description, prix, ancien prix barre, stock, categorie, couleur de fond et upload de photo.'),
    bulletItem('Upload de photo : zone de depot avec glisser-deposer ou selection depuis l\'explorateur. Formats acceptes : JPG, PNG, WebP. Taille maximale : 5 Mo. Apercu en temps reel avant soumission.'),
    bulletItem('Modification : tous les champs sont modifiables. L\'image actuelle est conservee si aucune nouvelle image n\'est fournie.'),
    bulletItem('Suppression : suppression definitive du produit apres confirmation.'),
    bulletItem('Vente flash : depuis le formulaire, le vendeur peut definir un prix flash et une date de fin. La vente flash est automatiquement desactivee a l\'expiration.'),

    blankLine(),
    infoBox('Controle d\'acces', 'Toutes les operations de modification et suppression verifient que le produit appartient bien au vendeur connecte (seller_id = user_id). Un vendeur ne peut jamais modifier le produit d\'un autre vendeur, meme en manipulant les parametres de l\'URL.', GREEN, GREEN_T),

    new Paragraph({ spacing: { before: 0, after: 0 }, children: [new PageBreak()] }),
  ];
}

// ─── SECTION 4 — COMMISSIONS ─────────────────────────────────────────────────
function buildSection4() {
  const cw = CONTENT_W;
  const c3 = [Math.round(cw * 0.3), Math.round(cw * 0.2), Math.round(cw * 0.5)];
  return [
    heading1('Section 4 — Systeme de commissions', true),
    new Paragraph({ spacing: { before: 0, after: 40 }, children: [new TextRun({ text: 'Fichiers : seller/commissions.php, seller/orders.php', font: 'Calibri', size: 20, italics: true, color: GREY2 })] }),

    bodyText('Le systeme de commissions est entierement automatise. Des qu\'une commande est validee, les commissions dues a la plateforme sont calculees et enregistrees pour chaque produit vendeur present dans la commande.', { justify: true }),

    heading2('Regles de calcul'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: c3,
      rows: [
        headerRow(['Modele vendeur', 'Taux', 'Justification'], c3),
        dataRow([[{text: 'Plateforme geree', bold: true}], [{text: '12%'}], [{text: 'Couvre le stockage, l\'expedition et le service apres-vente assures par World Compass'}]], c3, false),
        dataRow([[{text: 'Vendeur autonome', bold: true}], [{text: '6%'}], [{text: 'Couvre l\'acces a la plateforme et les frais de visibilite uniquement'}]], c3, true),
      ],
    }),

    heading2('Vue vendeur — Page Commissions'),
    bodyText('Depuis la page seller/commissions.php, le vendeur peut consulter :'),
    bulletItem('Le montant total des commissions en attente de versement.'),
    bulletItem('Le montant total des commissions deja versees.'),
    bulletItem('L\'historique detaille de chaque transaction : numero de commande, date, produit vendu, montant de la vente, taux applique, montant de la commission et statut (en attente / verse).'),

    heading2('Vue vendeur — Page Commandes'),
    bodyText('La page seller/orders.php liste toutes les commandes comportant les produits du vendeur, avec le nom du client, la date, le produit concerne, la quantite, le montant et le statut logistique de la commande.'),

    blankLine(),
    infoBox('Declenchement automatique', 'La creation des commissions vendeurs est declenchee automatiquement a la fin de chaque commande validee via la fonction createCommissions(), appelee dans checkout.php. Si un produit n\'a pas de seller_id (produit de la plateforme sans vendeur assigne), aucune commission n\'est generee pour ce produit.', BLUE, BLUE_T),

    blankLine(),
    heading2('Commission plateforme World Compass'),
    bodyText('En complement des commissions vendeurs, la plateforme World Compass peut prelevar sa propre commission sur chaque vente realisee par un vendeur partenaire. Ce systeme est entierement independant des commissions vendeurs.', { justify: true }),
    blankLine(),

    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: c3,
      rows: [
        headerRow(['Parametre', 'Valeur par defaut', 'Description'], c3),
        dataRow([[{text: 'Taux de commission', bold: true}], [{text: '3%'}], [{text: 'Pourcentage preleve sur le montant total de chaque vente vendeur'}]], c3, false),
        dataRow([[{text: 'Activation', bold: true}], [{text: 'Activee'}], [{text: 'La commission peut etre activee ou desactivee instantanement depuis les parametres'}]], c3, true),
        dataRow([[{text: 'Perimetre', bold: true}], [{text: 'Toutes les ventes vendeur'}], [{text: 'S\'applique sur le sous-total de chaque vendeur dans la commande'}]], c3, false),
        dataRow([[{text: 'Table de stockage', bold: true}], [{text: 'platform_commissions'}], [{text: 'Historique de tous les montants preleves, avec statut pending ou paid'}]], c3, true),
      ],
    }),

    blankLine(),
    bodyText('Configuration depuis l\'administration :'),
    bulletItem('Le taux est saisissable avec deux decimales (ex. 3,50%).'),
    bulletItem('Le toggle "Commission activee / desactivee" permet de suspendre ou reprendre le prelevement a tout moment sans modifier le code.'),
    bulletItem('Si la commission est desactivee, aucune entree n\'est creee dans platform_commissions.'),
    bulletItem('Si le taux est 0, aucune commission n\'est generee meme si le toggle est actif.'),

    blankLine(),
    infoBox('Architecture double commission', 'Les deux systemes de commissions sont independants et s\'appliquent en parallele. La commission vendeur (6% ou 12%) est visible par le vendeur dans son espace commissions. La commission plateforme est uniquement visible par l\'administrateur et constitue le revenu direct de World Compass.', AMBER, AMBER_T),

    new Paragraph({ spacing: { before: 0, after: 0 }, children: [new PageBreak()] }),
  ];
}

// ─── SECTION 5 — VENTES FLASH ────────────────────────────────────────────────
function buildSection5() {
  const cw = CONTENT_W;
  return [
    heading1('Section 5 — Ventes flash', true),
    new Paragraph({ spacing: { before: 0, after: 40 }, children: [new TextRun({ text: 'Fichier : ventes-flash.php', font: 'Calibri', size: 20, italics: true, color: GREY2 })] }),

    bodyText('Le module de ventes flash permet de proposer des produits a prix reduit pour une duree limitee. Les reductions sont affichees avec un compte a rebours en temps reel sur la page dediee et sur toutes les cartes produits du catalogue.', { justify: true }),

    heading2('Page dediee — ventes-flash.php'),
    bulletItem('Bandeau hero avec titre et compte a rebours global visant la prochaine vente a expirer.'),
    bulletItem('Grille de tous les produits en flash active, tries par date de fin la plus proche.'),
    bulletItem('Chaque carte affiche un badge "FLASH", le pourcentage de reduction et un minuteur individuel en heures/minutes/secondes mis a jour en temps reel.'),
    bulletItem('Si aucune vente flash n\'est active, une page d\'attente professionnelle est affichee.'),
    bulletItem('Section informative expliquant le fonctionnement : duree limitee, prix garantis, stocks limites.'),

    heading2('Integration dans tout le catalogue'),
    bulletItem('Badge sur les cartes produits : tout produit en flash active affiche automatiquement un badge rouge "FLASH -X%" et un mini-countdown partout dans le site (accueil, categories, recherche, produits recommandes).'),
    bulletItem('Prix affiche : le prix flash remplace le prix normal tant que la vente est active. Le prix original est affiche barre a cote.'),
    bulletItem('Lien dans la navigation : le lien "Ventes Flash" apparait uniquement lorsqu\'il existe au moins un produit en flash active. Il disparait automatiquement sinon.'),

    heading2('Configuration d\'une vente flash'),
    bodyText('Une vente flash se configure depuis le formulaire de modification de produit, aussi bien en administration qu\'en espace vendeur :'),
    bulletItem('Prix flash : le prix de vente reduit pendant la periode flash.'),
    bulletItem('Date de fin : la date et l\'heure exacte de fin de la promotion.'),
    bodyText('Pour desactiver une vente flash, il suffit de laisser le champ "Prix flash" vide ou de definir une date de fin dans le passe.'),

    blankLine(),
    infoBox('Architecture technique', 'Le prix flash n\'est applique que cote affichage. Le prix de base du produit (products.price) n\'est pas modifie. Le prix normal est donc restaure automatiquement a l\'expiration, sans aucune action manuelle.', AMBER, AMBER_T),

    new Paragraph({ spacing: { before: 0, after: 0 }, children: [new PageBreak()] }),
  ];
}

// ─── SECTION 6 — FIDELITE ────────────────────────────────────────────────────
function buildSection6() {
  const cw = CONTENT_W;
  const c2 = [Math.round(cw * 0.45), Math.round(cw * 0.55)];
  return [
    heading1('Section 6 — Programme de fidelite', true),
    new Paragraph({ spacing: { before: 0, after: 40 }, children: [new TextRun({ text: 'Integration dans checkout.php et profile.php', font: 'Calibri', size: 20, italics: true, color: GREY2 })] }),

    bodyText('Le programme de fidelite recompense les acheteurs a chaque commande passee sur la plateforme. Les points accumules peuvent etre utilises comme moyen de paiement partiel lors des commandes suivantes.', { justify: true }),

    heading2('Regles du programme'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: c2,
      rows: [
        headerRow(['Regle', 'Valeur'], c2),
        dataRow([['Accumulation'], ['1 point pour chaque 100 FCFA depenses']], c2, false),
        dataRow([['Valeur d\'un point'], ['1 point = 10 FCFA de reduction']], c2, true),
        dataRow([['Seuil minimal d\'utilisation'], ['100 points (= 1 000 FCFA de reduction)']], c2, false),
        dataRow([['Increment d\'utilisation'], ['Par multiples de 100 points uniquement']], c2, true),
        dataRow([['Exemple : commande 50 000 FCFA'], ['Gain de 500 points = valeur de 5 000 FCFA']], c2, false),
      ],
    }),

    heading2('Fonctionnement a la caisse'),
    bodyText('Lorsqu\'un acheteur connecte dispose d\'au moins 100 points, un bloc de fidelite s\'affiche dans le formulaire de commande. Il peut y saisir le nombre de points a utiliser. La reduction correspondante est calculee dynamiquement et mise a jour dans le recapitulatif sans rechargement de page.'),
    bulletItem('Si l\'acheteur a moins de 100 points, un message informatif lui indique combien de points la commande en cours lui rapportera.'),
    bulletItem('L\'utilisation des points et le credit des nouveaux points sont enregistres simultanement a la validation de la commande.'),
    bulletItem('Un message de confirmation indique les points gagnes apres validation.'),

    heading2('Espace fidelite dans le profil'),
    bodyText('Un nouvel onglet "Fidelite" est disponible dans la page de profil de l\'utilisateur. Il affiche :'),
    bulletItem('Le solde actuel de points et son equivalent en FCFA.'),
    bulletItem('Un bouton direct vers la caisse pour utiliser les points.'),
    bulletItem('Les regles du programme.'),
    bulletItem('L\'historique complet des transactions de points (gains et utilisations) avec la date, le numero de commande et le mouvement en points.'),
    bodyText('Le solde de points est egalement visible en permanence dans la barre laterale du profil, sous forme de bloc dore affichant le nombre de points et leur valeur monetaire.'),

    new Paragraph({ spacing: { before: 0, after: 0 }, children: [new PageBreak()] }),
  ];
}

// ─── SECTION 7 — ADMINISTRATION VENDEURS ─────────────────────────────────────
function buildSection7() {
  const cw = CONTENT_W;
  return [
    heading1('Section 7 — Administration : Gestion des vendeurs', true),
    new Paragraph({ spacing: { before: 0, after: 40 }, children: [new TextRun({ text: 'Fichier : admin/sellers.php', font: 'Calibri', size: 20, italics: true, color: GREY2 })] }),

    bodyText('Une nouvelle page d\'administration est dediee a la gestion complete des vendeurs partenaires. Elle est accessible depuis le menu lateral de l\'administration, avec un badge de notification indiquant le nombre de demandes en attente.', { justify: true }),

    heading2('Traitement des candidatures'),
    bodyText('Les demandes en statut "en attente" sont affichees en priorite dans un encart mis en evidence. Pour chaque demande, l\'administrateur peut voir :'),
    bulletItem('Le nom complet, l\'email et le telephone du candidat.'),
    bulletItem('Le nom de la boutique et le modele de vente choisi.'),
    bulletItem('Le type de compte (Individuel ou Entreprise) avec les frais d\'ouverture correspondants affiches directement.'),
    bulletItem('Un extrait de la description de l\'activite.'),
    bulletItem('La date de soumission de la candidature.'),

    bodyText('Deux actions sont disponibles pour chaque demande, avec un champ de note optionnel :'),
    bulletItem('Approuver : le statut passe a "approuve", le role utilisateur est mis a jour en "vendeur", le nom de boutique, le type vendeur et le type de compte sont enregistres sur le profil. L\'acces a l\'espace vendeur est immediat.', ''),
    bulletItem('Rejeter : le statut passe a "rejete" avec la note explicative de l\'administrateur, visible par le candidat sur la page de candidature.', ''),

    heading2('Vue d\'ensemble des vendeurs actifs'),
    bodyText('Un tableau recapitulatif presente tous les vendeurs avec :'),
    bulletItem('Nom, email, nom de boutique.'),
    bulletItem('Type de compte : badge bleu Individuel ou badge violet Entreprise.'),
    bulletItem('Modele de vente : Plateforme geree ou Autonome.'),
    bulletItem('Nombre de produits publies.'),
    bulletItem('Chiffre d\'affaires total genere via la plateforme.'),
    bulletItem('Date d\'inscription.'),

    heading2('Tableau de bord — Indicateurs ajoutes'),
    bodyText('La page principale du tableau de bord admin a ete enrichie avec le nombre total de vendeurs actifs et un bouton d\'alerte en cas de candidatures en attente d\'examen.'),

    blankLine(),
    infoBox('Notification automatique', 'Le menu lateral de l\'administration affiche un badge orange sur le lien "Vendeurs" indiquant en temps reel le nombre de candidatures en attente. Ce badge disparait automatiquement quand toutes les demandes ont ete traitees.', AMBER, AMBER_T),

    new Paragraph({ spacing: { before: 0, after: 0 }, children: [new PageBreak()] }),
  ];
}

// ─── SECTION 8 — CAISSE ──────────────────────────────────────────────────────
function buildSection8() {
  const cw = CONTENT_W;
  const c2 = [Math.round(cw * 0.4), Math.round(cw * 0.6)];
  return [
    heading1('Section 8 — Ameliorations de la caisse', true),
    new Paragraph({ spacing: { before: 0, after: 40 }, children: [new TextRun({ text: 'Fichier : checkout.php', font: 'Calibri', size: 20, italics: true, color: GREY2 })] }),

    bodyText('La page de finalisation de commande a ete restructuree pour integrer les nouvelles fonctionnalites tout en preservant l\'experience utilisateur existante.', { justify: true }),

    heading2('Nouvelles fonctionnalites integrees'),
    bulletItem('Bloc fidelite conditionnel : affiche uniquement si l\'acheteur est connecte et possede au moins 100 points. Permet de saisir le nombre de points a utiliser avec mise a jour dynamique du total.'),
    bulletItem('Message d\'information points : pour les acheteurs connectes ayant moins de 100 points, un bandeau indique les points que la commande rapportera.'),
    bulletItem('Ligne de reduction dans le recapitulatif : si des points sont utilises, une ligne de reduction apparait avec le nombre de points et le montant deduit.'),
    bulletItem('Creation automatique des commissions : a la validation, les commissions dues sont calculees et enregistrees pour chaque produit vendeur present dans la commande.'),
    bulletItem('Credit automatique des points : les points gagnes sont credites immediatement apres validation, simultanement au debit des points utilises.'),
    bulletItem('Note de commande enrichie : le detail des points utilises et le montant de reduction sont enregistres dans la note de commande, visible par l\'administrateur.'),

    heading2('Logique de calcul du total'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: c2,
      rows: [
        headerRow(['Element', 'Calcul'], c2),
        dataRow([['Sous-total produits'], ['Somme des prix x quantites']], c2, false),
        dataRow([['Frais de livraison'], ['0 si sous-total superieur au seuil de gratuite, sinon tarif configure']], c2, true),
        dataRow([['Reduction points'], ['Nombre de points utilises x 10 FCFA']], c2, false),
        dataRow([[{text: 'Total a payer', bold: true}], [{text: 'Sous-total + Livraison — Reduction points (minimum 0)', bold: true}]], c2, true),
      ],
    }),

    new Paragraph({ spacing: { before: 0, after: 0 }, children: [new PageBreak()] }),
  ];
}

// ─── SECTION 9 — BASE DE DONNEES ─────────────────────────────────────────────
function buildSection9() {
  const cw = CONTENT_W;
  const cCol = [Math.round(cw * 0.22), Math.round(cw * 0.22), Math.round(cw * 0.15), Math.round(cw * 0.41)];
  const c3   = [Math.round(cw * 0.25), Math.round(cw * 0.28), Math.round(cw * 0.47)];
  const c2   = [Math.round(cw * 0.35), Math.round(cw * 0.65)];

  return [
    heading1('Section 9 — Modifications de la base de donnees', true),
    new Paragraph({ spacing: { before: 0, after: 40 }, children: [new TextRun({ text: 'Application automatique au premier chargement via includes/config.php', font: 'Calibri', size: 20, italics: true, color: GREY2 })] }),

    bodyText('Toutes les modifications de la base de donnees sont appliquees automatiquement au premier chargement de l\'application apres la mise a jour du code. Ce mecanisme utilise des instructions ALTER TABLE et CREATE TABLE IF NOT EXISTS encapsulees dans des blocs try/catch, garantissant qu\'elles ne causent aucune erreur si elles ont deja ete appliquees.', { justify: true }),

    heading2('Nouvelles colonnes ajoutees'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: cCol,
      rows: [
        headerRow(['Table', 'Colonne', 'Type', 'Description'], cCol),
        dataRow([['users'], ['loyalty_points'], ['INT DEFAULT 0'], ['Solde de points de fidelite de l\'utilisateur']], cCol, false),
        dataRow([['users'], ['business_name'], ['VARCHAR(200)'], ['Nom de la boutique du vendeur']], cCol, true),
        dataRow([['users'], ['seller_type'], ['ENUM'], ['Modele de vente : managed ou autonomous']], cCol, false),
        dataRow([['users'], ['account_type'], ['ENUM'], ['Type de compte vendeur : individual ou enterprise']], cCol, true),
        dataRow([['users'], ['role (modifie)'], ['ENUM'], ['Ajout de la valeur "seller" en plus de "customer" et "admin"']], cCol, false),
        dataRow([['products'], ['seller_id'], ['INT'], ['Identifiant du vendeur proprietaire du produit']], cCol, true),
        dataRow([['products'], ['flash_sale_price'], ['DECIMAL(10,2)'], ['Prix reduit pendant une vente flash']], cCol, false),
        dataRow([['products'], ['flash_sale_end'], ['DATETIME'], ['Date et heure de fin de la vente flash']], cCol, true),
        dataRow([['seller_applications'], ['account_type'], ['ENUM'], ['Type de compte choisi dans la candidature : individual ou enterprise']], cCol, false),
      ],
    }),

    heading2('Nouvelle table : seller_applications'),
    bodyText('Stocke les candidatures des utilisateurs souhaitant devenir vendeurs.'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: c3,
      rows: [
        headerRow(['Colonne', 'Type', 'Description'], c3),
        dataRow([['id'], ['INT AUTO_INCREMENT PK'], ['Identifiant unique']], c3, false),
        dataRow([['user_id'], ['INT (FK users)'], ['Utilisateur candidat']], c3, true),
        dataRow([['business_name'], ['VARCHAR(200)'], ['Nom de la boutique proposee']], c3, false),
        dataRow([['description'], ['TEXT'], ['Description de l\'activite commerciale']], c3, true),
        dataRow([['seller_type'], ['ENUM'], ['Modele souhaite : managed ou autonomous']], c3, false),
        dataRow([['status'], ['ENUM'], ['Statut : pending, approved ou rejected']], c3, true),
        dataRow([['admin_note'], ['TEXT'], ['Note de l\'administrateur lors du traitement']], c3, false),
      ],
    }),

    heading2('Nouvelle table : commissions'),
    bodyText('Enregistre toutes les commissions generees par les ventes des vendeurs partenaires.'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: c3,
      rows: [
        headerRow(['Colonne', 'Type', 'Description'], c3),
        dataRow([['order_id'], ['INT (FK orders)'], ['Commande source']], c3, false),
        dataRow([['seller_id'], ['INT (FK users)'], ['Vendeur concerne']], c3, true),
        dataRow([['sale_amount'], ['DECIMAL(10,2)'], ['Montant de la vente hors commission']], c3, false),
        dataRow([['commission_rate'], ['DECIMAL(5,2)'], ['Taux applique : 6% ou 12%']], c3, true),
        dataRow([['commission_amount'], ['DECIMAL(10,2)'], ['Montant de la commission prelevee']], c3, false),
        dataRow([['status'], ['ENUM'], ['Statut du versement : pending ou paid']], c3, true),
      ],
    }),

    heading2('Nouvelle table : loyalty_transactions'),
    bodyText('Historique de tous les mouvements de points de fidelite (gains et utilisations).'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: c2,
      rows: [
        headerRow(['Colonne', 'Description'], c2),
        dataRow([['user_id (FK users)'], ['Utilisateur concerne']], c2, false),
        dataRow([['order_id (FK orders)'], ['Commande associee (optionnel)']], c2, true),
        dataRow([['points'], ['Nombre de points du mouvement']], c2, false),
        dataRow([['type (ENUM earn/redeem)'], ['Gain de points ou utilisation']], c2, true),
        dataRow([['note'], ['Description du mouvement (ex: "Achat #ORD-2026-0001")']], c2, false),
      ],
    }),

    blankLine(),
    heading2('Nouvelle table : platform_commissions'),
    bodyText('Enregistre les commissions prelevees par World Compass sur les ventes des vendeurs partenaires.'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: c3,
      rows: [
        headerRow(['Colonne', 'Type', 'Description'], c3),
        dataRow([['order_id'], ['INT (FK orders)'], ['Commande source']], c3, false),
        dataRow([['seller_id'], ['INT (FK users)'], ['Vendeur concerne']], c3, true),
        dataRow([['sale_amount'], ['DECIMAL(10,2)'], ['Montant total vendu par ce vendeur dans la commande']], c3, false),
        dataRow([['commission_rate'], ['DECIMAL(5,2)'], ['Taux applique au moment de la commande']], c3, true),
        dataRow([['commission_amount'], ['DECIMAL(10,2)'], ['Montant preleve par World Compass']], c3, false),
        dataRow([['status'], ['ENUM pending/paid'], ['Statut du versement a la plateforme']], c3, true),
      ],
    }),

    blankLine(),
    heading2('Nouveaux parametres de configuration (table settings)'),
    bodyText('Quatre nouvelles entrees sont ajoutees automatiquement dans la table settings lors du premier chargement :'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: [Math.round(cw * 0.32), Math.round(cw * 0.18), Math.round(cw * 0.5)],
      rows: [
        headerRow(['Cle', 'Valeur par defaut', 'Description'], [Math.round(cw * 0.32), Math.round(cw * 0.18), Math.round(cw * 0.5)]),
        dataRow([['opening_fee_individual'], ['5 000'], ['Frais d\'ouverture Compte Individuel en FCFA']], [Math.round(cw * 0.32), Math.round(cw * 0.18), Math.round(cw * 0.5)], false),
        dataRow([['opening_fee_enterprise'], ['20 000'], ['Frais d\'ouverture Compte Entreprise en FCFA']], [Math.round(cw * 0.32), Math.round(cw * 0.18), Math.round(cw * 0.5)], true),
        dataRow([['platform_commission_rate'], ['3.00'], ['Taux de commission plateforme en pourcentage']], [Math.round(cw * 0.32), Math.round(cw * 0.18), Math.round(cw * 0.5)], false),
        dataRow([['platform_commission_enabled'], ['1'], ['1 = commission active, 0 = commission suspendue']], [Math.round(cw * 0.32), Math.round(cw * 0.18), Math.round(cw * 0.5)], true),
      ],
    }),

    new Paragraph({ spacing: { before: 0, after: 0 }, children: [new PageBreak()] }),
  ];
}

// ─── SECTION 10 — FICHIERS ────────────────────────────────────────────────────
function buildSection10() {
  const cw = CONTENT_W;
  const c2n = [Math.round(cw * 0.38), Math.round(cw * 0.62)];
  return [
    heading1('Section 10 — Recapitulatif des fichiers livres', true),

    heading2('Nouveaux fichiers crees'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: c2n,
      rows: [
        headerRow(['Fichier', 'Description'], c2n),
        dataRow([[{text: 'devenir-vendeur.php', bold: true}], ['Page de candidature vendeur avec formulaire, choix du modele et suivi de statut']], c2n, false),
        dataRow([[{text: 'ventes-flash.php', bold: true}], ['Page dediee aux ventes flash avec countdown global et liste des produits en promotion']], c2n, true),
        dataRow([[{text: 'seller/index.php', bold: true}], ['Tableau de bord vendeur avec statistiques, commandes recentes et apercu des produits']], c2n, false),
        dataRow([[{text: 'seller/products.php', bold: true}], ['Gestion complete des produits vendeur : ajout, modification, suppression, vente flash']], c2n, true),
        dataRow([[{text: 'seller/orders.php', bold: true}], ['Historique des commandes contenant les produits du vendeur']], c2n, false),
        dataRow([[{text: 'seller/commissions.php', bold: true}], ['Detail des commissions prelevees avec statistiques et historique']], c2n, true),
        dataRow([[{text: 'admin/sellers.php', bold: true}], ['Interface admin pour candidatures et gestion des vendeurs actifs']], c2n, false),
      ],
    }),

    blankLine(),
    heading2('Fichiers existants modifies'),
    new Table({
      width: { size: cw, type: WidthType.DXA },
      columnWidths: c2n,
      rows: [
        headerRow(['Fichier', 'Modifications apportees'], c2n),
        dataRow([[{text: 'includes/config.php', bold: true}], ['Migrations automatiques pour les nouvelles colonnes et tables']], c2n, false),
        dataRow([[{text: 'includes/functions.php', bold: true}], ['Ajout de 18 nouvelles fonctions : isSeller, requireSeller, getFlashProducts, hasActiveFlash, getLoyaltyPoints, addLoyaltyPoints, redeemLoyaltyPoints, loyaltyPointsValue, orderEarnPoints, getLoyaltyHistory, getSellerStats, getSellerProducts, getSellerOrders, createCommissions, isPlatformCommissionEnabled, getPlatformCommissionRate, createPlatformCommissions, getPlatformCommissionTotals']], c2n, true),
        dataRow([[{text: 'includes/header.php', bold: true}], ['Liens Ma Boutique, Vendre et Ventes Flash (conditionnel) en navigation']], c2n, false),
        dataRow([[{text: 'includes/footer.php', bold: true}], ['Liens Devenir vendeur, Ventes Flash, Programme fidelite dans la colonne La Plateforme']], c2n, true),
        dataRow([[{text: 'checkout.php', bold: true}], ['Bloc fidelite, calcul dynamique de reduction, credit/debit points, creation commissions vendeurs et commission plateforme']], c2n, false),
        dataRow([[{text: 'profile.php', bold: true}], ['Solde de points en sidebar, onglet Fidelite avec historique, lien espace vendeur']], c2n, true),
        dataRow([[{text: 'admin/index.php', bold: true}], ['Compteur vendeurs, bouton alerte candidatures en attente']], c2n, false),
        dataRow([[{text: 'admin/products.php', bold: true}], ['Section vente flash dans le formulaire produit (prix et date de fin)']], c2n, true),
        dataRow([[{text: 'admin/includes/admin_header.php', bold: true}], ['Lien Vendeurs avec badge de notification en navigation laterale']], c2n, false),
        dataRow([[{text: 'admin/settings.php', bold: true}], ['Ajout des champs : frais d\'ouverture Individuel/Entreprise, taux de commission plateforme, toggle activation/desactivation de la commission']], c2n, true),
        dataRow([[{text: 'admin/sellers.php', bold: true}], ['Affichage du type de compte (Individuel/Entreprise) et des frais dus dans les demandes et le tableau des vendeurs actifs']], c2n, false),
        dataRow([[{text: 'assets/css/style.css', bold: true}], ['Styles .seller-link, .flash-nav-link, .badge-flash, .product-card--flash, animation flash-blink']], c2n, true),
      ],
    }),

    blankLine(),
    heading2('Validation technique'),
    bodyText('L\'ensemble des fichiers PHP livres a ete valide sans erreur de syntaxe (php -l). Les validations fonctionnelles couvrent :'),
    bulletItem('Flux de candidature vendeur complet : selection du type de compte (Individuel/Entreprise), soumission, examen admin avec affichage des frais, activation.'),
    bulletItem('Modification des frais d\'ouverture depuis admin/settings.php avec prise d\'effet immediate sur la page de candidature.'),
    bulletItem('Commission plateforme : activation, desactivation via toggle, modification du taux, enregistrement dans platform_commissions a chaque commande vendeur.'),
    bulletItem('Ajout et modification de produit avec upload de photo depuis l\'espace vendeur.'),
    bulletItem('Activation d\'une vente flash et affichage du countdown sur les cartes produits.'),
    bulletItem('Accumulation et utilisation des points de fidelite a la caisse.'),
    bulletItem('Generation automatique des commissions vendeur et plateforme lors d\'une commande.'),
    bulletItem('Protection des acces : un vendeur ne peut voir et modifier que ses propres produits.'),
    bulletItem('Compatibilite avec les utilisateurs non connectes (panier anonyme, pas de fidelite).'),

    blankLine(),
    infoBox('Migration sans interruption', 'Aucune intervention manuelle sur la base de donnees n\'est requise. Toutes les modifications structurelles sont appliquees automatiquement et silencieusement au premier chargement suivant la mise a jour du code. La plateforme reste entierement operationnelle pendant et apres la mise a jour.', GREEN, GREEN_T),
  ];
}

// ─── ASSEMBLE DOCUMENT ────────────────────────────────────────────────────────
const doc = new Document({
  numbering: {
    config: [{
      reference: 'bullets',
      levels: [{
        level: 0,
        format: LevelFormat.BULLET,
        text: '\u2013',
        alignment: AlignmentType.LEFT,
        style: { paragraph: { indent: { left: 480, hanging: 300 } } },
      }],
    }],
  },
  styles: {
    default: {
      document: { run: { font: 'Calibri', size: 22 } },
    },
    paragraphStyles: [
      {
        id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 36, bold: true, font: 'Calibri', color: WHITE },
        paragraph: { spacing: { before: 200, after: 160 }, outlineLevel: 0 },
      },
      {
        id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 26, bold: true, font: 'Calibri', color: NAVY },
        paragraph: { spacing: { before: 280, after: 100 }, outlineLevel: 1 },
      },
      {
        id: 'Heading3', name: 'Heading 3', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 24, bold: true, font: 'Calibri', color: GREY1 },
        paragraph: { spacing: { before: 200, after: 80 }, outlineLevel: 2 },
      },
    ],
  },
  sections: [{
    properties: {
      page: {
        size: { width: PAGE_W, height: PAGE_H },
        margin: { top: MARGIN, right: MARGIN, bottom: MARGIN, left: MARGIN },
      },
    },
    headers: {
      default: new Header({
        children: [
          new Paragraph({
            alignment: AlignmentType.RIGHT,
            border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: NAVY } },
            spacing: { before: 0, after: 120 },
            children: [
              new TextRun({ text: 'WORLD COMPASS', font: 'Calibri', size: 18, bold: true, color: NAVY }),
              new TextRun({ text: '   |   Documentation Technique v2.1 — Avril 2026', font: 'Calibri', size: 18, color: GREY2 }),
            ],
          }),
        ],
      }),
    },
    footers: {
      default: new Footer({
        children: [
          new Paragraph({
            alignment: AlignmentType.CENTER,
            border: { top: { style: BorderStyle.SINGLE, size: 4, color: GREY3 } },
            spacing: { before: 120, after: 0 },
            children: [
              new TextRun({ text: 'Page ', font: 'Calibri', size: 18, color: GREY2 }),
              new TextRun({ children: [PageNumber.CURRENT], font: 'Calibri', size: 18, color: GREY2 }),
              new TextRun({ text: ' — Confidentiel — World Compass', font: 'Calibri', size: 18, color: GREY2 }),
            ],
          }),
        ],
      }),
    },
    children: [
      ...buildCoverPage(),
      ...buildExecSummary(),
      ...buildSection1(),
      ...buildSection2_3(),
      ...buildSection4(),
      ...buildSection5(),
      ...buildSection6(),
      ...buildSection7(),
      ...buildSection8(),
      ...buildSection9(),
      ...buildSection10(),
    ],
  }],
});

Packer.toBuffer(doc).then(buffer => {
  fs.writeFileSync('c:/xampp/htdocs/ecommerce/WorldCompass-Documentation-v2.1.docx', buffer);
  console.log('SUCCESS: WorldCompass-Documentation-v2.1.docx created');
}).catch(err => {
  console.error('ERROR:', err.message);
  process.exit(1);
});
