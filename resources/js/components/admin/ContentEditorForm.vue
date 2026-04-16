<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import {
    store as storeContent,
    update as updateContent,
} from '@/actions/App/Http/Controllers/Admin/ManagedContentController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    mode: 'create' | 'edit';
    providers: Array<{
        id: number;
        name: string;
        email: string;
        price_options: Array<{
            id: number;
            price: number;
            formatted_price: string;
            product_code: string;
        }>;
    }>;
    genres: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
    content?: {
        id: number;
        sku: string | null;
        title: string;
        slug: string;
        description: string;
        price: number | null;
        formatted_price: string | null;
        provider_price_option_id: number | null;
        product_code: string | null;
        cover_url: string | null;
        preview_urls: string[];
        download_name: string;
        provider_id: number;
        provider_name: string;
        genre_id: number;
        genre_name: string;
        tag_names: string;
    };
}>();

const form = useForm({
    provider_id: props.content?.provider_id?.toString() || '',
    provider_price_option_id: props.content?.provider_price_option_id?.toString() || '',
    genre_id: props.content?.genre_id?.toString() || '',
    title: props.content?.title || '',
    description: props.content?.description || '',
    tag_names: props.content?.tag_names || '',
    cover_image: null as File | null,
    preview_images: [] as File[],
    download_file: null as File | null,
});

const submitLabel = computed(() =>
    props.mode === 'create' ? 'Create Content' : 'Save Changes',
);

const selectedProvider = computed(() =>
    props.providers.find((provider) => provider.id.toString() === form.provider_id) || null,
);

const selectedPriceOption = computed(() =>
    selectedProvider.value?.price_options.find(
        (priceOption) => priceOption.id.toString() === form.provider_price_option_id,
    ) || null,
);

watch(
    () => form.provider_id,
    () => {
        if (
            !selectedProvider.value?.price_options.some(
                (priceOption) => priceOption.id.toString() === form.provider_price_option_id,
            )
        ) {
            form.provider_price_option_id = '';
        }
    },
);

function onCoverChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    form.cover_image = target.files?.[0] || null;
}

function onPreviewChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    form.preview_images = target.files ? Array.from(target.files) : [];
}

function onDownloadChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    form.download_file = target.files?.[0] || null;
}

function submit(): void {
    if (props.mode === 'create') {
        form.post(storeContent.url(), {
            forceFormData: true,
        });

        return;
    }

    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(
        updateContent.url({
            content: props.content?.slug || '',
        }),
        {
            forceFormData: true,
        },
    );
}
</script>

