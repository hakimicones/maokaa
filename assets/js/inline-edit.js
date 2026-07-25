// assets/js/inline-edit.js — Édition inline frontend (admin uniquement)
// - Champs texte (h1-h6, p, blockquote, cite, code, figcaption) : contenteditable individuel
// - Blocs dynamiques (.vep-block-wrapper) : bouton lien vers l'admin
// - Images : popup sélecteur depuis assets/images/

(function () {
    'use strict';

    var csrfToken = getMeta('csrf-token');
    var pageSlug  = getMeta('page-slug');
    var baseUrl   = getMeta('base-url') || '/';

    var TEXT_SELECTORS = 'h1, h2, h3, h4, h5, h6, p, blockquote, cite, code, figcaption';

    // ── Barre d'outils admin ─────────────────────────────────────────────
    var toolbar = document.createElement('div');
    toolbar.id = 'ie-toolbar';
    toolbar.innerHTML =
        '<i class="fas fa-pencil-alt ie-toolbar-icon"></i>' +
        '<span id="ie-toolbar-hint">Mode <strong>édition inline</strong> — cliquez sur un texte pour le modifier</span>' +
        '<button id="ie-new-page-btn" class="ie-toolbar-btn ie-toolbar-btn-success">' +
            '<i class="fas fa-plus"></i> Page' +
        '</button>' +
        '<a href="' + baseUrl + 'admin/" id="ie-toolbar-admin">' +
            'Tableau de bord <i class="fas fa-external-link-alt"></i>' +
        '</a>';
    document.body.prepend(toolbar);

    // ── Modal "Nouvelle page" ──────────────────────────────────────────────
    var pageModal = document.createElement('div');
    pageModal.id = 'ie-page-modal';
    pageModal.innerHTML =
        '<div class="ie-page-modal-overlay"></div>' +
        '<div class="ie-page-modal-dialog">' +
            '<div class="ie-page-modal-header">' +
                '<h5><i class="fas fa-file-circle-plus"></i> Créer une nouvelle page</h5>' +
                '<button type="button" class="ie-page-modal-close" id="ie-page-modal-close">&times;</button>' +
            '</div>' +
            '<form id="ie-page-form" autocomplete="off">' +
                '<div class="ie-page-modal-body">' +
                    '<div class="ie-form-row">' +
                        '<label for="ie-page-title">Titre *</label>' +
                        '<input type="text" id="ie-page-title" name="title" required maxlength="255" placeholder="Ex: Contactez-nous">' +
                    '</div>' +
                    '<div class="ie-form-row">' +
                        '<label for="ie-page-slug">Slug</label>' +
                        '<div class="ie-slug-input-wrap">' +
                            '<span class="ie-slug-prefix">' + baseUrl + '</span>' +
                            '<input type="text" id="ie-page-slug" name="slug" maxlength="191" placeholder="auto-généré du titre">' +
                        '</div>' +
                    '</div>' +
                    '<div class="ie-form-row ie-form-row-half">' +
                        '<div>' +
                            '<label for="ie-page-template">Template</label>' +
                            '<select id="ie-page-template" name="template"></select>' +
                        '</div>' +
                        '<div>' +
                            '<label for="ie-page-status">Statut</label>' +
                            '<select id="ie-page-status" name="status">' +
                                '<option value="draft">Brouillon</option>' +
                                '<option value="published">Publié</option>' +
                            '</select>' +
                        '</div>' +
                    '</div>' +
                    '<div class="ie-form-divider"></div>' +
                    '<div class="ie-form-row">' +
                        '<label class="ie-checkbox-label">' +
                            '<input type="checkbox" id="ie-page-add-menu" name="add_to_menu" value="1"> Ajouter au menu' +
                        '</label>' +
                    '</div>' +
                    '<div id="ie-menu-options" class="ie-menu-options" style="display:none;">' +
                        '<div class="ie-form-row ie-form-row-half">' +
                            '<div>' +
                                '<label for="ie-page-menu-name">Menu</label>' +
                                '<select id="ie-page-menu-name" name="menu_name"></select>' +
                            '</div>' +
                            '<div>' +
                                '<label for="ie-page-menu-parent">Sous-menu de</label>' +
                                '<select id="ie-page-menu-parent" name="menu_parent_id">' +
                                    '<option value="">Aucun (niveau racine)</option>' +
                                '</select>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="ie-page-modal-footer">' +
                    '<button type="button" class="ie-btn ie-btn-cancel" id="ie-page-modal-cancel">Annuler</button>' +
                    '<button type="submit" class="ie-btn ie-btn-primary" id="ie-page-submit">' +
                        '<i class="fas fa-check"></i> Créer la page' +
                    '</button>' +
                '</div>' +
            '</form>' +
        '</div>';
    document.body.appendChild(pageModal);

    // ── Variables DOM ──────────────────────────────────────────────────────
    var modal         = document.getElementById('ie-page-modal');
    var modalOverlay  = modal.querySelector('.ie-page-modal-overlay');
    var form          = document.getElementById('ie-page-form');
    var titleInput    = document.getElementById('ie-page-title');
    var slugInput     = document.getElementById('ie-page-slug');
    var templateSelect = document.getElementById('ie-page-template');
    var statusSelect  = document.getElementById('ie-page-status');
    var addMenuCheck  = document.getElementById('ie-page-add-menu');
    var menuOptions   = document.getElementById('ie-menu-options');
    var menuNameSel   = document.getElementById('ie-page-menu-name');
    var menuParentSel = document.getElementById('ie-page-menu-parent');
    var submitBtn     = document.getElementById('ie-page-submit');

    // ── Templates disponibles (chargés en AJAX) ────────────────────────────
    var templatesLoaded = false;
    var menusLoaded = false;
    var slugManuallyEdited = false;

    function slugify(str) {
        return str.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\-]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    function openModal() {
        modal.classList.add('ie-page-modal-open');
        document.body.style.overflow = 'hidden';
        titleInput.focus();
        if (!templatesLoaded) loadTemplates();
        if (!menusLoaded) loadMenus();
    }

    function closeModal() {
        modal.classList.remove('ie-page-modal-open');
        document.body.style.overflow = '';
        form.reset();
        slugManuallyEdited = false;
        menuOptions.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Créer la page';
    }

    function loadTemplates() {
        fetch(baseUrl + 'themes/default/theme.json', { credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(json) {
                var templates = json.templates || ['default'];
                templateSelect.innerHTML = '';
                templates.forEach(function(name) {
                    var o = document.createElement('option');
                    o.value = name;
                    o.textContent = name;
                    templateSelect.appendChild(o);
                });
                templatesLoaded = true;
            })
            .catch(function() {
                templateSelect.innerHTML = '<option value="default">default</option>';
                templatesLoaded = true;
            });
    }

    function loadMenus() {
        fetch(baseUrl + 'includes/api_menu_items.php?menu=main', { credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(items) {
                menuNameSel.innerHTML = '<option value="main">Menu Principal</option>';
                menuParentSel.innerHTML = '<option value="">Aucun (niveau racine)</option>';
                items.forEach(function(item) {
                    var o = document.createElement('option');
                    o.value = item.id;
                    o.textContent = item.title;
                    menuParentSel.appendChild(o);
                });
                menusLoaded = true;
            })
            .catch(function() {
                menusLoaded = true;
            });
    }

    // ── Événements ─────────────────────────────────────────────────────────
    document.getElementById('ie-new-page-btn').addEventListener('click', function(e) {
        e.preventDefault();
        openModal();
    });

    document.getElementById('ie-page-modal-close').addEventListener('click', closeModal);
    document.getElementById('ie-page-modal-cancel').addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', closeModal);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('ie-page-modal-open')) {
            closeModal();
        }
    });

    titleInput.addEventListener('input', function() {
        if (!slugManuallyEdited) {
            slugInput.value = slugify(titleInput.value);
        }
    });

    slugInput.addEventListener('input', function() {
        slugManuallyEdited = slugInput.value !== '';
    });

    addMenuCheck.addEventListener('change', function() {
        menuOptions.style.display = addMenuCheck.checked ? 'block' : 'none';
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var title = titleInput.value.trim();
        if (!title) {
            titleInput.focus();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...';

        var payload = {
            csrf_token: csrfToken,
            title: title,
            slug: slugInput.value.trim(),
            template: templateSelect.value,
            status: statusSelect.value,
            add_to_menu: addMenuCheck.checked ? 1 : 0,
            menu_name: menuNameSel.value,
            menu_parent_id: menuParentSel.value
        };

        fetch(baseUrl + 'includes/api_page_create.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function(r) {
            var ct = r.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                throw new Error('Réponse non JSON (status ' + r.status + ')');
            }
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                window.location.href = data.edit_url;
            } else {
                alert(data.message || 'Erreur lors de la création');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Créer la page';
            }
        })
        .catch(function(err) {
            console.error('Erreur création page:', err);
            alert('Erreur lors de la création. Réessayez.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Créer la page';
        });
    });

    // ── Champs simples : title, subtitle ─────────────────────────────────
    document.querySelectorAll('[data-inline-field="title"], [data-inline-field="subtitle"]').forEach(function (el) {
        initTextField(el, el.getAttribute('data-inline-field'));
    });

    // ── Champ body ────────────────────────────────────────────────────────
    var bodyEl = document.querySelector('[data-inline-field="body"]');
    if (bodyEl) {
        initBodyField(bodyEl);
        initAiButton(bodyEl);
    }

    // ── Toolbar WYSIWYG flottante (apparaît au focus d'un élément body) ──
    var wysiwygToolbar = null;
    var wysiwygTarget  = null;
    var wysiwygTimer   = null;

    function getWysiwygToolbar() {
        if (wysiwygToolbar) return wysiwygToolbar;
        wysiwygToolbar = document.createElement('div');
        wysiwygToolbar.id = 'ie-wysiwyg-toolbar';
        wysiwygToolbar.innerHTML =
            '<button type="button" data-cmd="bold" title="Gras (Ctrl+B)"><i class="fas fa-bold"></i></button>' +
            '<button type="button" data-cmd="italic" title="Italique (Ctrl+I)"><i class="fas fa-italic"></i></button>' +
            '<button type="button" data-cmd="underline" title="Souligné (Ctrl+U)"><i class="fas fa-underline"></i></button>' +
            '<span class="ie-wysiwyg-sep"></span>' +
            '<button type="button" data-cmd="formatBlock" data-arg="h2" title="Titre H2"><i class="fas fa-heading"></i> H2</button>' +
            '<button type="button" data-cmd="formatBlock" data-arg="h3" title="Titre H3"><i class="fas fa-heading"></i> H3</button>' +
            '<button type="button" data-cmd="formatBlock" data-arg="p" title="Paragraphe"><i class="fas fa-paragraph"></i></button>' +
            '<span class="ie-wysiwyg-sep"></span>' +
            '<button type="button" data-cmd="createLink" title="Insérer un lien"><i class="fas fa-link"></i></button>' +
            '<button type="button" data-cmd="unlink" title="Supprimer le lien"><i class="fas fa-unlink"></i></button>' +
            '<span class="ie-wysiwyg-sep"></span>' +
            '<button type="button" data-cmd="ai-rewrite" title="Assistant IA"><i class="fas fa-magic"></i> IA</button>';

        wysiwygToolbar.addEventListener('mousedown', function (e) {
            e.preventDefault();
            var btn = e.target.closest('button');
            if (!btn) return;
            var cmd = btn.getAttribute('data-cmd');
            var arg = btn.getAttribute('data-arg') || null;

            if (cmd === 'ai-rewrite') {
                openFieldAiModal(wysiwygTarget);
                return;
            }

            if (cmd === 'createLink') {
                openLinkModal(null, wysiwygTarget);
                return;
            } else {
                document.execCommand(cmd, false, arg);
            }
            if (wysiwygTarget) wysiwygTarget.focus();
        });

        document.body.appendChild(wysiwygToolbar);
        return wysiwygToolbar;
    }

    function showWysiwygToolbar(el) {
        cancelHideWysiwyg();
        var tb = getWysiwygToolbar();
        wysiwygTarget = el;
        var rect = el.getBoundingClientRect();
        var scrollY = window.scrollY || window.pageYOffset;
        var topPos = rect.top + scrollY - tb.offsetHeight - 8;
        if (rect.top < tb.offsetHeight + 12) {
            topPos = rect.bottom + scrollY + 8;
        }
        tb.style.top = topPos + 'px';
        tb.style.left = Math.max(8, rect.left + (rect.width / 2) - 100) + 'px';
        tb.classList.add('ie-wysiwyg-visible');
    }

    function hideWysiwygToolbar() {
        if (wysiwygTimer) clearTimeout(wysiwygTimer);
        wysiwygTimer = setTimeout(function () {
            if (wysiwygToolbar) wysiwygToolbar.classList.remove('ie-wysiwyg-visible');
            wysiwygTarget = null;
        }, 200);
    }

    function cancelHideWysiwyg() {
        if (wysiwygTimer) {
            clearTimeout(wysiwygTimer);
            wysiwygTimer = null;
        }
    }

    // ── Helper : texte sélectionné ─────────────────────────────────────────
    function getSelectedText() {
        var sel = window.getSelection();
        return sel ? sel.toString().trim() : '';
    }

    // ── Modal lien/bouton (création ou édition) ────────────────────────────
    function openLinkModal(anchorEl, contextEl) {
        var existing = document.getElementById('ie-link-modal');
        if (existing) existing.remove();

        var isEdit = anchorEl !== null;
        var currentUrl  = isEdit ? (anchorEl.getAttribute('href') || '') : '';
        var currentText = isEdit ? anchorEl.textContent : getSelectedText();
        var currentTarget = isEdit ? (anchorEl.getAttribute('target') || '') : '';

        var modal = document.createElement('div');
        modal.id = 'ie-link-modal';
        modal.innerHTML =
            '<div class="ie-link-dialog">' +
                '<div class="ie-link-header">' +
                    '<span><i class="fas fa-link"></i> ' + (isEdit ? 'Modifier le lien' : 'Insérer un lien') + '</span>' +
                    '<button class="ie-link-close" type="button" aria-label="Fermer">&times;</button>' +
                '</div>' +
                '<div class="ie-link-body">' +
                    '<label>Texte à afficher</label>' +
                    '<input type="text" id="ie-link-text" class="ie-link-input" value="' + escapeAttr(currentText) + '">' +
                    '<label>URL</label>' +
                    '<input type="url" id="ie-link-url" class="ie-link-input" value="' + escapeAttr(currentUrl) + '" placeholder="https://...">' +
                    '<label class="ie-link-checkbox">' +
                        '<input type="checkbox" id="ie-link-target"' + (currentTarget === '_blank' ? ' checked' : '') + '> ' +
                        'Ouvrir dans un nouvel onglet' +
                    '</label>' +
                '</div>' +
                '<div class="ie-link-footer">' +
                    '<button class="ie-link-action ie-link-action-secondary ie-link-cancel" type="button">Annuler</button>' +
                    '<button class="ie-link-action ie-link-action-primary ie-link-apply" type="button">' + (isEdit ? 'Appliquer' : 'Insérer') + '</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(modal);

        var textInput   = modal.querySelector('#ie-link-text');
        var urlInput    = modal.querySelector('#ie-link-url');
        var targetInput = modal.querySelector('#ie-link-target');
        var closeBtn    = modal.querySelector('.ie-link-close');
        var cancelBtn   = modal.querySelector('.ie-link-cancel');
        var applyBtn    = modal.querySelector('.ie-link-apply');

        // Remonter jusqu'au conteneur body pour la sauvegarde
        var bodyContainer = contextEl
            ? (contextEl.getAttribute && contextEl.getAttribute('data-inline-field') === 'body'
                ? contextEl
                : (typeof contextEl.closest === 'function' ? contextEl.closest('[data-inline-field="body"]') : null))
            : null;

        function close() { modal.remove(); }

        function saveAfterLinkEdit() {
            if (bodyContainer && typeof serializeAndSaveBody === 'function') {
                serializeAndSaveBody(bodyContainer, function (ok, data) {
                    if (ok) {
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message || 'Erreur lors de la sauvegarde', 'error');
                    }
                });
            }
        }

        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);
        modal.addEventListener('click', function (e) { if (e.target === modal) close(); });

        textInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') applyBtn.click(); });
        urlInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') applyBtn.click(); });

        applyBtn.addEventListener('click', function () {
            var text   = textInput.value.trim();
            var url    = urlInput.value.trim();
            var target = targetInput.checked ? '_blank' : '';

            if (!text || !url) {
                urlInput.focus();
                return;
            }

            if (isEdit) {
                anchorEl.textContent = text;
                anchorEl.setAttribute('href', url);
                if (target) {
                    anchorEl.setAttribute('target', target);
                    anchorEl.setAttribute('rel', 'noopener');
                } else {
                    anchorEl.removeAttribute('target');
                    anchorEl.removeAttribute('rel');
                }
            } else {
                document.execCommand('insertHTML', false,
                    '<a href="' + escapeAttr(url) + '"' +
                    (target ? ' target="_blank" rel="noopener"' : '') + '>' +
                    escapeHtml(text) + '</a>');
            }
            close();
            saveAfterLinkEdit();
        });

        urlInput.focus();
        if (currentUrl) urlInput.select();
    }

    // ── Champs texte simples (title, subtitle) : contenteditable ─────────
    function initTextField(el, field) {
        el.setAttribute('contenteditable', 'true');
        el.setAttribute('spellcheck', 'true');
        el.classList.add('ie-field');

        var originalHTML = el.innerHTML;

        el.addEventListener('focus', function () { this.classList.add('ie-editing'); });

        el.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.blur();
            }
            if (e.key === 'Escape') {
                this.innerHTML = originalHTML;
                this.blur();
            }
        });

        el.addEventListener('blur', function () {
            var self = this;
            self.classList.remove('ie-editing');
            var current = self.innerHTML;
            if (current === originalHTML) return;
            pulse(self, 'ie-saving');
            saveField(field, current, function (ok, data) {
                self.classList.remove('ie-saving');
                if (ok) {
                    originalHTML = current;
                    pulse(self, 'ie-success');
                    showToast(data.message, 'success');
                } else {
                    self.innerHTML = originalHTML;
                    pulse(self, 'ie-error');
                    showToast(data.message || 'Erreur inconnue', 'error');
                }
            });
        });
    }

    // ── Corps de page : édition granulaire ───────────────────────────────
    function initBodyField(bodyEl) {
        bodyEl.classList.add('ie-body');

        // 1. Blocs dynamiques → bouton admin
        bodyEl.querySelectorAll('.vep-block-wrapper').forEach(function (block) {
            decorateVepBlock(block);
        });

        // 2. Images hors blocs dynamiques → sélecteur d'image
        bodyEl.querySelectorAll('img').forEach(function (img) {
            if (!img.closest('.vep-block-wrapper')) {
                decorateImage(img, bodyEl);
            }
        });

        // 3. Éléments texte hors blocs dynamiques → contenteditable
        bodyEl.querySelectorAll(TEXT_SELECTORS).forEach(function (el) {
            if (el.closest('.vep-block-wrapper')) return;
            initInlineText(el, bodyEl);
        });

        // 4. Empêcher la navigation sur les liens hors contenteditable (standalone)
        //    pour que le double-clic puisse ouvrir le modal d'édition
        bodyEl.addEventListener('click', function (e) {
            var anchor = e.target.closest('a');
            if (anchor && !anchor.closest('[contenteditable]') && !anchor.closest('.ie-vep-block')) {
                e.preventDefault();
            }
        });

        // 5. Double-clic sur un lien → popup d'édition
        bodyEl.addEventListener('dblclick', function (e) {
            var anchor = e.target.closest('a');
            if (anchor && !anchor.closest('.ie-vep-block')) {
                e.preventDefault();
                openLinkModal(anchor, bodyEl);
            }
        });
    }

    // ── Décoration bloc dynamique ─────────────────────────────────────────
    function decorateVepBlock(block) {
        block.classList.add('ie-vep-block');
        var adminUrl = baseUrl + (block.getAttribute('data-vep-admin-url') || 'admin/dashboard.php');
        var sep = adminUrl.indexOf('?') === -1 ? '?' : '&';
        adminUrl += sep + 'return_url=' + encodeURIComponent(window.location.href);

        var btn = document.createElement('a');
        btn.className = 'ie-vep-btn';
        btn.href = adminUrl;
        btn.target = '_blank';
        btn.rel = 'noopener';
        btn.innerHTML = '<i class="fas fa-cogs"></i> Gérer dans l\'admin';
        block.appendChild(btn);
    }

    // ── Décoration image ──────────────────────────────────────────────────
    function decorateImage(img, bodyEl) {
        img.classList.add('ie-img');

        var wrap = document.createElement('span');
        wrap.className = 'ie-img-wrap';
        img.parentNode.insertBefore(wrap, img);
        wrap.appendChild(img);

        var overlay = document.createElement('span');
        overlay.className = 'ie-img-overlay';
        overlay.innerHTML = '<i class="fas fa-camera"></i>';
        wrap.appendChild(overlay);

        overlay.addEventListener('click', function (e) {
            e.stopPropagation();
            openImagePicker(img, bodyEl);
        });

        img.addEventListener('click', function (e) {
            if (img.closest('.ie-editing')) return;
            e.preventDefault();
            e.stopPropagation();
            openImagePicker(img, bodyEl);
        });
    }

    // ── Édition texte inline ──────────────────────────────────────────────
    function initInlineText(el, bodyEl) {
        el.setAttribute('contenteditable', 'true');
        el.setAttribute('spellcheck', 'true');
        el.classList.add('ie-field');

        var originalHTML = el.innerHTML;

        el.addEventListener('focus', function () {
            this.classList.add('ie-editing');
            showWysiwygToolbar(this);
        });

        el.addEventListener('mousedown', function () {
            cancelHideWysiwyg();
        });

        el.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.blur();
            }
            if (e.key === 'Escape') {
                this.innerHTML = originalHTML;
                this.blur();
            }
        });

        el.addEventListener('blur', function () {
            var self = this;
            self.classList.remove('ie-editing');
            hideWysiwygToolbar();
            var current = self.innerHTML;
            if (current === originalHTML) return;

            pulse(self, 'ie-saving');
            serializeAndSaveBody(bodyEl, function (ok, data) {
                self.classList.remove('ie-saving');
                if (ok) {
                    originalHTML = current;
                    pulse(self, 'ie-success');
                    showToast(data.message, 'success');
                } else {
                    self.innerHTML = originalHTML;
                    pulse(self, 'ie-error');
                    showToast(data.message || 'Erreur inconnue', 'error');
                }
            });
        });
    }

    // ── Sélecteur d'image ─────────────────────────────────────────────────
    function openImagePicker(img, bodyEl) {
        fetch(baseUrl + 'includes/list_images.php')
            .then(function (r) { return r.json(); })
            .then(function (data) { showImagePickerModal(img, data.images || [], bodyEl); })
            .catch(function () { showToast('Impossible de charger les images', 'error'); });
    }

    function showImagePickerModal(img, images, bodyEl) {
        var existing = document.getElementById('ie-img-picker');
        if (existing) existing.remove();

        var grid = images.map(function (src) {
            return '<button class="ie-img-picker-item" data-src="' + escapeAttr(src) + '" type="button">' +
                   '<img src="' + escapeAttr(src) + '" alt="" loading="lazy"></button>';
        }).join('');

        var picker = document.createElement('div');
        picker.id = 'ie-img-picker';
        picker.innerHTML =
            '<div class="ie-img-picker-dialog">' +
                '<div class="ie-img-picker-header">' +
                    '<span><i class="fas fa-images"></i> Choisir une image</span>' +
                    '<button class="ie-img-picker-close" type="button" aria-label="Fermer">&times;</button>' +
                '</div>' +
                '<div class="ie-img-picker-grid">' +
                    (grid || '<p class="ie-img-picker-empty">Aucune image dans assets/images/</p>') +
                '</div>' +
            '</div>';

        document.body.appendChild(picker);

        picker.querySelector('.ie-img-picker-close').addEventListener('click', function () { picker.remove(); });
        picker.addEventListener('click', function (e) { if (e.target === picker) picker.remove(); });

        picker.querySelectorAll('.ie-img-picker-item').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var src = this.getAttribute('data-src');
                img.src = src;
                picker.remove();
                serializeAndSaveBody(bodyEl, function (ok, data) {
                    if (ok) {
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message || 'Erreur inconnue', 'error');
                    }
                });
            });
        });
    }

    // ── Sérialisation du body (shortcodes reconstruits) ───────────────────
    function getCleanBodyHtml(bodyEl) {
        var clone = bodyEl.cloneNode(true);

        // Supprimer les UI d'édition injectées
        clone.querySelectorAll('.ie-vep-btn, .ie-img-overlay').forEach(function (n) { n.remove(); });

        // Défaire le wrapper des images, conserver l'img avec son src mis à jour
        clone.querySelectorAll('.ie-img-wrap').forEach(function (wrap) {
            var imgEl = wrap.querySelector('img');
            if (imgEl) wrap.replaceWith(imgEl);
        });

        // Nettoyer les attributs d'édition
        clone.querySelectorAll('[contenteditable]').forEach(function (n) { n.removeAttribute('contenteditable'); });
        clone.querySelectorAll('[spellcheck]').forEach(function (n) { n.removeAttribute('spellcheck'); });

        // Nettoyer les classes ie-* ajoutées
        var ieClasses = ['ie-field', 'ie-editing', 'ie-saving', 'ie-success', 'ie-error', 'ie-img', 'ie-vep-block'];
        clone.querySelectorAll('.' + ieClasses.join(', .')).forEach(function (n) {
            ieClasses.forEach(function (c) { n.classList.remove(c); });
        });
        clone.classList.remove('ie-body');

        // Remplacer chaque bloc dynamique par son shortcode original
        clone.querySelectorAll('.vep-block-wrapper').forEach(function (block) {
            var sc = block.getAttribute('data-vep-shortcode');
            if (sc) block.replaceWith(document.createTextNode(sc));
        });

        return clone.innerHTML;
    }

    function serializeAndSaveBody(bodyEl, callback) {
        saveField('body', getCleanBodyHtml(bodyEl), callback);
    }

    // ── Assistant IA (régénération du HTML du body) ───────────────────────
    function initAiButton(bodyEl) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'ie-ai-btn';
        btn.innerHTML = '<i class="fas fa-magic"></i> Assistant IA';

        var adminLink = document.getElementById('ie-toolbar-admin');
        if (adminLink) {
            toolbar.insertBefore(btn, adminLink);
        } else {
            toolbar.appendChild(btn);
        }

        btn.addEventListener('click', function () {
            openAiModal({
                getHtml: function () { return getCleanBodyHtml(bodyEl); },
                onApply: function (html, done) {
                    saveField('body', html, function (ok, data) {
                        if (ok) {
                            done(true);
                            window.location.reload();
                        } else {
                            done(false, data.message);
                        }
                    });
                }
            });
        });
    }

    // ── Assistant IA (régénération d'un champ inline isolé) ────────────────
    // Utilisé par le bouton "IA" de la barre WYSIWYG flottante, pour les
    // paragraphes/titres du corps de page et pour les champs produit
    // (nom, description, description_complete, caracteristiques_techniques).
    function openFieldAiModal(target) {
        if (!target) return;

        openAiModal({
            getHtml: function () { return target.innerHTML; },
            onApply: function (html, done) {
                target.innerHTML = unwrapIfSameTag(html, target.tagName);
                pulse(target, 'ie-saving');

                var productId = target.getAttribute('data-product-id');
                var save = productId
                    ? function (cb) {
                        saveProductField(parseInt(productId, 10), target.getAttribute('data-inline-field'), target.innerHTML, cb);
                    }
                    : function (cb) { serializeAndSaveBody(bodyEl, cb); };

                save(function (ok, data) {
                    target.classList.remove('ie-saving');
                    if (ok) {
                        pulse(target, 'ie-success');
                        showToast(data.message, 'success');
                        done(true);
                    } else {
                        pulse(target, 'ie-error');
                        showToast(data.message || 'Erreur inconnue', 'error');
                        done(false, data.message);
                    }
                });
            }
        });
    }

    // Si le HTML retourné par l'IA est un unique élément racine de même type
    // que le champ édité (ex: <h2>...</h2> pour un titre h2), on ne garde que
    // son contenu pour éviter une imbrication (<h2><h2>...</h2></h2>).
    function unwrapIfSameTag(html, tagName) {
        var tmp = document.createElement('div');
        tmp.innerHTML = html.trim();
        if (tmp.childNodes.length === 1 &&
            tmp.children.length === 1 &&
            tmp.children[0].tagName === tagName.toUpperCase()) {
            return tmp.children[0].innerHTML;
        }
        return html;
    }

    function openAiModal(options) {
        var existing = document.getElementById('ie-ai-modal');
        if (existing) existing.remove();

        var modal = document.createElement('div');
        modal.id = 'ie-ai-modal';
        modal.innerHTML =
            '<div class="ie-ai-dialog">' +
                '<div class="ie-ai-header">' +
                    '<span><i class="fas fa-magic"></i> Assistant IA</span>' +
                    '<button class="ie-ai-close" type="button" aria-label="Fermer">&times;</button>' +
                '</div>' +
                '<div class="ie-ai-body">' +
                    '<label for="ie-ai-instruction">Instruction</label>' +
                    '<textarea id="ie-ai-instruction" rows="3" placeholder="Ex. : Réécris le texte d\'introduction avec un ton plus commercial"></textarea>' +
                    '<div class="ie-ai-status"></div>' +
                    '<div class="ie-ai-preview" style="display:none;"></div>' +
                '</div>' +
                '<div class="ie-ai-footer">' +
                    '<button class="ie-ai-action ie-ai-action-secondary ie-ai-cancel" type="button">Annuler</button>' +
                    '<button class="ie-ai-action ie-ai-action-primary ie-ai-apply" type="button" style="display:none;">Appliquer et enregistrer</button>' +
                    '<button class="ie-ai-action ie-ai-action-primary ie-ai-generate" type="button">Générer</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(modal);

        var textarea    = modal.querySelector('#ie-ai-instruction');
        var statusEl    = modal.querySelector('.ie-ai-status');
        var previewEl   = modal.querySelector('.ie-ai-preview');
        var generateBtn = modal.querySelector('.ie-ai-generate');
        var applyBtn    = modal.querySelector('.ie-ai-apply');
        var cancelBtn   = modal.querySelector('.ie-ai-cancel');
        var closeBtn    = modal.querySelector('.ie-ai-close');

        var generatedHtml = null;

        function close() { modal.remove(); }

        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);
        modal.addEventListener('click', function (e) { if (e.target === modal) close(); });

        function setStatus(message, type) {
            statusEl.className = 'ie-ai-status' + (type ? ' ie-ai-' + type : '');
            statusEl.textContent = message || '';
        }

        generateBtn.addEventListener('click', function () {
            var instruction = textarea.value.trim();
            if (!instruction) {
                setStatus('Veuillez saisir une instruction.', 'error');
                return;
            }

            generateBtn.disabled = true;
            applyBtn.disabled = true;
            previewEl.style.display = 'none';
            setStatus('Génération en cours…', 'loading');

            fetch(baseUrl + 'includes/api_ai_content.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    html: options.getHtml(),
                    instruction: instruction
                })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                generateBtn.disabled = false;
                if (data.success) {
                    generatedHtml = data.html;
                    previewEl.innerHTML = data.html;
                    previewEl.style.display = 'block';
                    applyBtn.style.display = '';
                    applyBtn.disabled = false;
                    generateBtn.textContent = 'Régénérer';
                    setStatus('Aperçu généré. Vérifiez le résultat puis appliquez.', 'success');
                } else {
                    setStatus(data.message || 'Erreur inconnue', 'error');
                }
            })
            .catch(function () {
                generateBtn.disabled = false;
                setStatus('Erreur de connexion au serveur', 'error');
            });
        });

        applyBtn.addEventListener('click', function () {
            if (!generatedHtml) return;
            applyBtn.disabled = true;
            generateBtn.disabled = true;
            setStatus('Enregistrement…', 'loading');

            options.onApply(generatedHtml, function (ok, errorMessage) {
                if (ok) {
                    close();
                } else {
                    applyBtn.disabled = false;
                    generateBtn.disabled = false;
                    setStatus(errorMessage || 'Erreur lors de l\'enregistrement', 'error');
                }
            });
        });

        textarea.focus();
    }

    // ── Envoi AJAX ────────────────────────────────────────────────────────
    function saveField(field, value, callback) {
        fetch(baseUrl + 'includes/inline_edit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: csrfToken,
                slug:       pageSlug,
                field:      field,
                value:      value
            })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) { callback(data.success, data); })
        .catch(function () { callback(false, { message: 'Erreur de connexion au serveur' }); });
    }

    // ── Feedback visuel ───────────────────────────────────────────────────
    function pulse(el, cls) {
        el.classList.remove('ie-saving', 'ie-success', 'ie-error');
        el.classList.add(cls);
        if (cls !== 'ie-saving') {
            setTimeout(function () { el.classList.remove(cls); }, 1800);
        }
    }

    function showToast(message, type) {
        var existing = document.getElementById('ie-toast');
        if (existing) existing.remove();

        var icon = type === 'success'
            ? '<i class="fas fa-check-circle"></i>'
            : '<i class="fas fa-exclamation-circle"></i>';

        var toast = document.createElement('div');
        toast.id = 'ie-toast';
        toast.className = 'ie-toast ie-toast-' + type;
        toast.innerHTML = icon + ' ' + escapeHtml(message);
        document.body.appendChild(toast);

        requestAnimationFrame(function () {
            requestAnimationFrame(function () { toast.classList.add('ie-toast-visible'); });
        });

        setTimeout(function () {
            toast.classList.remove('ie-toast-visible');
            setTimeout(function () { toast.remove(); }, 300);
        }, 3000);
    }

    // ── Utilitaires ───────────────────────────────────────────────────────
    function getMeta(name) {
        var el = document.querySelector('meta[name="' + name + '"]');
        return el ? el.getAttribute('content') : null;
    }

    function escapeHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(String(str)));
        return d.innerHTML;
    }

    function escapeAttr(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // ── Champs produit inline ─────────────────────────────────────────────
    function initProductField(el, productId) {
        var field = el.getAttribute('data-inline-field');
        if (!field) return;

        el.setAttribute('contenteditable', 'true');
        el.setAttribute('spellcheck', 'true');
        el.classList.add('ie-field');

        var originalHTML = el.innerHTML;

        el.addEventListener('focus', function () {
            this.classList.add('ie-editing');
            showWysiwygToolbar(this);
        });

        el.addEventListener('mousedown', function () { cancelHideWysiwyg(); });

        el.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); this.blur(); }
            if (e.key === 'Escape') { this.innerHTML = originalHTML; this.blur(); }
        });

        el.addEventListener('blur', function () {
            var self = this;
            self.classList.remove('ie-editing');
            hideWysiwygToolbar();
            var current = self.innerHTML;
            if (current === originalHTML) return;
            pulse(self, 'ie-saving');
            saveProductField(productId, field, current, function (ok, data) {
                self.classList.remove('ie-saving');
                if (ok) {
                    originalHTML = current;
                    pulse(self, 'ie-success');
                    showToast(data.message, 'success');
                } else {
                    self.innerHTML = originalHTML;
                    pulse(self, 'ie-error');
                    showToast(data.message || 'Erreur', 'error');
                }
            });
        });
    }

    function saveProductField(productId, field, value, callback) {
        fetch(baseUrl + 'includes/inline_edit_product.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: csrfToken,
                product_id: productId,
                field:      field,
                value:      value
            })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) { callback(data.success, data); })
        .catch(function () { callback(false, { message: 'Erreur de connexion' }); });
    }

    // ── Image produit inline ──────────────────────────────────────────────
    function initProductImage(img, productId) {
        img.classList.add('ie-img');

        var wrap = document.createElement('span');
        wrap.className = 'ie-img-wrap';
        img.parentNode.insertBefore(wrap, img);
        wrap.appendChild(img);

        var overlay = document.createElement('span');
        overlay.className = 'ie-img-overlay';
        overlay.innerHTML = '<i class="fas fa-camera"></i>';
        wrap.appendChild(overlay);

        overlay.addEventListener('click', function (e) {
            e.stopPropagation();
            openProductImagePicker(img, productId);
        });

        img.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            openProductImagePicker(img, productId);
        });
    }

    function openProductImagePicker(img, productId) {
        fetch(baseUrl + 'includes/list_images.php')
            .then(function (r) { return r.json(); })
            .then(function (data) { showProductImagePickerModal(img, data.images || [], productId); })
            .catch(function () { showToast('Impossible de charger les images', 'error'); });
    }

    function showProductImagePickerModal(img, images, productId) {
        var existing = document.getElementById('ie-img-picker');
        if (existing) existing.remove();

        var grid = images.map(function (src) {
            return '<button class="ie-img-picker-item" data-src="' + escapeAttr(src) + '" type="button">' +
                   '<img src="' + escapeAttr(src) + '" alt="" loading="lazy"></button>';
        }).join('');

        var picker = document.createElement('div');
        picker.id = 'ie-img-picker';
        picker.innerHTML =
            '<div class="ie-img-picker-dialog">' +
                '<div class="ie-img-picker-header">' +
                    '<span><i class="fas fa-images"></i> Choisir une image produit</span>' +
                    '<button class="ie-img-picker-close" type="button" aria-label="Fermer">&times;</button>' +
                '</div>' +
                '<div class="ie-img-picker-grid">' +
                    (grid || '<p class="ie-img-picker-empty">Aucune image dans assets/images/</p>') +
                '</div>' +
            '</div>';

        document.body.appendChild(picker);

        picker.querySelector('.ie-img-picker-close').addEventListener('click', function () { picker.remove(); });
        picker.addEventListener('click', function (e) { if (e.target === picker) picker.remove(); });

        picker.querySelectorAll('.ie-img-picker-item').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var src = this.getAttribute('data-src');
                picker.remove();
                pulse(img, 'ie-saving');
                saveProductField(productId, 'image', src, function (ok, data) {
                    img.classList.remove('ie-saving');
                    if (ok) {
                        img.src = src;
                        pulse(img, 'ie-success');
                        showToast(data.message, 'success');
                    } else {
                        pulse(img, 'ie-error');
                        showToast(data.message || 'Erreur', 'error');
                    }
                });
            });
        });
    }

    // Initialiser les champs produit inline (en dehors du body)
    document.querySelectorAll('[data-inline-field][data-product-id]').forEach(function (el) {
        if (el.closest('.ie-body')) return;
        var pid = parseInt(el.getAttribute('data-product-id'), 10);
        if (pid > 0) initProductField(el, pid);
    });

    // Initialiser les images produit inline
    document.querySelectorAll('[data-product-img][data-product-id]').forEach(function (img) {
        var pid = parseInt(img.getAttribute('data-product-id'), 10);
        if (pid > 0) initProductImage(img, pid);
    });

})();
