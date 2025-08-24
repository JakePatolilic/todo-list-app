<script setup>
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    tasks: Object,
    filters: {
        type: Object,
        default: () => ({})
    }
});

const tasks = ref(props.tasks);
const showModal = ref(false);
const showEditModal = ref(false);
const selectedCategory = ref(props.filters?.category || '');
const categories = ref([]);

const form = ref({
    title: '',
    category: '',
    start: '',
    due_date: '',
});

const editForm = ref({
    id: null,
    title: '',
    category: '',
    is_completed: false,
    start: '',
    due_date:''
});

const searchQuery = ref(props.filters?.search || '');
console.log(props.filters);

watch(searchQuery, (newQuery) => {
        performSearch(newQuery);
});

watch(selectedCategory, () => {
    performSearch();
});

onMounted(async () => {
    const { data } = await axios.get('/tasks/categories');
    categories.value = data;
});

const performSearch = (query = searchQuery.value) => {
    const params = {
        search: query?.trim() || '',
        category: selectedCategory.value || '',
    };

    if ((params.search === '') && (params.category === '' || params.category === 'All')) {
        router.get('/tasks', {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                tasks.value = page.props.tasks;
            }
        });
    } else {
        router.get('/tasks/search', params, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                tasks.value = page.props.tasks;
            }
        });
    }
};

const openModal = () => {
    showModal.value = true;
}

const closeModal = () => {
    showModal.value = false;
}

const openEditModal = (task) => {
    showEditModal.value = true;
    editForm.value = { ...task };
}

const closeEditModal = () => {
    showEditModal.value = false;
}

