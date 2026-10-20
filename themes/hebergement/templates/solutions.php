<?php
require_once dirname(__DIR__, 3) . '/includes/shortcodes.php';
require_once dirname(__DIR__, 2) . '/welcome/includes/content.php';
$isAdmin = isLoggedIn();
$storedTitle = trim((string)($page['title'] ?? ''));
$storedMetaTitle = trim((string)($page['meta_title'] ?? ''));
$storedMetaDescription = trim((string)($page['meta_description'] ?? ''));
$pageBody = trim((string)($page['body'] ?? ''));
$pageSlug = strtolower(trim((string)($page['slug'] ?? '')));
$genericTitles = ['services', 'nos services', 'solutions', 'nos solutions'];
$hasCustomTitle = $storedTitle !== ''
    && !in_array(strtolower($storedTitle), $genericTitles, true)
    && !welcome_is_legacy_content($storedTitle);
$pageTitle = $hasCustomTitle ? $storedTitle : 'Nos services d’hébergement web';
$metaTitle = $storedMetaTitle !== '' && !welcome_is_legacy_content($storedMetaTitle)
    ? $storedMetaTitle
    : $pageTitle;
$metaDescription = $storedMetaDescription !== '' && !welcome_is_legacy_content($storedMetaDescription)
    ? $storedMetaDescription
    : 'Découvrez nos offres d’hébergement web, WordPress, VPS, noms de domaine et e-mail professionnel.';
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
    <meta name="page-slug" content="<?php echo htmlspecialchars($pageSlug !== '' ? $pageSlug : 'solutions', ENT_QUOTES, 'UTF-8'); ?>">
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
    <main class="hosting-solutions"<?php if ($isAdmin): ?> data-inline-field="body"<?php endif; ?>>
        <?php if ($hasCustomBody): ?>
            <?php echo do_shortcode($pageBody, $pdo); ?>
        <?php else: ?>
        <section class="solutions-hero" aria-labelledby="solutions-title">
            <div class="hosting-container solutions-hero__inner">
                <div class="solutions-hero__content">
                    <span class="hosting-eyebrow"><span class="hosting-eyebrow__dot"></span> Nos services & tarifs</span>
                    <h1 id="solutions-title"<?php if ($isAdmin && $hasCustomTitle): ?> data-inline-field="title"<?php endif; ?>>
                        <?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>
                    </h1>
                    <p>Du premier site vitrine au projet qui grandit, trouvez un hébergement et des services adaptés à votre activité.</p>
                    <div class="hosting-hero__actions">
                        <a class="hosting-button hosting-button--orange" href="#packs">Comparer les packs <i class="fa-solid fa-arrow-down" aria-hidden="true"></i></a>
                        <a class="hosting-button hosting-button--outline" href="<?php echo BASE_URL; ?>contact">Parler à un conseiller</a>
                    </div>
                </div>
                <div class="solutions-hero__aside">
                    <span class="solutions-hero__aside-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
                    <strong>Un seul partenaire pour votre présence en ligne</strong>
                    <span>Hébergement, domaine, e-mail et accompagnement : composez votre solution selon vos besoins.</span>
                </div>
            </div>
        </section>

        <section class="hosting-section solutions-services" aria-labelledby="solutions-services-title">
            <div class="hosting-container">
                <div class="hosting-section__heading">
                    <span class="hosting-kicker">Un service pour chaque besoin</span>
                    <h2 id="solutions-services-title">Tout pour lancer et faire évoluer votre projet</h2>
                    <p>Commencez avec l’essentiel, puis ajoutez les services utiles à votre activité.</p>
                </div>
                <div class="hosting-service-grid">
                    <article id="hebergement-web" class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-server" aria-hidden="true"></i></span>
                        <h3>Hébergement web</h3>
                        <p>Publiez votre site vitrine, votre blog ou votre site professionnel.</p>
                    </article>
                    <article id="wordpress" class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-brands fa-wordpress" aria-hidden="true"></i></span>
                        <h3>Hébergement WordPress</h3>
                        <p>Une offre pensée pour créer et gérer facilement un site WordPress.</p>
                    </article>
                    <article id="vps" class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-microchip" aria-hidden="true"></i></span>
                        <h3>Serveurs VPS</h3>
                        <p>Des ressources évolutives pour vos applications et projets spécifiques.</p>
                    </article>
                    <article id="domaines" class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-globe" aria-hidden="true"></i></span>
                        <h3>Noms de domaine</h3>
                        <p>Réservez votre adresse web et associez-la à votre hébergement.</p>
                    </article>
                    <article id="email" class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                        <h3>E-mail professionnel</h3>
                        <p>Utilisez des adresses e-mail personnalisées avec votre domaine.</p>
                    </article>
                    <article id="revendeur" class="hosting-service-card hosting-service-card--dark">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i></span>
                        <h3>Offre revendeur</h3>
                        <p>Proposez des services d’hébergement à vos propres clients.</p>
                    </article>
                    <article id="creation-site" class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-laptop-code" aria-hidden="true"></i></span>
                        <h3>Création de site web</h3>
                        <p>Présentez votre entreprise avec un site conçu autour de votre activité.</p>
                    </article>
                    <article id="securite" class="hosting-service-card">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
                        <h3>Sécurité & certificat SSL</h3>
                        <p>Protégez les échanges et renforcez la confiance des visiteurs de votre site.</p>
                    </article>
                    <article class="hosting-service-card hosting-service-card--dark">
                        <span class="hosting-service-card__icon"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i></span>
                        <h3>Migration & accompagnement</h3>
                        <p>Préparez le transfert de votre site et obtenez de l’aide pour démarrer.</p>
                    </article>
                </div>
                <div class="domain-tools hosting-server-options">
                    <div class="domain-tools__heading">
                        <span class="hosting-kicker">Serveurs & VPS</span>
                        <h3>Des ressources adaptées à vos projets</h3>
                    </div>
                    <div class="domain-tools__grid">
                        <article id="hebergement-mutualise" class="domain-tool-card">
                            <h4>Hébergement Web Mutualisé</h4>
                            <p>Hébergez votre site web avec des services e-mail, contacts et agendas associés.</p>
                            <a href="<?php echo BASE_URL; ?>contact">Demander des informations <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </article>
                        <article id="hebergement-algerie" class="domain-tool-card">
                            <h4>Hébergement localisé en Algérie</h4>
                            <p>Hébergez votre site web sur des serveurs installés physiquement en Algérie.</p>
                            <a href="<?php echo BASE_URL; ?>contact">Demander des informations <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </article>
                        <article id="serveur-vps" class="domain-tool-card">
                            <h4>Serveur dédié VPS</h4>
                            <p>Un serveur Virtuel Privé pour disposer d’un environnement adapté à vos applications et projets.</p>
                            <a href="<?php echo BASE_URL; ?>contact">Demander des informations <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </article>
                        <article id="serveur-dedie" class="domain-tool-card">
                            <h4>Serveur dédié Virtuel PRO</h4>
                            <p>Puissance et flexibilité, avec licence WHM/cPanel incluse dans l’offre.</p>
                            <a href="<?php echo BASE_URL; ?>contact">Demander des informations <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </article>
                        <article id="serveur-vps-algerie" class="domain-tool-card">
                            <h4>Serveurs VPS localisés en Algérie</h4>
                            <p>Des serveurs VPS installés en Algérie pour vos besoins d’hébergement local.</p>
                            <a href="<?php echo BASE_URL; ?>contact">Demander des informations <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </article>
                    </div>
                </div>
                <div class="domain-tools">
                    <div class="domain-tools__heading">
                        <span class="hosting-kicker">Services de domaine</span>
                        <h3>Tout pour trouver et gérer votre domaine</h3>
                    </div>
                    <div class="domain-tools__grid">
                        <article id="domain-bulk-search" class="domain-tool-card">
                            <h4>Recherche de domaines par lot</h4>
                            <p>Envoyez plusieurs noms ou extensions à vérifier dans une seule demande.</p>
                            <a href="<?php echo BASE_URL; ?>contact">Faire une demande groupée <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </article>
                        <article id="domain-transfer" class="domain-tool-card">
                            <h4>Transfert de domaines</h4>
                            <p>Demandez le transfert de votre domaine et les informations utiles au renouvellement.</p>
                            <a href="<?php echo BASE_URL; ?>contact">Demander un transfert <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </article>
                        <article id="domain-pricing" class="domain-tool-card">
                            <h4>Tarifs et liste des noms de domaine</h4>
                            <p>Contactez-nous pour connaître les tarifs et extensions actuellement disponibles.</p>
                            <a href="<?php echo BASE_URL; ?>contact">Demander les tarifs <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </article>
                        <article class="domain-tool-card">
                            <h4>Whois check</h4>
                            <p>Consultez les informations publiques associées à un nom de domaine.</p>
                            <a href="https://whois.octenium.com/" target="_blank" rel="noopener noreferrer">Ouvrir le Whois <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                        </article>
                        <article id="domain-management" class="domain-tool-card">
                            <h4>Gestion de domaine</h4>
                            <p>Accompagnement pour le DNS, les serveurs de noms et les enregistrements A, CNAME, SPF, DKIM et AAAA.</p>
                            <a href="<?php echo BASE_URL; ?>contact">Parler à un conseiller <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section id="packs" class="hosting-section hosting-plans solutions-plans" aria-labelledby="solutions-plans-title">
            <div class="hosting-container">
                <div class="hosting-section__heading">
                    <span class="hosting-kicker">Nos packs</span>
                    <h2 id="solutions-plans-title">Choisissez votre formule</h2>
                    <p>Trois points de départ pour vous aider à comparer. Nous ajusterons les caractéristiques à vos besoins réels.</p>
                </div>
                <div class="solutions-price-notice" role="note">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    <span><strong>Tarifs de démonstration :</strong> montants et caractéristiques provisoires, à confirmer avant publication.</span>
                </div>
                <div class="hosting-plan-grid">
                    <article class="hosting-plan">
                        <div class="hosting-plan__icon"><i class="fa-solid fa-seedling" aria-hidden="true"></i></div>
                        <span class="hosting-plan__label">Pour démarrer</span>
                        <h3>Essentiel</h3>
                        <p>L’essentiel pour mettre en ligne un premier site ou un blog.</p>
                        <div class="solutions-price"><strong>1 500</strong><span>DA <small>/ mois</small></span></div>
                        <ul>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Hébergement d’un site</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Certificat SSL</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Assistance de démarrage</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>contact" class="hosting-plan__link">Choisir Essentiel <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="hosting-plan hosting-plan--featured">
                        <span class="hosting-plan__ribbon">Le plus choisi</span>
                        <div class="hosting-plan__icon"><i class="fa-solid fa-rocket" aria-hidden="true"></i></div>
                        <span class="hosting-plan__label">Pour développer votre activité</span>
                        <h3>Business</h3>
                        <p>Une base confortable pour un site professionnel ou WordPress.</p>
                        <div class="solutions-price"><strong>3 500</strong><span>DA <small>/ mois</small></span></div>
                        <ul>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Plusieurs sites selon la formule</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> E-mails professionnels</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Aide à la migration</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>contact" class="hosting-plan__link">Choisir Business <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="hosting-plan">
                        <div class="hosting-plan__icon"><i class="fa-solid fa-bolt" aria-hidden="true"></i></div>
                        <span class="hosting-plan__label">Pour vos projets ambitieux</span>
                        <h3>Performance</h3>
                        <p>Une formule évolutive pour plusieurs projets ou un trafic croissant.</p>
                        <div class="solutions-price"><strong>7 500</strong><span>DA <small>/ mois</small></span></div>
                        <ul>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Ressources renforcées</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Plusieurs projets hébergés</li>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Accompagnement prioritaire</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>contact" class="hosting-plan__link">Choisir Performance <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                </div>
                <p class="hosting-plans__note">Les tarifs réels, périodes de facturation, ressources et conditions seront définis selon les offres proposées.</p>
            </div>
        </section>

        <section class="solutions-extras" aria-labelledby="solutions-extras-title">
            <div class="hosting-container solutions-extras__inner">
                <div>
                    <span class="hosting-kicker">Services complémentaires</span>
                    <h2 id="solutions-extras-title">Besoin d’une solution sur mesure ?</h2>
                    <p>Nom de domaine, VPS, hébergement revendeur ou migration : discutons de votre projet pour construire une offre adaptée.</p>
                </div>
                <a class="hosting-button hosting-button--orange" href="<?php echo BASE_URL; ?>contact">Demander un devis <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
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
