/**
 * Filtres de la vue globale des menus.
 * Les résultats sont actualisés dynamiquement (fetch + JSON) sans rechargement de page.
 * Sans JavaScript, le formulaire reste fonctionnel en GET classique (amélioration progressive).
 */
(function () {
    'use strict';

    const form = document.getElementById('menu-filters');
    const results = document.getElementById('menu-results');
    const counter = document.getElementById('results-count');
    if (!form || !results || !counter) {
        return;
    }

    // Avec JavaScript, le bouton « Appliquer » devient inutile
    form.querySelectorAll('.js-hide').forEach((el) => el.classList.add('d-none'));

    let timer = null;
    let controller = null;

    function buildQuery() {
        const params = new URLSearchParams();
        new FormData(form).forEach((value, key) => {
            if (String(value).trim() !== '') {
                params.append(key, String(value).trim());
            }
        });
        return params.toString();
    }

    async function refresh() {
        const query = buildQuery();

        // Annule la requête précédente si l'utilisateur continue de saisir
        if (controller) {
            controller.abort();
        }
        controller = new AbortController();

        results.classList.add('results-loading');
        results.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch('/api/menus' + (query ? '?' + query : ''), {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            const data = await response.json();
            // Le HTML provient de notre serveur et a été échappé côté PHP
            results.innerHTML = data.html;
            counter.textContent = data.count + (data.count > 1 ? ' menus trouvés' : ' menu trouvé');
            // L'URL reflète les filtres (partage du lien, bouton précédent)
            history.replaceState(null, '', '/menus' + (query ? '?' + query : ''));
        } catch (error) {
            if (error.name !== 'AbortError') {
                counter.textContent = 'Impossible de charger les menus. Veuillez réessayer.';
            }
        } finally {
            results.classList.remove('results-loading');
            results.removeAttribute('aria-busy');
        }
    }

    function scheduleRefresh() {
        clearTimeout(timer);
        timer = setTimeout(refresh, 300);
    }

    form.addEventListener('input', scheduleRefresh);
    form.addEventListener('change', scheduleRefresh);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        refresh();
    });

    document.getElementById('reset-filters')?.addEventListener('click', (event) => {
        event.preventDefault();
        form.reset();
        form.querySelectorAll('input').forEach((input) => { input.value = ''; });
        form.querySelectorAll('select').forEach((select) => { select.value = ''; });
        refresh();
    });
})();
