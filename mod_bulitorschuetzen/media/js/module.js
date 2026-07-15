/*
 * JBuli Fußball-Torschützen
 * Copyright (C) 2026 Markus Krupp
 * License: GNU General Public License version 2 or later; see LICENSE.txt.
 */

(function () {
    'use strict';

    function responseData(payload) {
        if (Array.isArray(payload.data)) {
            return payload.data.length ? payload.data[0] : '';
        }
        return typeof payload.data === 'string' ? payload.data : '';
    }

    async function loadModule(root) {
        if (root.dataset.jbuliInitialized === '1') {
            return;
        }
        root.dataset.jbuliInitialized = '1';

        const body = new URLSearchParams({
            option: 'com_ajax',
            module: 'bulitorschuetzen',
            method: 'getTorschuetzen',
            format: 'json',
            module_id: root.dataset.moduleId || '0'
        });

        try {
            const response = await fetch(root.dataset.endpoint || 'index.php', {
                method: 'POST',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString()
            });
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            const payload = await response.json();
            const html = responseData(payload);
            if (payload.success === false || !html) {
                throw new Error(typeof payload.message === 'string' ? payload.message : 'Leere Antwort');
            }
            root.innerHTML = html;
        } catch (error) {
            const message = root.dataset.error || 'Die Torschützenliste konnte derzeit nicht geladen werden.';
            root.innerHTML = '';
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning';
            alert.textContent = message;
            root.appendChild(alert);
        }
    }

    function initialize() {
        document.querySelectorAll('.jbuli-scorers[data-module-id]').forEach(loadModule);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, {once: true});
    } else {
        initialize();
    }
}());
