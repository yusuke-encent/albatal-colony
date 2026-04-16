<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import FlashMessage from '@/components/FlashMessage.vue';
import type { ContentCard } from '@/types/marketplace';

defineProps<{
    featuredContents: ContentCard[];
    genres: Array<{
        id: number;
        name: string;
        slug: string;
        description: string | null;
        contents: ContentCard[];
    }>;
    heroVideoUrl: string | null;
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Home" />

    <div class="min-h-screen bg-[#f6f1e7] text-[#241914]">
        <div class="absolute inset-x-0 top-0 -z-10 h-[32rem] bg-[radial-gradient(circle_at_top_left,_rgba(224,122,95,0.35),_transparent_35%),radial-gradient(circle_at_top_right,_rgba(42,157,143,0.28),_transparent_28%),linear-gradient(180deg,_#fff8ef_0%,_#f6f1e7_100%)]" />

        <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
            <div>
                <p class="font-mono text-xs uppercase tracking-[0.35em] text-[#8d5b4c]">
                    Jp Con Colony
                </p>
                <h1 class="mt-2 font-serif text-2xl text-[#241914]">
                    Creator Marketplace
                </h1>
            </div>
            <nav class="flex items-center gap-3 text-sm">
                <Link
                    v-if="user"
                    href="/dashboard"
                    class="rounded-full border border-[#241914]/15 bg-white/80 px-5 py-2.5 backdrop-blur"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link href="/login" class="rounded-full px-4 py-2 text-[#5f453b]">
                        Log in
                    </Link>
                    <Link
                        href="/register"
                        class="rounded-full bg-[#241914] px-5 py-2.5 text-white"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <main class="mx-auto max-w-7xl px-6 pb-20 lg:px-8">
            <FlashMessage />

            <section class="relative isolate overflow-hidden rounded-[2.5rem] border border-white/30 bg-[#120d0b] px-6 py-10 shadow-[0_32px_120px_rgba(36,25,20,0.24)] sm:px-8 lg:grid lg:grid-cols-[1.2fr_0.8fr] lg:items-end lg:gap-8 lg:px-10">
                <div class="absolute inset-0 overflow-hidden rounded-[inherit]">
                    <video
                        v-if="heroVideoUrl"
                        :src="heroVideoUrl"
                        autoplay
                        muted
                        loop
                        playsinline
                        preload="metadata"
                        class="h-full w-full rounded-[inherit] object-cover opacity-60"
                    />
                    <div class="absolute inset-0 rounded-[inherit] bg-[radial-gradient(circle_at_top_left,_rgba(209,109,79,0.4),_transparent_30%),radial-gradient(circle_at_top_right,_rgba(76,134,255,0.22),_transparent_34%),linear-gradient(135deg,_rgba(11,9,8,0.55)_0%,_rgba(18,13,11,0.74)_48%,_rgba(11,9,8,0.9)_100%)]" />
                    <div class="crt-scanlines pointer-events-none absolute inset-0 rounded-[inherit] opacity-70" />
                    <div class="crt-noise pointer-events-none absolute inset-0 rounded-[inherit] opacity-35" />
                </div>

                <div class="relative z-10">
                    <p class="font-mono text-xs uppercase tracking-[0.4em] text-[#f2aa8d]">
                        Publish Worldwide
                    </p>
                    <h2 class="mt-4 max-w-3xl font-serif text-5xl leading-tight text-[#fff6ea] md:text-7xl">
                        Digital content built for audiences worldwide.
                    </h2>
                    <p class="mt-6 max-w-2xl text-base leading-8 text-[#f8e8d8]/88 md:text-lg">
                        Sell illustrations, videos, and asset bundles from independent creators in one place.
                        Admins manage product operations while international buyers complete checkout and download with ease.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            href="/library"
                            class="rounded-full bg-[#d16d4f] px-6 py-3 text-sm font-medium text-white shadow-[0_14px_40px_rgba(209,109,79,0.4)]"
                        >
                            Purchase Library
                        </Link>
                        <Link
                            href="/dashboard"
                            class="rounded-full border border-white/25 bg-white/12 px-6 py-3 text-sm font-medium text-[#fff6ea] backdrop-blur"
                        >
                            Seller & Admin Hub
                        </Link>
                    </div>
                </div>

                <div class="relative z-10 mt-8 grid gap-4 sm:grid-cols-2 lg:mt-0">
                    <article
                        v-for="content in featuredContents"
                        :key="content.id"
                        class="overflow-hidden rounded-[2rem] border border-white/12 bg-white/12 shadow-[0_24px_80px_rgba(8,6,5,0.32)] backdrop-blur-md"
                    >
                        <img
                            :src="content.cover_url || content.preview_urls[0]"
                            :alt="content.title"
                            class="h-56 w-full object-cover"
                        />
                        <div class="space-y-3 p-5">
                            <div class="flex items-center justify-between text-xs uppercase tracking-[0.25em] text-[#f1b59d]/78">
                                <span>{{ content.genre.name }}</span>
                                <span>{{ content.sku }}</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-[#fff6ea]">
                                    {{ content.title }}
                                </h3>
                                <p class="mt-1 text-sm text-[#f8e8d8]/76">
                                    {{ content.provider.name }}
                                </p>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-[#fff6ea]">
                                    {{ content.formatted_price }}
                                </span>
                                <Link
                                    :href="`/contents/${content.slug}`"
                                    class="rounded-full border border-white/20 px-4 py-2 text-sm text-[#fff6ea]"
                                >
                                    View
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <section class="mt-12 space-y-10">
                <div
                    v-for="genre in genres"
                    :key="genre.id"
                    class="rounded-[2rem] border border-white/60 bg-white/60 p-6 shadow-[0_24px_80px_rgba(36,25,20,0.08)] backdrop-blur"
                >
                    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="font-mono text-xs uppercase tracking-[0.3em] text-[#d16d4f]">
                                {{ genre.slug }}
                            </p>
                            <h3 class="mt-2 font-serif text-3xl text-[#241914]">
                                {{ genre.name }}
                            </h3>
                            <p class="mt-2 text-sm text-[#5f453b]">
                                {{ genre.description || 'Discover the latest releases in this genre.' }}
                            </p>
                        </div>
                        <p class="text-sm text-[#5f453b]">
                            {{ genre.contents.length }} items
                        </p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <article
                            v-for="content in genre.contents"
                            :key="content.id"
                            class="overflow-hidden rounded-[1.75rem] bg-[#fffaf3]"
                        >
                            <img
                                :src="content.cover_url || content.preview_urls[0]"
                                :alt="content.title"
                                class="h-64 w-full object-cover"
                            />
                            <div class="space-y-4 p-5">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="tag in content.tags"
                                        :key="tag.id"
                                        class="rounded-full bg-[#f3e3d2] px-3 py-1 text-xs text-[#6d4d40]"
                                    >
                                        {{ tag.name }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-xl font-semibold text-[#241914]">
                                        {{ content.title }}
                                    </h4>
                                    <p class="mt-1 text-sm text-[#5f453b]">
                                        {{ content.provider.name }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-[#241914]">
                                        {{ content.formatted_price }}
                                    </span>
                                    <Link
                                        :href="`/contents/${content.slug}`"
                                        class="rounded-full bg-[#241914] px-4 py-2 text-sm text-white"
                                    >
                                        Product Detail
                                    </Link>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<style scoped>
.crt-noise {
    background-image:
        radial-gradient(circle at 20% 20%, rgb(255 255 255 / 0.2) 0, transparent 18%),
        radial-gradient(circle at 78% 32%, rgb(255 255 255 / 0.16) 0, transparent 20%),
        radial-gradient(circle at 52% 78%, rgb(255 255 255 / 0.14) 0, transparent 24%),
        repeating-linear-gradient(0deg, rgb(255 255 255 / 0.08) 0 1px, transparent 1px 3px);
    background-size: 24rem 16rem, 22rem 14rem, 20rem 14rem, 100% 100%;
    animation: crt-noise-shift 220ms steps(2) infinite, crt-flicker 6s linear infinite;
    mix-blend-mode: soft-light;
}

.crt-scanlines {
    background-image:
        linear-gradient(180deg, rgb(255 255 255 / 0.12) 0, rgb(255 255 255 / 0.02) 30%, rgb(0 0 0 / 0.28) 100%),
        repeating-linear-gradient(0deg, rgb(255 255 255 / 0.05) 0 1px, rgb(0 0 0 / 0.18) 1px 3px);
    mix-blend-mode: screen;
}

@keyframes crt-noise-shift {
    0% {
        transform: translate3d(0, 0, 0);
    }

    25% {
        transform: translate3d(-1%, 1%, 0);
    }

    50% {
        transform: translate3d(1%, -1%, 0);
    }

    75% {
        transform: translate3d(0.5%, 1%, 0);
    }

    100% {
        transform: translate3d(-0.5%, -1%, 0);
    }
}

@keyframes crt-flicker {
    0%,
    100% {
        opacity: 0.32;
    }

    10% {
        opacity: 0.38;
    }

    20% {
        opacity: 0.28;
    }

    35% {
        opacity: 0.44;
    }

    60% {
        opacity: 0.3;
    }

    80% {
        opacity: 0.4;
    }
}
</style>
