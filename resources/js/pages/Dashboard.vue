<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    role: 'admin' | 'provider' | 'customer';
    stats: {
        contents: number;
        paid_purchases: number;
        revenue: number;
        users: number;
    };
    highlights: {
        recentSales: Array<{
            id: number;
            content_sku: string | null;
            content_title: string;
            provider_name: string;
            buyer_name: string;
            price: number;
            created_at: string | null;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 md:p-6">
            <section class="rounded-[2rem] bg-[linear-gradient(135deg,#17120f_0%,#513b31_50%,#d06d4f_100%)] p-8 text-white">
                <p class="text-xs uppercase tracking-[0.35em] text-white/70">
                    {{ role }}
                </p>
                <h1 class="mt-3 font-serif text-4xl">Marketplace dashboard</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-white/80">
                    Track key metrics by role and monitor the latest global sales of Japanese content.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <Link href="/" class="rounded-full bg-white px-5 py-2.5 text-sm font-medium text-[#241914]">
                        Storefront
                    </Link>
                    <Link href="/library" class="rounded-full border border-white/20 px-5 py-2.5 text-sm font-medium text-white">
                        Library
                    </Link>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-[1.75rem] border border-black/5 bg-white p-5 text-[#241914] shadow-sm">
                    <p class="text-sm text-[#6d4d40]">Contents</p>
                    <p class="mt-3 text-3xl font-semibold">{{ stats.contents }}</p>
                </article>
                <article class="rounded-[1.75rem] border border-black/5 bg-white p-5 text-[#241914] shadow-sm">
                    <p class="text-sm text-[#6d4d40]">Paid Purchases</p>
                    <p class="mt-3 text-3xl font-semibold">{{ stats.paid_purchases }}</p>
                </article>
                <article class="rounded-[1.75rem] border border-black/5 bg-white p-5 text-[#241914] shadow-sm">
                    <p class="text-sm text-[#6d4d40]">Revenue</p>
                    <p class="mt-3 text-3xl font-semibold">
                        {{ stats.revenue.toLocaleString() }} JPY
                    </p>
                </article>
                <article class="rounded-[1.75rem] border border-black/5 bg-white p-5 text-[#241914] shadow-sm">
                    <p class="text-sm text-[#6d4d40]">Users</p>
                    <p class="mt-3 text-3xl font-semibold">{{ stats.users }}</p>
                </article>
            </section>

            <section class="rounded-[1.75rem] border border-black/5 bg-white p-6 text-[#241914] shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold">Recent Activity</h2>
                        <p class="mt-1 text-sm text-[#6d4d40]">
                            Latest sales and purchase activity.
                        </p>
                    </div>
                </div>

                <div v-if="highlights.recentSales.length" class="mt-6 space-y-3">
                    <div
                        v-for="sale in highlights.recentSales"
                        :key="sale.id"
                        class="flex flex-col gap-3 rounded-2xl border border-black/5 px-4 py-4 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <p class="font-medium">{{ sale.content_title }}</p>
                            <p class="mt-1 text-xs text-[#8d5b4c]">
                                SKU: {{ sale.content_sku || '-' }}
                            </p>
                            <p class="mt-1 text-sm text-[#6d4d40]">
                                buyer: {{ sale.buyer_name }} / provider: {{ sale.provider_name }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">{{ sale.price.toLocaleString() }} JPY</p>
                            <p class="mt-1 text-sm text-[#6d4d40]">
                                {{ sale.created_at }}
                            </p>
                        </div>
                    </div>
                </div>
                <div
                    v-else
                    class="mt-6 rounded-2xl border border-dashed border-black/10 px-4 py-10 text-center text-sm text-[#6d4d40]"
                >
                    No activity to display.
                </div>
            </section>
        </div>
    </AppLayout>
</template>
