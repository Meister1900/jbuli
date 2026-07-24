(function ($) {
    'use strict';
    function fit(root) {
        var table = root.querySelector('.jbuli-standings');
        if (!table) { return; }
        table.querySelectorAll('.jbuli-responsive-column').forEach(function (cell) { cell.classList.remove('jbuli-column-hidden'); });
        ['.jbuli-form', '.jbuli-goals', '.jbuli-diff', '.jbuli-played'].forEach(function (selector) {
            if (table.scrollWidth > root.clientWidth + 1) { table.querySelectorAll(selector).forEach(function (cell) { cell.classList.add('jbuli-column-hidden'); }); }
        });
    }
    function response(xhr) {
        try { return $.parseJSON(xhr.responseText.substring(xhr.responseText.indexOf('success') - 2)); }
        catch (error) { return {success: false, message: 'Keine Verbindung zum Ergebnisserver. Bitte später erneut versuchen.'}; }
    }
    function logoFallback(event) {
        var image = event.target, fallback;
        if (!image || image.tagName !== 'IMG') { return; }
        fallback = image.getAttribute('data-fallback-src') || '';
        if (!fallback) { return; }
        image.removeAttribute('data-fallback-src');
        image.src = fallback;
    }
    function load(root) {
        var id = root.dataset.moduleId;
        var loader = $('#bulitabelle_loading_' + id).show();
        $.post(root.dataset.endpoint || 'index.php', {option: 'com_ajax', module: 'bulitabelle', Itemid: root.dataset.itemId || '0', method: 'getTabelle', format: 'json', module_id: id}, function (data) {
            loader.hide(); root.innerHTML = data.success === false ? data.message : data.data; fit(root);
        }).fail(function (xhr) { var data = response(xhr); loader.hide(); root.innerHTML = data.success === false ? data.message : data.data; fit(root); });
    }
    function initialize(root) {
        if (root.dataset.jbuliInitialized === '1') { return; }
        root.dataset.jbuliInitialized = '1'; root.addEventListener('error', logoFallback, true); load(root);
        if (window.ResizeObserver) { new ResizeObserver(function () { window.requestAnimationFrame(function () { fit(root); }); }).observe(root); }
        else { $(window).on('resize.bulitabelle_' + root.dataset.moduleId, function () { fit(root); }); }
    }
    function start() { document.querySelectorAll('.jbuli-standings-root[data-module-id]').forEach(initialize); }
    $(start);
}(jQuery));
