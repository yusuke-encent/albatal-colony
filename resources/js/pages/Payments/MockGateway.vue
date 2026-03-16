<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    paymentSession: {
        token: string;
        external_reference: string;
        status: string;
        redirect_url: string | null;
        purchase: {
            id: number;
            price: number;
            currency: string;
            content_title: string;
        };
    };
}>();

const approveForm = useForm({
    approved: true,
});

const rejectForm = useForm({
    approved: false,
});

function approve(): void {
    approveForm.post(`/mock-gateway/sessions/${props.paymentSession.token}/complete`);
}

function reject(): void {
    rejectForm.post(`/mock-gateway/sessions/${props.paymentSession.token}/complete`);
}
</script>

<template>
    <Head title="Mock Gateway" />

    <div class="flex min-h-screen items-center justify-center bg-[#121212] px-6 py-12 text-white">
        <div class="w-full max-w-xl rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-[0_30px_120px_rgba(0,0,0,0.45)] backdrop-blur">
            <p class="font-mono text-xs uppercase tracking-[0.35em] text-[#d7a68d]">
                Hosted payment page
            </p>
            <h1 class="mt-4 text-3xl font-semibold">
                {{ paymentSession.purchase.content_title }}
            </h1>
            <div class="mt-8 space-y-3 rounded-[1.5rem] bg-white/5 p-5 text-sm text-white/75">
                <div class="flex items-center justify-between">
                    <span>Reference</span>
                    <span>{{ paymentSession.external_reference }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Amount</span>
                    <span>
                        {{ paymentSession.purchase.price.toLocaleString() }}
                        {{ paymentSession.purchase.currency }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Status</span>
                    <span class="uppercase">{{ paymentSession.status }}</span>
                </div>
            </div>

            <div class="mt-8 grid gap-3 md:grid-cols-2">
                <button
                    type="button"
                    class="rounded-full bg-[#d16d4f] px-5 py-3 text-sm font-medium text-white disabled:opacity-60"
                    :disabled="approveForm.processing || rejectForm.processing"
                    @click="approve"
                >
                    Approve Payment
                </button>
                <button
                    type="button"
                    class="rounded-full border border-white/20 px-5 py-3 text-sm font-medium text-white disabled:opacity-60"
                    :disabled="approveForm.processing || rejectForm.processing"
                    @click="reject"
                >
                    Fail Payment
                </button>
            </div>
        </div>
    </div>
</template>
