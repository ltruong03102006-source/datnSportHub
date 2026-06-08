<div
    x-data="reviewsPanel({ venueId: {{ $venue->id }}, courts: @js($venue->courts->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values()) })"
    class="space-y-6"
>
    <div x-show="loading" class="rounded-2xl border border-stone-200 bg-white py-16 text-center shadow-sm">
        <p class="text-sm font-medium text-stone-500">Đang tải đánh giá…</p>
    </div>

    <template x-if="!loading">
        <div class="space-y-6">
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="grid gap-8 sm:grid-cols-[auto_minmax(0,1fr)] sm:items-center">
                    <div class="text-center sm:border-r sm:border-stone-100 sm:pr-8">
                        <div class="text-5xl font-extrabold tracking-tight text-zinc-900" x-text="average.toFixed(1)"></div>
                        <div class="mt-2 flex justify-center gap-0.5">
                            <template x-for="i in 5" :key="i">
                                <svg class="h-5 w-5" :class="i <= Math.round(average) ? 'text-amber-400' : 'text-stone-300'" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006Z" />
                                </svg>
                            </template>
                        </div>
                        <p class="mt-2 text-sm text-stone-500"><span x-text="count"></span> đánh giá</p>
                    </div>

                    <div class="space-y-2">
                        <template x-for="star in [5, 4, 3, 2, 1]" :key="star">
                            <div class="flex items-center gap-3">
                                <span class="flex w-8 items-center gap-1 text-xs font-semibold text-stone-500">
                                    <span x-text="star"></span>
                                    <svg class="h-3 w-3 text-amber-400" viewBox="0 0 24 24" fill="currentColor"><path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006Z" /></svg>
                                </span>
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-stone-100">
                                    <div class="h-full rounded-full bg-amber-400 transition-all duration-500" :style="`width: ${percent(star)}%`"></div>
                                </div>
                                <span class="w-10 text-right text-xs font-medium tabular-nums text-stone-500" x-text="distribution[star]"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                <template x-if="canReview">
                    <div>
                        <h4 class="text-sm font-bold text-zinc-900">Viết đánh giá của bạn</h4>

                        <div x-show="message" x-cloak class="mt-4 rounded-lg px-4 py-3 text-sm"
                            :class="message?.type === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'"
                            x-text="message?.text"></div>

                        <div class="mt-4 grid gap-4">
                            <div x-show="courts.length > 1">
                                <label class="mb-1.5 block text-xs font-semibold text-stone-500">Chọn sân đã đặt</label>
                                <select x-model="form.courtId" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10">
                                    <template x-for="c in courts" :key="c.id">
                                        <option :value="c.id" x-text="c.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-stone-500">Chấm sao</label>
                                <div class="flex gap-1" @mouseleave="hoverRating = 0">
                                    <template x-for="i in 5" :key="i">
                                        <button type="button" @click="form.rating = i" @mouseenter="hoverRating = i" class="transition-transform hover:scale-110">
                                            <svg class="h-8 w-8" :class="i <= (hoverRating || form.rating) ? 'text-amber-400' : 'text-stone-300'" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006Z" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-stone-500">Nhận xét (tuỳ chọn)</label>
                                <textarea x-model="form.content" rows="3" maxlength="1000" placeholder="Chia sẻ trải nghiệm của bạn…"
                                    class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10"></textarea>
                            </div>

                            <div>
                                <button type="button" @click="submit" :disabled="submitting"
                                    class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:opacity-60">
                                    <span x-show="!submitting">Gửi đánh giá</span>
                                    <span x-show="submitting" x-cloak>Đang gửi…</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="!canReview">
                    <div class="flex flex-col items-center gap-2 py-4 text-center">
                        <p class="text-sm text-stone-500">Đăng nhập và đặt sân để có thể đánh giá.</p>
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Đăng nhập →</a>
                    </div>
                </template>
            </div>

            <div x-show="count === 0" x-cloak class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 py-12 text-center">
                <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-full bg-amber-50 text-2xl">⭐</div>
                <p class="text-sm font-medium text-stone-500">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
            </div>

            <div class="space-y-4">
                <template x-for="review in reviews" :key="review.id">
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700"
                                x-text="(review.user_name || '?').charAt(0).toUpperCase()"></div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <p class="text-sm font-bold text-zinc-900" x-text="review.user_name"></p>
                                    <span class="rounded bg-stone-100 px-1.5 py-0.5 text-[11px] font-semibold text-stone-500" x-text="review.court_name"></span>
                                    <span class="text-xs text-stone-400" x-text="review.created_at"></span>
                                </div>
                                <div class="mt-1 flex gap-0.5">
                                    <template x-for="i in 5" :key="i">
                                        <svg class="h-4 w-4" :class="i <= review.rating ? 'text-amber-400' : 'text-stone-300'" viewBox="0 0 24 24" fill="currentColor"><path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006Z" /></svg>
                                    </template>
                                </div>
                                <p x-show="review.content" class="mt-2 text-sm leading-relaxed text-zinc-700" x-text="review.content"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
