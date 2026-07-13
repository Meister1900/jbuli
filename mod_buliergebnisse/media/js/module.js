(function ($) {
    'use strict';
    function tooltip(root) {
        var id = root.dataset.moduleId, popover = $('#jbuli-goal-popover_' + id);
        if (!popover.length) { popover = $('<div class="jbuli-goal-popover" id="jbuli-goal-popover_' + id + '" role="tooltip"></div>').appendTo(document.body); }
        root.$ = $(root).off('.jbuliGoalTooltip').on('mouseenter.jbuliGoalTooltip focusin.jbuliGoalTooltip', '.jbuli-goal-tooltip', function () {
            var target = this, lines = String(target.getAttribute('data-tooltip') || '').split(/\r?\n/).filter(Boolean);
            if (!lines.length) { return; }
            popover.empty().append($('<div class="jbuli-tooltip-heading"></div>').text('Torschützen'));
            lines.forEach(function (line) { var parts = line.match(/^(\d+:\d+)\s+(.*)$/), row = $('<div class="jbuli-tooltip-row"></div>'); row.append($('<span class="jbuli-tooltip-score"></span>').text(parts ? parts[1] : '')).append($('<span></span>').text(parts ? parts[2] : line)); popover.append(row); });
            popover.addClass('is-visible'); var rect = target.getBoundingClientRect(), left = Math.max(8, Math.min(rect.left + rect.width / 2 - popover.outerWidth() / 2, window.innerWidth - popover.outerWidth() - 8)), top = rect.top - popover.outerHeight() - 9; if (top < 8) { top = rect.bottom + 9; } popover.css({left: left + 'px', top: top + 'px'});
        }).on('mouseleave.jbuliGoalTooltip focusout.jbuliGoalTooltip', '.jbuli-goal-tooltip', function () { popover.removeClass('is-visible'); });
    }
    function response(xhr) { try { return $.parseJSON(xhr.responseText.substring(xhr.responseText.indexOf('success') - 2)); } catch (error) { return {success: false, message: 'Keine Verbindung zum Ergebnisserver. Bitte später erneut versuchen.'}; } }
    function load(root) {
        var id = root.dataset.moduleId, request = (Number(root.dataset.request) || 0) + 1; root.dataset.request = request;
        var loader = $('#buliergebnisse_loading_' + id).show();
        $.post(root.dataset.endpoint || 'index.php', {option:'com_ajax', module:'buliergebnisse', Itemid:root.dataset.itemId || '0', method:'getErgebnisse', format:'json', module_id:id, spieltag:$('#spieltag_' + id + ' option:selected').text()}, function (data) {
            if (request !== Number(root.dataset.request)) { return; } loader.hide(); root.innerHTML = data.success === false ? data.message : data.data; tooltip(root);
        }).fail(function (xhr) { if (request !== Number(root.dataset.request)) { return; } var data = response(xhr); loader.hide(); root.innerHTML = data.success === false ? data.message : data.data; tooltip(root); });
    }
    function initialize(root) { if (root.dataset.jbuliInitialized === '1') { return; } root.dataset.jbuliInitialized = '1'; tooltip(root); load(root); $(root).on('change', function () { load(root); }); }
    $(function () { document.querySelectorAll('.jbuli-results-root[data-module-id]').forEach(initialize); });
}(jQuery));
