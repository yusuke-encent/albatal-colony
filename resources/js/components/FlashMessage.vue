<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const success = computed(() => page.props.flash?.success);
const error = computed(() => page.props.flash?.error);
const generatedPassword = computed(() => page.props.flash?.generated_password);
</script>

<template>
    <div
        v-if="success || error || generatedPassword"
        class="space-y-3 px-4 pt-4 md:px-6"
    >
        <div
            v-if="success"
            class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
        >
            {{ success }}
        </div>
        <div
            v-if="error"
            class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
        >
            {{ error }}
        </div>
        <div
            v-if="generatedPassword"
            class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
        >
            <p class="font-medium">Generated initial password</p>
            <p class="mt-1 text-amber-800">
                Share this securely with the creator. It is shown only once.
            </p>
            <p
                class="mt-3 rounded-xl bg-white/80 px-3 py-2 font-mono text-[0.95rem] tracking-[0.08em]"
            >
                {{ generatedPassword }}
            </p>
        </div>
    </div>
</template>
