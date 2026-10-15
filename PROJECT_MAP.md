# PROJECT MAP — Maokaa CMS

> Généré le 14 Octobre 2026 — Audit code

---

## TECH_STACK

| Couche | Technologie | Version |
|--------|-----------|---------|
| **Backend** | PHP natif (sans framework) | 8.x |
| **Base de données** | MySQL via PDO | 5.7+ / MariaDB |
| **Frontend admin** | Bootstrap 5.3 + Font Awesome 6 | 5.3.0 / 6.4.0 |
| **Éditeur visuel** | GrapesJS | 0.21.9 |
| **Plugins GrapesJS** | export (1.0.11), style-bg (2.0.2), custom-code (1.0.2) | — |
| **Sanitisation HTML** | DOMPurify (client) + regex PHP (serveur) | 3.1.6 |
| **Sélecteur images produits** | Modal bibliothèque depuis `assets/images/products/` + upload direct | — |
| **Carousel** | Splide.js | 4.1.4 |
| **Toolbar WYSIWYG inline** | Floating toolbar via `document.execCommand` (B/I/U/H2/H3/lien) | — |
| **Tables admin** | Simple-DataTables (recherche, tri, pagination sans jQuery) | 10.2.0 |
| **Shortcodes admin URLs** | `_wrap_vep_block_admin()` mappe chaque shortcode vers sa section admin | — |
| **Assistant IA de contenu** | API compatible OpenAI (Chat Completions), configurable via `.env` (`AI_API_URL`/`AI_API_KEY`/`AI_MODEL`/`AI_MAX_TOKENS`) | — |
| **Serveur** | XAMPP (Apache + MySQL) | — |

### API Interne
- `includes/api_products.php` — Endpoint JSON pour le bloc produits interactif
- `includes/api_quote.php` — Endpoint JSON pour soumettre une demande de devis (POST, public)
- `includes/inline_edit_product.php` — Endpoint AJAX pour l'édition inline des produits (POST, admin)
- `includes/inline_edit_setting.php` — Endpoint AJAX pour l'édition inline des settings (footer : phone, email, address, etc.) (POST, admin)
- `includes/settings_helpers.php` — Helpers `get_setting($pdo, $key, $default)` / `set_setting($pdo, $key, $value)` pour la table `settings`
- `includes/api_ai_content.php` — Endpoint AJAX : régénération du HTML d'une page via IA (POST, admin), s'appuie sur `includes/ai_client.php`
- `includes/api_menu_items.php` — Endpoint JSON pour la gestion des menus (POST, admin)
- `includes/api_page_create.php` — Endpoint pour la création rapide de pages (POST, admin)

### Dépendances CDN (aucun package manager)
- Bootstrap 5.3 (CSS + JS)
- Font Awesome 6.4.0
- GrapesJS 0.21.9 + plugins
- DOMPurify 3.1.6
- Splide.js 4.1.4 (CDN chargé dans tous les templates frontend + auto-init DOMContentLoaded)
- Google reCAPTCHA v3 API (recaptcha/api.js?render=KEY) — chargé depuis le template contact-form.php
- Quill.js 1.3.7 (chargé mais non utilisé — remplacé par toolbar flottante inline)
- Simple-DataTables 10.2.0 (CDN) — `assets/js/admin-tables.js`

---

## SYSTEM_FLOW

### 1. Front Controller (`index.php`)
```
Requête HTTP
  → index.php (front controller)
    → includes/config.php
    → includes/db.php          (connexion PDO)
    → includes/auth.php        (session + CSRF)
    → includes/theme.php       (ThemeManager)
    → includes/modules.php     (ModuleRegistry)
    → app/models/Content.php
    → Résolution slug depuis l'URL
      → /login       → login.php
      → /admin/*     → admin/dashboard.php (si connecté)
      → /{slug}      → Content::findBySlug() → template theme
      → 404          → page 404 ou erreur
```

### 2. Authentication Flow
```
login.php
  → POST credentials
  → login() dans auth.php
    → Vérification rate limiting (5 tentatives/15min)
    → Requête préparée PDO
    → password_verify() bcrypt
    → session_regenerate_id()
    → Si password = 'admin123' → force change password
    → logAudit()
  → Dashboard admin
```

