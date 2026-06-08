export default (config = {}) => ({
    venueId: config.venueId,
    courts: config.courts ?? [],
    loading: true,
    average: 0,
    count: 0,
    distribution: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 },
    reviews: [],
    canReview: false,
    form: { courtId: '', rating: 0, content: '' },
    hoverRating: 0,
    submitting: false,
    message: null,

    init() {
        this.canReview = !!localStorage.getItem('sporthub_token');
        if (this.courts.length) {
            this.form.courtId = this.courts[0].id;
        }
        this.load();
    },

    async load() {
        this.loading = true;
        try {
            const res = await fetch(`/api/venues/${this.venueId}/reviews`, {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();
            const data = json.data ?? {};
            this.average = data.average ?? 0;
            this.count = data.count ?? 0;
            this.distribution = data.distribution ?? this.distribution;
            this.reviews = data.reviews ?? [];
        } catch (error) {
            this.reviews = [];
        } finally {
            this.loading = false;
        }
    },

    percent(star) {
        if (!this.count) return 0;
        return Math.round((this.distribution[star] / this.count) * 100);
    },

    async submit() {
        this.message = null;

        if (!this.form.rating) {
            this.message = { type: 'error', text: 'Vui lòng chấm sao (1–5).' };
            return;
        }

        this.submitting = true;

        try {
            const token = localStorage.getItem('sporthub_token');
            const res = await fetch(`/api/courts/${this.form.courtId}/reviews`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    Authorization: `Bearer ${token}`,
                },
                body: JSON.stringify({ rating: this.form.rating, content: this.form.content }),
            });
            const json = await res.json();

            if (!res.ok) {
                this.message = { type: 'error', text: json.message ?? 'Gửi đánh giá thất bại.' };
                return;
            }

            this.message = { type: 'success', text: json.message ?? 'Đã gửi đánh giá!' };
            this.form.rating = 0;
            this.form.content = '';
            this.load();
        } catch (error) {
            this.message = { type: 'error', text: 'Không kết nối được máy chủ. Vui lòng thử lại.' };
        } finally {
            this.submitting = false;
        }
    },
});
