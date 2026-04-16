<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { create as createCreator, index as creatorsIndex } from '@/actions/App/Http/Controllers/Admin/CreatorManagementController';
import CreatorEditorForm from '@/components/admin/CreatorEditorForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Creators',
        href: creatorsIndex.url(),
    },
    {
        title: 'Create',
        href: createCreator.url(),
    },
];
</script>

<template>
    <Head title="Create Creator" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <section class="rounded-[2rem] bg-white p-6 shadow-sm">
                <h1 class="text-3xl font-semibold">Create creator</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Creator accounts are stored as `provider` users and can be assigned to published or stocked content.
                </p>
                <p class="mt-2 text-sm text-muted-foreground">
                    A secure initial password is generated automatically when the account is created.
                </p>

                <div class="mt-8">
                    <CreatorEditorForm mode="create" />
                </div>
            </section>
        </div>
    </AppLayout>
</template>
