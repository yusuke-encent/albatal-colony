<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    store as storeCreator,
    update as updateCreator,
} from '@/actions/App/Http/Controllers/Admin/CreatorManagementController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    mode: 'create' | 'edit';
    creator?: {
        id: number;
        name: string;
        email: string;
        provided_contents_count: number;
        stocked_contents_count: number;
        created_at: string | null;
    };
}>();

const form = useForm({
    name: props.creator?.name || '',
    email: props.creator?.email || '',
    password: '',
    password_confirmation: '',
});

const submitLabel = computed(() =>
    props.mode === 'create' ? 'Create Creator' : 'Save Changes',
);

function submit(): void {
    if (props.mode === 'create') {
        form.post(storeCreator.url());

        return;
    }

    form.put(
        updateCreator.url({
            creator: props.creator?.id || 0,
        }),
    );
}
</script>

<template>
    <form class="space-y-8" @submit.prevent="submit">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-2">
                <Label for="name">Display Name</Label>
                <Input id="name" v-model="form.name" placeholder="Aki Tanaka" />
                <InputError :message="form.errors.name" />
            </div>

            <div class="space-y-2">
                <Label for="email">Login Email</Label>
                <Input id="email" v-model="form.email" type="email" placeholder="creator@example.com" />
                <InputError :message="form.errors.email" />
            </div>
        </div>

        <div
            v-if="mode === 'create'"
            class="rounded-[1.5rem] border border-dashed border-amber-300 bg-amber-50/80 p-5"
        >
            <p class="text-sm font-medium text-amber-950">Initial password is generated automatically.</p>
            <p class="mt-2 text-sm leading-6 text-amber-900">
                After the account is created, the generated password will be shown once so you can share it securely.
            </p>
        </div>

        <div v-else class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-2">
                <Label for="password">New Password</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Leave blank to keep the current password"
                />
                <p class="text-xs text-muted-foreground">
                    Leave both password fields blank if you only want to update profile details.
                </p>
                <InputError :message="form.errors.password" />
            </div>

            <div class="space-y-2">
                <Label for="password_confirmation">Password Confirmation</Label>
                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    placeholder="Repeat the password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>
        </div>

        <div
            v-if="creator"
            class="grid gap-4 rounded-[1.5rem] border border-black/5 bg-[#fbfaf8] p-5 md:grid-cols-3"
        >
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-muted-foreground">Published</p>
                <p class="mt-2 text-2xl font-semibold">{{ creator.provided_contents_count }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-muted-foreground">Stocked</p>
                <p class="mt-2 text-2xl font-semibold">{{ creator.stocked_contents_count }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-muted-foreground">Joined</p>
                <p class="mt-2 text-sm font-medium">{{ creator.created_at || 'N/A' }}</p>
            </div>
        </div>

        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