### 3. Shortcode Engine (`includes/shortcodes.php`)
```
Page body stocké en DB avec shortcodes:
  [carousel slider_id="1"]
  [products limit="6" category="3"]
  [featured_products limit="4"]
  [news limit="3"]
  [brands] [partners] [contact_form]

do_shortcode($html, $pdo)
  → preg_replace_callback détecte [tag attr="val"]
  → render_shortcode() dispatche par tag
  → render_block_*() génère HTML via modèle partiel
  → Si admin connecté: _wrap_vep_block_admin() enveloppe le bloc
    dans .vep-block-wrapper avec data-vep-admin-url et
    data-vep-shortcode ; le JS inline affiche un bouton
    "Gérer dans l'admin" qui pointe vers la section correspondante.

Bloc interactif [products] (depuis juin 2026):
  → render_block_products() n'utilise plus de modèle PHP
  → Le template client-side (products.php) charge les données
    via includes/api_products.php (JSON)
  → Fonctionnalités : recherche prédictive avec autocomplétion,
    filtres par catégories (pills), tri (populaire/A-Z/nouveautés),
    fuzzy search (coefficient Dice), skeleton loading,
    état vide intelligent avec suggestions de produits populaires,
    favoris localStorage, carte moderne (hover zoom, wishlist, aperçu)
  → api_products.php supporte ?search=&category=&sort=&limit=
  → Chaque carte produit a un bouton "Devis" qui ouvre la modale quoteModal
    (composant partagé app/views/partials/blocks/quote-form.php)
  → La soumission POST est envoyée à includes/api_quote.php (JSON)
    qui crée une entrée dans la table contacts avec sujet "[Demande de devis]"
```

### Bloc contact-form
```
[contact_form]
  → render_block_contact_form() gère POST + validation
  → Modèle Contact.php (table contacts : nom, email, telephone, sujet, message)
  → Google reCAPTCHA v3 (invisible, score ≥ 0.5)
    → Clés dans .env : RECAPTCHA_SITE_KEY, RECAPTCHA_SECRET_KEY
    → Script chargé via https://www.google.com/recaptcha/api.js?render=SITE_KEY
    → Token généré par grecaptcha.execute() sur submit du formulaire (JS)
    → Token placé dans input hidden g-recaptcha-response
    → Validation serveur : file_get_contents() → https://www.google.com/recaptcha/api/siteverify
    → Score minimum : 0.5 (configurable dans shortcodes.php)
```

### Demande de devis
```
Bouton "Demander un devis" sur la page détail produit
  → Ouvre la modale Bootstrap #quoteModal (quote-form.php)
  → Soumission AJAX → includes/api_quote.php → table contacts (sujet: [Demande de devis] ...)

Bouton "Gérer les Produits" visible sur la page produits quand isLoggedIn()
  → Lien vers admin/dashboard.php?section=products

Bouton "Modifier ce produit" visible sur la vue détail produit (admin)
  → Lien vers admin/products/edit.php?id=N

Champs produit inline-editables (vue détail, admin uniquement) :
  → nom, description, description_complete, caracteristiques_techniques, image
  → Attributs data-inline-field + data-product-id sur les éléments HTML
  → Pour l'admin, les blocs description / description_complete /
    caracteristiques_techniques sont toujours rendus (même si la valeur DB
    est NULL/vide), avec un texte indicatif (attribut data-ie-placeholder +
    CSS .ie-field:empty::before) afin de pouvoir y ajouter du contenu via
    l'édition inline. Côté visiteur, ces blocs restent masqués si vides.
  → Texts : initProductField() dans inline-edit.js → saveProductField()
    → POST JSON → includes/inline_edit_product.php
    → Vérifie CSRF + whitelist des champs
    → Product::update() → UPDATE produits SET ... WHERE id = ?
    → logAudit() journalise chaque modification
  → Image : data-product-img + data-product-id
    → initProductImage() → openProductImagePicker() → sélecteur images
    → Sauvegarde via saveProductField(pid, 'image', src)
    → Même modal que le body (list_images.php) mais sauvegarde directe champ image
```

