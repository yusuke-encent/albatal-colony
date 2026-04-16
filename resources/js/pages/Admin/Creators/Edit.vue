<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { index as creatorsIndex } from '@/actions/App/Http/Controllers/Admin/CreatorManagementController';
import CreatorEditorForm from '@/components/admin/CreatorEditorForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    creator: {
        id: number;
        name: string;
        email: string;
        apc_merchant_id: number;
        provided_contents_count: number;
        stocked_contents_count: number;
        created_at: string | null;
        price_options: Array<{
            id: number;
            price: number;
            formatted_price: string;
            product_code: string;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Creators',
        href: creatorsIndex.url(),
    },
    {
        title: 'Edit',
        href: creatorsIndex.url(),
    },
];
</script>

<template>
    <Head title="Edit Creator" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <section class="rounded-[2rem] bg-white p-6 shadow-sm">
                <h1 class="text-3xl font-semibold">Edit creator</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Update login details and review whether the account still owns active or stocked content.
                </p>

                <div class="mt-8">
                    <CreatorEditorForm mode="edit" :creator="creator" />
                </div>
            </section>
        </div>
    </AppLayout>
</template>
