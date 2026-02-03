<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const mobileMenuOpen = ref(false);

const features = [
    {
        title: 'Facturation conforme',
        description: 'Numérotation séquentielle légale et mentions obligatoires automatiques pour le Luxembourg.',
        icon: 'document',
        gradient: 'from-[#9b5de5] to-[#f15bb5]',
    },
    {
        title: 'Gestion clients',
        description: 'Fichier clients complet avec validation TVA intracommunautaire.',
        icon: 'users',
        gradient: 'from-[#00bbf9] to-[#00f5d4]',
    },
    {
        title: 'Devis en un clic',
        description: 'Créez des devis professionnels et convertissez-les en factures instantanément.',
        icon: 'clipboard',
        gradient: 'from-[#f15bb5] to-[#fee440]',
    },
    {
        title: 'Avoirs liés',
        description: 'Émettez des avoirs avec traçabilité complète et conformité légale.',
        icon: 'refresh',
        gradient: 'from-[#00f5d4] to-[#00bbf9]',
    },
    {
        title: 'Suivi du temps',
        description: 'Saisissez vos heures et facturez automatiquement le temps passé.',
        icon: 'clock',
        gradient: 'from-[#fee440] to-[#f15bb5]',
    },
    {
        title: 'Export FAIA',
        description: 'Exportez vos données au format FAIA pour l\'Administration des contributions.',
        icon: 'download',
        gradient: 'from-[#9b5de5] to-[#00bbf9]',
    },
];
</script>