<template>
    <form class="space-y-8" @submit.prevent="submit">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-2">
                <Label for="provider_id">Content Creator</Label>
                <select
                    id="provider_id"
                    v-model="form.provider_id"
                    class="w-full rounded-2xl border border-input bg-background px-4 py-3 text-sm"
                >
                    <option value="">
                        {{ mode === 'create' ? 'Optional for stocked content' : 'Select a creator' }}
                    </option>
                    <option
                        v-for="provider in providers"
                        :key="provider.id"
                        :value="provider.id.toString()"
                    >
                        {{ provider.name }} ({{ provider.email }})
                    </option>
                </select>
                <p v-if="mode === 'create'" class="text-xs text-muted-foreground">
                    Leave blank to save this item as stocked content for later provider assignment.
                </p>
                <InputError :message="form.errors.provider_id" />
            </div>

            <div class="space-y-2">
                <Label for="genre_id">Genre</Label>
                <select
                    id="genre_id"
                    v-model="form.genre_id"
                    class="w-full rounded-2xl border border-input bg-background px-4 py-3 text-sm"
                >
                    <option value="">Select a genre</option>
                    <option
                        v-for="genre in genres"
                        :key="genre.id"
                        :value="genre.id.toString()"
                    >
                        {{ genre.name }}
                    </option>
                </select>
                <InputError :message="form.errors.genre_id" />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="title">Title</Label>
            <Input id="title" v-model="form.title" />
            <InputError :message="form.errors.title" />
        </div>

        <div class="grid gap-6 lg:grid-cols-[0.6fr_1.4fr]">
            <div class="space-y-2">
                <Label for="provider_price_option_id">Price Set</Label>
                <select
                    id="provider_price_option_id"
                    v-model="form.provider_price_option_id"
                    class="w-full rounded-2xl border border-input bg-background px-4 py-3 text-sm"
                    :disabled="!selectedProvider"
                >
                    <option value="">
                        {{
                            mode === 'create'
                                ? 'Optional for stocked content'
                                : 'Select a price option'
                        }}
                    </option>
                    <option
                        v-for="priceOption in selectedProvider?.price_options || []"
                        :key="priceOption.id"
                        :value="priceOption.id.toString()"
                    >
                        {{ priceOption.formatted_price }} / {{ priceOption.product_code }}
                    </option>
                </select>
                <p v-if="mode === 'create'" class="text-xs text-muted-foreground">
                    If provider or price option is missing, the upload is stored as stocked content.
                </p>
                <p
                    v-else-if="content?.provider_price_option_id === null"
                    class="text-xs text-amber-700"
                >
                    This content uses a legacy price. Select a creator price option before saving.
                </p>
                <InputError :message="form.errors.provider_price_option_id" />
            </div>

            <div class="space-y-2">
                <Label for="tag_names">Tags</Label>
                <Input
                    id="tag_names"
                    v-model="form.tag_names"
                    placeholder="anime, manga, cinematic"
                />
                <p class="text-xs text-muted-foreground">
                    Add multiple tags separated by commas.
                </p>
                <InputError :message="form.errors.tag_names" />
            </div>
        </div>

        <div
            v-if="selectedPriceOption || (mode === 'edit' && content?.provider_price_option_id === null)"
            class="rounded-[1.5rem] border border-black/5 bg-[#fbfaf8] p-5"
        >
            <template v-if="selectedPriceOption">
                <p class="text-xs tracking-[0.24em] text-muted-foreground uppercase">
                    Selected Price Set
                </p>
                <div class="mt-3 flex flex-wrap items-end gap-4">
                    <div>
                        <p class="text-2xl font-semibold">{{ selectedPriceOption.formatted_price }}</p>
                        <p class="text-xs text-muted-foreground">
                            Provider-defined selling price
                        </p>
                    </div>
                    <div class="rounded-full border border-black/10 px-4 py-2 text-sm font-medium">
                        {{ selectedPriceOption.product_code }}
                    </div>
                </div>
            </template>
            <template v-else>
                <p class="text-sm font-medium text-amber-950">
                    Legacy price: {{ content?.formatted_price }}
                </p>
                <p class="mt-2 text-sm text-amber-900">
                    This listing can still be viewed, but updates now require one of the creator&apos;s configured price sets.
                </p>
            </template>
        </div>

        <div class="space-y-2">
            <Label for="description">Description</Label>
            <textarea
                id="description"
                v-model="form.description"
                rows="7"
                class="w-full rounded-[1.5rem] border border-input bg-background px-4 py-3 text-sm leading-7"
            />
            <InputError :message="form.errors.description" />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-3 rounded-[1.5rem] border border-black/5 bg-[#fbfaf8] p-4">
                <div>
                    <p class="text-sm font-medium">Cover Image</p>
                    <p class="text-xs text-muted-foreground">
                        Thumbnail shown on listings
                    </p>
                </div>
                <img
                    v-if="content?.cover_url"
                    :src="content.cover_url"
                    alt="cover preview"
                    class="h-40 w-full rounded-2xl object-cover"
                />
                <input type="file" accept="image/*" @change="onCoverChange" />
                <InputError :message="form.errors.cover_image" />
            </div>

            <div class="space-y-3 rounded-[1.5rem] border border-black/5 bg-[#fbfaf8] p-4">
                <div>
                    <p class="text-sm font-medium">Preview Images</p>
                    <p class="text-xs text-muted-foreground">Up to 3 files</p>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <img
                        v-for="preview in content?.preview_urls || []"
                        :key="preview"
                        :src="preview"
                        alt="preview image"
                        class="h-20 w-full rounded-xl object-cover"
                    />
                </div>
                <input
                    type="file"
                    accept="image/*"
                    multiple
                    @change="onPreviewChange"
                />
                <InputError :message="form.errors.preview_images" />
            </div>

            <div class="space-y-3 rounded-[1.5rem] border border-black/5 bg-[#fbfaf8] p-4">
                <div>
                    <p class="text-sm font-medium">Delivery File</p>
                    <p class="text-xs text-muted-foreground">
                        zip / image / video
                    </p>
                </div>
                <p v-if="content?.download_name" class="text-sm text-muted-foreground">
                    current: {{ content.download_name }}
                </p>
                <input type="file" @change="onDownloadChange" />
                <InputError :message="form.errors.download_file" />
            </div>
        </div>

        <div class="flex justify-end">
            <Button :disabled="form.processing" class="rounded-full px-6">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
