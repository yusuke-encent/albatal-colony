<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    create as createCreator,
    destroy as destroyCreator,
    edit as editCreator,
    index as creatorsIndex,
} from '@/actions/App/Http/Controllers/Admin/CreatorManagementController';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    creators: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            provided_contents_count: number;
            stocked_contents_count: number;
            created_at: string | null;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Creators',
        href: creatorsIndex.url(),
    },
];

function destroyCreatorAccount(creatorId: number): void {
    if (!window.confirm('Delete this creator account?')) {
        return;
    }

    router.delete(
        destroyCreator.url({
            creator: creatorId,
        }),
    );
}
</script>

<template>
    <Head title="Creators" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 md:p-6">
            <section
                class="flex flex-col gap-4 rounded-[2rem] bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <p class="font-mono text-xs uppercase tracking-[0.35em] text-[#d16d4f]">
                        Admin only
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold">Creator accounts</h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Create and maintain provider accounts used for catalog ownership and sales reporting.
                    </p>
                </div>
                <Link
                    :href="createCreator.url()"
                    class="rounded-full bg-[#241914] px-5 py-3 text-sm font-medium text-white"
                >
                    New Creator
                </Link>
            </section>

            <section
                v-if="creators.data.length"
                class="overflow-hidden rounded-[1.75rem] border border-black/5 bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#fbfaf8] text-muted-foreground">
                            <tr>
                                <th class="px-6 py-4">Creator</th>
                                <th class="px-6 py-4">Published</th>
                                <th class="px-6 py-4">Stocked</th>
                                <th class="px-6 py-4">Joined</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="creator in creators.data"
                                :key="creator.id"
                                class="border-t border-black/5"
                            >
                                <td class="px-6 py-5">
                                    <div class="font-medium">{{ creator.name }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ creator.email }}
                                    </div>
                                </td>
                                <td class="px-6 py-5">{{ creator.provided_contents_count }}</td>
                                <td class="px-6 py-5">{{ creator.stocked_contents_count }}</td>
                                <td class="px-6 py-5">{{ creator.created_at || 'N/A' }}</td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-end gap-3">
                                        <Link
                                            :href="editCreator.url({ creator: creator.id })"
                                            class="rounded-full border border-black/10 px-4 py-2 text-sm"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            type="button"
                                            class="rounded-full bg-rose-50 px-4 py-2 text-sm text-rose-600"
                                            @click="destroyCreatorAccount(creator.id)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section
                v-else
                class="rounded-[1.75rem] border border-dashed border-black/10 bg-white p-10 text-center shadow-sm"
            >
                <h2 class="text-2xl font-semibold">No creators yet</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Add the first creator account to start assigning content ownership.
                </p>
                <Link
                    :href="createCreator.url()"
                    class="mt-6 inline-flex rounded-full bg-[#241914] px-5 py-3 text-sm font-medium text-white"
                >
                    Create Creator
                </Link>
            </section>
        </div>
    </AppLayout>
</template>
