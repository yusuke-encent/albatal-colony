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
    defaultPriceOptions?: number[];
    creator?: {
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

const form = useForm({
    name: props.creator?.name || '',
    email: props.creator?.email || '',
    apc_merchant_id: props.creator?.apc_merchant_id?.toString() || '',
    password: '',
    password_confirmation: '',
    new_price_option: '',
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
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    placeholder="creator@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>
        </div>

        <div
            v-if="mode === 'create'"
            class="rounded-[1.5rem] border border-dashed border-amber-300 bg-amber-50/80 p-5"
        >
            <p class="text-sm font-medium text-amber-950">
                Initial password is generated automatically.
            </p>
            <p class="mt-2 text-sm leading-6 text-amber-900">
                After the account is created, the generated password will be
                shown once so you can share it securely.
            </p>
            <div class="mt-4">
                <p class="text-xs font-medium tracking-[0.24em] text-amber-900 uppercase">
                    Default Price Set
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span
                        v-for="price in defaultPriceOptions || []"
                        :key="price"
                        class="rounded-full border border-amber-300 bg-white px-3 py-1 text-sm text-amber-950"
                    >
                        {{ price.toLocaleString() }} JPY
                    </span>
                </div>
            </div>
        </div>

        <div v-else class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-2">
                <Label for="apc_merchant_id">APC Merchant ID</Label>
                <Input
                    id="apc_merchant_id"
                    v-model="form.apc_merchant_id"
                    type="number"
                    min="1"
                    placeholder="1001"
                />
                <p class="text-xs text-muted-foreground">
                    Product codes are generated from this numeric merchant ID and the selected price.
                </p>
                <InputError :message="form.errors.apc_merchant_id" />
            </div>

            <div class="space-y-2">
                <Label for="password">New Password</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Leave blank to keep the current password"
                />
                <p class="text-xs text-muted-foreground">
                    Leave both password fields blank if you only want to update
                    profile details.
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
                <p
                    class="text-xs tracking-[0.24em] text-muted-foreground uppercase"
                >
                    Published
                </p>
                <p class="mt-2 text-2xl font-semibold">
                    {{ creator.provided_contents_count }}
                </p>
            </div>
            <div>
                <p
                    class="text-xs tracking-[0.24em] text-muted-foreground uppercase"
                >
                    Stocked
                </p>
                <p class="mt-2 text-2xl font-semibold">
                    {{ creator.stocked_contents_count }}
                </p>
            </div>
            <div>
                <p
                    class="text-xs tracking-[0.24em] text-muted-foreground uppercase"
                >
                    APC Merchant
                </p>
                <p class="mt-2 text-2xl font-semibold">
                    {{ creator.apc_merchant_id }}
                </p>
            </div>
            <div>
                <p
                    class="text-xs tracking-[0.24em] text-muted-foreground uppercase"
                >
                    Joined
                </p>
                <p class="mt-2 text-sm font-medium">
                    {{ creator.created_at || 'N/A' }}
                </p>
            </div>
        </div>

        <div
            v-if="creator"
            class="space-y-5 rounded-[1.5rem] border border-black/5 bg-white p-5"
        >
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs tracking-[0.24em] text-muted-foreground uppercase">
                        Price Sets
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Existing creator prices and their deterministic product codes.
                    </p>
                </div>
                <div class="w-full max-w-xs space-y-2">
                    <Label for="new_price_option">Add Price</Label>
                    <Input
                        id="new_price_option"
                        v-model="form.new_price_option"
                        type="number"
                        min="1"
                        placeholder="5500"
                    />
                    <InputError :message="form.errors.new_price_option" />
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="priceOption in creator.price_options"
                    :key="priceOption.id"
                    class="rounded-[1.25rem] border border-black/5 bg-[#fbfaf8] p-4"
                >
                    <p class="text-lg font-semibold">{{ priceOption.formatted_price }}</p>
                    <p class="mt-2 text-xs tracking-[0.2em] text-muted-foreground uppercase">
                        Product Code
                    </p>
                    <p class="mt-1 font-mono text-sm text-[#241914]">
                        {{ priceOption.product_code }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