### 4. Inline Edit (Frontend Admin)
```
Page rendue avec isLoggedIn()
  → Templates injectent data-inline-field="body" sur le conteneur
    (home, default, page, products, listing, news)
  → inline-edit.js détecte [data-inline-field="body"]
  → initBodyField() parcourt les enfants :
    → Éléments texte (h1-h6, p, ...) → contenteditable + toolbar WYSIWYG
    → Images → sélecteur d'image (assets/images/)
    → .vep-block-wrapper → bouton "Gérer dans l'admin"
      (URL mappée par _wrap_vep_block_admin())
      Le lien inclut ?return_url=<page courante> pour le retour
  → Liaisons existantes (double-clic) → openLinkModal() (popup URL + label + target)
  → Sauvegarde : serializeAndSaveBody() → POST → inline_edit.php
    → sanitize_body_html() (PHP) → UPDATE content SET body

Footer inline editing (depuis V2) :
  → Footer partial injecte data-ie-setting="footer_*" sur les champs
  → Petit script embarqué dans footer.php (admin uniquement) :
    → contenteditable + sauvegarde AJAX → inline_edit_setting.php
    → settings table (footer_description, footer_phone, footer_email, footer_address, footer_copyright)

Link/Button modal (depuis V2) :
  → openLinkModal(anchorEl, contextEl) dans inline-edit.js
  → Modale avec champs : Texte, URL, Target (_blank)
  → Double-clic sur un lien existant → édition
  → Bouton "🔗 lien" dans toolbar WYSIWYG → création
  → Applicable aussi sur les boutons (<a class="btn">)

Retour depuis l'admin :
  → return_url() dans config.php : $_GET['return_url'] > Referer > défaut
  → Utilisé par les boutons "Retour" / "Annuler" des pages admin
    (sliders, messages, etc.)
```

### 5. GrapesJS Editor Flow
```
Chargement:
  DB body → shortcodesToBlocks() (JS) → editor.setComponents()
  → loadVepBlockPreview() (AJAX → preview-block.php)
  → initSplideInCanvas()

Sauvegarde:
  editor.getHtml() → blocksToShortcodes() (JS)
  → DOMPurify.sanitize() (client)
  → POST body → sanitize_body_html() (PHP)
  → UPDATE content SET body = ...
```

### Blocs GrapesJS disponibles (V1 + V2)
| Bloc | Catégorie | Vague |
|---|---|---|
| Hero | Blocs | V1 |
| Deux colonnes | Blocs | V1 |
| Deux colonnes 2-1 | Blocs | V1 |
| Trois colonnes | Blocs | V1 |
| Quatre colonnes | Blocs | V1 |
| Deux colonnes 25/75 | Blocs | V1 |
| Deux colonnes 75/25 | Blocs | V1 |
| Une colonne | Blocs | V1 |
| Pleine largeur | Blocs | V1 |
| Conteneur | Blocs | V1 |
| Espaceur | Blocs | V1 |
| Texte | Blocs | V1 |
| Call to action | Blocs | V1 |
| Image | Blocs | V1 |
| Hero couverture | **Hero** | V2 |
| Hero centré | **Hero** | V2 |
| Hero splité | **Hero** | V2 |
| Titre H1 | Texte | V1 |
| Titre H2 | Texte | V1 |
| Titre H3 | Texte | V1 |
| Paragraphe | Texte | V1 |
| Citation | Texte | V1 |
| Séparateur | Texte | V1 |
| Liste à puces | Texte | V1 |
| Liste numérotée | Texte | V1 |
| Bouton | Composants | V1 |
| Carte | Composants | V1 |
| Accordéon | Composants | V1 |
| Image + légende | Médias | V1 |
| Vidéo | Médias | V1 |
| Section avec fond | Médias | V1 |
| Formulaire contact | **Formulaires** | V2 |
| Newsletter | **Formulaires** | V2 |
| Section newsletter | **Formulaires** | V2 |
| Carte icône | **Cards** | V2 |
| Carte horizontale | **Cards** | V2 |
| Carte overlay | **Cards** | V2 |
| Grille 3 cartes | **Cards** | V2 |
| Tableau tarifs | **Cards** | V2 |
| Produits Populaires | Contenu Dynamique | V1 |
| Catalogue Produits | Contenu Dynamique | V1 |
| Actualités | Contenu Dynamique | V1 |
| Marques | Contenu Dynamique | V1 |
| Marques Carousel | Contenu Dynamique | V1 |
| Partenaires | Contenu Dynamique | V1 |
| Formulaire de Contact | Contenu Dynamique | V1 |
| Carousel | Caroussel | V1 |

