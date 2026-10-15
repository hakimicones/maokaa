# Maokaa — CMS PHP sur mesure

Application web complète pour la gestion de contenu, produits, actualités et médias. Développée en PHP natif avec une architecture MVC légère, un éditeur visuel GrapesJS, un système de shortcodes et un moteur de thèmes.

> Secteur : importation et distribution de matériels et consommables de laboratoire en Algérie.

---

## Stack technique

| Composant | Technologie |
|---|---|
| Backend | PHP 8.x natif (sans framework) |
| Base de données | MySQL via PDO |
| Frontend admin | Bootstrap 5.3 + Font Awesome 6 |
| Éditeur visuel | GrapesJS 0.21.9 |
| Carousel | Splide.js 4.1.4 |
| Sanitisation HTML | DOMPurify 3.1.6 |
| Serveur local | XAMPP (Apache + MySQL) |

---

## Prérequis

- PHP 8.0+
- MySQL 5.7+ / MariaDB
- Apache avec `mod_rewrite`
- XAMPP (environnement recommandé)

---

## Installation

### 1. Placer les fichiers

```
c:\xampp\htdocs\Hebergement\maokaa\
```

### 2. Configurer l'environnement

Créer/éditer le fichier `.env` à la racine :

```env
BASE_URL=/Hebergement/maokaa/
DB_HOST=localhost
DB_NAME=maokaa
DB_USER=root
DB_PASS=

UPLOAD_DIR=assets/images/
BROCHURE_DIR=assets/brochures/
MAX_IMAGE_SIZE=2097152
MAX_PDF_SIZE=5242880
```

### 3. Créer la base de données

Dans phpMyAdmin, créer la base `maokaa` puis exécuter :

```bash
# Schéma principal
mysql -u root maokaa < db/schema.sql

# Table carousels (si pas incluse dans le schéma)
mysql -u root maokaa < db/migration_sliders.sql
```

Ou via le navigateur :
```
http://localhost/Hebergement/maokaa/setup.php
```

### 4. Premier accès

```
URL   : http://localhost/Hebergement/maokaa/
Admin : http://localhost/Hebergement/maokaa/login
```

```
Identifiants par défaut
Username : admin
Password : admin123
```

> **Important :** Le changement de mot de passe est forcé dès le premier login.

---

## Structure du projet

```
maokaa/
├── .env                          # Variables d'environnement
├── index.php                     # Front controller — routeur principal
├── login.php                     # Page de connexion admin
├── setup.php                     # Script d'installation initial
│
├── includes/                     # Couche système
│   ├── config.php                # Chargement de l'environnement
│   ├── env.php                   # Parseur de fichier .env
│   ├── db.php                    # Connexion PDO ($pdo global)
│   ├── auth.php                  # Sessions, CSRF, rate limiting, audit
│   ├── shortcodes.php            # Moteur de shortcodes + rendus de blocs
│   ├── inline_edit.php           # Édition inline via AJAX
│   └── theme.php                 # Gestionnaire de thèmes (ThemeManager)
│
├── app/
│   ├── models/                   # Modèles PDO
│   │   ├── Brand.php
│   │   ├── Category.php
│   │   ├── Contact.php
│   │   ├── Content.php
│   │   ├── Menu.php
│   │   ├── News.php
│   │   ├── Partner.php
│   │   ├── Product.php
│   │   └── Slider.php
│   └── views/
│       ├── templates/            # Templates fallback (hors thème)
│       │   ├── home.php
│       │   ├── page.php
│       │   ├── default.php
│       │   └── listing.php
│       ├── partials/
│       │   ├── navbar.php
│       │   ├── footer.php
│       │   └── blocks/           # Rendus partiels des shortcodes
│       └── errors/
│           └── 404.php
│
├── admin/                        # Interface d'administration
│   ├── dashboard.php             # Dashboard (stats + navigation)
│   ├── login.php / logout.php
│   ├── change-password.php
│   ├── content/                  # Gestion des pages CMS
│   │   ├── index.php
│   │   ├── create.php / edit.php
│   │   ├── body-editor.php       # Éditeur visuel GrapesJS
│   │   ├── body-editor.js        # Logique JS de l'éditeur
│   │   ├── preview-block.php     # Aperçu AJAX des blocs (canvas)
│   │   └── upload-asset.php      # Upload d'images
│   ├── products/                 # CRUD produits + galerie
│   ├── categories/               # CRUD catégories
│   ├── brands/                   # CRUD marques
│   ├── partners/                 # CRUD partenaires
│   ├── news/                     # CRUD actualités
│   ├── sliders/                  # Gestion des carousels Splide
│   │   ├── index.php             # Liste de tous les sliders
│   │   ├── manage.php            # Slides d'un slider donné
│   │   ├── create.php            # Ajouter un slide
│   │   └── edit.php              # Modifier un slide
│   ├── menus/                    # Gestion de la navigation
│   ├── messages/                 # Messages de contact reçus
│   ├── themes/                   # Sélection du thème actif
│   └── tools/                    # Utilitaires (migration shortcodes)
│
├── themes/                       # Thèmes frontend
│   ├── default/
│       ├── templates/            # Templates du thème
│       └── assets/               # CSS/JS du thème
│   └── welcome/                  # Thème générique « Accueil simple »
│       ├── templates/
│       ├── partials/
│       └── assets/
│
├── assets/                       # Fichiers publics partagés
│   ├── css/style.css
│   ├── js/main.js
│   └── images/
│
└── db/
    ├── schema.sql                # Schéma complet de la base
    └── migration_sliders.sql     # Migration table sliders
```

