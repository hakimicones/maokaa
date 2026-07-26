<?php
// includes/shortcodes.php — Moteur de shortcodes et blocs dynamiques

/**
 * Traite les shortcodes [tag attr="val"] et les blocs data-vep-block legacy dans le HTML.
 */
function do_shortcode(string $html, PDO $pdo): string
{
    if (empty($html)) return '';

    // Shortcodes WordPress-style : [tag attr="val"]
    $html = preg_replace_callback(
        '/\[([a-z_]+)((?:\s+[a-z_]+=\s*"[^"]*")*)\s*\]/',
        function (array $m) use ($pdo): string {
            $tag      = $m[1];
            $atts     = parse_shortcode_atts($m[2]);
            $rendered = render_shortcode($tag, $atts, $pdo);
            if ($rendered === '') return '';
            if (function_exists('isLoggedIn') && isLoggedIn()) {
                $sc = _rebuild_shortcode($tag, $atts);
                return _wrap_vep_block_admin($tag, $sc, $rendered);
            }
            return $rendered;
        },
        $html
    );

    // Blocs legacy GrapesJS : <div data-vep-block="..." data-limit="6"></div>
    $html = preg_replace_callback(
        '/<div([^>]*\bdata-vep-block=["\'][^"\']+["\'][^>]*)>\s*<\/div>/i',
        function (array $m) use ($pdo): string {
            $attrs = _parse_vep_attrs($m[1]);
            $type  = str_replace('-', '_', $attrs['vep-block'] ?? '');
            unset($attrs['vep-block']);
            $rendered = render_shortcode($type, $attrs, $pdo);
            if ($rendered === '') return '';
            if (function_exists('isLoggedIn') && isLoggedIn()) {
                $sc = _rebuild_shortcode($type, $attrs);
                return _wrap_vep_block_admin($type, $sc, $rendered);
            }
            return $rendered;
        },
        $html
    );

    return $html ?? '';
}

function _rebuild_shortcode(string $tag, array $atts): string
{
    $sc = '[' . $tag;
    foreach ($atts as $k => $v) $sc .= ' ' . $k . '="' . $v . '"';
    return $sc . ']';
}

function _wrap_vep_block_admin(string $type, string $shortcode, string $html): string
{
    static $adminUrls = [
        'featured_products' => 'admin/dashboard.php?section=products',
        'products'          => 'admin/dashboard.php?section=products',
        'news'              => 'admin/dashboard.php?section=news',
        'brands_carousel'   => 'admin/dashboard.php?section=brands',
        'brands'            => 'admin/dashboard.php?section=brands',
        'partners'          => 'admin/dashboard.php?section=partners',
        'contact_form'      => 'admin/dashboard.php?section=messages',
        'carousel'          => 'admin/sliders/index.php',
        'splide_carousel'   => 'admin/sliders/index.php',
    ];
    $adminUrl = $adminUrls[$type] ?? 'admin/dashboard.php';
    return '<div class="vep-block-wrapper"'
        . ' data-vep-shortcode="' . htmlspecialchars($shortcode, ENT_QUOTES, 'UTF-8') . '"'
        . ' data-vep-type="'      . htmlspecialchars($type,      ENT_QUOTES, 'UTF-8') . '"'
        . ' data-vep-admin-url="' . htmlspecialchars($adminUrl,  ENT_QUOTES, 'UTF-8') . '">'
        . $html
        . '</div>';
}

// Alias pour compatibilité avec les templates existants
function process_vep_blocks(string $html, PDO $pdo): string
{
    return do_shortcode($html, $pdo);
}

function parse_shortcode_atts(string $text): array
{
    $atts = [];
    preg_match_all('/([a-z_]+)\s*=\s*"([^"]*)"/', $text, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) $atts[$m[1]] = $m[2];
    return $atts;
}

