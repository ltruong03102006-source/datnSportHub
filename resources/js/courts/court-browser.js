const SEARCH_DEBOUNCE = 350;

export default (config = {}) => ({
    sports: config.sports ?? [],
    sport: config.sport ?? null,
    query: '',
    page: 1,
    loading: false,
    items: [],
    meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
    empty: null,
    error: false,
    searchTimer: null,

    init() {
        this.applyUrlState();
        this.load();
    },

    applyUrlState() {
        const params = new URLSearchParams(window.location.search);
        const slug = params.get('sport');
        if (slug === 'all') {
            this.sport = 'all';
        } else if (slug) {
            const match = this.sports.find((sport) => sport.slug === slug);
            if (match) this.sport = match.id;
        }
        this.query = params.get('q') ?? '';
        const page = parseInt(params.get('page'), 10);
        this.page = page > 0 ? page : 1;
    },

    syncUrl() {
        const params = new URLSearchParams();
        if (this.sport && this.sport !== 'all') {
            const match = this.sports.find((sport) => sport.id === this.sport);
            if (match) params.set('sport', match.slug);
        }
        if (this.query.trim() !== '') params.set('q', this.query.trim());
        if (this.page > 1) params.set('page', this.page);
        const qs = params.toString();
        window.history.replaceState(null, '', qs ? `${window.location.pathname}?${qs}` : window.location.pathname);
    },

    selectSport(id) {
        if (this.sport === id) return;
        this.sport = id;
        this.page = 1;
        this.load();
    },

    onSearch() {
        clearTimeout(this.searchTimer);
        this.searchTimer = setTimeout(() => {
            this.page = 1;
            this.load();
        }, SEARCH_DEBOUNCE);
    },

    async load() {
        this.syncUrl();
        this.loading = true;
        this.error = false;
        this.empty = null;

        try {
            const path = this.query.trim() !== ''
                ? '/api/courts/search'
                : (this.sport && this.sport !== 'all' ? `/api/courts/sport/${this.sport}` : '/api/courts');
            const url = new URL(path, window.location.origin);
            if (this.query.trim() !== '') {
                url.searchParams.set('q', this.query.trim());
            }
            if (this.query.trim() !== '' && this.sport && this.sport !== 'all') {
                url.searchParams.set('sport', this.sport);
            }
            url.searchParams.set('page', this.page);

            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Request failed');

            const data = await response.json();
            this.items = data.data ?? [];
            this.meta = data.meta ?? this.meta;
            this.empty = data.empty ?? null;
            this.syncCounts(data.counts);
        } catch (error) {
            this.items = [];
            this.error = true;
        } finally {
            this.loading = false;
        }
    },

    syncCounts(counts) {
        if (!counts) return;
        this.sports = this.sports.map((sport) => ({
            ...sport,
            courts_count: counts[sport.id] ?? sport.courts_count ?? 0,
        }));
    },

    goTo(page) {
        if (page < 1 || page > this.meta.last_page || page === this.meta.current_page) return;
        this.page = page;
        this.load();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    clearSearch() {
        if (this.query === '') return;
        this.query = '';
        this.page = 1;
        this.load();
    },

    get pages() {
        const { current_page: current, last_page: last } = this.meta;
        const from = Math.max(1, current - 2);
        const to = Math.min(last, current + 2);
        const list = [];
        for (let i = from; i <= to; i += 1) list.push(i);
        return list;
    },

    get activeSportName() {
        if (this.sport === 'all') return 'Tất cả';
        return this.sports.find((sport) => sport.id === this.sport)?.name ?? '';
    },

    get hasResults() {
        return !this.loading && this.items.length > 0;
    },

    get isEmpty() {
        return !this.loading && !this.error && this.items.length === 0;
    },

    get rangeLabel() {
        if (this.meta.total === 0) return '0';
        const start = (this.meta.current_page - 1) * this.meta.per_page + 1;
        const end = Math.min(this.meta.current_page * this.meta.per_page, this.meta.total);
        return `${start}–${end}`;
    },
});
