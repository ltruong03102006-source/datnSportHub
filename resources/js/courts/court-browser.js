const SEARCH_DEBOUNCE = 350;

// Preset price brackets (VND). min/max null = open-ended.
export const PRICE_BUCKETS = [
    { key: 'lt100', label: 'Dưới 100k', min: null, max: 100000 },
    { key: '100-200', label: '100–200k', min: 100000, max: 200000 },
    { key: '200-300', label: '200–300k', min: 200000, max: 300000 },
    { key: 'gt300', label: 'Trên 300k', min: 300000, max: null },
];

export const RATING_OPTIONS = [
    { value: 4.5, label: '4.5★ trở lên' },
    { value: 4, label: '4★ trở lên' },
    { value: 3, label: '3★ trở lên' },
];

export const DISTANCE_OPTIONS = [
    { value: 2, label: 'Trong 2 km' },
    { value: 5, label: 'Trong 5 km' },
    { value: 10, label: 'Trong 10 km' },
];

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

    // Location
    provinces: [],
    wards: [],
    province: '',
    ward: '',

    // Price / rating / distance
    priceBuckets: PRICE_BUCKETS,
    ratingOptions: RATING_OPTIONS,
    distanceOptions: DISTANCE_OPTIONS,
    price: '',
    minRating: 0,
    radius: '',
    userLat: null,
    userLng: null,
    geoError: '',
    geoLoading: false,

    init() {
        this.applyUrlState();
        this.loadProvinces();
        if (this.province) this.loadWards(this.province);
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
        this.province = params.get('province_code') ?? '';
        this.ward = params.get('ward_code') ?? '';

        // Restore price bucket by matching min/max from the URL
        const pMin = params.get('price_min');
        const pMax = params.get('price_max');
        if (pMin || pMax) {
            const match = this.priceBuckets.find(
                (b) => String(b.min ?? '') === (pMin ?? '') && String(b.max ?? '') === (pMax ?? ''),
            );
            if (match) this.price = match.key;
        }

        this.minRating = parseFloat(params.get('min_rating')) || 0;

        const radius = parseFloat(params.get('radius'));
        const lat = parseFloat(params.get('lat'));
        const lng = parseFloat(params.get('lng'));
        if (radius && !Number.isNaN(lat) && !Number.isNaN(lng)) {
            this.radius = radius;
            this.userLat = lat;
            this.userLng = lng;
        }

        const page = parseInt(params.get('page'), 10);
        this.page = page > 0 ? page : 1;
    },

    get priceRange() {
        return this.priceBuckets.find((b) => b.key === this.price) ?? null;
    },

    syncUrl() {
        const params = new URLSearchParams();
        if (this.sport && this.sport !== 'all') {
            const match = this.sports.find((sport) => sport.id === this.sport);
            if (match) params.set('sport', match.slug);
        }
        if (this.query.trim() !== '') params.set('q', this.query.trim());
        if (this.province) params.set('province_code', this.province);
        if (this.ward) params.set('ward_code', this.ward);

        const range = this.priceRange;
        if (range) {
            if (range.min !== null) params.set('price_min', range.min);
            if (range.max !== null) params.set('price_max', range.max);
        }
        if (this.minRating) params.set('min_rating', this.minRating);
        if (this.radius && this.userLat !== null && this.userLng !== null) {
            params.set('radius', this.radius);
            params.set('lat', this.userLat);
            params.set('lng', this.userLng);
        }
        if (this.page > 1) params.set('page', this.page);

        const qs = params.toString();
        window.history.replaceState(null, '', qs ? `${window.location.pathname}?${qs}` : window.location.pathname);
    },

    async loadProvinces() {
        try {
            const response = await fetch('/api/provinces', { headers: { Accept: 'application/json' } });
            const data = await response.json();
            this.provinces = data.data ?? [];
        } catch (error) {
            this.provinces = [];
        }
    },

    async loadWards(provinceCode) {
        if (!provinceCode) {
            this.wards = [];
            return;
        }
        try {
            const response = await fetch(`/api/provinces/${provinceCode}/wards`, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            this.wards = data.data ?? [];
        } catch (error) {
            this.wards = [];
        }
    },

    selectProvince(code) {
        this.province = code;
        this.ward = '';
        this.wards = [];
        this.page = 1;
        if (code) this.loadWards(code);
        this.load();
    },

    selectWard(code) {
        this.ward = code;
        this.page = 1;
        this.load();
    },

    selectSport(id) {
        if (this.sport === id) return;
        this.sport = id;
        this.page = 1;
        this.load();
    },

    selectPrice(key) {
        this.price = this.price === key ? '' : key;
        this.page = 1;
        this.load();
    },

    selectRating(value) {
        this.minRating = this.minRating === value ? 0 : value;
        this.page = 1;
        this.load();
    },

    async selectDistance(km) {
        // Toggle off
        if (this.radius === km) {
            this.radius = '';
            this.userLat = null;
            this.userLng = null;
            this.page = 1;
            this.load();
            return;
        }

        if (!('geolocation' in navigator)) {
            this.geoError = 'Trình duyệt không hỗ trợ định vị.';
            return;
        }

        this.geoError = '';
        this.geoLoading = true;
        try {
            const pos = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 8000, enableHighAccuracy: true });
            });
            this.userLat = pos.coords.latitude;
            this.userLng = pos.coords.longitude;
            this.radius = km;
            this.page = 1;
            this.load();
        } catch (error) {
            this.geoError = 'Không lấy được vị trí. Hãy cho phép truy cập vị trí rồi thử lại.';
        } finally {
            this.geoLoading = false;
        }
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
            if (this.province) url.searchParams.set('province_code', this.province);
            if (this.ward) url.searchParams.set('ward_code', this.ward);

            const range = this.priceRange;
            if (range) {
                if (range.min !== null) url.searchParams.set('price_min', range.min);
                if (range.max !== null) url.searchParams.set('price_max', range.max);
            }
            if (this.minRating) url.searchParams.set('min_rating', this.minRating);
            if (this.radius && this.userLat !== null && this.userLng !== null) {
                url.searchParams.set('lat', this.userLat);
                url.searchParams.set('lng', this.userLng);
                url.searchParams.set('radius', this.radius);
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

    clearFilters() {
        if (!this.hasActiveFilters) return;
        this.resetFilterState();
        this.page = 1;
        this.load();
    },

    // Reset every filter (incl. search keyword) in a single reload
    clearAll() {
        if (!this.hasActiveFilters && this.query.trim() === '') return;
        this.query = '';
        this.resetFilterState();
        this.page = 1;
        this.load();
    },

    resetFilterState() {
        this.province = '';
        this.ward = '';
        this.wards = [];
        this.price = '';
        this.minRating = 0;
        this.radius = '';
        this.userLat = null;
        this.userLng = null;
        this.geoError = '';
    },

    get hasLocationFilter() {
        return this.province !== '' || this.ward !== '';
    },

    get hasActiveFilters() {
        return this.hasLocationFilter || this.price !== '' || this.minRating !== 0 || this.radius !== '';
    },

    // Labels for the active-filter summary chips
    get provinceName() {
        return this.provinces.find((p) => p.code === this.province)?.name ?? '';
    },

    get wardName() {
        return this.wards.find((w) => w.code === this.ward)?.name ?? '';
    },

    get priceLabel() {
        return this.priceRange?.label ?? '';
    },

    get ratingLabel() {
        return this.ratingOptions.find((r) => r.value === this.minRating)?.label ?? '';
    },

    get distanceLabel() {
        return this.distanceOptions.find((d) => d.value === this.radius)?.label ?? '';
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

    formatPrice(value) {
        if (value === null || value === undefined) return '';
        return new Intl.NumberFormat('vi-VN').format(value);
    },
});