function render_shortcode(string $tag, array $atts, PDO $pdo): string
{
    $limit    = max(1, (int)($atts['limit']    ?? 6));
    $category = (int)($atts['category'] ?? 0);
    $title    = $atts['title'] ?? '';

    // Pas de limite par défaut pour les produits (le bloc JS charge tout)
    if ($tag === 'products') {
        $limit = max(0, (int)($atts['limit'] ?? 0));
    }

    // Vérification des modules activés
    $moduleMap = [
        'products'          => 'products',
        'featured_products' => 'products',
        'product_slider'    => 'products',
        'news'              => 'news',
        'brands'            => 'brands',
        'brands_carousel'   => 'brands',
        'partners'          => 'partners',
        'carousel'          => 'sliders',
        'splide_carousel'   => 'sliders',
        'contact_form'      => 'messages',
        'quote_form'        => 'messages',
        'menu'              => 'menus',
        'pages_list'        => 'pages',
    ];
    if (isset($moduleMap[$tag]) && function_exists('is_module_enabled') && !is_module_enabled($moduleMap[$tag])) {
        return '';
    }

    switch ($tag) {
        case 'products':          return render_block_products($pdo, $limit, $category, $title);
        case 'featured_products': return render_block_featured_products($pdo, $limit, $title);
        case 'news':              return render_block_news($pdo, $limit, $title);
        case 'brands_carousel': return render_block_brands_carousel($pdo, $title);
        case 'brands':            return render_block_brands($pdo, $title);
        case 'partners':          return render_block_partners($pdo, $title);
        case 'contact_form':      return render_block_contact_form($pdo, $title);
        case 'splide_carousel':
        case 'carousel':          return render_block_carousel($pdo, (int)($atts['slider_id'] ?? 0));
        default:                  return '';
    }
}

// Parse les attributs data-* d'une balise HTML (usage interne)
function _parse_vep_attrs(string $attrString): array
{
    $result = [];
    preg_match_all('/\bdata-([\w-]+)=["\']([^"\']*)["\']/', $attrString, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) $result[$m[1]] = $m[2];
    return $result;
}

// --- Fonctions de rendu ---

function render_block_featured_products(PDO $pdo, int $limit = 6, string $blockTitle = ''): string
{
    require_once __DIR__ . '/../app/models/Product.php';
    $model = new Product($pdo);
    $items = $model->getFeatured($limit);
    ob_start();
    include __DIR__ . '/../app/views/partials/blocks/featured-products.php';
    return ob_get_clean();
}

function render_block_products(PDO $pdo, int $limit = 0, int $category = 0, string $blockTitle = ''): string
{
    ob_start();
    include __DIR__ . '/../app/views/partials/blocks/products.php';
    return ob_get_clean();
}

function render_block_news(PDO $pdo, int $limit = 3, string $blockTitle = ''): string
{
    require_once __DIR__ . '/../app/models/News.php';
    $model = new News($pdo);
    $items = $model->getRecent($limit);
    ob_start();
    include __DIR__ . '/../app/views/partials/blocks/news.php';
    return ob_get_clean();
}

function render_block_brands(PDO $pdo, string $blockTitle = ''): string
{
    require_once __DIR__ . '/../app/models/Brand.php';
    $model = new Brand($pdo);
    $items = $model->getAll();
    ob_start();
    include __DIR__ . '/../app/views/partials/blocks/brands.php';
    return ob_get_clean();
}

function render_block_brands_carousel(PDO $pdo, string $blockTitle = ''): string
{
    static $assetsLoaded = false;
    $assets = '';
    if (!$assetsLoaded) {
        $assetsLoaded = true;
        $assets = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">'
                . '<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>';
    }

    require_once __DIR__ . '/../app/models/Brand.php';
    $model = new Brand($pdo);
    $items = $model->getAll();
    ob_start();
    include __DIR__ . '/../app/views/partials/blocks/brands-carousel.php';
    return $assets . ob_get_clean();
}

function render_block_partners(PDO $pdo, string $blockTitle = ''): string
{
    require_once __DIR__ . '/../app/models/Partner.php';
    $model = new Partner($pdo);
    $items = $model->getAll();
    ob_start();
    include __DIR__ . '/../app/views/partials/blocks/partners.php';
    return ob_get_clean();
}

