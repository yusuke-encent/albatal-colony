<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
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
    genre_id: props.content?.genre_id?.toString() || '',
    title: props.content?.title || '',
    description: props.content?.description || '',
    price: props.content?.price?.toString() || '',
    tag_names: props.content?.tag_names || '',
    cover_image: null as File | null,
    preview_images: [] as File[],
    download_file: null as File | null,
});

const submitLabel = computed(() =>
    props.mode === 'create' ? 'Create Content' : 'Save Changes',
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
        form.post('/admin/contents', {
            forceFormData: true,
        });

        return;
    }

    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/admin/contents/${props.content?.slug}`, {
        forceFormData: true,
    });
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
                <Label for="price">Price (JPY)</Label>
                <Input
                    id="price"
                    v-model="form.price"
                    type="number"
                    min="100"
                    :placeholder="mode === 'create' ? 'Optional for stocked content' : undefined"
                />
                <p v-if="mode === 'create'" class="text-xs text-muted-foreground">
                    If provider or price is missing, the upload is stored as stocked content.
                </p>
                <InputError :message="form.errors.price" />
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
