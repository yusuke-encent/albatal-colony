<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ContentEditorForm from '@/components/admin/ContentEditorForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    providers: Array<{
        id: number;
        name: string;
        email: string;
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
        title: 'Create',
        href: '/admin/contents/create',
    },
];
</script>

<template>
    <Head title="Create Content" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <section class="rounded-[2rem] bg-white p-6 shadow-sm">
                <h1 class="text-3xl font-semibold">Create content</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Only admin users can register new Japanese content listings.
                </p>

                <div class="mt-8">
                    <ContentEditorForm mode="create" :providers="providers" :genres="genres" />
                </div>
            </section>
        </div>
    </AppLayout>
</template>
