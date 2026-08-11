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
        // Le nom du logo s'édite via sa propre fenêtre de style
        if (el.getAttribute('data-ie-setting') === 'site_name') return;

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

    /* ---------- Édition du style du logo (data-ie-color-edit) ---------- */
    function brandValue(key, fallback) {
        if (window.__ieBrand && window.__ieBrand[key] !== undefined && window.__ieBrand[key] !== '') {
            return window.__ieBrand[key];
        }
        return fallback;
    }

    function applyBrandStyles(b) {
        document.querySelectorAll('[data-ie-setting="site_name"]').forEach(function (el) {
            var style = 'color:' + b.color + ';';
            if (b.font) style += 'font-family:' + b.font + ';';
            if (b.size) style += 'font-size:' + b.size + ';';
            style += 'font-weight:' + (b.bold ? '700' : '400') + ';';
            style += 'font-style:' + (b.italic ? 'italic' : 'normal') + ';';
            style += 'text-decoration:' + (b.underline ? 'underline' : 'none') + ';';
            el.setAttribute('style', style);
            if (b.html && b.html !== '') {
                el.innerHTML = b.html;
            } else {
                el.innerHTML = esc(b.nameFirst) +
                    (b.nameRest !== '' ? ' <span style="color:' + b.accent + ';">' + esc(b.nameRest) + '</span>' : '');
            }
        });
    }

    function openBrandEditor() {
        var nameEl = document.querySelector('[data-ie-setting="site_name"]');
        var nameText = '';
        if (nameEl) {
            nameText = nameEl.textContent.replace(/\s+/g, ' ').trim();
            if (nameText === '') nameText = brandValue('name', 'Noor Guide');
        } else {
            nameText = brandValue('name', 'Noor Guide');
        }
        var np = nameText.split(/ (.*)/s);
        var nameFirst = np[0] || '';
        var nameRest = np[1] !== undefined ? np[1] : '';

        var mainColor = brandValue('color', '#1A1A2E');
        var accentColor = brandValue('accent', '#FF6B00');
        var brandHtml = brandValue('html', '');
        var fontFamily = brandValue('font', '');
        var fontSize = brandValue('size', '');
        var bold = brandValue('bold', 1);
        var italic = brandValue('italic', 0);
        var underline = brandValue('underline', 0);

        var fontOptions = [
            ['', 'Police par défaut'],
            ['"Playfair Display", serif', 'Playfair Display'],
            ['Georgia, serif', 'Georgia'],
            ['"Times New Roman", serif', 'Times New Roman'],
            ['Arial, sans-serif', 'Arial'],
            ['Verdana, sans-serif', 'Verdana'],
            ['"Trebuchet MS", sans-serif', 'Trebuchet MS'],
            ['"Courier New", monospace', 'Courier New']
        ].map(function (f) {
            return '<option value="' + esc(f[0]) + '"' + (fontFamily === f[0] ? ' selected' : '') + '>' + esc(f[1]) + '</option>';
        }).join('');

        var sizeOptions = [
            ['', 'Taille par défaut'],
            ['1rem', 'Petit'],
            ['1.3rem', 'Moyen'],
            ['1.5rem', 'Grand'],
            ['1.8rem', 'Très grand'],
            ['2.1rem', 'Énorme']
        ].map(function (s) {
            return '<option value="' + esc(s[0]) + '"' + (fontSize === s[0] ? ' selected' : '') + '>' + esc(s[1]) + '</option>';
        }).join('');

        var overlay = openModal('');
        var box = overlay.firstChild;

        var body = document.createElement('div');
        body.style.cssText = 'padding:1.4rem 1.25rem 0;';
        body.innerHTML =
            '<h3 style="margin:0 0 0.9rem;font-size:1.05rem;color:#1A1A2E;">Éditeur du logo</h3>' +
            /* Barre d'outils compacte */
            '<div style="display:flex;align-items:center;gap:0.35rem;border:1px solid #d0d0e0;border-radius:10px;padding:0.4rem 0.5rem;margin-bottom:0.7rem;background:#fff;flex-wrap:wrap;">' +
                '<button type="button" data-ie-tool="bold" title="Gras" style="width:32px;height:30px;border:1px solid transparent;background:' + (bold ? '#FF6B00' : '#fff') + ';color:' + (bold ? '#fff' : '#333') + ';border-radius:6px;font-weight:700;cursor:pointer;">B</button>' +
                '<button type="button" data-ie-tool="italic" title="Italique" style="width:32px;height:30px;border:1px solid transparent;background:' + (italic ? '#FF6B00' : '#fff') + ';color:' + (italic ? '#fff' : '#333') + ';border-radius:6px;font-style:italic;font-weight:700;cursor:pointer;">I</button>' +
                '<button type="button" data-ie-tool="underline" title="Souligné" style="width:32px;height:30px;border:1px solid transparent;background:' + (underline ? '#FF6B00' : '#fff') + ';color:' + (underline ? '#fff' : '#333') + ';border-radius:6px;text-decoration:underline;font-weight:700;cursor:pointer;">U</button>' +
                '<span style="width:1px;height:20px;background:#e0e0ee;margin:0 0.15rem;"></span>' +
                '<input type="color" data-ie-color-main value="' + esc(mainColor) + '" title="Couleur du texte" style="width:34px;height:30px;border:1px solid #d0d0e0;border-radius:6px;cursor:pointer;padding:0;background:#fff;">' +
                '<span style="width:1px;height:20px;background:#e0e0ee;margin:0 0.15rem;"></span>' +
                '<select data-ie-font title="Police" style="max-width:120px;padding:0.25rem 0.3rem;border:1px solid #d0d0e0;border-radius:6px;font-size:0.8rem;">' + fontOptions + '</select>' +
                '<select data-ie-size title="Taille" style="max-width:80px;padding:0.25rem 0.3rem;border:1px solid #d0d0e0;border-radius:6px;font-size:0.8rem;">' + sizeOptions + '</select>' +
            '</div>' +
            /* Zone de texte éditée en direct (WYSIWYG) */
            '<div data-ie-brand-name contenteditable="true" spellcheck="false" style="border:1px dashed #d0d0e0;border-radius:10px;padding:0.8rem 1rem;background:#fafafe;text-align:center;min-height:56px;outline:none;line-height:1.4;"></div>' +
            '<p data-ie-msg style="margin:0.8rem 0 1rem;font-size:0.85rem;color:#c0392b;display:none;"></p>';

        var brandArea = body.querySelector('[data-ie-brand-name]');
        var mainInput = body.querySelector('[data-ie-color-main]');
        var fontInput = body.querySelector('[data-ie-font]');
        var sizeInput = body.querySelector('[data-ie-size]');
        var boldInput = body.querySelector('[data-ie-tool="bold"]');
        var italicInput = body.querySelector('[data-ie-tool="italic"]');
        var underlineInput = body.querySelector('[data-ie-tool="underline"]');
        var msg = body.querySelector('[data-ie-msg]');

        function isActive(btn) {
            return btn.style.background === 'rgb(255, 107, 0)' || btn.style.background === '#FF6B00';
        }

        function renderEditor() {
            var style = 'color:' + (mainColor || '#1A1A2E') + ';';
            if (fontInput.value) style += 'font-family:' + fontInput.value + ';';
            if (sizeInput.value) style += 'font-size:' + sizeInput.value + ';';
            style += 'font-weight:' + (isActive(boldInput) ? '700' : '400') + ';';
            style += 'font-style:' + (isActive(italicInput) ? 'italic' : 'normal') + ';';
            style += 'text-decoration:' + (isActive(underlineInput) ? 'underline' : 'none') + ';';
            brandArea.setAttribute('style', 'border:1px dashed #d0d0e0;border-radius:10px;padding:0.8rem 1rem;background:#fafafe;text-align:center;min-height:56px;outline:none;line-height:1.4;' + style);
        }

        /* Applique la couleur uniquement au(x) mot(s) sélectionné(s) dans la zone. */
        function applyColorToSelection(color) {
            var sel = window.getSelection();
            if (!sel) return;
            var hasSelection = !sel.isCollapsed && brandArea.contains(sel.anchorNode);
            if (hasSelection) {
                document.execCommand('foreColor', false, color);
            } else {
                /* Aucune sélection : appliquer à tout le texte */
                var range = document.createRange();
                range.selectNodeContents(brandArea);
                sel.removeAllRanges();
                sel.addRange(range);
                document.execCommand('foreColor', false, color);
                sel.removeAllRanges();
            }
        }

        function setTool(btn, on) {
            btn.style.background = on ? '#FF6B00' : '#fff';
            btn.style.color = on ? '#fff' : '#333';
        }

        setTool(boldInput, bold);
        setTool(italicInput, italic);
        setTool(underlineInput, underline);

        boldInput.addEventListener('click', function () { setTool(boldInput, !isActive(boldInput)); renderEditor(); });
        italicInput.addEventListener('click', function () { setTool(italicInput, !isActive(italicInput)); renderEditor(); });
        underlineInput.addEventListener('click', function () { setTool(underlineInput, !isActive(underlineInput)); renderEditor(); });
        mainInput.addEventListener('input', function () { applyColorToSelection(mainInput.value); renderEditor(); });
        fontInput.addEventListener('change', renderEditor);
        sizeInput.addEventListener('change', renderEditor);

        if (brandHtml !== '') {
            brandArea.innerHTML = brandHtml;
        } else {
            brandArea.textContent = nameText;
        }
        renderEditor();

        box.appendChild(body);
        box.appendChild(modalButtons(overlay, function () {
            msg.style.display = 'none';
            var newName = brandArea.textContent.replace(/\s+/g, ' ').trim() || nameText;
            /* La couleur de base ne change pas : la pipette colore uniquement la sélection */
            var mainVal = mainColor || '#1A1A2E';
            var fontVal = fontInput.value || '';
            var sizeVal = sizeInput.value || '';
            var boldVal = isActive(boldInput) ? '1' : '0';
            var italicVal = isActive(italicInput) ? '1' : '0';
            var underlineVal = isActive(underlineInput) ? '1' : '0';

            /* HTML riche : gardé uniquement s'il contient des couleurs par mot */
            var htmlOut = brandArea.innerHTML;
            var hasColor = /<(?:span|font)[^>]*\bcolor\s*[:=]/i.test(htmlOut);
            var htmlVal = hasColor ? htmlOut : '';

            var jobs = [
                ['site_name', newName],
                ['site_name_html', htmlVal],
                ['site_name_color', mainVal],
                ['site_name_font_family', fontVal],
                ['site_name_font_size', sizeVal],
                ['site_name_bold', boldVal],
                ['site_name_italic', italicVal],
                ['site_name_underline', underlineVal]
            ];

            Promise.all(jobs.map(function (j) {
                return fetch(baseUrl + 'includes/inline_edit_setting.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ csrf_token: csrfToken, key: j[0], value: j[1] })
                }).then(function (r) { return r.json(); });
            }))
            .then(function (results) {
                var ok = results.every(function (d) { return d.success; });
                if (ok) {
                    var np2 = newName.split(/ (.*)/s);
                    var nFirst = np2[0] || '';
                    var nRest = np2[1] !== undefined ? np2[1] : '';
                    window.__ieBrand = {
                        color: mainVal,
                        accent: brandValue('accent', '#FF6B00'),
                        font: fontVal,
                        size: sizeVal,
                        bold: boldVal === '1',
                        italic: italicVal === '1',
                        underline: underlineVal === '1',
                        nameFirst: nFirst,
                        nameRest: nRest,
                        html: htmlVal
                    };
                    applyBrandStyles(window.__ieBrand);
                    closeModal(overlay);
                } else {
                    msg.textContent = 'Erreur lors de l\'enregistrement.';
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

    document.querySelectorAll('[data-ie-color-edit]').forEach(function (el) {
        el.style.cursor = 'pointer';
        el.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            openBrandEditor();
        });
    });

    /* Cliquer sur le texte du logo ouvre l'éditeur de style (admin) */
    document.querySelectorAll('[data-ie-setting="site_name"]').forEach(function (el) {
        el.classList.add('ie-field');
        el.style.cursor = 'pointer';
        el.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            openBrandEditor();
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
