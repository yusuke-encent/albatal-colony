<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    destroyPriceOption,
    updatePriceOption,
} from '@/actions/App/Http/Controllers/Admin/CreatorManagementController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    creatorId: number;
    priceOption: {
        id: number;
        price: number;
        formatted_price: string;
        product_code: string | null;
    };
}>();

const form = useForm({
    price: props.priceOption.price.toString(),
    product_code: props.priceOption.product_code || '',
});

function submit(): void {
    form.patch(
        updatePriceOption.url({
            creator: props.creatorId,
            priceOption: props.priceOption.id,
        }),
    );
}

function remove(): void {
    form.delete(
        destroyPriceOption.url({
            creator: props.creatorId,
            priceOption: props.priceOption.id,
        }),
    );
}
</script>

<template>
    <form class="space-y-4 rounded-[1.25rem] border border-black/5 bg-[#fbfaf8] p-4" @submit.prevent="submit">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <Label :for="`price-${priceOption.id}`">Price</Label>
                <Input
                    :id="`price-${priceOption.id}`"
                    v-model="form.price"
                    type="number"
                    min="1"
                />
                <InputError :message="form.errors.price" />
            </div>

            <div class="space-y-2">
                <Label :for="`product-code-${priceOption.id}`">Product Code</Label>
                <Input
                    :id="`product-code-${priceOption.id}`"
                    v-model="form.product_code"
                    placeholder="TT10001234"
                />
                <InputError :message="form.errors.product_code" />
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 text-sm text-muted-foreground">
            <span>Current: {{ priceOption.formatted_price }}</span>
            <div class="flex gap-2">
                <Button type="button" variant="outline" :disabled="form.processing" @click="remove">
                    Delete
                </Button>
                <Button type="submit" :disabled="form.processing">
                    Save
                </Button>
            </div>
        </div>
    </form>
</template>
