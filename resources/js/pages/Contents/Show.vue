<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import FlashMessage from '@/components/FlashMessage.vue';

const props = defineProps<{
    content: {
        id: number;
        sku: string | null;
        title: string;
        slug: string;
        description: string;
        price: number;
        formatted_price: string;
        cover_url: string | null;
        preview_urls: string[];
        download_name: string;
        download_mime_type: string | null;
        provider: {
            id: number;
            name: string;
        };
        genre: {
            id: number;
            name: string;
        };
        tags: Array<{
            id: number;
            name: string;
            slug: string;
        }>;
    };
    purchase: {
        id: number;
        status: string;
        is_paid: boolean;
    } | null;
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);
const heroImage = computed(
    () => props.content.cover_url || props.content.preview_urls[0] || '',
);
</script>

<template>
    <Head :title="content.title" />

    <div class="min-h-screen bg-[#f7f3ec] px-6 py-8 text-[#241914] lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex items-center justify-between">
                <Link href="/" class="text-sm text-[#6d4d40]">Back to Store</Link>
                <div class="flex items-center gap-3 text-sm">
                    <Link v-if="user" href="/library" class="rounded-full border border-black/10 px-4 py-2">
                        Library
                    </Link>
                    <Link v-if="user" href="/dashboard" class="rounded-full bg-[#241914] px-4 py-2 text-white">
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link href="/login" class="rounded-full border border-black/10 px-4 py-2">
                            Log in
                        </Link>
                        <Link href="/register" class="rounded-full bg-[#241914] px-4 py-2 text-white">
                            Register
                        </Link>
                    </template>
                </div>
            </div>

            <FlashMessage />

            <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                <section class="space-y-5">
                    <div class="overflow-hidden rounded-[2rem] bg-white shadow-[0_20px_60px_rgba(36,25,20,0.12)]">
                        <img
                            :src="heroImage"
                            :alt="content.title"
                            class="h-[30rem] w-full object-cover"
                        />
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div
                            v-for="(preview, index) in content.preview_urls"
                            :key="`${preview}-${index}`"
                            class="overflow-hidden rounded-[1.5rem] bg-white"
                        >
                            <img
                                :src="preview"
                                :alt="`${content.title} preview ${index + 1}`"
                                class="h-48 w-full object-cover"
                            />
                        </div>
                    </div>
                </section>

                <aside class="space-y-5">
                    <div class="rounded-[2rem] bg-white p-7 shadow-[0_20px_60px_rgba(36,25,20,0.12)]">
                        <p class="font-mono text-xs uppercase tracking-[0.3em] text-[#d16d4f]">
                            {{ content.genre.name }}
                        </p>
                        <h1 class="mt-3 font-serif text-4xl leading-tight">
                            {{ content.title }}
                        </h1>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-for="tag in content.tags"
                                :key="tag.id"
                                class="rounded-full bg-[#f3e3d2] px-3 py-1 text-xs text-[#6d4d40]"
                            >
                                {{ tag.name }}
                            </span>
                        </div>
                        <div class="mt-6 space-y-3 text-sm text-[#5f453b]">
                            <p>SKU: {{ content.sku }}</p>
                            <p>Provider: {{ content.provider.name }}</p>
                            <p>Delivery file: {{ content.download_name }}</p>
                            <p>Format: {{ content.download_mime_type || 'application/octet-stream' }}</p>
                        </div>
                        <div class="mt-8 flex items-end justify-between border-t border-black/5 pt-6">
                            <div>
                                <p class="text-sm text-[#5f453b]">Price</p>
                                <p class="mt-1 text-3xl font-semibold">{{ content.formatted_price }}</p>
                            </div>
                            <Link
                                v-if="purchase?.is_paid"
                                href="/library"
                                class="rounded-full bg-[#241914] px-6 py-3 text-sm font-medium text-white"
                            >
                                Open Library
                            </Link>
                            <Link
                                v-else-if="user"
                                :href="`/checkout/${content.slug}`"
                                class="rounded-full bg-[#d16d4f] px-6 py-3 text-sm font-medium text-white"
                            >
                                Purchase
                            </Link>
                            <Link
                                v-else
                                href="/login"
                                class="rounded-full bg-[#241914] px-6 py-3 text-sm font-medium text-white"
                            >
                                Sign in to Buy
                            </Link>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-black/5 bg-white/70 p-6">
                        <h2 class="text-lg font-semibold">About this content</h2>
                        <p class="mt-4 text-sm leading-8 text-[#5f453b]">
                            {{ content.description }}
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</template>
