<?php
require_once dirname(__DIR__, 3) . '/includes/shortcodes.php';
require_once dirname(__DIR__, 2) . '/welcome/includes/content.php';
$isAdmin = isLoggedIn();
$storedTitle = trim((string)($page['title'] ?? ''));
$storedMetaTitle = trim((string)($page['meta_title'] ?? ''));
$storedMetaDescription = trim((string)($page['meta_description'] ?? ''));
$pageBody = trim((string)($page['body'] ?? ''));
$genericTitles = ['', 'accueil', 'home', 'bienvenue sur votre site'];
$hasCustomTitle = !in_array(strtolower($storedTitle), $genericTitles, true)
    && !welcome_is_legacy_content($storedTitle);
$pageTitle = $hasCustomTitle
    ? $storedTitle
    : 'Hébergement web en Algérie, domaines et services pour vos projets';
$metaTitle = $storedMetaTitle !== '' && !in_array(strtolower($storedMetaTitle), $genericTitles, true)
    && !welcome_is_legacy_content($storedMetaTitle)
    ? $storedMetaTitle
    : $pageTitle;
$metaDescription = $storedMetaDescription !== '' && !welcome_is_legacy_content($storedMetaDescription)
    ? $storedMetaDescription
    : 'Découvrez nos solutions d’hébergement web, noms de domaine, serveurs et services professionnels pour lancer et développer votre présence en ligne.';