### 6. Theme Resolution
```
ThemeManager::template($name)
  → themes/{active}/templates/{name}.php
  → fallback: themes/default/templates/{name}.php
  → fallback: app/views/templates/{name}.php
  → return '' si introuvable

Thèmes disponibles :
  - default   (thème de base)
  - default1  (variante)
  - ocean     (thème bleu)
  - violet    (thème violet)
  - rose      (thème rose)

Chaque thème contient :
  - theme.json (manifeste : name, version, description)
  - templates/ (home, page, products, listing, news, about, solutions)
  - partials/  (navbar, footer)
  - assets/css/theme.css
  - assets/js/maokaa.js, site-editing.js
  - preview.svg (aperçu visuel généré automatiquement)

Installation de thème :
  → Upload ZIP via admin/dashboard.php?section=themes
  → Validation : magic bytes PK\x03\x04, taille ≤ 10 Mo
  → Extraction en staging, vérification theme.json obligatoire
  → Déplacement vers themes/{folder}
  → Génération automatique de preview.svg depuis theme.css
  → Vérification structure via includes/theme_structure.json
  → Activation automatique
```

### 7. Admin CRUD Architecture
```
admin/dashboard.php (single page, sections via ?section=)
  ├── POST actions centralisées (delete_product, delete_brand, delete_user, toggle_module, etc.)
  ├── CSRF + logAudit sur chaque action
  └── Sections: overview, products, categories, content, brands,
                partners, news, messages, users, modules, themes, settings

Sous-pages (CRUD dédiés):
  admin/products/, admin/categories/, admin/brands/, admin/partners/
  admin/news/, admin/content/, admin/sliders/, admin/menus/
  admin/messages/, admin/themes/, admin/media/, admin/users/

Gestion des utilisateurs (depuis oct 2026):
  → admin/users/ : CRUD complet (create, edit)
  → Modèle User.php : table admins
  → Champs : username, password_hash, fullname, email, active, must_change_password
  → Création : mot de passe hashé (bcrypt), flag must_change_password=1
  → Édition : option changement mot de passe
  → Droits : section users réservée aux admins (hasRole('admin'))
  → Audit : logAudit sur chaque action CRUD

Recherche & pagination (Simple-DataTables) :
  → 7 tables du dashboard activées via data-datatable + data-dt-columns
  → Initialiseur partagé : assets/js/admin-tables.js
  → Colonne Actions désactivée du tri sur chaque table
  → Labels français, pagination 10/25/50/100 par page

Gestionnaire de médias (admin/media/) :
  → Page standalone listant toutes les images de assets/images/ (scan récursif)
  → Upload : upload_image() helper avec validation MIME (JPG, PNG, GIF, SVG, WebP)
  → Renommage via modale Bootstrap
  → Suppression avec confirmation + vérification path (anti-traversal)
  → Grille responsive, tri par date de modification (plus récent en premier)
  → Liens depuis la sidebar du dashboard (après Messages)
```

### 8. Upload Flow
```
upload_image() / upload_pdf() dans includes/upload.php
  → Validation MIME (finfo)
  → Validation taille (2MB image, 5MB PDF)
  → Génération nom uniqid + extension
  → move_uploaded_file() vers assets/images/ ou assets/brochures/
```

### 9. Tables Database
```
admins → categories → marques → partenaires → actualites
→ produits → produit_images → contacts → content
→ sliders → menus → settings → audit_logs → login_attempts
→ modules (depuis juin 2026)
```

### 10. Module Registry (`includes/modules.php`)
```
ModuleRegistry::init($pdo) → charge les modules depuis la table modules

Types de modules :
  - core   : toujours actifs, non désactivables (content, menus)
  - annex  : fonctionnalités secondaires (brands, partners, news, messages, media, sliders)
  - addon  : extensions optionnelles (non utilisées actuellement)

API :
  → is_module_enabled($slug) : bool
  → ModuleRegistry::isEnabled($slug) : bool
  → ModuleRegistry::enable($slug) : bool (vérifie dépendances)
  → ModuleRegistry::disable($slug) : bool (vérifie dépendances inverses)
  → ModuleRegistry::getEnabled($type) : array

Activation/Désactivation :
  → Interface : admin/dashboard.php?section=modules
  → Vérification des dépendances avant activation
  → Vérification des modules dépendants avant désactivation
  → logAudit sur chaque changement
```