const saveTask = async () => {
    try {
        await axios.post('/tasks', form.value);

        Swal.fire({
            title: 'Success!',
            text: 'Task saved successfully.',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
        });

        if (searchQuery.value.trim() !== '') {
            performSearch(searchQuery.value);
        } else {
            router.get('/tasks', {}, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    tasks.value = page.props.tasks;
                }
            });
        }

        form.value = {
            title: '',
            category: '',
            start: '',
            due_date: '',
        };

        closeModal();
    } catch (error) {
        console.error(error);

        Swal.fire({
            title: 'Error!',
            text: 'Failed to save task. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

const updateTask = async () => {
    try {
        await axios.put(`/tasks/${editForm.value.id}`, editForm.value);

        Swal.fire({
            title: 'Success!',
            text: 'Task updated successfully.',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
        });

        if (searchQuery.value.trim() !== '') {
            performSearch(searchQuery.value);
        } else {
            router.get('/tasks', {}, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    tasks.value = page.props.tasks;
                }
            });
        }

        closeEditModal();
    } catch (error) {
        console.error(error);

        Swal.fire({
            title: 'Error!',
            text: 'Failed to update task. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

const deleteTask = async () => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        await axios.delete(`/tasks/${editForm.value.id}`);

        Swal.fire({
            title: 'Deleted!',
            text: 'Task deleted successfully.',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
        });

        if (searchQuery.value.trim() !== '') {
            performSearch(searchQuery.value);
        } else {
            router.get('/tasks', {}, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    tasks.value = page.props.tasks;
                }
            });
        }
        closeEditModal();
    } catch (error) {
        console.error(error);

        Swal.fire({
            title: 'Error!',
            text: 'Failed to delete task. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

watch(() => form.value.start, (newStart) => {
    if (newStart && form.value.due_date) {
        const startDate = new Date(newStart);
        const dueDate = new Date(form.value.due_date);

        if (startDate > dueDate) {
            form.value.due_date = '';
        }
    }
});

watch(() => editForm.value.start, (newStart) => {
    if (newStart && editForm.value.due_date) {
        const startDate = new Date(newStart);
        const dueDate = new Date(editForm.value.due_date);

        if (startDate > dueDate) {
            editForm.value.due_date = '';
        }
    }
});

</script>

<template>
    <Head title="Tasks" />
    <AuthenticatedLayout>
        <!-- HEADER -->
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tasks</h2>
                <button 
                @click="openModal"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                >
                    Add Task
                </button>
            </div>
        </template>
        <div class="bg-white shadow-xl m-6 sm:rounded-2xl border border-gray-100 p-8 mx-auto max-w-6xl sm:px-8 hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between space-x-6">
                <!-- Search -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Search tasks..." 
                        class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all duration-200 text-gray-700 placeholder-gray-400 bg-gray-50 hover:bg-white focus:bg-white shadow-sm"
                    />
                </div>

                <!-- Filter -->
                <div class="flex items-center space-x-3">
                    <label for="filter" class="text-sm font-semibold text-gray-700 flex items-center whitespace-nowrap">
                        <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                        </svg>
                        Filter:
                    </label>
                    <div class="relative">
                        <select v-model="selectedCategory" 
                                class="appearance-none bg-gray-50 hover:bg-white focus:bg-white border border-gray-200 rounded-xl px-4 py-3 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all duration-200 text-gray-700 cursor-pointer shadow-sm">
                            <option value="">All</option>
                            <option v-for="cat in categories" :key="cat" :value="cat" :title="cat">
                                {{ cat.length > 12 ? cat.slice(0, 12) + '…' : cat }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Task List -->
        <div class="py-6 overflow-y-auto max-h-[500px]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="grid gap-6 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="task in tasks.data"
                        :key="task.id"
                        @click="openEditModal(task)"
                        class="group relative overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100 hover:shadow-lg hover:border-gray-200 transition-all duration-200 hover:-translate-y-1"
                    >
                        <div class="absolute top-0 right-0 w-3 h-3 m-4 rounded-full" 
                            :class="task.is_completed ? 'bg-green-400' : (new Date(task.due_date) < new Date() ? 'bg-red-400' : 'bg-orange-400')">
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <h3 class="text-xl font-semibold text-gray-900 leading-tight group-hover:text-blue-600 transition-colors duration-200"
                            :class="task.title.length > 16 ? 'truncate' : ''"
                            :title="task.title.length > 16 ? task.title : ''"
                            :style="task.title.length > 16 ? 'width: 150px;' : ''">
                                {{ task.title }}
                            </h3>
                            
                            <div class="relative inline-block">
                                <div 
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100 cursor-pointer"
                                    :class="task.category.length > 16 ? 'max-w-32' : ''"
                                    :title="task.category.length > 16 ? task.category : ''"
                                >
                                    <span :class="task.category.length > 16 ? 'truncate block' : ''">
                                        {{ task.category }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="space-y-3 pt-2">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="font-medium">Start:</span>
                                    <span>{{ new Date(task.start).toLocaleDateString('en-US', { 
                                        month: 'short', 
                                        day: 'numeric',
                                        year: 'numeric'
                                    }) }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="font-medium">Due:</span>
                                    <span :class="new Date(task.due_date) < new Date() && !task.is_completed ? 'text-red-600 font-medium' : ''">
                                        {{ new Date(task.due_date).toLocaleDateString('en-US', { 
                                            month: 'short', 
                                            day: 'numeric',
                                            year: 'numeric'
                                        }) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="pt-3 border-t border-gray-50">
                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium"
                                    :class="task.is_completed
                                    ? 'bg-green-50 text-green-700 border border-green-100'
                                    : (new Date(task.due_date) < new Date()
                                    ? 'bg-red-50 text-red-700 border border-red-100'
                                : 'bg-orange-50 text-orange-700 border border-orange-100')">
                                    <div class="w-2 h-2 rounded-full" 
                                        :class="task.is_completed 
                                            ? 'bg-green-400' 
                                            : (new Date(task.due_date) < new Date() 
                                            ? 'bg-red-400' 
                                    : 'bg-orange-400')">
                                    </div>
                                    <span v-if="task.is_completed">Completed</span>
                                    <span v-else-if="new Date(task.due_date) < new Date()">Overdue</span>
                                    <span v-else>In Progress</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Buttons-->
        <div class="flex items-center justify-center mt-8 mb-6 space-x-2" v-if="tasks.links.length > 1">
            <!-- First page button -->
            <button
                @click="router.get(tasks.first_page_url, { search: searchQuery })"
                :disabled="!tasks.first_page_url || tasks.current_page === 1"
                :class="[
                    'flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                    (!tasks.first_page_url || tasks.current_page === 1) 
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200' 
                        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:border-gray-400 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500'
                ]"
                title="First page"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>

            <!-- Previous page button -->
            <button
                @click="tasks.prev_page_url && router.get(tasks.prev_page_url, { search: searchQuery })"
                :disabled="!tasks.prev_page_url"
                :class="[
                    'flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                    !tasks.prev_page_url 
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200' 
                        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:border-gray-400 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500'
                ]"
                title="Previous page"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Previous
            </button>

            <div class="flex items-center px-6 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg">
                <span class="text-sm font-medium text-blue-800">
                    <span class="font-bold text-blue-900 mx-1">{{ tasks.current_page }}</span>
                    of 
                    <span class="font-bold text-blue-900 mx-1">{{ tasks.last_page }}</span>
                </span>
            </div>

            <!-- Next page button -->
            <button
                @click="tasks.next_page_url && router.get(tasks.next_page_url, { search: searchQuery })"
                :disabled="!tasks.next_page_url"
                :class="[
                    'flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                    !tasks.next_page_url 
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200' 
                        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:border-gray-400 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500'
                ]"
                title="Next page"
            >
                Next
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <!-- Last page button -->
            <button
                @click="router.get(tasks.last_page_url, { search: searchQuery })"
                :disabled="!tasks.last_page_url || tasks.current_page === tasks.last_page"
                :class="[
                    'flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                    (!tasks.last_page_url || tasks.current_page === tasks.last_page) 
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200' 
                        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:border-gray-400 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500'
                ]"
                title="Last page"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <!-- Add Task Modal -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click.self="closeModal"
        >
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto transform transition-all duration-300 ease-out">
                <div class="flex items-center justify-between p-6 border-b border-gray-100">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add New Task
                    </h3>
                    <button 
                        type="button" 
                        @click="showModal = false"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <form @submit.prevent="saveTask" class="space-y-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a2 2 0 012-2z"/>
                                </svg>
                                Title <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input 
                                v-model="form.title" 
                                type="text" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 placeholder-gray-400" 
                                placeholder="Enter task title..."
                                required 
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a2 2 0 012-2z"/>
                                </svg>
                                Category <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input 
                                v-model="form.category" 
                                type="text" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 placeholder-gray-400" 
                                placeholder="e.g. Work, Personal, Study..."
                                required
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Start Date <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input 
                                    v-model="form.start" 
                                    type="date"
                                    required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
                                />
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Due Date <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input 
                                    v-model="form.due_date" 
                                    type="date"
                                    required
                                    :min="form.start"
                                    :disabled="!form.start" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200
                                    disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:cursor-not-allowed" 
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                            <button 
                                type="button" 
                                @click="showModal = false" 
                                class="px-6 py-3 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Task Modal -->
        <div 
            v-if="showEditModal" 
            class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click.self="closeEditModal"
        >
            <form 
                @submit.prevent="updateTask" 
                class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto transform transition-all duration-300 ease-out"
            >
                <div class="flex items-center justify-between p-6 pb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Edit Task</h2>
                    <button 
                        type="button" 
                        @click="showEditModal = false"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 pb-6 space-y-5">
                    <div class="space-y-2">
                        <label for="editTitle" class="block text-sm font-medium text-gray-700">Task Title</label>
                        <input 
                            type="text" 
                            v-model="editForm.title" 
                            id="editTitle" 
                            placeholder="Enter task title..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 placeholder-gray-400" 
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="editCategory" class="block text-sm font-medium text-gray-700">Category</label>
                        <input 
                            type="text" 
                            v-model="editForm.category" 
                            id="editCategory" 
                            placeholder="Work, Personal, Study..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 placeholder-gray-400" 
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="editStart" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input 
                                type="date" 
                                v-model="editForm.start" 
                                id="editStart" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
                            />
                        </div>

                        <div class="space-y-2">
                            <label for="editDueDate" class="block text-sm font-medium text-gray-700">Due Date</label>
                            <input 
                                type="date" 
                                v-model="editForm.due_date" 
                                required
                                :min="editForm.start"
                                :disabled="!editForm.start" 
                                id="editDueDate" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200
                                disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:cursor-not-allowed" 
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Completed</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                v-model="editForm.is_completed"
                                true-value="1" 
                                false-value="0"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-600 peer-focus:ring-2 peer-focus:ring-green-500 transition-all duration-300"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transform peer-checked:translate-x-5 transition-all duration-300"></div>
                        </label>
                    </div>
                </div>
                
                <div class="flex items-center justify-end space-x-3 p-6 pt-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                    <button 
                        type="button" 
                        @click="deleteTask()" 
                        class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Task
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>   
        </div>
    </AuthenticatedLayout>
</template>