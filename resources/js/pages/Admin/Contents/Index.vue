<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    contents: {
        data: Array<{
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
            provider_id: number;
            provider_name: string;
            genre_id: number;
            genre_name: string;
            tag_names: string;
            published_at: string | null;
            updated_at: string | null;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manage Contents',
        href: '/admin/contents',
    },
];

function destroyContent(slug: string): void {
    if (! window.confirm('Delete this content listing?')) {
        return;
    }

    router.delete(`/admin/contents/${slug}`);
}
</script>

<template>
    <Head title="Manage Contents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 md:p-6">
            <section class="flex flex-col gap-4 rounded-[2rem] bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="font-mono text-xs uppercase tracking-[0.35em] text-[#d16d4f]">
                        Admin only
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold">Contents</h1>
                </div>
                <Link
                    href="/admin/contents/create"
                    class="rounded-full bg-[#241914] px-5 py-3 text-sm font-medium text-white"
                >
                    New Content
                </Link>
                <Link
                    href="/admin/stocked-contents"
                    class="rounded-full border border-black/10 px-5 py-3 text-sm font-medium"
                >
                    Stocked Contents
                </Link>
            </section>

            <section class="grid gap-5 xl:grid-cols-2">
                <article
                    v-for="content in contents.data"
                    :key="content.id"
                    class="overflow-hidden rounded-[1.75rem] border border-black/5 bg-white shadow-sm"
                >
                    <img
                        :src="content.cover_url || content.preview_urls[0] || 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80'"
                        :alt="content.title"
                        class="h-60 w-full object-cover"
                    />
                    <div class="space-y-4 p-6">
                        <div class="flex items-center justify-between text-sm text-muted-foreground">
                            <span>{{ content.sku }}</span>
                            <span>{{ content.genre_name }}</span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-semibold">{{ content.title }}</h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ content.provider_name }}
                            </p>
                        </div>
                        <p class="line-clamp-2 text-sm leading-7 text-[#5f453b]">
                            {{ content.description }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">{{ content.formatted_price }}</span>
                            <div class="flex gap-3">
                                <Link
                                    :href="`/admin/contents/${content.slug}/edit`"
                                    class="rounded-full border border-black/10 px-4 py-2 text-sm"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="rounded-full bg-rose-50 px-4 py-2 text-sm text-rose-600"
                                    @click="destroyContent(content.slug)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            </section>
        </div>
    </AppLayout>
</template>
