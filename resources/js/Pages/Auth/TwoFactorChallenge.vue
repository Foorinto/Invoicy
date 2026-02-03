<script setup>
import { ref, computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggleRecovery = () => {
    recovery.value = !recovery.value;
    form.code = '';
    form.recovery_code = '';
};

const submit = () => {
    form.post(route('two-factor.login'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Vérification en deux étapes" />

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            <template v-if="!recovery">
                Veuillez entrer le code à 6 chiffres généré par votre application
                d'authentification (Google Authenticator, Authy, etc.).
            </template>
            <template v-else>
                Veuillez entrer l'un de vos codes de récupération d'urgence.
            </template>
        </div>

        <form @submit.prevent="submit">
            <div v-if="!recovery">
                <InputLabel for="code" value="Code d'authentification" />
                <TextInput
                    id="code"
                    ref="codeInput"
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    class="mt-1 block w-full text-center text-2xl tracking-widest"
                    autofocus
                    autocomplete="one-time-code"
                    placeholder="000000"
                />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div v-else>
                <InputLabel for="recovery_code" value="Code de récupération" />
                <TextInput
                    id="recovery_code"
                    ref="recoveryCodeInput"
                    v-model="form.recovery_code"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="one-time-code"
                    placeholder="XXXX-XXXX-XXXX"
                />
                <InputError class="mt-2" :message="form.errors.recovery_code" />
            </div>

            <div class="mt-6 flex items-center justify-between">
                <button
                    type="button"
                    class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer dark:text-gray-400 dark:hover:text-gray-100"
                    @click="toggleRecovery"
                >
                    <template v-if="!recovery">
                        Utiliser un code de récupération
                    </template>
                    <template v-else>
                        Utiliser un code d'authentification
                    </template>
                </button>

                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Vérifier
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
