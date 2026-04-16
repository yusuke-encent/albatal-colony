<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ContentEditorForm from '@/components/admin/ContentEditorForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    content: {
        id: number;
        sku: string | null;
        title: string;
        slug: string;
        description: string;
        price: number;
        formatted_price: string;
        provider_price_option_id: number | null;
        product_code: string | null;
        cover_url: string | null;
        preview_urls: string[];
        download_name: string;
        provider_id: number;
        provider_name: string;
        genre_id: number;
        genre_name: string;
        tag_names: string;
    };
    providers: Array<{
        id: number;
        name: string;
        email: string;
        price_options: Array<{
            id: number;
            price: number;
            formatted_price: string;
            product_code: string;
        }>;
    }>;
    genres: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manage Contents',
        href: '/admin/contents',
    },
    {
        title: 'Edit',
        href: '/admin/contents',
    },
];
</script>

<template>
    <Head title="Edit Content" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <section class="rounded-[2rem] bg-white p-6 shadow-sm">
                <h1 class="text-3xl font-semibold">Edit content</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Update existing listings and replace delivery files or media assets.
                </p>

                <div class="mt-8">
                    <ContentEditorForm
                        mode="edit"
                        :content="content"
                        :providers="providers"
                        :genres="genres"
                    />
                </div>
            </section>
        </div>
    </AppLayout>
</template>