---

## Base de données

| Table | Description |
|---|---|
| `admins` | Comptes administrateurs |
| `content` | Pages CMS (slug, body, template, meta SEO) |
| `categories` | Catégories de produits (hiérarchiques) |
| `produits` | Produits (catégorie, marque, featured, ordre) |
| `produit_images` | Galerie photos des produits |
| `marques` | Marques (logo, site web) |
| `partenaires` | Partenaires (logo, site web) |
| `actualites` | Actualités (publié / brouillon) |
| `contacts` | Soumissions du formulaire de contact |
| `sliders` | Slides des carousels (`slider_id`, label, bg, image) |
| `menus` | Éléments de navigation |
| `settings` | Configuration globale (thème actif, etc.) |
| `audit_logs` | Journal des actions administrateur |

---

## Routeur (Front Controller)

`index.php` extrait le slug depuis l'URL et dispatch vers le bon template :

| URL | Résolution |
|---|---|
| `/Hebergement/maokaa/` | slug `home` → template `home.php` |
| `/Hebergement/maokaa/contact` | slug `contact` → template selon la page |
| `/Hebergement/maokaa/login` | → `login.php` |
| `/Hebergement/maokaa/admin` | → `admin/dashboard.php` (si connecté) |
| Slug inconnu | → page 404 |

Le template est résolu via `ThemeManager::template($template)` avec fallback sur `app/views/templates/`.

---

## Système de Shortcodes

Le corps des pages (champ `body` en base) peut contenir des shortcodes style WordPress :

```
[carousel slider_id="1"]
[products limit="6" category="3"]
[featured_products limit="4"]
[news limit="3"]
[brands]
[partners]
[contact_form]
```

`process_vep_blocks($html, $pdo)` dans `includes/shortcodes.php` détecte et remplace chaque shortcode par son rendu HTML côté serveur, avant envoi au navigateur.

---

## Éditeur visuel (GrapesJS)

Accessible via **Admin → Pages CMS → Édition visuelle**.

Plugins actifs : `grapesjs-plugin-export`, `grapesjs-style-bg`, `grapesjs-custom-code`.

### Blocs disponibles

| Catégorie | Blocs |
|---|---|
| Blocs | Hero, Deux colonnes, Texte, CTA, Image, Divider |
| Dynamiques | Produits, Produits vedettes, Actualités, Marques, Partenaires, Formulaire de contact |
| Caroussel | Carousel Splide |

### Flux de sauvegarde

```
Éditeur GrapesJS
  → blocksToShortcodes()    <div data-vep-block="carousel" data-slider-id="1">
                             devient  [carousel slider_id="1"]
  → DOMPurify.sanitize()    nettoyage HTML (liste blanche d'attributs)
  → POST body               envoi au serveur
  → sanitize_body_html()    suppression PHP des <script>, handlers on*, javascript:
  → UPDATE content SET body = ...
```

### Flux de chargement

```
DB body = "[carousel slider_id="1"] <p>texte</p>"
  → shortcodesToBlocks()    shortcodes → placeholders <div data-vep-block>
  → editor.setComponents()  chargement dans GrapesJS
  → loadVepBlockPreview()   fetch aperçu réel (AJAX → preview-block.php)
  → initSplideInCanvas()    mount Splide dans l'iframe du canvas
```

---

## Composant Carousel (Splide.js)

### Gestion admin

`admin/sliders/` — interface CRUD complète.

Chaque **slider_id** regroupe plusieurs slides dans un même carousel :

1. `admin/sliders/index.php` — liste de tous les carousels
2. `admin/sliders/manage.php?slider_id=X` — slides du carousel X
3. `admin/sliders/create.php` — ajouter un slide (label, couleur, image, ordre)
4. `admin/sliders/edit.php?id=X` — modifier un slide

Shortcode à insérer dans une page : `[carousel slider_id="1"]`

