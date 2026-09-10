import './bootstrap';
import 'flowbite';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

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