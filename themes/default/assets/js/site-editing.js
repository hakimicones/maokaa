// themes/default/assets/js/site-editing.js
// Édition inline réutilisable des réglages du site (header / footer).
// Rendu actif uniquement pour l'administrateur connecté.
(function () {
    'use strict';

    if (window.__siteEditingLoaded) return;
    window.__siteEditingLoaded = true;

    var csrfToken = '';
    var baseUrl = '/';

    var metaCsrf = document.querySelector('meta[name="csrf-token"]');
    var metaBase = document.querySelector('meta[name="base-url"]');
    if (metaCsrf) csrfToken = metaCsrf.getAttribute('content');
    if (metaBase) baseUrl = metaBase.getAttribute('content') || '/';

    if (!csrfToken) return;

    /* ---------- Petit utilitaire modal réutilisable ---------- */
    function openModal(html) {
        var overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(10,10,25,0.65);z-index:99999;display:flex;align-items:center;justify-content:center;padding:1rem;';
        overlay.innerHTML = '<div style="background:#fff;border-radius:16px;max-width:520px;width:100%;max-height:88vh;overflow:auto;box-shadow:0 20px 60px rgba(0,0,0,0.35);">' + html + '</div>';
        document.body.appendChild(overlay);
        return overlay;
    }

    function closeModal(overlay) {
        if (overlay) overlay.remove();
    }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function modalButtons(overlay, onSave, saveLabel) {
        var footer = document.createElement('div');
        footer.style.cssText = 'display:flex;justify-content:flex-end;gap:0.6rem;border-top:1px solid #eef;padding:0.9rem 1.25rem;';
        footer.innerHTML =
            '<button type="button" data-ie-close style="padding:0.5rem 1.1rem;border:1px solid #d0d0e0;background:#fff;color:#444;border-radius:8px;cursor:pointer;">Annuler</button>' +
            '<button type="button" data-ie-save style="padding:0.5rem 1.2rem;border:none;background:#FF6B00;color:#fff;border-radius:8px;font-weight:600;cursor:pointer;">' + esc(saveLabel || 'Enregistrer') + '</button>';
        footer.querySelector('[data-ie-close]').addEventListener('click', function () { closeModal(overlay); });
        footer.querySelector('[data-ie-save]').addEventListener('click', function () {
            footer.querySelector('[data-ie-save]').disabled = true;
            onSave();
        });
        return footer;
    }

    /* ---------- Édition inline des textes (data-ie-setting) ---------- */
    document.querySelectorAll('[data-ie-setting]').forEach(function (el) {
        el.setAttribute('contenteditable', 'true');
        el.classList.add('ie-field');

        var original = el.innerHTML;

        el.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });

        el.addEventListener('focus', function () {
            this.classList.add('ie-editing');
        });

        el.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.blur();
            }
            if (e.key === 'Escape') {
                this.innerHTML = original;
                this.blur();
            }
        });

        el.addEventListener('blur', function () {
            var self = this;
            self.classList.remove('ie-editing');
            var current = self.innerHTML;
            if (current === original) return;
            self.classList.add('ie-saving');

            var key = self.getAttribute('data-ie-setting');
            // Normaliser les espaces insécables en espaces normales
            var clean = current.replace(/\u00A0/g, ' ').replace(/&nbsp;/gi, ' ');

            fetch(baseUrl + 'includes/inline_edit_setting.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    key: key,
                    value: clean
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                self.classList.remove('ie-saving');
                if (data.success) {
                    original = current;
                    self.classList.add('ie-success');
                    setTimeout(function () { self.classList.remove('ie-success'); }, 1800);
                } else {
                    self.innerHTML = original;
                    self.classList.add('ie-error');
                    setTimeout(function () { self.classList.remove('ie-error'); }, 1800);
                }
            })
            .catch(function () {
                self.classList.remove('ie-saving');
                self.innerHTML = original;
                self.classList.add('ie-error');
                setTimeout(function () { self.classList.remove('ie-error'); }, 1800);
            });
        });
    });

    /* ---------- Édition du logo (data-ie-logo) ---------- */
    function openLogoEditor() {
        var currentImg = document.querySelector('img[data-ie-logo]');
        var hasLogo = currentImg !== null;

        var overlay = openModal('');
        var box = overlay.firstChild;

        var body = document.createElement('div');
        body.style.cssText = 'padding:1.4rem 1.25rem 0;';
        body.innerHTML =
            '<h3 style="margin:0 0 0.3rem;font-size:1.15rem;color:#1A1A2E;">Modifier le logo</h3>' +
            '<p style="margin:0 0 1rem;font-size:0.85rem;color:#777;">Choisissez une nouvelle image (PNG, JPEG ou WebP, max 2 Mo).</p>' +
            '<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">' +
                '<img data-ie-preview src="" alt="Aperçu du logo" style="max-height:56px;max-width:160px;border-radius:10px;' + (hasLogo ? '' : 'display:none;') + '">' +
                '<div style="font-size:0.85rem;color:#999;">Aperçu</div>' +
            '</div>' +
            '<input type="file" data-ie-file accept="image/png,image/jpeg,image/webp" style="width:100%;margin-bottom:1rem;">' +
            '<p data-ie-msg style="margin:0 0 1rem;font-size:0.85rem;color:#c0392b;display:none;"></p>' +
            (hasLogo
                ? '<label style="display:flex;align-items:center;gap:0.5rem;font-size:0.9rem;color:#c0392b;margin-bottom:1rem;cursor:pointer;">' +
                    '<input type="checkbox" data-ie-remove> Supprimer le logo actuel</label>'
                : '');
        box.appendChild(body);

        var preview = body.querySelector('[data-ie-preview]');
        var fileInput = body.querySelector('[data-ie-file]');
        var msg = body.querySelector('[data-ie-msg]');

        if (hasLogo) preview.src = currentImg.src;

        fileInput.addEventListener('change', function () {
            var f = fileInput.files && fileInput.files[0];
            if (!f) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(f);
        });

        box.appendChild(modalButtons(overlay, function () {
            var file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;

            var removeCheck = body.querySelector('[data-ie-remove]');
            var removeFlag = removeCheck && removeCheck.checked;

            if (!file && !removeFlag) {
                msg.textContent = 'Choisissez une image ou cochez la suppression.';
                msg.style.display = 'block';
                var saveBtn = overlay.querySelector('[data-ie-save]');
                if (saveBtn) saveBtn.disabled = false;
                return;
            }

            var fd = new FormData();
            fd.append('csrf_token', csrfToken);
            if (file) fd.append('site_logo', file);
            if (removeFlag) fd.append('remove_logo', '1');

            msg.style.display = 'none';

            fetch(baseUrl + 'includes/inline_upload_logo.php', {
                method: 'POST',
                body: fd
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    closeModal(overlay);
                    window.location.reload();
                } else {
                    msg.textContent = data.message || 'Erreur lors de l\'upload.';
                    msg.style.display = 'block';
                    var saveBtn = overlay.querySelector('[data-ie-save]');
                    if (saveBtn) saveBtn.disabled = false;
                }
            })
            .catch(function () {
                msg.textContent = 'Erreur réseau.';
                msg.style.display = 'block';
                var saveBtn = overlay.querySelector('[data-ie-save]');
                if (saveBtn) saveBtn.disabled = false;
            });
        }, 'Changer le logo'));
    }

    document.querySelectorAll('[data-ie-logo]').forEach(function (el) {
        el.style.cursor = 'pointer';
        el.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            openLogoEditor();
        });
    });

    /* ---------- Édition des colonnes du footer (data-ie-footer-edit) ---------- */
    var colsContainer = document.querySelector('[data-footer-cols]');

    function readFooterCols() {
        if (!colsContainer) return [];
        try {
            var parsed = JSON.parse(colsContainer.getAttribute('data-footer-cols'));
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function saveFooterCols(cols) {
        return fetch(baseUrl + 'includes/inline_edit_setting.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: csrfToken,
                key: 'footer_columns',
                value: JSON.stringify(cols)
            })
        }).then(function (r) { return r.json(); });
    }

    function openFooterColEditor(index) {
        var cols = readFooterCols();
        var col = cols[index] || { title: '', links: [] };
        var links = Array.isArray(col.links) ? col.links : [];

        var overlay = openModal('');
        var box = overlay.firstChild;

        var body = document.createElement('div');
        body.style.cssText = 'padding:1.4rem 1.25rem 0;';
        body.innerHTML =
            '<h3 style="margin:0 0 1rem;font-size:1.15rem;color:#1A1A2E;">Modifier la colonne du footer</h3>' +
            '<label style="display:block;font-size:0.85rem;font-weight:600;color:#555;margin-bottom:0.3rem;">Titre</label>' +
            '<input type="text" data-ie-col-title style="width:100%;padding:0.55rem 0.8rem;border:1px solid #d0d0e0;border-radius:8px;margin-bottom:1rem;font-size:0.95rem;">' +
            '<label style="display:block;font-size:0.85rem;font-weight:600;color:#555;margin-bottom:0.3rem;">Liens</label>' +
            '<div data-ie-links style="margin-bottom:0.6rem;"></div>' +
            '<button type="button" data-ie-add-link style="padding:0.4rem 0.9rem;border:1px dashed #FF6B00;background:#fff7f0;color:#FF6B00;border-radius:8px;cursor:pointer;font-size:0.85rem;margin-bottom:1rem;">+ Ajouter un lien</button>' +
            '<p data-ie-msg style="margin:0 0 1rem;font-size:0.85rem;color:#c0392b;display:none;"></p>';

        var titleInput = body.querySelector('[data-ie-col-title]');
        titleInput.value = col.title || '';

        var linksBox = body.querySelector('[data-ie-links]');
        var msg = body.querySelector('[data-ie-msg]');

        function addLinkRow(link) {
            var row = document.createElement('div');
            row.style.cssText = 'display:flex;gap:0.5rem;margin-bottom:0.55rem;';
            row.innerHTML =
                '<input type="text" data-ie-label placeholder="Libellé" style="flex:1;padding:0.5rem 0.7rem;border:1px solid #d0d0e0;border-radius:8px;font-size:0.9rem;" value="' + esc(link.label || '') + '">' +
                '<input type="text" data-ie-url placeholder="Lien" style="flex:1;padding:0.5rem 0.7rem;border:1px solid #d0d0e0;border-radius:8px;font-size:0.9rem;" value="' + esc(link.url || '') + '">' +
                '<button type="button" data-ie-del style="padding:0.3rem 0.7rem;border:none;background:#fee;color:#c0392b;border-radius:8px;cursor:pointer;">✕</button>';
            row.querySelector('[data-ie-del]').addEventListener('click', function () {
                row.remove();
            });
            linksBox.appendChild(row);
        }

        links.forEach(addLinkRow);
        addLinkRow({ label: '', url: '' });

        body.querySelector('[data-ie-add-link]').addEventListener('click', function () {
            addLinkRow({ label: '', url: '' });
        });

        box.appendChild(body);
        box.appendChild(modalButtons(overlay, function () {
            var rows = linksBox.querySelectorAll('[data-ie-label]');
            var newLinks = [];
            rows.forEach(function (labelInput) {
                var urlInput = labelInput.parentNode.querySelector('[data-ie-url]');
                newLinks.push({
                    label: labelInput.value.trim(),
                    url: urlInput.value.trim()
                });
            });
            newLinks = newLinks.filter(function (l) { return l.label !== ''; });

            var cols2 = readFooterCols();
            cols2[index] = { title: titleInput.value.trim(), links: newLinks };

            msg.style.display = 'none';

            saveFooterCols(cols2)
                .then(function (data) {
                    if (data.success) {
                        closeModal(overlay);
                        window.location.reload();
                    } else {
                        msg.textContent = data.message || 'Erreur lors de l\'enregistrement.';
                        msg.style.display = 'block';
                        var saveBtn = overlay.querySelector('[data-ie-save]');
                        if (saveBtn) saveBtn.disabled = false;
                    }
                })
                .catch(function () {
                    msg.textContent = 'Erreur réseau.';
                    msg.style.display = 'block';
                    var saveBtn = overlay.querySelector('[data-ie-save]');
                    if (saveBtn) saveBtn.disabled = false;
                });
        }, 'Enregistrer'));
    }

    document.querySelectorAll('[data-ie-footer-edit]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            openFooterColEditor(parseInt(btn.getAttribute('data-ie-footer-edit'), 10) || 0);
        });
    });

    /* ---------- Édition du menu principal (data-ie-menu-*) ---------- */
    var menuData = window.__ieMenu || null;

    function menuPost(action, payload) {
        var fd = new FormData();
        fd.append('csrf_token', csrfToken);
        fd.append('action', action);
        Object.keys(payload).forEach(function (k) {
            if (payload[k] !== null && payload[k] !== undefined) fd.append(k, payload[k]);
        });
        return fetch(baseUrl + 'includes/inline_edit_menu.php', {
            method: 'POST',
            body: fd
        }).then(function (r) { return r.json(); });
    }

    function openMenuEditor(itemId) {
        var items = (menuData && menuData.items) || [];
        var existing = itemId ? items.filter(function (i) { return i.id === itemId; })[0] : null;
        var isEdit = !!existing;

        // Parents candidats : éléments de premier niveau (parent === 0), hors élément lui-même
        var parents = items.filter(function (i) {
            return !i.parent && (!isEdit || i.id !== itemId);
        });

        var overlay = openModal('');
        var box = overlay.firstChild;

        var parentOptions = '<option value="0">Aucun (élément principal)</option>' +
            parents.map(function (p) {
                return '<option value="' + p.id + '"' +
                    (isEdit && existing.parent === p.id ? ' selected' : '') + '>' +
                    esc(p.title) + '</option>';
            }).join('');

        var body = document.createElement('div');
        body.style.cssText = 'padding:1.4rem 1.25rem 0;';
        body.innerHTML =
            '<h3 style="margin:0 0 1rem;font-size:1.15rem;color:#1A1A2E;">' + (isEdit ? 'Modifier l\'élément' : 'Ajouter un élément') + '</h3>' +
            '<label style="display:block;font-size:0.85rem;font-weight:600;color:#555;margin-bottom:0.3rem;">Titre</label>' +
            '<input type="text" data-ie-menu-title style="width:100%;padding:0.55rem 0.8rem;border:1px solid #d0d0e0;border-radius:8px;margin-bottom:1rem;font-size:0.95rem;">' +
            '<label style="display:block;font-size:0.85rem;font-weight:600;color:#555;margin-bottom:0.3rem;">Lien</label>' +
            '<input type="text" data-ie-menu-url style="width:100%;padding:0.55rem 0.8rem;border:1px solid #d0d0e0;border-radius:8px;margin-bottom:1rem;font-size:0.95rem;" placeholder="ex. documentation ou #features">' +
            '<label style="display:block;font-size:0.85rem;font-weight:600;color:#555;margin-bottom:0.3rem;">Icône (classes Font Awesome, facultatif)</label>' +
            '<input type="text" data-ie-menu-icon style="width:100%;padding:0.55rem 0.8rem;border:1px solid #d0d0e0;border-radius:8px;margin-bottom:1rem;font-size:0.95rem;" placeholder="ex. fas fa-info-circle">' +
            '<div style="display:flex;gap:1rem;margin-bottom:1rem;">' +
                '<div style="flex:1;"><label style="display:block;font-size:0.85rem;font-weight:600;color:#555;margin-bottom:0.3rem;">Élément parent</label>' +
                '<select data-ie-menu-parent style="width:100%;padding:0.55rem 0.8rem;border:1px solid #d0d0e0;border-radius:8px;font-size:0.95rem;">' + parentOptions + '</select></div>' +
                '<div style="flex:1;"><label style="display:block;font-size:0.85rem;font-weight:600;color:#555;margin-bottom:0.3rem;">Position</label>' +
                '<input type="number" data-ie-menu-position min="0" style="width:100%;padding:0.55rem 0.8rem;border:1px solid #d0d0e0;border-radius:8px;font-size:0.95rem;"></div>' +
            '</div>' +
            (isEdit
                ? '<label style="display:flex;align-items:center;gap:0.5rem;font-size:0.9rem;color:#555;margin-bottom:1rem;cursor:pointer;">' +
                    '<input type="checkbox" data-ie-menu-active> Afficher cet élément</label>'
                : '') +
            '<p data-ie-msg style="margin:0 0 1rem;font-size:0.85rem;color:#c0392b;display:none;"></p>';

        var titleInput = body.querySelector('[data-ie-menu-title]');
        var urlInput = body.querySelector('[data-ie-menu-url]');
        var iconInput = body.querySelector('[data-ie-menu-icon]');
        var parentInput = body.querySelector('[data-ie-menu-parent]');
        var positionInput = body.querySelector('[data-ie-menu-position]');
        var activeInput = body.querySelector('[data-ie-menu-active]');
        var msg = body.querySelector('[data-ie-msg]');

        if (isEdit) {
            var li = document.querySelector('[data-ie-menu-item="' + itemId + '"]');
            titleInput.value = li ? li.getAttribute('data-ie-menu-title') : (existing.title || '');
            urlInput.value = li ? li.getAttribute('data-ie-menu-url') : (existing.url || '');
            iconInput.value = li ? li.getAttribute('data-ie-menu-icon') : '';
            parentInput.value = li ? (li.getAttribute('data-ie-menu-parent') || '0') : (existing.parent || 0);
            positionInput.value = li ? (li.getAttribute('data-ie-menu-position') || '0') : (existing.position || 0);
            if (activeInput) activeInput.checked = !li || li.getAttribute('data-ie-menu-active') !== '0';
        } else {
            positionInput.value = 0;
        }

        box.appendChild(body);
        box.appendChild(modalButtons(overlay, function () {
            var title = titleInput.value.trim();
            if (title === '') {
                msg.textContent = 'Le titre est obligatoire.';
                msg.style.display = 'block';
                var saveBtn = overlay.querySelector('[data-ie-save]');
                if (saveBtn) saveBtn.disabled = false;
                return;
            }

            var url = urlInput.value.trim();
            // Construire un lien valide : relatif ou ancre
            if (url === '' || url === '#') {
                url = '#';
            } else if (!/^(https?:)?\/\//i.test(url) && !/^#/.test(url) && !/^(\.\.?|\/)/.test(url)) {
                url = '/' + url.replace(/^\/+/, '');
            }

            var payload = {
                menu_id: menuData.menuId,
                title: title,
                url: url,
                icon: iconInput.value.trim() || '',
                parent_id: parentInput.value !== '0' ? parentInput.value : '',
                position: positionInput.value,
                active: activeInput ? (activeInput.checked ? '1' : '') : '1'
            };
            if (isEdit) payload.id = existing.id;

            msg.style.display = 'none';

            menuPost(isEdit ? 'update' : 'add', payload)
                .then(function (data) {
                    if (data.success) {
                        closeModal(overlay);
                        window.location.reload();
                    } else {
                        msg.textContent = data.message || 'Erreur lors de l\'enregistrement.';
                        msg.style.display = 'block';
                        var saveBtn = overlay.querySelector('[data-ie-save]');
                        if (saveBtn) saveBtn.disabled = false;
                    }
                })
                .catch(function () {
                    msg.textContent = 'Erreur réseau.';
                    msg.style.display = 'block';
                    var saveBtn = overlay.querySelector('[data-ie-save]');
                    if (saveBtn) saveBtn.disabled = false;
                });
        }, isEdit ? 'Enregistrer' : 'Ajouter'));
    }

    function confirmDeleteMenu(itemId) {
        var items = (menuData && menuData.items) || [];
        var it = items.filter(function (i) { return i.id === itemId; })[0];
        var label = it ? it.title : 'cet élément';
        var overlay = openModal('');
        var box = overlay.firstChild;

        var body = document.createElement('div');
        body.style.cssText = 'padding:1.4rem 1.25rem 0;';
        body.innerHTML =
            '<h3 style="margin:0 0 0.6rem;font-size:1.15rem;color:#1A1A2E;">Supprimer l\'élément</h3>' +
            '<p style="margin:0 0 1rem;font-size:0.9rem;color:#555;">Voulez-vous vraiment supprimer « ' + esc(label) + ' » ?</p>' +
            '<p data-ie-msg style="margin:0 0 1rem;font-size:0.85rem;color:#c0392b;display:none;"></p>';

        box.appendChild(body);
        box.appendChild(modalButtons(overlay, function () {
            var msg = body.querySelector('[data-ie-msg]');
            msg.style.display = 'none';
            menuPost('delete', { id: itemId })
                .then(function (data) {
                    if (data.success) {
                        closeModal(overlay);
                        window.location.reload();
                    } else {
                        msg.textContent = data.message || 'Erreur lors de la suppression.';
                        msg.style.display = 'block';
                        var saveBtn = overlay.querySelector('[data-ie-save]');
                        if (saveBtn) saveBtn.disabled = false;
                    }
                })
                .catch(function () {
                    msg.textContent = 'Erreur réseau.';
                    msg.style.display = 'block';
                    var saveBtn = overlay.querySelector('[data-ie-save]');
                    if (saveBtn) saveBtn.disabled = false;
                });
        }, 'Supprimer'));
    }

    if (menuData && menuData.menuId) {
        document.querySelectorAll('[data-ie-menu-edit]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var li = btn.closest('[data-ie-menu-item]');
                if (li) openMenuEditor(parseInt(li.getAttribute('data-ie-menu-item'), 10) || 0);
            });
        });

        document.querySelectorAll('[data-ie-menu-delete]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var li = btn.closest('[data-ie-menu-item]');
                if (li) confirmDeleteMenu(parseInt(li.getAttribute('data-ie-menu-item'), 10) || 0);
            });
        });

        var addBtn = document.querySelector('[data-ie-menu-add]');
        if (addBtn) {
            addBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openMenuEditor(0);
            });
        }
    }

    /* Fermer le modal avec Échap */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[data-ie-close]').forEach(function (btn) {
                btn.click();
            });
        }
    });
})();
