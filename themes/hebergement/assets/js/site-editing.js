(function () {
    'use strict';

    if (window.__hostingSiteEditingLoader) return;
    window.__hostingSiteEditingLoader = true;

    var baseUrl = document.querySelector('meta[name="base-url"]');
    if (!baseUrl) return;

    var editor = document.createElement('script');
    editor.src = baseUrl.content + 'themes/welcome/assets/js/site-editing.js';
    editor.defer = true;
    document.head.appendChild(editor);
})();
