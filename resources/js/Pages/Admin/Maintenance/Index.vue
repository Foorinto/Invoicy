<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    systemInfo: Object,
    storage: Object,
    maintenanceMode: Boolean,
});

const processing = ref(false);

const clearCache = (type) => {
    processing.value = true;
    router.post(route('admin.maintenance.cache-clear'), { type }, {
        onFinish: () => processing.value = false,
    });
};

const toggleMaintenance = () => {
    processing.value = true;
    router.post(route('admin.maintenance.toggle'), {}, {
        onFinish: () => processing.value = false,
    });
};
</script>

<template>
    <Head title="Maintenance" />

    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-white">Maintenance</h1>
        </template>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- System info -->
            <div class="rounded-xl bg-slate-800 p-6">
                <h2 class="mb-4 text-lg font-semibold text-white">Informations Système</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Environnement</dt>
                        <dd :class="systemInfo.environment === 'production' ? 'text-green-400' : 'text-yellow-400'">
                            {{ systemInfo.environment }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Mode Debug</dt>
                        <dd :class="systemInfo.debug_mode ? 'text-yellow-400' : 'text-green-400'">
                            {{ systemInfo.debug_mode ? 'Activé' : 'Désactivé' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">PHP</dt>
                        <dd class="text-white">{{ systemInfo.php_version }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Laravel</dt>
                        <dd class="text-white">{{ systemInfo.laravel_version }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Cache</dt>
                        <dd class="text-white">{{ systemInfo.cache_driver }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Session</dt>
                        <dd class="text-white">{{ systemInfo.session_driver }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Queue</dt>
                        <dd class="text-white">{{ systemInfo.queue_driver }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Mail</dt>
                        <dd class="text-white">{{ systemInfo.mail_driver }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Storage -->
            <div class="rounded-xl bg-slate-800 p-6">
                <h2 class="mb-4 text-lg font-semibold text-white">Stockage</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Logs</dt>
                        <dd class="text-white">{{ storage.logs }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Cache</dt>
                        <dd class="text-white">{{ storage.cache }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Sessions</dt>
                        <dd class="text-white">{{ storage.sessions }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Cache management -->
            <div class="rounded-xl bg-slate-800 p-6">
                <h2 class="mb-4 text-lg font-semibold text-white">Gestion du Cache</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-lg bg-slate-700/50 p-3">
                        <div>
                            <p class="font-medium text-white">Configuration</p>
                            <p class="text-sm text-slate-400">config:clear</p>
                        </div>
                        <SecondaryButton :disabled="processing" @click="clearCache('config')">
                            Vider
                        </SecondaryButton>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-700/50 p-3">
                        <div>
                            <p class="font-medium text-white">Routes</p>
                            <p class="text-sm text-slate-400">route:clear</p>
                        </div>
                        <SecondaryButton :disabled="processing" @click="clearCache('route')">
                            Vider
                        </SecondaryButton>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-700/50 p-3">
                        <div>
                            <p class="font-medium text-white">Vues</p>
                            <p class="text-sm text-slate-400">view:clear</p>
                        </div>
                        <SecondaryButton :disabled="processing" @click="clearCache('view')">
                            Vider
                        </SecondaryButton>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-700/50 p-3">
                        <div>
                            <p class="font-medium text-white">Cache Application</p>
                            <p class="text-sm text-slate-400">Cache::flush()</p>
                        </div>
                        <SecondaryButton :disabled="processing" @click="clearCache('cache')">
                            Vider
                        </SecondaryButton>
                    </div>
                    <PrimaryButton
                        class="w-full justify-center bg-purple-600 hover:bg-purple-700"
                        :disabled="processing"
                        @click="clearCache('all')"
                    >
                        Vider tous les caches
                    </PrimaryButton>
                </div>
            </div>

            <!-- Maintenance mode -->
            <div class="rounded-xl bg-slate-800 p-6">
                <h2 class="mb-4 text-lg font-semibold text-white">Mode Maintenance</h2>
                <div class="rounded-lg bg-slate-700/50 p-4">
                    <div class="mb-4 flex items-center">
                        <div
                            :class="[
                                'mr-3 h-3 w-3 rounded-full',
                                maintenanceMode ? 'bg-red-500' : 'bg-green-500',
                            ]"
                        />
                        <span class="font-medium text-white">
                            {{ maintenanceMode ? 'Mode maintenance actif' : 'Application en ligne' }}
                        </span>
                    </div>
                    <p class="mb-4 text-sm text-slate-400">
                        {{ maintenanceMode
                            ? 'Les utilisateurs ne peuvent pas accéder à l\'application.'
                            : 'L\'application est accessible à tous.'
                        }}
                    </p>
                    <DangerButton
                        v-if="!maintenanceMode"
                        class="w-full justify-center"
                        :disabled="processing"
                        @click="toggleMaintenance"
                    >
                        Activer le mode maintenance
                    </DangerButton>
                    <PrimaryButton
                        v-else
                        class="w-full justify-center bg-green-600 hover:bg-green-700"
                        :disabled="processing"
                        @click="toggleMaintenance"
                    >
                        Désactiver le mode maintenance
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
