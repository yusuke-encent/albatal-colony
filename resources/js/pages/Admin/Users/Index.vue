<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            role: 'admin' | 'provider' | 'customer';
            provided_contents_count: number;
            purchases_count: number;
            created_at: string | null;
        }>;
    };
}>();

const createForm = useForm({
    name: '',
    email: '',
    role: 'provider',
    password: '',
    password_confirmation: '',
});

const roleForm = useForm({
    role: 'customer',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/admin/users',
    },
];

function submitCreate(): void {
    createForm.post('/admin/users');
}

function updateRole(userId: number, role: string): void {
    roleForm.role = role;
    roleForm.patch(`/admin/users/${userId}/role`);
}

function handleRoleChange(event: Event, userId: number): void {
    const target = event.target as HTMLSelectElement | null;

    if (!target) {
        return;
    }

    updateRole(userId, target.value);
}
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 md:p-6">
            <section class="rounded-[1.75rem] bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold">Create user</h1>
                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <input
                        v-model="createForm.name"
                        type="text"
                        placeholder="Name"
                        class="rounded-2xl border border-input px-4 py-3 text-sm"
                    />
                    <input
                        v-model="createForm.email"
                        type="email"
                        placeholder="Email"
                        class="rounded-2xl border border-input px-4 py-3 text-sm"
                    />
                    <select
                        v-model="createForm.role"
                        class="rounded-2xl border border-input px-4 py-3 text-sm"
                    >
                        <option value="admin">admin</option>
                        <option value="provider">provider</option>
                        <option value="customer">customer</option>
                    </select>
                    <input
                        v-model="createForm.password"
                        type="password"
                        placeholder="Password"
                        class="rounded-2xl border border-input px-4 py-3 text-sm"
                    />
                    <input
                        v-model="createForm.password_confirmation"
                        type="password"
                        placeholder="Password Confirmation"
                        class="rounded-2xl border border-input px-4 py-3 text-sm"
                    />
                </div>
                <button
                    type="button"
                    class="mt-5 rounded-full bg-[#241914] px-5 py-3 text-sm font-medium text-white"
                    :disabled="createForm.processing"
                    @click="submitCreate"
                >
                    Create User
                </button>
            </section>

            <section class="rounded-[1.75rem] bg-white p-6 shadow-sm">
                <h2 class="text-2xl font-semibold">User list</h2>
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-muted-foreground">
                            <tr>
                                <th class="pb-3">User</th>
                                <th class="pb-3">Role</th>
                                <th class="pb-3">Contents</th>
                                <th class="pb-3">Purchases</th>
                                <th class="pb-3">Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in props.users.data"
                                :key="user.id"
                                class="border-t border-black/5"
                            >
                                <td class="py-4">
                                    <div class="font-medium">{{ user.name }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ user.email }}
                                    </div>
                                </td>
                                <td class="py-4">
                                    <select
                                        :value="user.role"
                                        class="rounded-full border border-input px-3 py-2 text-sm"
                                        @change="handleRoleChange($event, user.id)"
                                    >
                                        <option value="admin">admin</option>
                                        <option value="provider">provider</option>
                                        <option value="customer">customer</option>
                                    </select>
                                </td>
                                <td class="py-4">{{ user.provided_contents_count }}</td>
                                <td class="py-4">{{ user.purchases_count }}</td>
                                <td class="py-4">{{ user.created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