$hasCustomBody = $pageBody !== '' && !welcome_is_legacy_content($pageBody);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1930">
    <title><?php echo htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($isAdmin): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="page-slug" content="<?php echo htmlspecialchars($page['slug'] ?? 'home', ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="base-url" content="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
    <link href="<?php echo theme_url('assets/css/theme.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/assets/css/theme.css') . '-' . filesize(dirname(__DIR__) . '/assets/css/theme.css'); ?>" rel="stylesheet">
    <?php if ($isAdmin): ?>
    <link href="<?php echo BASE_URL; ?>assets/css/inline-edit.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body class="hosting-site">
    <?php theme_partial('navbar'); ?>
    <main class="hosting-home"<?php if ($isAdmin): ?> data-inline-field="body"<?php endif; ?>>
        <?php if ($hasCustomBody): ?>
            <?php echo do_shortcode($pageBody, $pdo); ?>
        <?php else: ?>
        <section class="hosting-hero" aria-labelledby="hosting-hero-title">
            <div class="hosting-container hosting-hero__inner">
                <div class="hosting-hero__content">
                    <span class="hosting-eyebrow"><span class="hosting-eyebrow__dot"></span> Hébergement web · Domaines · Services numériques</span>
                    <h1 id="hosting-hero-title"<?php if ($isAdmin && $hasCustomTitle): ?> data-inline-field="title"<?php endif; ?>>
                        <?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>
                    </h1>
                    <p class="hosting-hero__lead">Tout ce qu’il vous faut pour lancer votre site, héberger vos projets et développer votre activité en ligne.</p>
                    <div class="hosting-hero__actions">
                        <a class="hosting-button hosting-button--orange" href="#offres">Découvrir nos offres <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        <a class="hosting-button hosting-button--outline" href="#services">Explorer nos services</a>
                    </div>
                    <div class="hosting-hero__note">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        <span>Des solutions adaptées, du premier site aux projets qui grandissent.</span>
                    </div>
                </div>
                <div class="hosting-visual" aria-hidden="true">
                    <div class="hosting-visual__glow"></div>
                    <div class="hosting-visual__orbit hosting-visual__orbit--one"></div>
                    <div class="hosting-visual__orbit hosting-visual__orbit--two"></div>
                    <div class="hosting-server">
                        <div class="hosting-server__top">
                            <span class="hosting-server__brand"><i class="fa-solid fa-cube" aria-hidden="true"></i> ESPACE WEB</span>
                            <span class="hosting-server__menu" aria-hidden="true"><i></i><i></i><i></i></span>
                        </div>
                        <div class="hosting-server__row">
                            <span class="hosting-server__icon"><i class="fa-solid fa-globe" aria-hidden="true"></i></span>
                            <span class="hosting-server__details"><strong>Votre site web</strong><small>Prêt pour votre prochain projet</small></span>
                            <span class="hosting-server__status"><i></i> En ligne</span>
                        </div>
                        <div class="hosting-server__row">
                            <span class="hosting-server__icon hosting-server__icon--blue"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                            <span class="hosting-server__details"><strong>E-mail professionnel</strong><small>Associé à votre domaine</small></span>
                            <span class="hosting-server__arrow"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
                        </div>
                        <div class="hosting-server__bottom">
                            <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Services réunis au même endroit</span>
                            <span class="hosting-server__spark" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></span>
                        </div>
                    </div>
                    <div class="hosting-float hosting-float--domain"><span><i class="fa-solid fa-at" aria-hidden="true"></i></span><strong>Votre nom de domaine</strong></div>
                    <div class="hosting-float hosting-float--secure"><i class="fa-solid fa-lock" aria-hidden="true"></i> Espace sécurisé</div>
                    <div class="hosting-visual__caption"><span></span> Votre activité en ligne commence ici</div>
                </div>
            </div>
            <div class="hosting-hero__bottom">
                <div class="hosting-container hosting-trust">
                    <p>Une solution pour chaque besoin</p>
                    <span><i class="fa-solid fa-server" aria-hidden="true"></i> Hébergement web</span>
                    <span><i class="fa-brands fa-wordpress" aria-hidden="true"></i> WordPress</span>
                    <span><i class="fa-solid fa-network-wired" aria-hidden="true"></i> Serveurs VPS</span>
                    <span><i class="fa-solid fa-globe" aria-hidden="true"></i> Noms de domaine</span>
                </div>
            </div>
        </section>

        <section id="services" class="hosting-section hosting-services">
            <div class="hosting-container">
                <div class="hosting-section__heading">
                    <span class="hosting-kicker">Nos services</span>
                    <h2>Les bons outils pour chaque projet</h2>
                    <p>Que vous lanciez votre premier site ou gériez plusieurs projets, choisissez les services qui vous correspondent.</p>
                </div>
                <div class="hosting-service-grid">
                    <article class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-server" aria-hidden="true"></i></span>
                        <h3>Hébergement web</h3>
                        <p>Un espace pour publier votre site et le faire évoluer avec votre activité.</p>
                        <a href="#offres">Découvrir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-brands fa-wordpress" aria-hidden="true"></i></span>
                        <h3>Hébergement WordPress</h3>
                        <p>Une solution pensée pour créer et administrer votre site WordPress.</p>
                        <a href="#offres">Découvrir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-network-wired" aria-hidden="true"></i></span>
                        <h3>Serveurs VPS</h3>
                        <p>Des ressources dédiées pour les applications et projets aux besoins spécifiques.</p>
                        <a href="#offres">Découvrir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-globe" aria-hidden="true"></i></span>
                        <h3>Noms de domaine</h3>
                        <p>Trouvez une adresse claire et mémorable pour votre présence en ligne.</p>
                        <a href="#domaine">Trouver un domaine <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                        <h3>E-mail professionnel</h3>
                        <p>Associez des adresses e-mail professionnelles à votre nom de domaine.</p>
                        <a href="#offres">Découvrir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="hosting-service-card hosting-service-card--dark">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
                        <h3>Revendeur d’hébergement</h3>
                        <p>Développez votre activité en proposant des solutions d’hébergement à vos clients.</p>
                        <a href="#offres">Découvrir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                </div>
            </div>
        </section>

        <section id="offres" class="hosting-section hosting-plans">
            <div class="hosting-container">
                <div class="hosting-section__heading">
                    <span class="hosting-kicker">Nos solutions</span>
                    <h2>Choisissez votre point de départ</h2>
                    <p>Comparez les grandes familles d’offres et trouvez celle qui répond à votre besoin.</p>
                </div>
                <div class="hosting-plan-grid">
                    <article class="hosting-plan">
                        <div class="hosting-plan__icon"><i class="fa-solid fa-seedling" aria-hidden="true"></i></div>
                        <span class="hosting-plan__label">Pour commencer</span>
                        <h3>Hébergement web</h3>
                        <p>L’essentiel pour mettre en ligne un site vitrine, un blog ou un projet professionnel.</p>
                        <ul>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Un espace pour votre site</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Gestion de votre nom de domaine</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Options selon la formule choisie</li>
                        </ul>
                        <a href="#contact" class="hosting-plan__link">En savoir plus <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="hosting-plan hosting-plan--featured">
                        <span class="hosting-plan__ribbon">Pour vos contenus</span>
                        <div class="hosting-plan__icon"><i class="fa-brands fa-wordpress" aria-hidden="true"></i></div>
                        <span class="hosting-plan__label">Pour vos contenus</span>
                        <h3>WordPress</h3>
                        <p>Un environnement pour concevoir et gérer votre site avec WordPress.</p>
                        <ul>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Adapté aux sites WordPress</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Gestion simplifiée des contenus</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Options selon la formule choisie</li>
                        </ul>
                        <a href="#contact" class="hosting-plan__link">En savoir plus <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="hosting-plan">
                        <div class="hosting-plan__icon"><i class="fa-solid fa-microchip" aria-hidden="true"></i></div>
                        <span class="hosting-plan__label">Pour aller plus loin</span>
                        <h3>VPS & revendeur</h3>
                        <p>Des options flexibles pour vos applications ou pour héberger les sites de vos clients.</p>
                        <ul>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Projets aux besoins spécifiques</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Ressources adaptées à votre usage</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Plusieurs projets à administrer</li>
                        </ul>
                        <a href="#contact" class="hosting-plan__link">En savoir plus <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                </div>
                <p id="contact" class="hosting-plans__note">Les caractéristiques et options dépendent de chaque formule. Contactez-nous pour choisir celle qui convient à votre projet.</p>
            </div>
        </section>

        <section id="domaine" class="hosting-domain">
            <div class="hosting-container hosting-domain__inner">
                <div class="hosting-domain__icon"><i class="fa-solid fa-globe" aria-hidden="true"></i></div>
                <div>
                    <span class="hosting-kicker">Votre identité en ligne</span>
                    <h2>Un projet commence par une adresse</h2>
                    <p>Choisissez un nom de domaine à votre image, notamment en .dz si votre projet y est éligible, et associez-le à vos services web.</p>
                </div>
                <a class="hosting-button hosting-button--orange" href="#offres">Découvrir les domaines <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </section>

        <section class="hosting-section hosting-steps">
            <div class="hosting-container">
                <div class="hosting-section__heading">
                    <span class="hosting-kicker">Simple et progressif</span>
                    <h2>Votre projet en trois étapes</h2>
                </div>
                <div class="hosting-step-grid">
                    <article class="hosting-step"><span>01</span><h3>Choisissez vos services</h3><p>Identifiez l’hébergement, le domaine ou les options dont vous avez besoin.</p></article>
                    <article class="hosting-step"><span>02</span><h3>Préparez votre site</h3><p>Installez votre projet et configurez les services associés à votre activité.</p></article>
                    <article class="hosting-step"><span>03</span><h3>Développez votre présence</h3><p>Faites évoluer votre solution à mesure que vos besoins changent.</p></article>
                </div>
            </div>
        </section>

        <section class="hosting-faq">
            <div class="hosting-container hosting-faq__inner">
                <div class="hosting-faq__intro">
                    <span class="hosting-kicker">Besoin d’un repère ?</span>
                    <h2>Quelques réponses pour bien choisir</h2>
                    <p>Le bon service dépend de votre projet. Voici quelques pistes pour vous orienter.</p>
                </div>
                <div class="hosting-faq__list">
                    <details>
                        <summary>Quel hébergement choisir pour mon premier site ?</summary>
                        <p>Un hébergement web classique convient souvent à un site vitrine ou à un blog. Pour un site WordPress, choisissez une formule adaptée à ce CMS.</p>
                    </details>
                    <details>
                        <summary>À quoi sert un nom de domaine ?</summary>
                        <p>C’est l’adresse que vos visiteurs saisissent pour retrouver votre site. Vous pouvez la relier à votre hébergement et à vos services e-mail.</p>
                    </details>
                    <details>
                        <summary>Quand choisir un VPS ou une offre revendeur ?</summary>
                        <p>Un VPS s’adresse aux projets qui ont besoin de ressources et de réglages spécifiques. Une offre revendeur peut convenir aux professionnels qui gèrent plusieurs sites clients.</p>
                    </details>
                </div>
            </div>
        </section>

        <section class="hosting-final-cta">
            <div class="hosting-container hosting-final-cta__inner">
                <div>
                    <span class="hosting-kicker">Prêt à vous lancer ?</span>
                    <h2>Faisons grandir votre projet en ligne.</h2>
                    <p>Parcourez les solutions disponibles et choisissez le service adapté à votre prochaine étape.</p>
                </div>
                <a class="hosting-button hosting-button--orange" href="#offres">Trouver mon offre <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </section>
        <?php endif; ?>
    </main>
    <?php theme_partial('footer'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($isAdmin): ?>
    <script src="<?php echo BASE_URL; ?>assets/js/inline-edit.js"></script>
    <?php endif; ?>
</body>
</html>
