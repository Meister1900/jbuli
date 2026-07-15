(function () {
    'use strict';
    function resultData(payload) { return Array.isArray(payload.data) ? (payload.data[0] || {}) : (payload.data || {}); }
    function setStatus(status, text, kind) { status.className = 'jbuli-scorers-upload-status mt-2' + (kind ? ' text-' + kind : ''); status.textContent = text; }
    function initialize(root) {
        const select = root.querySelector('.jbuli-scorers-player-select');
        const file = root.querySelector('.jbuli-scorers-file-input');
        const button = root.querySelector('.jbuli-scorers-upload-button');
        const status = root.querySelector('.jbuli-scorers-upload-status');
        if (!select || !file || !button || !status) { return; }
        button.addEventListener('click', async function () {
            if (!file.files || !file.files.length) { setStatus(status, 'Bitte zuerst eine Bilddatei auswählen.', 'danger'); return; }
            const data = new FormData();
            data.append('option', 'com_ajax'); data.append('module', 'bulitorschuetzen'); data.append('method', 'uploadPlayerPortrait'); data.append('format', 'json');
            data.append('module_id', root.dataset.moduleId || '0'); data.append('player_id', select.value); data.append('portrait', file.files[0]); data.append(root.dataset.token || '', '1');
            button.disabled = true; setStatus(status, 'Bild wird hochgeladen …', 'muted');
            try {
                const response = await fetch(window.location.pathname, {method: 'POST', body: data, credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest'}});
                const payload = await response.json();
                if (!response.ok || payload.success === false) { throw new Error(typeof payload.message === 'string' ? payload.message : 'Der Upload ist fehlgeschlagen.'); }
                const result = resultData(payload); setStatus(status, result.message || 'Spielerbild gespeichert.', 'success'); file.value = '';
            } catch (error) { setStatus(status, error instanceof Error ? error.message : 'Der Upload ist fehlgeschlagen.', 'danger'); }
            finally { button.disabled = false; }
        });
    }
    document.querySelectorAll('.jbuli-scorers-admin-upload').forEach(initialize);
}());
