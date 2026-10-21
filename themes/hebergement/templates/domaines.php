<?php
$isAdmin = isLoggedIn();
$domainExtensions = [
    ['extension' => '.xyz', 'price' => 2500, 'category' => 'generique'],
    ['extension' => '.com', 'price' => 2500, 'category' => 'popular'],
    ['extension' => '.net', 'price' => 3000, 'category' => 'popular'],
    ['extension' => '.org', 'price' => 2500, 'category' => 'popular'],
    ['extension' => '.info', 'price' => 6400, 'category' => 'generique'],
    ['extension' => '.biz', 'price' => 2500, 'category' => 'business'],
    ['extension' => '.club', 'price' => 2000, 'category' => 'creative'],
    ['extension' => '.co', 'price' => 4900, 'category' => 'popular'],
    ['extension' => '.dz', 'price' => 0, 'category' => 'algerian'],
    ['extension' => '.gov.dz', 'price' => 0, 'category' => 'algerian'],
    ['extension' => '.com.dz', 'price' => 0, 'category' => 'algerian'],
    ['extension' => '.org.dz', 'price' => 0, 'category' => 'algerian'],
    ['extension' => '.net.dz', 'price' => 0, 'category' => 'algerian'],
    ['extension' => '.edu.dz', 'price' => 0, 'category' => 'algerian'],
    ['extension' => '.asso.dz', 'price' => 0, 'category' => 'algerian'],
    ['extension' => '.art.dz', 'price' => 0, 'category' => 'algerian'],
    ['extension' => '.pol.dz', 'price' => 0, 'category' => 'algerian'],
    ['extension' => '.pub', 'price' => 4900, 'category' => 'generique'],
    ['extension' => '.ca', 'price' => 2500, 'category' => 'generique'],
    ['extension' => '.fr', 'price' => 2000, 'category' => 'popular'],
    ['extension' => '.pro', 'price' => 2500, 'category' => 'business'],
    ['extension' => '.online', 'price' => 6400, 'category' => 'popular'],
    ['extension' => '.live', 'price' => 4000, 'category' => 'creative'],
    ['extension' => '.site', 'price' => 4900, 'category' => 'generique'],
    ['extension' => '.design', 'price' => 8200, 'category' => 'creative'],
    ['extension' => '.studio', 'price' => 4000, 'category' => 'creative'],
    ['extension' => '.photo', 'price' => 5200, 'category' => 'creative'],
    ['extension' => '.coffee', 'price' => 5200, 'category' => 'creative'],
    ['extension' => '.tech', 'price' => 8400, 'category' => 'creative'],
    ['extension' => '.io', 'price' => 8400, 'category' => 'creative'],
    ['extension' => '.life', 'price' => 5200, 'category' => 'creative'],
    ['extension' => '.art', 'price' => 2400, 'category' => 'creative'],
    ['extension' => '.me', 'price' => 4800, 'category' => 'generique'],
    ['extension' => '.name', 'price' => 2000, 'category' => 'generique'],
    ['extension' => '.news', 'price' => 3800, 'category' => 'creative'],
    ['extension' => '.tv', 'price' => 6200, 'category' => 'creative'],
    ['extension' => '.hosting', 'price' => 72000, 'category' => 'business'],
    ['extension' => '.game', 'price' => 72000, 'category' => 'creative'],
    ['extension' => '.games', 'price' => 3000, 'category' => 'creative'],
    ['extension' => '.zone', 'price' => 5200, 'category' => 'creative'],
    ['extension' => '.us', 'price' => 1700, 'category' => 'generique'],
    ['extension' => '.doctor', 'price' => 16000, 'category' => 'business'],
    ['extension' => '.tel', 'price' => 2500, 'category' => 'generique'],
    ['extension' => '.ws', 'price' => 4500, 'category' => 'generique'],
    ['extension' => '.xxx', 'price' => 16900, 'category' => 'generique'],
    ['extension' => '.mobi', 'price' => 3900, 'category' => 'generique'],
    ['extension' => '.cc', 'price' => 2000, 'category' => 'generique'],
    ['extension' => '.education', 'price' => 3200, 'category' => 'business'],
    ['extension' => '.host', 'price' => 16900, 'category' => 'business'],
    ['extension' => '.press', 'price' => 12000, 'category' => 'business'],
    ['extension' => '.buzz', 'price' => 6400, 'category' => 'creative'],
    ['extension' => '.world', 'price' => 5200, 'category' => 'creative'],
    ['extension' => '.protection', 'price' => 490000, 'category' => 'business'],
    ['extension' => '.football', 'price' => 3200, 'category' => 'creative'],
    ['extension' => '.futbol', 'price' => 2000, 'category' => 'creative'],
    ['extension' => '.tools', 'price' => 5200, 'category' => 'business'],
    ['extension' => '.wiki', 'price' => 4600, 'category' => 'creative'],
    ['extension' => '.pictures', 'price' => 2000, 'category' => 'creative'],
    ['extension' => '.trading', 'price' => 12200, 'category' => 'business'],
    ['extension' => '.trade', 'price' => 4800, 'category' => 'business'],
    ['extension' => '.vip', 'price' => 2600, 'category' => 'business'],
    ['extension' => '.menu', 'price' => 6000, 'category' => 'business'],
    ['extension' => '.jobs', 'price' => 23000, 'category' => 'business'],
    ['extension' => '.group', 'price' => 3400, 'category' => 'business'],
    ['extension' => '.agency', 'price' => 3400, 'category' => 'business'],
    ['extension' => '.shop', 'price' => 6000, 'category' => 'business'],
];
$popularExtensions = ['.com', '.dz', '.fr', '.net', '.org', '.online'];
$domainFilters = [
    'all' => 'Toutes les extensions (' . count($domainExtensions) . ')',
    'popular' => 'Populaires',
    'algerian' => 'Algériennes',
    'generique' => 'Génériques',
    'business' => 'Affaires',
    'creative' => 'Créatives',
];
$formatDomainPrice = static function (int $price): string {
    return number_format($price, 0, ',', ' ');
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1930">
    <title>Nom de domaine — Rechercher et choisir une extension</title>
    <meta name="description" content="Trouvez un nom de domaine pour votre projet et consultez les extensions disponibles et leurs tarifs en dinars algériens.">
    <?php if ($isAdmin): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="page-slug" content="domaine">
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
    <main class="domain-page">
        <section id="domain-search" class="domain-hero" aria-labelledby="domain-hero-title">
            <div class="hosting-container domain-hero__inner">
                <div class="domain-hero__content">
                    <span class="hosting-eyebrow"><span class="hosting-eyebrow__dot"></span> Votre identité sur le web</span>
                    <h1 id="domain-hero-title">Trouvez le domaine qui vous représente</h1>
                    <p>Choisissez une adresse facile à retenir et donnez à votre projet une présence professionnelle en ligne.</p>
                    <form class="domain-search-form" method="post" novalidate>
                        <label for="hosting-domain-query">Rechercher un nom de domaine</label>
                        <div class="domain-search-form__field">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <input
                                id="hosting-domain-query"
                                type="text"
                                name="domain"
                                placeholder="exemple.dz"
                                pattern="(?:[A-Za-z0-9](?:[A-Za-z0-9\x2d]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,63}"
                                maxlength="253"
                                autocomplete="off"
                                required
                            >
                            <button type="submit">Rechercher <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                        </div>
                        <small>La disponibilité affichée est indicative et doit être confirmée avant l’achat.</small>
                        <div id="domain-search-result" class="domain-search-result" aria-live="polite"></div>
                    </form>
                </div>
                <div class="domain-hero__visual" aria-hidden="true">
                    <div class="domain-orbit domain-orbit--outer"></div>
                    <div class="domain-orbit domain-orbit--inner"></div>
                    <div class="domain-hero__globe"><i class="fa-solid fa-globe"></i></div>
                    <span class="domain-extension-bubble domain-extension-bubble--com">.com</span>
                    <span class="domain-extension-bubble domain-extension-bubble--dz">.dz</span>
                    <span class="domain-extension-bubble domain-extension-bubble--net">.net</span>
                </div>
            </div>
        </section>

        <section class="domain-popular hosting-section" aria-labelledby="domain-popular-title">
            <div class="hosting-container">
                <div class="hosting-section__heading">
                    <span class="hosting-kicker">Pour commencer</span>
                    <h2 id="domain-popular-title">Les extensions populaires</h2>
                    <p>Comparez quelques extensions souvent choisies pour les projets professionnels et personnels.</p>
                </div>
                <div class="domain-popular__grid">
                    <?php foreach ($domainExtensions as $extension):
                        if (!in_array($extension['extension'], $popularExtensions, true)) {
                            continue;
                        }
                    ?>
                    <article class="domain-popular-card">
                        <strong><?php echo htmlspecialchars($extension['extension'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <span><?php echo $extension['price'] === 0 ? '0' : $formatDomainPrice($extension['price']); ?> <small>DA HT / an</small></span>
                        <a href="#domain-pricing">Voir les extensions <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="domain-pricing" class="domain-catalog hosting-section" aria-labelledby="domain-catalog-title">
            <div class="hosting-container">
                <div class="hosting-section__heading">
                    <span class="hosting-kicker">Extensions & tarifs</span>
                    <h2 id="domain-catalog-title">Choisissez l’extension qui vous ressemble</h2>
                    <p>Parcourez les extensions tarifées de votre liste et filtrez-les selon votre besoin. Prix d’enregistrement HT par an.</p>
                </div>
                <div class="domain-catalog__toolbar">
                    <label class="domain-catalog__search">
                        <span class="visually-hidden">Rechercher une extension</span>
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input id="domain-extension-filter" type="search" placeholder="Rechercher une extension (.com, .dz...)">
                    </label>
                    <div class="domain-catalog__filters" role="group" aria-label="Filtrer les extensions par catégorie">
                        <?php foreach ($domainFilters as $filter => $label): ?>
                        <button type="button" class="domain-filter<?php echo $filter === 'all' ? ' is-active' : ''; ?>" data-domain-filter="<?php echo htmlspecialchars($filter, ENT_QUOTES, 'UTF-8'); ?>" aria-pressed="<?php echo $filter === 'all' ? 'true' : 'false'; ?>">
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="domain-catalog__grid" id="domain-extension-list">
                    <?php foreach ($domainExtensions as $extension): ?>
                    <article class="domain-extension-card" data-domain-category="<?php echo htmlspecialchars($extension['category'], ENT_QUOTES, 'UTF-8'); ?>" data-domain-name="<?php echo htmlspecialchars(strtolower($extension['extension']), ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="domain-extension-card__name"><?php echo htmlspecialchars($extension['extension'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="domain-extension-card__price">
                            <?php echo $formatDomainPrice($extension['price']); ?> <small>DA HT <span>/ an</span></small>
                        </span>
                    </article>
                    <?php endforeach; ?>
                </div>
                <p class="domain-catalog__empty" id="domain-extension-empty" hidden>Aucune extension ne correspond à votre recherche.</p>
                <p class="domain-catalog__note" role="note">
                    Tarifs indicatifs repris de la liste communiquée, à confirmer avant commande. Les extensions en .dz peuvent être soumises à des conditions d’éligibilité ; les tarifs affichés à 0 DA sont à vérifier auprès de notre équipe.
                </p>
            </div>
        </section>

        <section class="domain-services hosting-section" aria-labelledby="domain-services-title">
            <div class="hosting-container">
                <div class="hosting-section__heading">
                    <span class="hosting-kicker">Plus que la recherche</span>
                    <h2 id="domain-services-title">Tous nos services de domaine</h2>
                    <p>Notre équipe peut également vous accompagner dans la gestion et le transfert de votre domaine.</p>
                </div>
                <div class="domain-service-grid">
                    <article id="domain-bulk-search" class="domain-service-card">
                        <span class="domain-service-card__icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
                        <h3>Recherche de domaines par lot</h3>
                        <p>Envoyez plusieurs noms ou extensions dans une seule demande.</p>
                        <a href="<?php echo BASE_URL; ?>contact">Faire une demande groupée <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article id="domain-transfer" class="domain-service-card">
                        <span class="domain-service-card__icon"><i class="fa-solid fa-right-left" aria-hidden="true"></i></span>
                        <h3>Transfert de domaine</h3>
                        <p>Transférez votre domaine et demandez son renouvellement pour un an.</p>
                        <a href="<?php echo BASE_URL; ?>contact">Demander un transfert <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                    <article class="domain-service-card">
                        <span class="domain-service-card__icon"><i class="fa-solid fa-magnifying-glass-chart" aria-hidden="true"></i></span>
                        <h3>Whois check</h3>
                        <p>Consultez les informations publiques associées à un nom de domaine.</p>
                        <a href="https://whois.octenium.com/" target="_blank" rel="noopener noreferrer">Ouvrir le Whois <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                    </article>
                    <article id="domain-management" class="domain-service-card">
                        <span class="domain-service-card__icon"><i class="fa-solid fa-sliders" aria-hidden="true"></i></span>
                        <h3>Gestion de domaine</h3>
                        <p>Accompagnement pour le DNS, les serveurs de noms et les enregistrements courants.</p>
                        <a href="<?php echo BASE_URL; ?>contact">Parler à un conseiller <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                </div>
            </div>
        </section>
    </main>
    <?php theme_partial('footer'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const search = document.getElementById('domain-extension-filter');
            const cards = Array.from(document.querySelectorAll('.domain-extension-card'));
            const filters = Array.from(document.querySelectorAll('[data-domain-filter]'));
            const emptyState = document.getElementById('domain-extension-empty');
            const domainForm = document.querySelector('.domain-search-form');
            const domainInput = document.getElementById('hosting-domain-query');
            const resultBox = document.getElementById('domain-search-result');
            let activeFilter = 'all';

            const renderResult = (payload) => {
                if (!resultBox) return;

                if (!payload || !payload.success) {
                    resultBox.className = 'domain-search-result is-error';
                    resultBox.replaceChildren();
                    const errorLabel = document.createElement('strong');
                    errorLabel.textContent = 'Erreur : ';
                    resultBox.append(errorLabel, document.createTextNode(payload && payload.error ? payload.error : 'Impossible de vérifier le domaine.'));
                    return;
                }

                const data = payload.data || {};
                const status = (data.status || '').toLowerCase();
                const domain = data.domain || domainInput.value.trim();
                const stateLabel = status === 'available' ? 'Disponible' : status === 'active' ? 'Déjà enregistré' : 'Statut inconnu';
                const stateClass = status === 'available' ? 'is-success' : status === 'active' ? 'is-danger' : 'is-neutral';

                resultBox.className = 'domain-search-result ' + stateClass;
                resultBox.replaceChildren();

                const header = document.createElement('div');
                header.className = 'domain-search-result__header';
                const domainLabel = document.createElement('strong');
                domainLabel.textContent = domain;
                const statusLabel = document.createElement('span');
                statusLabel.textContent = stateLabel;
                header.append(domainLabel, statusLabel);
                resultBox.appendChild(header);

                const details = document.createElement('div');
                details.className = 'domain-search-result__details';
                const addDetail = (label, value) => {
                    if (!value) return;
                    const row = document.createElement('div');
                    const rowLabel = document.createElement('strong');
                    rowLabel.textContent = label + ' :';
                    row.append(rowLabel, document.createTextNode(' ' + value));
                    details.appendChild(row);
                };

                addDetail('Registrar', data.registrar);
                addDetail('Création', data.creation_date);
                addDetail('Expiration', data.expiration_date);
                addDetail('Serveurs DNS', Array.isArray(data.nameservers) ? data.nameservers.join(', ') : '');
                resultBox.appendChild(details);
            };

            const updateCatalog = () => {
                const query = search.value.trim().toLowerCase();
                let visibleCount = 0;

                cards.forEach((card) => {
                    const matchesCategory = activeFilter === 'all' || card.dataset.domainCategory === activeFilter;
                    const matchesQuery = card.dataset.domainName.includes(query);
                    const isVisible = matchesCategory && matchesQuery;
                    card.hidden = !isVisible;
                    if (isVisible) visibleCount += 1;
                });

                if (emptyState) emptyState.hidden = visibleCount > 0;
            };

            if (domainForm && domainInput) {
                domainForm.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const domain = domainInput.value.trim();
                    if (!domain) {
                        renderResult({ success: false, error: 'Veuillez saisir un nom de domaine.' });
                        return;
                    }

                    resultBox.className = 'domain-search-result is-loading';
                    resultBox.innerHTML = '<span>Recherche du domaine en cours…</span>';

                    try {
                        const response = await fetch('<?php echo BASE_URL; ?>includes/domain_search.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                            body: new URLSearchParams({ domain })
                        });

                        const payload = await response.json();
                        renderResult(payload);
                    } catch (error) {
                        renderResult({ success: false, error: 'Erreur réseau lors de la recherche.' });
                    }
                });
            }

            filters.forEach((button) => {
                button.addEventListener('click', () => {
                    activeFilter = button.dataset.domainFilter;
                    filters.forEach((filterButton) => {
                        const isActive = filterButton === button;
                        filterButton.classList.toggle('is-active', isActive);
                        filterButton.setAttribute('aria-pressed', String(isActive));
                    });
                    updateCatalog();
                });
            });

            if (search) search.addEventListener('input', updateCatalog);
        })();
    </script>
    <?php if ($isAdmin): ?>
    <script src="<?php echo BASE_URL; ?>assets/js/inline-edit.js"></script>
    <?php endif; ?>
</body>
</html>