<template>
    <Head title="Facturation simplifiée pour le Luxembourg" />

    <div class="min-h-screen bg-slate-950 text-white overflow-hidden">
        <!-- Background decorations -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <!-- Gradient orbs -->
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-[#9b5de5] rounded-full mix-blend-screen filter blur-[128px] opacity-50"></div>
            <div class="absolute top-1/3 -left-40 w-80 h-80 bg-[#f15bb5] rounded-full mix-blend-screen filter blur-[128px] opacity-40"></div>
            <div class="absolute bottom-1/4 right-1/4 w-72 h-72 bg-[#00bbf9] rounded-full mix-blend-screen filter blur-[128px] opacity-30"></div>
            <div class="absolute -bottom-20 left-1/3 w-96 h-96 bg-[#00f5d4] rounded-full mix-blend-screen filter blur-[128px] opacity-20"></div>
            <!-- Grid pattern -->
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.02)_1px,transparent_1px)] bg-[size:64px_64px]"></div>
        </div>

        <!-- Header -->
        <header class="fixed top-0 left-0 right-0 z-50">
            <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4">
                <div class="backdrop-blur-xl bg-white/5 border border-white/10 rounded-2xl px-6 py-3">
                    <div class="flex items-center justify-between">
                        <!-- Logo -->
                        <Link href="/" class="flex items-center space-x-3 group">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-[#9b5de5] to-[#f15bb5] rounded-xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity"></div>
                                <div class="relative bg-gradient-to-r from-[#9b5de5] to-[#f15bb5] p-2 rounded-xl">
                                    <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>
                            <span class="text-xl font-bold bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent">faktur.lu</span>
                        </Link>

                        <!-- Desktop Navigation -->
                        <div class="hidden md:flex items-center space-x-1">
                            <a href="#features" class="px-4 py-2 text-sm font-medium text-white/70 hover:text-white rounded-xl hover:bg-white/5 transition-all">
                                Fonctionnalités
                            </a>
                            <a href="#pricing" class="px-4 py-2 text-sm font-medium text-white/70 hover:text-white rounded-xl hover:bg-white/5 transition-all">
                                Tarifs
                            </a>
                        </div>

                        <!-- Auth links -->
                        <div v-if="canLogin" class="hidden md:flex items-center space-x-3">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="px-4 py-2 text-sm font-medium text-white/70 hover:text-white transition-colors"
                            >
                                Tableau de bord
                            </Link>
                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="px-4 py-2 text-sm font-medium text-white/70 hover:text-white transition-colors"
                                >
                                    Connexion
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="relative group"
                                >
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-[#f15bb5] to-[#fee440] rounded-xl blur opacity-60 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="relative px-5 py-2.5 bg-slate-950 rounded-xl text-sm font-semibold text-white group-hover:bg-slate-900 transition-colors">
                                        Créer un compte
                                    </div>
                                </Link>
                            </template>
                        </div>

                        <!-- Mobile menu button -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden p-2 text-white/70 hover:text-white rounded-xl hover:bg-white/5"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile menu -->
                    <div v-if="mobileMenuOpen" class="md:hidden mt-4 pt-4 border-t border-white/10">
                        <div class="flex flex-col space-y-2">
                            <a href="#features" class="px-4 py-2 text-sm font-medium text-white/70 hover:text-white rounded-xl hover:bg-white/5">
                                Fonctionnalités
                            </a>
                            <a href="#pricing" class="px-4 py-2 text-sm font-medium text-white/70 hover:text-white rounded-xl hover:bg-white/5">
                                Tarifs
                            </a>
                            <template v-if="canLogin && !$page.props.auth.user">
                                <Link :href="route('login')" class="px-4 py-2 text-sm font-medium text-white/70 hover:text-white rounded-xl hover:bg-white/5">
                                    Connexion
                                </Link>
                                <Link v-if="canRegister" :href="route('register')" class="px-4 py-2 text-sm font-semibold text-center bg-gradient-to-r from-[#f15bb5] to-[#fee440] text-slate-950 rounded-xl">
                                    Créer un compte
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 sm:pt-44 sm:pb-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-4xl mx-auto">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 backdrop-blur-sm mb-8">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#00f5d4] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#00f5d4]"></span>
                        </span>
                        <span class="text-sm text-white/70">100% conforme Luxembourg</span>
                    </div>

                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight leading-[1.1]">
                        <span class="text-white">Facturation</span>
                        <br />
                        <span class="bg-gradient-to-r from-[#9b5de5] via-[#f15bb5] to-[#fee440] bg-clip-text text-transparent">simplifiée</span>
                        <br />
                        <span class="text-white/80">pour le Luxembourg</span>
                    </h1>

                    <p class="mt-8 text-lg sm:text-xl text-white/60 max-w-2xl mx-auto leading-relaxed">
                        Créez des factures conformes en quelques clics. Gérez clients, devis et avoirs depuis une interface moderne et intuitive.
                    </p>

                    <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="relative group w-full sm:w-auto"
                        >
                            <div class="absolute -inset-1 bg-gradient-to-r from-[#9b5de5] via-[#f15bb5] to-[#fee440] rounded-2xl blur-lg opacity-70 group-hover:opacity-100 transition-opacity"></div>
                            <div class="relative flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-[#9b5de5] to-[#f15bb5] rounded-2xl text-base font-semibold text-white shadow-2xl">
                                Commencer gratuitement
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </div>
                        </Link>
                        <Link
                            v-if="canLogin && !$page.props.auth.user"
                            :href="route('login')"
                            class="group flex items-center gap-2 px-8 py-4 text-base font-medium text-white/70 hover:text-white transition-colors"
                        >
                            Se connecter
                            <span class="text-[#00bbf9] group-hover:translate-x-1 transition-transform">→</span>
                        </Link>
                    </div>
                </div>

                <!-- Hero Card -->
                <div class="mt-20 max-w-4xl mx-auto">
                    <div class="relative">
                        <!-- Glow effect -->
                        <div class="absolute -inset-4 bg-gradient-to-r from-[#9b5de5]/20 via-[#f15bb5]/20 to-[#00bbf9]/20 rounded-3xl blur-2xl"></div>

                        <!-- Card -->
                        <div class="relative backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl p-8 sm:p-10">
                            <!-- Header -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-white/10">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 rounded-2xl bg-gradient-to-br from-[#9b5de5] to-[#f15bb5]">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-white/50">Facture</p>
                                        <p class="text-xl font-bold text-white">F-2026-001</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#00f5d4]/10 border border-[#00f5d4]/20 text-[#00f5d4] text-sm font-medium">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Payée
                                    </span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="py-6 space-y-4">
                                <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 hover:bg-white/[0.07] transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#00bbf9] to-[#00f5d4] flex items-center justify-center text-white text-sm font-bold">10h</div>
                                        <span class="text-white/80">Développement web</span>
                                    </div>
                                    <span class="text-white font-semibold">850,00 €</span>
                                </div>
                                <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 hover:bg-white/[0.07] transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#f15bb5] to-[#fee440] flex items-center justify-center text-white text-sm font-bold">5h</div>
                                        <span class="text-white/80">Design UI/UX</span>
                                    </div>
                                    <span class="text-white font-semibold">250,00 €</span>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="pt-6 border-t border-white/10">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-white/50">TVA 17%</p>
                                        <p class="text-white/70">187,00 €</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-white/50">Total TTC</p>
                                        <p class="text-3xl font-bold bg-gradient-to-r from-[#9b5de5] to-[#f15bb5] bg-clip-text text-transparent">1 287,00 €</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="relative py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <p class="text-[#00bbf9] font-semibold mb-4">Fonctionnalités</p>
                    <h2 class="text-4xl sm:text-5xl font-bold text-white mb-6">
                        Tout ce qu'il vous faut
                    </h2>
                    <p class="text-lg text-white/60 max-w-2xl mx-auto">
                        Une solution complète pour gérer votre facturation au Luxembourg
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="group relative"
                    >
                        <div class="absolute -inset-0.5 bg-gradient-to-r opacity-0 group-hover:opacity-100 rounded-3xl blur transition-opacity duration-300" :class="feature.gradient"></div>
                        <div class="relative h-full backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl p-8 hover:bg-white/[0.07] transition-all">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br flex items-center justify-center mb-6" :class="feature.gradient">
                                <!-- Document icon -->
                                <svg v-if="feature.icon === 'document'" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <!-- Users icon -->
                                <svg v-else-if="feature.icon === 'users'" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <!-- Clipboard icon -->
                                <svg v-else-if="feature.icon === 'clipboard'" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <!-- Refresh icon -->
                                <svg v-else-if="feature.icon === 'refresh'" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <!-- Clock icon -->
                                <svg v-else-if="feature.icon === 'clock'" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <!-- Download icon -->
                                <svg v-else-if="feature.icon === 'download'" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-white mb-3">
                                {{ feature.title }}
                            </h3>
                            <p class="text-white/60 leading-relaxed">
                                {{ feature.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="relative py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-r from-[#9b5de5]/10 via-[#f15bb5]/10 to-[#00bbf9]/10 rounded-3xl blur-2xl"></div>
                    <div class="relative backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl p-12">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-4">
                            <div class="text-center">
                                <p class="text-4xl sm:text-5xl font-bold bg-gradient-to-r from-[#9b5de5] to-[#f15bb5] bg-clip-text text-transparent">100%</p>
                                <p class="mt-2 text-white/60">Conforme FAIA</p>
                            </div>
                            <div class="text-center">
                                <p class="text-4xl sm:text-5xl font-bold bg-gradient-to-r from-[#f15bb5] to-[#fee440] bg-clip-text text-transparent">17%</p>
                                <p class="mt-2 text-white/60">TVA Luxembourg</p>
                            </div>
                            <div class="text-center">
                                <p class="text-4xl sm:text-5xl font-bold bg-gradient-to-r from-[#00bbf9] to-[#00f5d4] bg-clip-text text-transparent">∞</p>
                                <p class="mt-2 text-white/60">Factures possibles</p>
                            </div>
                            <div class="text-center">
                                <p class="text-4xl sm:text-5xl font-bold bg-gradient-to-r from-[#00f5d4] to-[#9b5de5] bg-clip-text text-transparent">24/7</p>
                                <p class="mt-2 text-white/60">Accès en ligne</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="relative py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <p class="text-[#f15bb5] font-semibold mb-4">Tarification</p>
                    <h2 class="text-4xl sm:text-5xl font-bold text-white mb-6">
                        Simple et transparent
                    </h2>
                    <p class="text-lg text-white/60 max-w-2xl mx-auto">
                        Des offres adaptées à votre activité
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <!-- Plan Gratuit -->
                    <div class="relative backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl p-8">
                        <div class="mb-8">
                            <h3 class="text-xl font-semibold text-white">Découverte</h3>
                            <p class="text-white/50 mt-1">Pour démarrer</p>
                        </div>
                        <div class="mb-8">
                            <span class="text-5xl font-bold text-white">Gratuit</span>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center gap-3 text-white/70">
                                <svg class="w-5 h-5 text-[#00f5d4] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                5 factures / mois
                            </li>
                            <li class="flex items-center gap-3 text-white/70">
                                <svg class="w-5 h-5 text-[#00f5d4] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                3 clients
                            </li>
                            <li class="flex items-center gap-3 text-white/70">
                                <svg class="w-5 h-5 text-[#00f5d4] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Export PDF
                            </li>
                        </ul>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="block w-full py-4 text-center font-semibold text-white border border-white/20 rounded-2xl hover:bg-white/5 transition-colors"
                        >
                            Commencer
                        </Link>
                    </div>

                    <!-- Plan Pro -->
                    <div class="relative">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-[#9b5de5] via-[#f15bb5] to-[#fee440] rounded-3xl blur opacity-50"></div>
                        <div class="relative backdrop-blur-xl bg-slate-950 border border-white/20 rounded-3xl p-8">
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                                <span class="px-4 py-1.5 text-xs font-bold bg-gradient-to-r from-[#f15bb5] to-[#fee440] text-slate-950 rounded-full">
                                    POPULAIRE
                                </span>
                            </div>
                            <div class="mb-8">
                                <h3 class="text-xl font-semibold text-white">Professionnel</h3>
                                <p class="text-white/50 mt-1">Pour les indépendants</p>
                            </div>
                            <div class="mb-8">
                                <span class="text-5xl font-bold bg-gradient-to-r from-[#9b5de5] to-[#f15bb5] bg-clip-text text-transparent">À venir</span>
                            </div>
                            <ul class="space-y-4 mb-8">
                                <li class="flex items-center gap-3 text-white/70">
                                    <svg class="w-5 h-5 text-[#f15bb5] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Factures illimitées
                                </li>
                                <li class="flex items-center gap-3 text-white/70">
                                    <svg class="w-5 h-5 text-[#f15bb5] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Clients illimités
                                </li>
                                <li class="flex items-center gap-3 text-white/70">
                                    <svg class="w-5 h-5 text-[#f15bb5] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Export FAIA
                                </li>
                                <li class="flex items-center gap-3 text-white/70">
                                    <svg class="w-5 h-5 text-[#f15bb5] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Suivi du temps
                                </li>
                            </ul>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="block w-full py-4 text-center font-semibold text-slate-950 bg-gradient-to-r from-[#f15bb5] to-[#fee440] rounded-2xl hover:opacity-90 transition-opacity"
                            >
                                S'inscrire
                            </Link>
                        </div>
                    </div>

                    <!-- Plan Entreprise -->
                    <div class="relative backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl p-8">
                        <div class="mb-8">
                            <h3 class="text-xl font-semibold text-white">Entreprise</h3>
                            <p class="text-white/50 mt-1">Pour les équipes</p>
                        </div>
                        <div class="mb-8">
                            <span class="text-5xl font-bold text-white/30">À venir</span>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center gap-3 text-white/50">
                                <svg class="w-5 h-5 text-[#00bbf9] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Multi-utilisateurs
                            </li>
                            <li class="flex items-center gap-3 text-white/50">
                                <svg class="w-5 h-5 text-[#00bbf9] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Gestion des rôles
                            </li>
                            <li class="flex items-center gap-3 text-white/50">
                                <svg class="w-5 h-5 text-[#00bbf9] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Support prioritaire
                            </li>
                            <li class="flex items-center gap-3 text-white/50">
                                <svg class="w-5 h-5 text-[#00bbf9] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                API access
                            </li>
                        </ul>
                        <button
                            disabled
                            class="block w-full py-4 text-center font-semibold text-white/30 border border-white/10 rounded-2xl cursor-not-allowed"
                        >
                            Bientôt disponible
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-r from-[#9b5de5] via-[#f15bb5] to-[#fee440] rounded-3xl blur-2xl opacity-30"></div>
                    <div class="relative backdrop-blur-xl bg-gradient-to-r from-[#9b5de5]/20 to-[#f15bb5]/20 border border-white/10 rounded-3xl px-8 py-16 sm:px-16 sm:py-20 text-center">
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">
                            Prêt à simplifier<br />votre facturation ?
                        </h2>
                        <p class="text-lg text-white/60 mb-10 max-w-2xl mx-auto">
                            Rejoignez les entrepreneurs luxembourgeois qui font confiance à faktur.lu
                        </p>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-white text-slate-950 font-semibold rounded-2xl hover:bg-white/90 transition-colors"
                        >
                            Créer mon compte gratuit
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative border-t border-white/10 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 rounded-xl bg-gradient-to-r from-[#9b5de5] to-[#f15bb5]">
                            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="font-semibold text-white">faktur.lu</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-white/50">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#00f5d4]/10 text-[#00f5d4] text-xs font-medium">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            FAIA
                        </span>
                        Conforme aux exigences luxembourgeoises
                    </div>
                    <p class="text-sm text-white/50">
                        © 2026 faktur.lu
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
html {
    scroll-behavior: smooth;
}
</style>