### 11. Sitemap XML Dynamique (`sitemap.php`)
```
sitemap.php → génère sitemap.xml à la volée

Sections incluses :
  1. Pages content publiées (status = 'published')
  2. Produits actifs (si module products activé)
  3. Actualités publiées (si module news activé)

Format :
  → <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  → <loc>, <lastmod>, <changefreq> pour chaque URL
  → Exclusion automatique : /login, /admin, /404
  → Headers : Content-Type: application/xml, X-Robots-Tag: index, follow
```

### 12. Assistant IA (régénération HTML)
```
Configuration : .env → AI_API_URL, AI_API_KEY, AI_MODEL, AI_MAX_TOKENS
  (API compatible OpenAI Chat Completions : OpenAI, Mistral, OpenRouter, etc.)

Déclenchement (admin uniquement, 2 emplacements) :
  1. Éditeur GrapesJS (admin/content/body-editor.php)
     → bouton panel "Assistant IA" (commande 'ai-rewrite')
  2. Toolbar inline frontend (assets/js/inline-edit.js)
     → bouton #ie-ai-btn dans #ie-toolbar

Flux commun :
  Modale instruction (texte libre)
  → POST includes/api_ai_content.php { csrf_token, html, instruction }
    → isLoggedIn() + verifyCSRFToken()
    → ai_client.php::ai_generate_html() : appel API Chat Completions
      (system prompt : conserve les shortcodes [carousel]/[products]/...,
       répond avec un fragment HTML Bootstrap 5 sans markdown)
    → sanitize_body_html() sur la réponse (anti-XSS)
    → logAudit('ai_content', instruction tronquée)
    → { success, html }

  Aperçu :
    - GrapesJS : injecté dans le canvas via shortcodesToBlocks() + setComponents()
      → bouton "Enregistrer" existant persiste (pipeline inchangé)
    - Inline (corps de page entier) : aperçu dans la modale, bouton
      "Appliquer et enregistrer" → saveField('body', html) → inline_edit.php
      → reload de la page

Assistant IA par champ (bouton "IA" de la barre WYSIWYG flottante) :
  → Disponible sur chaque paragraphe/titre du corps de page (initInlineText)
    et sur chaque champ produit éditable (initProductField : nom, description,
    description_complete, caracteristiques_techniques)
  → openFieldAiModal(target) : même modale/endpoint que ci-dessus, mais
    html = target.innerHTML (le champ ciblé uniquement)
  → Application : target.innerHTML = résultat (dépaqueté si l'IA renvoie le
    même tag racine via unwrapIfSameTag()), puis sauvegarde directe
    (serializeAndSaveBody() pour un champ du body, saveProductField() pour un
    champ produit) — sans rechargement de page
```

---

## Architecture Patterns

- **Pattern**: Front Controller + MVC léger
- **DB Layer**: PDO avec requêtes préparées (sauf LIMIT/OFFSET)
- **Templates**: PHP natif avec include (pas de moteur de templates)
- **Thèmes**: Dossier themes/{name}/ avec fallback hiérarchique
- **Shortcodes**: Moteur style WordPress avec parsing regex
- **Slider**: Support images upload + fallback couleur de fond
- **Editor**: Configuration dynamique via JS (baseUrl depuis PHP)
- **Sécurité**: CSRF tokens, bcrypt, rate limiting, prepared statements, sanitize_body_html
- **Tables admin**: Simple-DataTables via `data-datatable` (déclaratif, DRY, pas de jQuery)
- **Modules**: Système modulaire avec dépendances et types (core/annex/addon)
- **Gestion utilisateurs**: RBAC basique (rôle admin), changement mot de passe forcé

---

## Nouveautés récentes (Octobre 2026)

1. **Système de modules** : Activation/désactivation dynamique des fonctionnalités
2. **Sitemap XML dynamique** : Génération automatique pour SEO
3. **Gestion des utilisateurs** : CRUD complet avec gestion des rôles
4. **Amélioration du dashboard** : Interface modernisée avec cartes visuelles pour les pages CMS
5. **Thèmes multiples** : Support de 5 thèmes (default, default1, ocean, violet, rose)
6. **Installation de thèmes** : Upload ZIP avec validation et génération d'aperçu automatique
