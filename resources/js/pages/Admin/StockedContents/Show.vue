<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    stockedContent: {
        id: number;
        title: string;
        description: string;
        price: number | null;
        formatted_price: string | null;
        cover_url: string | null;
        preview_urls: string[];
        download_name: string;
        download_mime_type: string | null;
        download_size: number | null;
        provider: {
            id: number;
            name: string;
            email: string;
        } | null;
        genre: {
            id: number;
            name: string;
            slug: string;
        };
        tags: Array<{
            id: number;
            name: string;
            slug: string;
        }>;
        created_at: string | null;
        updated_at: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Stocked Contents',
        href: '/admin/stocked-contents',
    },
    {
        title: 'Details',
        href: '/admin/stocked-contents',
    },
];
</script>

<template>
    <Head :title="stockedContent.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 md:p-6">
            <section class="flex flex-col gap-4 rounded-[2rem] bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="font-mono text-xs uppercase tracking-[0.35em] text-[#d16d4f]">
                        Stocked inventory
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold">{{ stockedContent.title }}</h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Review uploaded assets before assigning provider and price.
                    </p>
                </div>
                <Link
                    href="/admin/stocked-contents"
                    class="rounded-full border border-black/10 px-5 py-3 text-sm font-medium"
                >
                    Back to List
                </Link>
            </section>

            <div class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr]">
                <section class="space-y-5">
                    <div class="overflow-hidden rounded-[2rem] bg-white shadow-[0_20px_60px_rgba(36,25,20,0.12)]">
                        <img
                            :src="stockedContent.cover_url || stockedContent.preview_urls[0] || 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80'"
                            :alt="stockedContent.title"
                            class="h-[30rem] w-full object-cover"
                        />
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div
                            v-for="(preview, index) in stockedContent.preview_urls"
                            :key="`${preview}-${index}`"
                            class="overflow-hidden rounded-[1.5rem] bg-white shadow-sm"
                        >
                            <img
                                :src="preview"
                                :alt="`${stockedContent.title} preview ${index + 1}`"
                                class="h-48 w-full object-cover"
                            />
                        </div>
                    </div>
                </section>

                <aside class="space-y-5">
                    <div class="rounded-[2rem] bg-white p-7 shadow-[0_20px_60px_rgba(36,25,20,0.12)]">
                        <p class="font-mono text-xs uppercase tracking-[0.3em] text-[#d16d4f]">
                            {{ stockedContent.genre.name }}
                        </p>
                        <h2 class="mt-3 font-serif text-4xl leading-tight">
                            {{ stockedContent.title }}
                        </h2>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-for="tag in stockedContent.tags"
                                :key="tag.id"
                                class="rounded-full bg-[#f3e3d2] px-3 py-1 text-xs text-[#6d4d40]"
                            >
                                {{ tag.name }}
                            </span>
                        </div>

                        <div class="mt-6 space-y-3 text-sm text-[#5f453b]">
                            <p>Provider: {{ stockedContent.provider?.name || 'Not assigned yet' }}</p>
                            <p v-if="stockedContent.provider">
                                Contact: {{ stockedContent.provider.email }}
                            </p>
                            <p>Price: {{ stockedContent.formatted_price || 'Not set yet' }}</p>
                            <p>Delivery file: {{ stockedContent.download_name }}</p>
                            <p>Format: {{ stockedContent.download_mime_type || 'application/octet-stream' }}</p>
                            <p v-if="stockedContent.download_size !== null">
                                File size: {{ stockedContent.download_size.toLocaleString() }} bytes
                            </p>
                            <p>Created at: {{ stockedContent.created_at }}</p>
                            <p>Updated at: {{ stockedContent.updated_at }}</p>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-black/5 bg-white/70 p-6">
                        <h2 class="text-lg font-semibold">Description</h2>
                        <p class="mt-4 text-sm leading-8 text-[#5f453b]">
                            {{ stockedContent.description }}
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>
