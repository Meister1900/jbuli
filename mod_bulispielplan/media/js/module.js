(function ($) {
    'use strict';
    function fit(root) { var scroll = root.querySelector('.jbuli-schedule-scroll'), table = scroll && scroll.querySelector('.jbuli-schedule'); if (!table) { return; } table.classList.remove('jbuli-hide-year'); if (scroll.clientWidth < 460 || table.scrollWidth > scroll.clientWidth + 1) { table.classList.add('jbuli-hide-year'); } }
    function tooltip(root) {
        var id = root.dataset.moduleId, popover = $('#jbuli-goal-popover_' + id);
        if (!popover.length) { popover = $('<div class="jbuli-goal-popover" id="jbuli-goal-popover_' + id + '" role="tooltip"></div>').appendTo(document.body); }
        $(root).off('.jbuliGoalTooltip').on('mouseenter.jbuliGoalTooltip focusin.jbuliGoalTooltip', '.jbuli-goal-tooltip', function () {
            var target = this, lines = String(target.getAttribute('data-tooltip') || '').split(/\r?\n/).filter(Boolean); if (!lines.length) { return; }
            popover.empty().append($('<div class="jbuli-tooltip-heading"></div>').text('Torschützen'));
            lines.forEach(function (line) { var parts = line.match(/^(\d+:\d+)\s+(.*)$/), row = $('<div class="jbuli-tooltip-row"></div>'); row.append($('<span class="jbuli-tooltip-score"></span>').text(parts ? parts[1] : '')).append($('<span></span>').text(parts ? parts[2] : line)); popover.append(row); });
            popover.addClass('is-visible'); var rect = target.getBoundingClientRect(), left = Math.max(8, Math.min(rect.left + rect.width / 2 - popover.outerWidth() / 2, window.innerWidth - popover.outerWidth() - 8)), top = rect.top - popover.outerHeight() - 9; if (top < 8) { top = rect.bottom + 9; } popover.css({left:left + 'px', top:top + 'px'});
        }).on('mouseleave.jbuliGoalTooltip focusout.jbuliGoalTooltip', '.jbuli-goal-tooltip', function () { popover.removeClass('is-visible'); });
    }
    function response(xhr) { try { return $.parseJSON(xhr.responseText.substring(xhr.responseText.indexOf('success') - 2)); } catch (error) { return {success:false, message:'Keine Verbindung zum Ergebnisserver. Bitte später erneut versuchen.'}; } }
    function logoFallback(event) {
        var image = event.target, fallback;
        if (!image || image.tagName !== 'IMG') { return; }
        fallback = image.getAttribute('data-fallback-src') || '';
        if (!fallback) { return; }
        image.removeAttribute('data-fallback-src');
        image.src = fallback;
    }
    function enhance(root) {
        var id = root.dataset.moduleId, select = $('#verein_' + id), wrapper = select.closest('.jbuli-team-select'); if (!select.length || wrapper.hasClass('jbuli-enhanced')) { return; }
        var trigger = $('<button type="button" class="jbuli-select-button" aria-haspopup="listbox" aria-expanded="false"></button>'), menu = $('<div class="jbuli-select-menu" role="listbox"></div>');
        function imageFor(option) { var url = option.attr('data-logo') || '', fallback = option.attr('data-logo-fallback') || '', image; if (!url) { return null; } image = $('<img alt="">').attr('src', url); if (fallback) { image.attr('data-fallback-src', fallback); } return image; }
        function fill(option) { var image; trigger.empty(); image = imageFor(option); if (image) { trigger.append(image); } trigger.append($('<span></span>').text(option.text())); }
        select.find('option').each(function () { var option = $(this), item = $('<button type="button" class="jbuli-select-option" role="option"></button>'), image = imageFor(option); if (image) { item.append(image); } item.append($('<span></span>').text(option.text())).attr('aria-selected', option.is(':selected') ? 'true' : 'false').on('click', function (event) { event.stopPropagation(); select.val(option.val()); menu.removeClass('is-open'); trigger.attr('aria-expanded', 'false'); fill(option); select.trigger('change'); }); menu.append(item); });
        trigger.on('click', function (event) {
            event.stopPropagation();
            var open = !menu.hasClass('is-open');
            if (open) {
                var surface = wrapper.parent();
                while (surface.length) {
                    var background = window.getComputedStyle(surface[0]).backgroundColor;
                    if (background && background !== 'rgba(0, 0, 0, 0)' && background !== 'transparent') {
                        menu.css({backgroundColor: background, color: window.getComputedStyle(surface[0]).color});
                        break;
                    }
                    surface = surface.parent();
                }
            }
            menu.toggleClass('is-open', open);
            trigger.attr('aria-expanded', open ? 'true' : 'false');
        });
        $(document).off('click.jbuli_' + id).on('click.jbuli_' + id, function () { menu.removeClass('is-open'); trigger.attr('aria-expanded', 'false'); }); wrapper.addClass('jbuli-enhanced').append(trigger, menu); fill(select.find('option:selected'));
    }
    function load(root) {
        var id = root.dataset.moduleId, request = (Number(root.dataset.request) || 0) + 1; root.dataset.request = request; var loader = $('#bulispielplan_loading_' + id).show();
        $.post(root.dataset.endpoint || 'index.php', {option:'com_ajax', module:'bulispielplan', Itemid:root.dataset.itemId || '0', method:'getSpielplan', format:'json', module_id:id, verein:$('#verein_' + id).val()}, function (data) { if (request !== Number(root.dataset.request)) { return; } loader.hide(); root.innerHTML = data.success === false ? data.message : data.data; enhance(root); fit(root); tooltip(root); }).fail(function (xhr) { if (request !== Number(root.dataset.request)) { return; } var data = response(xhr); loader.hide(); root.innerHTML = data.success === false ? data.message : data.data; enhance(root); fit(root); tooltip(root); });
    }
    function initialize(root) { if (root.dataset.jbuliInitialized === '1') { return; } root.dataset.jbuliInitialized = '1'; root.addEventListener('error', logoFallback, true); tooltip(root); load(root); $(root).on('change', '#verein_' + root.dataset.moduleId, function () { load(root); }); if (window.ResizeObserver) { new ResizeObserver(function () { window.requestAnimationFrame(function () { fit(root); }); }).observe(root); } else { $(window).on('resize.bulispielplan_' + root.dataset.moduleId, function () { fit(root); }); } }
    $(function () { document.querySelectorAll('.jbuli-schedule-root[data-module-id]').forEach(initialize); });
}(jQuery));