### Rendu frontend

Le shortcode génère un bloc HTML **autonome** :

```html
<!-- Chargé une seule fois par page (variable statique PHP) -->
<link rel="stylesheet" href=".../splide.min.css">
<script src=".../splide.min.js"></script>

<!-- Structure Splide -->
<section id="splide-vep-1" class="splide" aria-label="Carousel">
  <div class="splide__track">
    <ul class="splide__list">
      <li class="splide__slide" style="height:420px;background:#dde4ee;">
        <h2>Label du slide</h2>
      </li>
    </ul>
  </div>
</section>

<!-- Init autonome avec retry jusqu'à ce que window.Splide soit disponible -->
<script>
(function() {
  function mount() {
    if (window.Splide) {
      new Splide('#splide-vep-1', { type:'loop', autoplay:true, interval:3000 }).mount();
    } else { setTimeout(mount, 150); }
  }
  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', mount)
    : mount();
})();
</script>
```

---

## Sécurité

| Mécanisme | Détail |
|---|---|
| Mots de passe | `password_hash()` / `password_verify()` |
| CSRF | Token généré par `generateCSRFToken()`, vérifié sur chaque POST |
| Rate limiting | 5 tentatives max / 15 min par IP |
| Requêtes DB | 100 % prepared statements PDO |
| HTML sauvegardé | `sanitize_body_html()` : supprime `<script>`, `on*`, `javascript:` |
| HTML éditeur | `DOMPurify.sanitize()` côté client avant envoi |
| Accès admin | `requirePasswordChange()` bloque si mot de passe non changé |
| Audit | Toutes les actions admin loguées dans `audit_logs` |

---

## Moteur de thèmes

Le thème actif est stocké dans la table `settings`. `ThemeManager` résout les templates :

```
themes/{theme_name}/templates/{template}.php
```

Templates disponibles : `home`, `page`, `default`, `listing`, `products`, `news`, `404`.

Fallback automatique sur `app/views/templates/` si le fichier n'existe pas dans le thème.

Le thème **Accueil simple** (`themes/welcome`) est sélectionnable depuis la gestion des thèmes. Il fournit une page d'accueil générique indépendante du contenu de la page d'accueil en base de données, une navigation limitée au lien « Accueil » et des gabarits neutres pour les autres pages.

---

## Pages publiques

| URL | Template | Description |
|---|---|---|
| `/` | `home` | Page d'accueil |
| `/products` | `listing` | Catalogue produits |
| `/brands` | `listing` | Marques |
| `/partners` | `listing` | Partenaires |
| `/news` | `listing` | Actualités |
| `/contact` | `page` | Formulaire de contact |
| `/login` | — | Connexion admin |

---

## Uploads

| Type | Répertoire | Limite |
|---|---|---|
| Images produits/contenu | `assets/images/` | 2 Mo |
| Brochures PDF | `assets/brochures/` | 5 Mo |

---

## Dépannage

**Page blanche**
- Activer l'affichage des erreurs PHP : `error_reporting(E_ALL)` dans `config.php`
- Vérifier que le template de la page existe

**Impossible de se connecter**
- Vérifier les identifiants dans la table `admins`
- Vérifier `BASE_URL` dans `.env`
- S'assurer que les sessions PHP sont activées

**Carousel ne s'affiche pas**
- Vérifier que la table `sliders` existe (exécuter `db/migration_sliders.sql`)
- Vérifier que le `slider_id` contient des slides actives (`active = 1`)
- Ouvrir la console navigateur pour détecter une erreur JS

**Token CSRF invalide**
- Vérifier que `session_start()` est bien appelé
- S'assurer que le formulaire inclut `<input type="hidden" name="csrf_token" value="...">`

---

## Sauvegarde

```bash
# Base de données
mysqldump -u root maokaa > backup_maokaa_$(date +%Y%m%d).sql

# Fichiers uploadés
cp -r assets/images/ backup/images/
cp -r assets/brochures/ backup/brochures/
```

---

## Changelog

### 2026
- Système de thèmes dynamiques (`ThemeManager`)
- Éditeur visuel GrapesJS avec blocs dynamiques
- Moteur de shortcodes (`[tag attr="val"]`)
- Composant Carousel Splide.js (DB-driven)
- Interface admin sliders CRUD
- Plugins GrapesJS : export, style-bg, custom-code
- Édition inline des pages
- Import HTML depuis l'éditeur visuel

### v1.0.0 (initial)
- Architecture MVC
- Dashboard administration
- CRUD : produits, catégories, marques, partenaires, actualités
- Formulaire de contact
- Authentification sécurisée
- Design responsive Bootstrap 5

---

*Tous droits réservés © VEP Algérie*