function render_block_carousel(PDO $pdo = null, int $slider_id = 0): string
{
    static $count = 0;
    $count++;
    $id = 'splide-vep-' . $count;

    if ($slider_id > 0 && $pdo !== null) {
        require_once __DIR__ . '/../app/models/Slider.php';
        $model  = new Slider($pdo);
        $dbRows = $model->getBySlider($slider_id);
        if (!empty($dbRows)) {
            $slides = array_map(fn($r) => [
                'bg'            => $r['bg']            ?? '#dde4ee',
                'label'         => $r['label']         ?? '',
                'subtitle'      => $r['subtitle']      ?? null,
                'image'         => $r['image']         ?? null,
                'text_position' => $r['text_position'] ?? 'center',
            ], $dbRows);
        } else {
            $slides = [
                ['bg' => '#dde4ee', 'label' => 'Bienvenue sur notre site', 'subtitle' => null, 'image' => null, 'text_position' => 'center'],
                ['bg' => '#c8d4e8', 'label' => 'Nos produits',             'subtitle' => null, 'image' => null, 'text_position' => 'center'],
                ['bg' => '#b5c4de', 'label' => 'Contactez-nous',           'subtitle' => null, 'image' => null, 'text_position' => 'center'],
            ];
        }
    } else {
        $slides = [
            ['bg' => '#dde4ee', 'label' => 'Bienvenue sur notre site', 'subtitle' => null, 'image' => null, 'text_position' => 'center'],
            ['bg' => '#c8d4e8', 'label' => 'Nos produits',             'subtitle' => null, 'image' => null, 'text_position' => 'center'],
            ['bg' => '#b5c4de', 'label' => 'Contactez-nous',           'subtitle' => null, 'image' => null, 'text_position' => 'center'],
        ];
    }

    $positionMap = [
        'top-left'      => ['align-items:flex-start;', 'justify-content:flex-start;', 'text-align:left;'],
        'top-center'    => ['align-items:flex-start;', 'justify-content:center;',     'text-align:center;'],
        'top-right'     => ['align-items:flex-start;', 'justify-content:flex-end;',   'text-align:right;'],
        'center-left'   => ['align-items:center;',     'justify-content:flex-start;', 'text-align:left;'],
        'center'        => ['align-items:center;',     'justify-content:center;',     'text-align:center;'],
        'center-right'  => ['align-items:center;',     'justify-content:flex-end;',   'text-align:right;'],
        'bottom-left'   => ['align-items:flex-end;',   'justify-content:flex-start;', 'text-align:left;'],
        'bottom-center' => ['align-items:flex-end;',   'justify-content:center;',     'text-align:center;'],
        'bottom-right'  => ['align-items:flex-end;',   'justify-content:flex-end;',   'text-align:right;'],
    ];
    $items = '';
    foreach ($slides as $s) {
        $bg     = htmlspecialchars($s['bg'],    ENT_QUOTES, 'UTF-8');
        $label  = htmlspecialchars($s['label'], ENT_QUOTES, 'UTF-8');
        $sub    = $s['subtitle'] ? htmlspecialchars($s['subtitle'], ENT_QUOTES, 'UTF-8') : null;
        $pos    = $s['text_position'] ?? 'center';
        $ai     = $positionMap[$pos][0] ?? 'align-items:center;';
        $jc     = $positionMap[$pos][1] ?? 'justify-content:center;';
        $ta     = $positionMap[$pos][2] ?? 'text-align:center;';

        $textBlock = '<div  class="slider__overlay" style="' . $ai . $jc . 'display:flex;flex-direction:column;gap:6px;padding:40px 20px;width:100%;">'
                   . '<h2 style="color:#fff;' . $ta . 'text-shadow:0 2px 6px rgba(0,0,0,.6);margin:0;font-size:1.8rem;">' . $label . '</h2>';
        if ($sub) {
            $textBlock .= '<p style="color:rgba(255,255,255,.9);' . $ta . 'text-shadow:0 1px 4px rgba(0,0,0,.5);margin:0;font-size:1.1rem;max-width:600px;">' . $sub . '</p>';
        }
        $textBlock .= '</div>';

        if (!empty($s['image'])) {
            $img    = htmlspecialchars(    $s['image'], ENT_QUOTES, 'UTF-8');
            $items .= '<li class="splide__slide myclass" data-splide-cover="true" style="position:relative;overflow:hidden;"> '
                    . '<img src="' . $img . '" alt="' . $label . '" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">'
                    . '<div class="container" style="position:absolute;inset:0;display:flex;' . $ai . $jc . ' ">'
                    . $textBlock
                    . '</div>'
                    . '</li>';
        } else {
            $items .= '<li class="splide__slide myclass" style="height:420px;background:' . $bg . ';display:flex;' . $ai . $jc . '">'
                    . $textBlock
                    . '</li>';
        }
    }

    static $assetsLoaded = false;

    $assets = '';
    if (!$assetsLoaded) {
        $assetsLoaded = true;
        $assets = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">'
                . '<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>';
    }

    $html = $assets
          . '<style>'
          . '#' . $id . '.splide,'
          . '#' . $id . ' .splide__track,'
          . '#' . $id . ' .splide__list,'
          . '#' . $id . ' .splide__slide{height:100%;}'
          . '#' . $id . '{padding:0!important;margin:0!important;}'
          . '#' . $id . ' .splide__pagination{top:auto!important;bottom:12px!important;}'
          . '#' . $id . ' .splide__arrow{opacity:.85;}'
          . '</style>'
          . '<section id="' . $id . '" class="splide" aria-label="Carousel" style="height:420px;">'
          . '<div class="splide__track" style="height:100%;">'
          . '<ul class="splide__list" style="height:100%;">' . $items . '</ul>'
          . '</div>'
          . '</section>';

    $html .= '<script>'
           . '(function(){'
           .   'var id="#' . $id . '";'
           .   'function mount(){'
           .     'if(window.Splide){'
           .       'new Splide(id,{type:"fade",autoplay:true,interval:4000,pauseOnHover:true,rewind:true,cover:true,heightRatio:0.4}).mount();'
           .     '}else{setTimeout(mount,150);}'
           .   '}'
           .   'if(document.readyState==="loading"){'
           .     'document.addEventListener("DOMContentLoaded",mount);'
           .   '}else{mount();}'
           . '})();'
           . '</script>';

    return $html;
}

