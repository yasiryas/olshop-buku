import './bootstrap';
import 'flowbite';
import Alpine from 'alpinejs';
import $ from 'jquery';
import 'select2/dist/css/select2.min.css';
import select2 from 'select2';

window.Alpine = Alpine;
window.jQuery = window.$ = $;
select2(window, $);

Alpine.directive('select2', (el, { expression }, { Alpine: alpine }) => {
    const cfg = Object.assign(
        { width: '100%', allowClear: true },
        expression ? (() => { try { return JSON.parse(expression); } catch (_) { return {}; } })() : {}
    );

    const init = () => {
        if (!$(el).data('select2')) {
            $(el)
                .on('change', () => {
                    if (el.__s2Forwarding) return;
                    el.__s2Forwarding = true;
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                    setTimeout(() => { el.__s2Forwarding = false; }, 0);
                })
                .select2(cfg);
        }
    };

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(init, 0);
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }

    window.addEventListener('rajaongkir:cities-updated', () => {
        setTimeout(() => {
            if ($(el).data('select2')) $(el).trigger('change.select2');
            else init();
        }, 0);
    });
});

Alpine.data('searchableList', (baseUrl, resultsId) => ({
    keyword: '',
    loading: false,

    apiUrl() {
        const url = new URL(baseUrl, window.location.origin);
        if (this.keyword.trim()) {
            url.searchParams.set('search', this.keyword.trim());
        }
        return url;
    },

    search() {
        this.loading = true;
        fetch(this.apiUrl(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.text())
            .then(html => {
                const results = document.getElementById(resultsId);
                results.innerHTML = html;
                Alpine.initTree(results);
            })
            .catch(err => {
                console.error('Gagal memuat hasil pencarian:', err);
            })
            .finally(() => {
                this.loading = false;
            });
    }
}));

Alpine.start();
