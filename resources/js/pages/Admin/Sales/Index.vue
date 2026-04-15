<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    summary: {
        revenue: number;
        paid_purchases: number;
    };
    sales: {
        data: Array<{
            id: number;
            price: number;
            currency: string;
            purchased_at: string | null;
            buyer: {
                id: number;
                name: string;
                email: string;
            };
            content: {
                id: number;
                title: string;
                sku: string | null;
            };
            provider: {
                id: number;
                name: string;
            };
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sales Report',
        href: '/admin/sales',
    },
];
</script>

<template>
    <Head title="Sales Report" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 md:p-6">
            <section class="grid gap-4 md:grid-cols-2">
                <article class="rounded-[1.75rem] bg-white p-6 shadow-sm">
                    <p class="text-sm text-muted-foreground">Revenue</p>
                    <p class="mt-3 text-3xl font-semibold">
                        {{ summary.revenue.toLocaleString() }} JPY
                    </p>
                </article>
                <article class="rounded-[1.75rem] bg-white p-6 shadow-sm">
                    <p class="text-sm text-muted-foreground">Paid Purchases</p>
                    <p class="mt-3 text-3xl font-semibold">{{ summary.paid_purchases }}</p>
                </article>
            </section>

            <section class="rounded-[1.75rem] bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold">Sales list</h1>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-muted-foreground">
                            <tr>
                                <th class="pb-3">Content</th>
                                <th class="pb-3">Creator</th>
                                <th class="pb-3">Buyer</th>
                                <th class="pb-3">Amount</th>
                                <th class="pb-3">Purchased At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="sale in sales.data"
                                :key="sale.id"
                                class="border-t border-black/5"
                            >
                                <td class="py-4">
                                    <div class="font-medium">{{ sale.content.title }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ sale.content.sku }}
                                    </div>
                                </td>
                                <td class="py-4">{{ sale.provider.name }}</td>
                                <td class="py-4">
                                    <div>{{ sale.buyer.name }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ sale.buyer.email }}
                                    </div>
                                </td>
                                <td class="py-4">
                                    {{ sale.price.toLocaleString() }} {{ sale.currency }}
                                </td>
                                <td class="py-4">{{ sale.purchased_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