function render_block_contact_form(PDO $pdo, string $blockTitle = ''): string
{
    require_once __DIR__ . '/../app/models/Contact.php';
    $formSent  = false;
    $formError = '';
    $formData  = [];

    if (($_POST['vep_contact_form'] ?? '') === '1') {
        if (!function_exists('verifyCSRFToken') || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $formError = 'Token de sécurité invalide.';
        } else {
            $recaptchaToken = $_POST['g-recaptcha-response'] ?? '';
            $recaptchaSecret = defined('RECAPTCHA_SECRET_KEY') ? RECAPTCHA_SECRET_KEY : '';
            $captchaOk = false;
            if ($recaptchaToken !== '' && $recaptchaSecret !== '') {
                $resp = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => http_build_query([
                            'secret'   => $recaptchaSecret,
                            'response' => $recaptchaToken,
                            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
                        ]),
                    ],
                ]));
                if ($resp !== false) {
                    $json = json_decode($resp, true);
                    $captchaOk = ($json['success'] ?? false) && ($json['score'] ?? 0) >= 0.5;
                }
            }
            if (!$captchaOk) {
                $formError = 'Vérification anti-spam échouée. Veuillez réessayer.';
            } else {
                $formData = [
                    'nom'       => trim($_POST['contact_nom']       ?? ''),
                    'email'     => trim($_POST['contact_email']     ?? ''),
                    'telephone' => trim($_POST['contact_telephone'] ?? ''),
                    'sujet'     => trim($_POST['contact_sujet']     ?? ''),
                    'message'   => trim($_POST['contact_message']   ?? ''),
                ];
                if (empty($formData['nom']) || empty($formData['email']) || empty($formData['message'])) {
                    $formError = 'Veuillez remplir les champs obligatoires (nom, email, message).';
                } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
                    $formError = 'Adresse email invalide.';
                } else {
                    $model = new Contact($pdo);
                    if ($model->create($formData)) {
                        $formSent = true;
                        $formData = [];
                    } else {
                        $formError = "Erreur lors de l'envoi du message.";
                    }
                }
            }
        }
    }

    ob_start();
    include __DIR__ . '/../app/views/partials/blocks/contact-form.php';
    return ob_get_clean();
}
