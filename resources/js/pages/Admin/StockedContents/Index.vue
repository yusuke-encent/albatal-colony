<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    stockedContents: {
        data: Array<{
            id: number;
            title: string;
            description: string;
            price: number | null;
            formatted_price: string | null;
            cover_url: string | null;
            preview_urls: string[];
            download_name: string;
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
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Stocked Contents',
        href: '/admin/stocked-contents',
    },
];

async function destroyStockedContent(id: number, title: string): Promise<void> {
    const result = await Swal.fire({
        title: 'Delete stocked content?',
        text: `${title} will be removed from stocked inventory.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc2626',
        background: '#fffaf6',
        color: '#241914',
    });

    if (! result.isConfirmed) {
        return;
    }

    router.delete(`/admin/stocked-contents/${id}`);
}
</script>

<template>
    <Head title="Stocked Contents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 md:p-6">
            <section class="flex flex-col gap-4 rounded-[2rem] bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="font-mono text-xs uppercase tracking-[0.35em] text-[#d16d4f]">
                        Admin only
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold">Stocked Contents</h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Uploads waiting for provider assignment or pricing.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Link
                        href="/admin/contents/create"
                        class="rounded-full bg-[#241914] px-5 py-3 text-sm font-medium text-white"
                    >
                        New Upload
                    </Link>
                    <Link
                        href="/admin/contents"
                        class="rounded-full border border-black/10 px-5 py-3 text-sm font-medium"
                    >
                        Published Contents
                    </Link>
                </div>
            </section>

            <section class="grid gap-5 xl:grid-cols-2">
                <article
                    v-for="stockedContent in stockedContents.data"
                    :key="stockedContent.id"
                    class="overflow-hidden rounded-[1.75rem] border border-black/5 bg-white shadow-sm"
                >
                    <img
                        :src="stockedContent.cover_url || stockedContent.preview_urls[0] || 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80'"
                        :alt="stockedContent.title"
                        class="h-60 w-full object-cover"
                    />
                    <div class="space-y-4 p-6">
                        <div class="flex items-center justify-between text-sm text-muted-foreground">
                            <span>{{ stockedContent.genre.name }}</span>
                            <span>{{ stockedContent.created_at }}</span>
                        </div>

                        <div class="grid grid-cols-4 gap-3">
                            <div class="overflow-hidden rounded-2xl border border-black/5 bg-[#fbfaf8]">
                                <img
                                    :src="stockedContent.cover_url || stockedContent.preview_urls[0] || 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=300&q=80'"
                                    :alt="`${stockedContent.title} cover`"
                                    class="h-20 w-full object-cover"
                                />
                            </div>
                            <div
                                v-for="(preview, index) in stockedContent.preview_urls.slice(0, 3)"
                                :key="`${stockedContent.id}-${preview}-${index}`"
                                class="overflow-hidden rounded-2xl border border-black/5 bg-[#fbfaf8]"
                            >
                                <img
                                    :src="preview"
                                    :alt="`${stockedContent.title} preview ${index + 1}`"
                                    class="h-20 w-full object-cover"
                                />
                            </div>
                        </div>

                        <div>
                            <h2 class="text-2xl font-semibold">{{ stockedContent.title }}</h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ stockedContent.provider?.name || 'Provider not assigned yet' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in stockedContent.tags"
                                :key="tag.id"
                                class="rounded-full bg-[#f3e3d2] px-3 py-1 text-xs text-[#6d4d40]"
                            >
                                {{ tag.name }}
                            </span>
                        </div>

                        <p class="line-clamp-2 text-sm leading-7 text-[#5f453b]">
                            {{ stockedContent.description }}
                        </p>

                        <div class="flex items-center justify-between gap-4">
                            <div class="space-y-1 text-sm">
                                <p class="font-semibold">
                                    {{ stockedContent.formatted_price || 'Price not set yet' }}
                                </p>
                                <p class="text-muted-foreground">
                                    {{ stockedContent.download_name }}
                                </p>
                            </div>
                            <div class="flex gap-3">
                                <Link
                                    :href="`/admin/stocked-contents/${stockedContent.id}`"
                                    class="rounded-full border border-black/10 px-4 py-2 text-sm"
                                >
                                    Details
                                </Link>
                                <button
                                    type="button"
                                    class="rounded-full bg-rose-50 px-4 py-2 text-sm text-rose-600"
                                    @click="destroyStockedContent(stockedContent.id, stockedContent.title)"
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
