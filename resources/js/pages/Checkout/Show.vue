<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    content: {
        id: number;
        title: string;
        slug: string;
        formatted_price: string;
        cover_url: string | null;
        provider_name: string;
        genre_name: string;
        tags: string[];
        download_name: string;
    };
}>();

const form = useForm({});

function submit(): void {
    form.post(`/checkout/${props.content.slug}/start`);
}
</script>

<template>
    <Head title="Checkout" />

    <div class="min-h-screen bg-[#f7f3ec] px-6 py-10 text-[#241914] lg:px-8">
        <div class="mx-auto max-w-5xl">
            <div class="mb-8 flex items-center justify-between">
                <Link :href="`/contents/${content.slug}`" class="text-sm text-[#6d4d40]">
                    Back to Product
                </Link>
                <Link href="/library" class="rounded-full border border-black/10 px-4 py-2 text-sm">
                    Library
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                <section class="rounded-[2rem] bg-white p-7 shadow-[0_20px_60px_rgba(36,25,20,0.1)]">
                    <p class="font-mono text-xs uppercase tracking-[0.3em] text-[#d16d4f]">
                        Mock checkout
                    </p>
                    <h1 class="mt-3 font-serif text-4xl">Order Review</h1>
                    <p class="mt-4 text-sm leading-8 text-[#5f453b]">
                        The server creates a payment session and redirects to a hosted checkout page.
                        This demo routes to a mock gateway and simulates webhook-style completion.
                    </p>

                    <div class="mt-6 rounded-[1.5rem] bg-[#fff8ef] p-5">
                        <h2 class="text-lg font-semibold">{{ content.title }}</h2>
                        <div class="mt-4 space-y-2 text-sm text-[#5f453b]">
                            <p>Provider: {{ content.provider_name }}</p>
                            <p>Genre: {{ content.genre_name }}</p>
                            <p>Download file: {{ content.download_name }}</p>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-for="tag in content.tags"
                                :key="tag"
                                class="rounded-full bg-[#f3e3d2] px-3 py-1 text-xs text-[#6d4d40]"
                            >
                                {{ tag }}
                            </span>
                        </div>
                    </div>
                </section>

                <aside class="rounded-[2rem] bg-[#241914] p-7 text-white shadow-[0_20px_60px_rgba(36,25,20,0.18)]">
                    <img
                        :src="content.cover_url || 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80'"
                        :alt="content.title"
                        class="h-56 w-full rounded-[1.5rem] object-cover"
                    />
                    <div class="mt-6">
                        <p class="text-sm text-white/65">Total</p>
                        <p class="mt-2 text-4xl font-semibold">{{ content.formatted_price }}</p>
                    </div>
                    <button
                        type="button"
                        class="mt-8 w-full rounded-full bg-[#d16d4f] px-5 py-3 text-sm font-medium text-white disabled:opacity-60"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        Continue to Payment
                    </button>
                </aside>
            </div>
        </div>
    </div>
</template>
