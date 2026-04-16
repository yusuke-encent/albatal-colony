<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    store as storeCreator,
    storePriceOption,
    update as updateCreator,
} from '@/actions/App/Http/Controllers/Admin/CreatorManagementController';
import CreatorPriceOptionRow from '@/components/admin/CreatorPriceOptionRow.vue';
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
        apc_merchant_id: number;
        provided_contents_count: number;
        stocked_contents_count: number;
        created_at: string | null;
        price_options: Array<{
            id: number;
            price: number;
            formatted_price: string;
            product_code: string | null;
        }>;
    };
}>();

const form = useForm({
    name: props.creator?.name || '',
    email: props.creator?.email || '',
    apc_merchant_id: props.creator?.apc_merchant_id?.toString() || '',
    password: '',
    password_confirmation: '',
});

const createPriceOptionForm = useForm({
    price: '',
    product_code: '',
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

function createPriceOption(): void {
    if (! props.creator) {
        return;
    }

    createPriceOptionForm.post(
        storePriceOption.url({
            creator: props.creator.id,
        }),
        {
            onSuccess: () => createPriceOptionForm.reset(),
        },
    );
}
</script>

<template>
    <div class="space-y-8">
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
                        This merchant ID is stored separately. Price option product codes can be edited independently below.
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

            <div class="flex justify-end">
                <Button type="submit" :disabled="form.processing">
                    {{ submitLabel }}
                </Button>
            </div>
        </form>

        <section
            v-if="creator"
            class="space-y-5 rounded-[1.5rem] border border-black/5 bg-white p-5"
        >
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs tracking-[0.24em] text-muted-foreground uppercase">
                        Price Sets
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Stored price and product code pairs used for creator selection.
                    </p>
                </div>
            </div>

            <form
                class="grid gap-4 rounded-[1.25rem] border border-dashed border-black/10 bg-white p-4 md:grid-cols-[0.8fr_1.2fr_auto]"
                @submit.prevent="createPriceOption"
            >
                <div class="space-y-2">
                    <Label for="new-price-option-price">Price</Label>
                    <Input
                        id="new-price-option-price"
                        v-model="createPriceOptionForm.price"
                        type="number"
                        min="1"
                        placeholder="5500"
                    />
                    <InputError :message="createPriceOptionForm.errors.price" />
                </div>
                <div class="space-y-2">
                    <Label for="new-price-option-code">Product Code</Label>
                    <Input
                        id="new-price-option-code"
                        v-model="createPriceOptionForm.product_code"
                        placeholder="TT10001234"
                    />
                    <InputError :message="createPriceOptionForm.errors.product_code" />
                </div>
                <div class="flex items-end">
                    <Button type="submit" :disabled="createPriceOptionForm.processing" class="w-full md:w-auto">
                        Add Pair
                    </Button>
                </div>
            </form>

            <div class="grid gap-3">
                <CreatorPriceOptionRow
                    v-for="priceOption in creator.price_options"
                    :key="priceOption.id"
                    :creator-id="creator.id"
                    :price-option="priceOption"
                />
            </div>
        </section>
    </div>
</template>
