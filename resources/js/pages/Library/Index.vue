<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    purchases: Array<{
        id: number;
        purchased_at: string | null;
        content: {
            id: number;
            slug: string;
            title: string;
            formatted_price: string;
            cover_url: string | null;
            provider_name: string;
            genre_name: string;
            tags: string[];
        };
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Library',
        href: '/library',
    },
];
</script>

<template>
    <Head title="Library" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 md:p-6">
            <section class="rounded-[2rem] bg-[linear-gradient(135deg,#f3e3d2_0%,#fff9f2_100%)] p-8">
                <p class="font-mono text-xs uppercase tracking-[0.35em] text-[#d16d4f]">
                    Purchased contents
                </p>
                <h1 class="mt-3 font-serif text-4xl text-[#241914]">Your Library</h1>
                <p class="mt-3 max-w-2xl text-sm leading-8 text-[#5f453b]">
                    Re-download Japanese content you have already purchased.
                </p>
            </section>

            <section v-if="purchases.length" class="grid gap-5 lg:grid-cols-2">
                <article
                    v-for="purchase in purchases"
                    :key="purchase.id"
                    class="overflow-hidden rounded-[1.75rem] border border-black/5 bg-white shadow-sm"
                >
                    <img
                        :src="purchase.content.cover_url || 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80'"
                        :alt="purchase.content.title"
                        class="h-56 w-full object-cover"
                    />
                    <div class="space-y-4 p-6">
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in purchase.content.tags"
                                :key="tag"
                                class="rounded-full bg-[#f3e3d2] px-3 py-1 text-xs text-[#6d4d40]"
                            >
                                {{ tag }}
                            </span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-semibold text-[#241914]">{{ purchase.content.title }}</h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ purchase.content.provider_name }} / {{ purchase.content.genre_name }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-muted-foreground">
                                purchased at {{ purchase.purchased_at }}
                            </div>
                            <div class="flex gap-3">
                                <Link
                                    :href="`/contents/${purchase.content.slug}`"
                                    class="rounded-full border border-black/10 px-4 py-2 text-sm text-[#241914]"
                                >
                                    Detail
                                </Link>
                                <a
                                    :href="`/downloads/${purchase.id}`"
                                    class="rounded-full bg-[#241914] px-4 py-2 text-sm text-white"
                                >
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <section
                v-else
                class="rounded-[1.75rem] border border-dashed border-black/10 bg-white px-6 py-12 text-center text-sm text-muted-foreground"
            >
                You have no purchased content yet.
            </section>
        </div>
    </AppLayout>
</template>
