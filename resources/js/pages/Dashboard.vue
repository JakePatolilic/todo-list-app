<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-16">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                <div class="overflow-hidden bg-white/90 backdrop-blur-sm shadow-xl sm:rounded-2xl p-8 text-gray-900 border border-white/20 hover:shadow-2xl transition-all duration-300">
                    <p class="text-2xl font-bold">Welcome, {{ $page.props.auth.user.name }}!</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                    <div class="group p-8 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 border border-green-100 hover:scale-105">
                        <p class="text-sm font-medium text-green-600 uppercase tracking-wider">Completed Tasks</p>
                        <p class="text-3xl font-bold text-green-700 mt-2">{{ $page.props.completedCount }}</p>
                    </div>

                    <div class="group p-8 bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 border border-orange-100 hover:scale-105">
                        <p class="text-sm font-medium text-orange-600 uppercase tracking-wider">Pending Tasks</p>
                        <p class="text-3xl font-bold text-orange-700 mt-2">{{ $page.props.pendingCount }}</p>
                    </div>

                    <div class="group p-8 bg-gradient-to-br from-red-50 to-pink-50 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 border border-red-100 hover:scale-105">
                        <p class="text-sm font-medium text-red-600 uppercase tracking-wider">Overdue Tasks</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ $page.props.overdueCount }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
