<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useTranslations } from '@/Composables/useTranslations';

const { t } = useTranslations();

const props = defineProps({
    invoice: Object,
});

const processing = ref(false);
const showCreditNoteModal = ref(false);
const creditNoteType = ref('full'); // 'full' or 'partial'
const selectedItemIds = ref([]);

// Preview modal state
const showPreviewModal = ref(false);
const previewHtml = ref('');
const loadingPreview = ref(false);

// PDF language selection
const pdfLocale = ref(props.invoice.buyer_snapshot?.locale || 'fr');

const pdfLanguages = [
    { value: 'fr', label: 'Français', flag: '🇫🇷' },
    { value: 'de', label: 'Deutsch', flag: '🇩🇪' },
    { value: 'en', label: 'English', flag: '🇬🇧' },
    { value: 'lb', label: 'Lëtzebuergesch', flag: '🇱🇺' },
];

const pdfUrl = computed(() => {
    const baseUrl = route('invoices.pdf.stream', props.invoice.id);
    return `${baseUrl}?locale=${pdfLocale.value}`;
});

// Load preview with locale
const loadPreview = async () => {
    loadingPreview.value = true;
    try {
        const url = route('invoices.preview-html', props.invoice.id) + `?locale=${pdfLocale.value}`;
        const response = await axios.get(url);
        previewHtml.value = response.data.html;
    } catch (error) {
        console.error('Error loading preview:', error);
        previewHtml.value = `<p style="color: red; padding: 20px;">${t('error_loading_preview')}</p>`;
    } finally {
        loadingPreview.value = false;
    }
};

// Reload preview when language changes
const changePdfLanguage = (locale) => {
    pdfLocale.value = locale;
    if (showPreviewModal.value) {
        loadPreview();
    }
};

const openPreview = () => {
    showPreviewModal.value = true;
    loadPreview();
};

const creditNoteForm = useForm({
    reason: 'cancellation',
    item_ids: null,
});

const creditNoteReasons = computed(() => ({
    billing_error: t('billing_error'),
    return: t('return_merchandise'),
    commercial_discount: t('commercial_discount'),
    cancellation: t('invoice_cancellation'),
    other: t('other'),
}));

// Compute if form can be submitted
const canSubmitCreditNote = computed(() => {
    if (creditNoteType.value === 'partial') {
        return selectedItemIds.value.length > 0;
    }
    return true;
});

// Calculate partial credit note total
const partialTotal = computed(() => {
    if (!props.invoice.items) return 0;
    return props.invoice.items
        .filter(item => selectedItemIds.value.includes(item.id))
        .reduce((sum, item) => sum + parseFloat(item.total_ttc || 0), 0);
});

const getStatusBadgeClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        finalized: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        sent: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[status] || classes.draft;
};

const getStatusLabel = (status) => {
    const labels = {
        draft: t('draft'),
        finalized: t('finalized'),
        sent: t('sent'),
        paid: t('paid'),
        cancelled: t('cancelled'),
    };
    return labels[status] || status;
};

const formatCurrency = (amount, currency = 'EUR') => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: currency,
    }).format(amount);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR');
};

const markAsSent = () => {
    if (processing.value) return;
    processing.value = true;
    router.post(route('invoices.mark-sent', props.invoice.id), {}, {
        preserveScroll: true,
        onFinish: () => processing.value = false,
    });
};

const markAsPaid = () => {
    if (processing.value) return;
    processing.value = true;
    router.post(route('invoices.mark-paid', props.invoice.id), {}, {
        preserveScroll: true,
        onFinish: () => processing.value = false,
    });
};

const openCreditNoteModal = () => {
    creditNoteType.value = 'full';
    selectedItemIds.value = [];
    creditNoteForm.reason = 'cancellation';
    creditNoteForm.item_ids = null;
    showCreditNoteModal.value = true;
};

const closeCreditNoteModal = () => {
    showCreditNoteModal.value = false;
};

const toggleItemSelection = (itemId) => {
    const index = selectedItemIds.value.indexOf(itemId);
    if (index === -1) {
        selectedItemIds.value.push(itemId);
    } else {
        selectedItemIds.value.splice(index, 1);
    }
};

const submitCreditNote = () => {
    if (!canSubmitCreditNote.value) return;

    // Set item_ids based on type
    if (creditNoteType.value === 'partial') {
        creditNoteForm.item_ids = selectedItemIds.value;
    } else {
        creditNoteForm.item_ids = null;
    }

    creditNoteForm.post(route('invoices.credit-note', props.invoice.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCreditNoteModal.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`${t('invoice')} ${invoice.number}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('invoices.index')"
                        class="text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
                        </svg>
                    </Link>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                        <span v-if="invoice.type === 'credit_note'" class="text-red-600 dark:text-red-400">{{ t('credit_note') }} </span>
                        {{ invoice.number }}
                    </h1>
                    <span
                        :class="getStatusBadgeClass(invoice.status)"
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                    >
                        {{ getStatusLabel(invoice.status) }}
                    </span>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Preview Button -->
                    <button
                        type="button"
                        @click="openPreview"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    >
                        <svg class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                            <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        {{ t('preview') }}
                    </button>

                    <!-- Mark as Sent -->
                    <button
                        v-if="invoice.status === 'finalized'"
                        @click="markAsSent"
                        :disabled="processing"
                        class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 disabled:opacity-50"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z" />
                            <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z" />
                        </svg>
                        {{ t('mark_as_sent') }}
                    </button>

                    <!-- Mark as Paid -->
                    <button
                        v-if="invoice.status === 'sent'"
                        @click="markAsPaid"
                        :disabled="processing"
                        class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 disabled:opacity-50"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        {{ t('mark_as_paid') }}
                    </button>

                    <!-- Create Credit Note -->
                    <button
                        v-if="invoice.type === 'invoice' && ['finalized', 'sent', 'paid'].includes(invoice.status) && !invoice.credit_note"
                        @click="openCreditNoteModal"
                        :disabled="processing"
                        class="inline-flex items-center rounded-md border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-700 shadow-sm hover:bg-red-50 dark:border-red-600 dark:bg-gray-700 dark:text-red-400 dark:hover:bg-gray-600"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4zm1 14a1 1 0 100-2 1 1 0 000 2zm5-1.757l4.9-4.9a2.121 2.121 0 013 3l-4.9 4.9a2.121 2.121 0 01-1.5.621h-1a.5.5 0 01-.5-.5v-1a2.121 2.121 0 01.621-1.5z" clip-rule="evenodd" />
                        </svg>
                        {{ t('credit_note') }}
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Invoice Header Info -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Seller Info -->
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('issuer') }}</h2>
                    </div>
                    <div class="px-6 py-4">
                        <div v-if="invoice.seller_snapshot" class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                            <p class="font-semibold">{{ invoice.seller_snapshot.company_name }}</p>
                            <p v-if="invoice.seller_snapshot.legal_name">{{ invoice.seller_snapshot.legal_name }}</p>
                            <p>{{ invoice.seller_snapshot.address_line1 }}</p>
                            <p v-if="invoice.seller_snapshot.address_line2">{{ invoice.seller_snapshot.address_line2 }}</p>
                            <p>{{ invoice.seller_snapshot.postal_code }} {{ invoice.seller_snapshot.city }}</p>
                            <p>{{ invoice.seller_snapshot.country }}</p>
                            <p class="pt-2">
                                <span class="text-gray-500">{{ t('matricule') }}:</span> {{ invoice.seller_snapshot.matricule }}
                            </p>
                            <p v-if="invoice.seller_snapshot.vat_number">
                                <span class="text-gray-500">{{ t('vat_number_short') }}:</span> {{ invoice.seller_snapshot.vat_number }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Buyer Info -->
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('client') }}</h2>
                    </div>
                    <div class="px-6 py-4">
                        <div v-if="invoice.buyer_snapshot" class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                            <p class="font-semibold">{{ invoice.buyer_snapshot.name }}</p>
                            <p v-if="invoice.buyer_snapshot.company_name">{{ invoice.buyer_snapshot.company_name }}</p>
                            <p>{{ invoice.buyer_snapshot.address_line1 }}</p>
                            <p v-if="invoice.buyer_snapshot.address_line2">{{ invoice.buyer_snapshot.address_line2 }}</p>
                            <p>{{ invoice.buyer_snapshot.postal_code }} {{ invoice.buyer_snapshot.city }}</p>
                            <p>{{ invoice.buyer_snapshot.country }}</p>
                            <p v-if="invoice.buyer_snapshot.vat_number" class="pt-2">
                                <span class="text-gray-500">{{ t('vat_number_short') }}:</span> {{ invoice.buyer_snapshot.vat_number }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('details') }}</h2>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('issue_date') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(invoice.issued_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('due_date') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(invoice.due_at) }}</dd>
                        </div>
                        <div v-if="invoice.sent_at">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('sent_on') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(invoice.sent_at) }}</dd>
                        </div>
                        <div v-if="invoice.paid_at">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('paid_on') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(invoice.paid_at) }}</dd>
                        </div>
                        <div v-if="invoice.credit_note_for">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('credit_note_for') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <Link
                                    v-if="invoice.original_invoice"
                                    :href="route('invoices.show', invoice.credit_note_for)"
                                    class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                                >
                                    {{ t('invoice') }} {{ invoice.original_invoice.number }}
                                </Link>
                                <Link
                                    v-else
                                    :href="route('invoices.show', invoice.credit_note_for)"
                                    class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                                >
                                    {{ t('see_original_invoice') }}
                                </Link>
                            </dd>
                        </div>
                        <div v-if="invoice.credit_note_reason">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('reason') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ creditNoteReasons[invoice.credit_note_reason] || invoice.credit_note_reason }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('invoice_lines') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ t('description') }}
                                </th>
                                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ t('qty') }}
                                </th>
                                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ t('price_ht') }}
                                </th>
                                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ t('vat') }}
                                </th>
                                <th class="py-3.5 pl-3 pr-6 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ t('total_ht') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr v-for="item in invoice.items" :key="item.id">
                                <td class="py-4 pl-6 pr-3 text-sm text-gray-900 dark:text-white">
                                    {{ item.description }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-500 dark:text-gray-400">
                                    {{ item.quantity }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatCurrency(item.unit_price, invoice.currency) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-500 dark:text-gray-400">
                                    {{ item.vat_rate }}%
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatCurrency(item.total_ht, invoice.currency) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <td colspan="4" class="py-3 pl-6 pr-3 text-right text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('total_ht') }}
                                </td>
                                <td class="whitespace-nowrap py-3 pl-3 pr-6 text-right text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatCurrency(invoice.total_ht, invoice.currency) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="py-3 pl-6 pr-3 text-right text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('total_vat') }}
                                </td>
                                <td class="whitespace-nowrap py-3 pl-3 pr-6 text-right text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatCurrency(invoice.total_vat, invoice.currency) }}
                                </td>
                            </tr>
                            <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                                <td colspan="4" class="py-3 pl-6 pr-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                                    {{ t('total_ttc') }}
                                </td>
                                <td class="whitespace-nowrap py-3 pl-3 pr-6 text-right text-sm font-bold"
                                    :class="invoice.type === 'credit_note' ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'"
                                >
                                    {{ formatCurrency(invoice.total_ttc, invoice.currency) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="invoice.notes" class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('notes') }}</h2>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ invoice.notes }}</p>
                </div>
            </div>

            <!-- Credit Notes linked to this invoice -->
            <div v-if="invoice.credit_notes && invoice.credit_notes.length > 0" class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('related_credit_notes') }}</h2>
                </div>
                <div class="px-6 py-4">
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="creditNote in invoice.credit_notes" :key="creditNote.id" class="py-3 flex justify-between items-center">
                            <Link
                                :href="route('invoices.show', creditNote.id)"
                                class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                            >
                                {{ creditNote.number }}
                            </Link>
                            <span class="text-sm text-red-600 dark:text-red-400">
                                {{ formatCurrency(creditNote.total_ttc, creditNote.currency) }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Credit Note Modal -->
        <div v-if="showCreditNoteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeCreditNoteModal"></div>

                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <div class="px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                            {{ t('create_credit_note_for').replace(':number', invoice.number) }}
                        </h3>

                        <!-- Credit Note Type -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('credit_note_type') }}
                            </label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input
                                        type="radio"
                                        v-model="creditNoteType"
                                        value="full"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ t('full_credit_note') }}</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        type="radio"
                                        v-model="creditNoteType"
                                        value="partial"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ t('partial_credit_note') }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="mb-4">
                            <label for="credit_note_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('credit_note_reason') }}
                            </label>
                            <select
                                id="credit_note_reason"
                                v-model="creditNoteForm.reason"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option v-for="(label, value) in creditNoteReasons" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <!-- Item Selection for Partial -->
                        <div v-if="creditNoteType === 'partial'" class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('lines_to_cancel') }}
                            </label>
                            <div class="border rounded-md divide-y dark:border-gray-600 dark:divide-gray-600 max-h-48 overflow-y-auto">
                                <label
                                    v-for="item in invoice.items"
                                    :key="item.id"
                                    class="flex items-center px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="selectedItemIds.includes(item.id)"
                                        @change="toggleItemSelection(item.id)"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    />
                                    <span class="ml-3 flex-1 text-sm text-gray-700 dark:text-gray-300">
                                        {{ item.title || item.description }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatCurrency(item.total_ttc, invoice.currency) }}
                                    </span>
                                </label>
                            </div>
                            <p v-if="selectedItemIds.length === 0" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ t('select_at_least_one_line') }}
                            </p>
                        </div>

                        <!-- Summary -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-3 mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('credit_note_amount') }} :
                                </span>
                                <span class="text-lg font-bold text-red-600 dark:text-red-400">
                                    {{ creditNoteType === 'partial'
                                        ? formatCurrency(-partialTotal, invoice.currency)
                                        : formatCurrency(-invoice.total_ttc, invoice.currency)
                                    }}
                                </span>
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ t('credit_note_draft_info') }}
                        </p>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 dark:bg-gray-700 sm:flex sm:flex-row-reverse sm:px-6">
                        <button
                            type="button"
                            @click="submitCreditNote"
                            :disabled="!canSubmitCreditNote || creditNoteForm.processing"
                            class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 disabled:opacity-50 sm:ml-3 sm:w-auto"
                        >
                            {{ creditNoteForm.processing ? t('creating') : t('create_credit_note') }}
                        </button>
                        <button
                            type="button"
                            @click="closeCreditNoteModal"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-600 dark:text-white dark:ring-gray-500 sm:mt-0 sm:w-auto"
                        >
                            {{ t('cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="showPreviewModal" class="fixed inset-0 z-50 overflow-hidden">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showPreviewModal = false"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            <span v-if="invoice.type === 'credit_note'" class="text-red-600 dark:text-red-400">{{ t('credit_note') }} </span>
                            {{ invoice.number }}
                        </h3>
                        <div class="flex items-center space-x-2">
                            <!-- Language selector -->
                            <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
                                <button
                                    v-for="lang in pdfLanguages"
                                    :key="lang.value"
                                    type="button"
                                    @click="changePdfLanguage(lang.value)"
                                    :title="lang.label"
                                    class="px-2 py-1.5 text-base transition-colors"
                                    :class="pdfLocale === lang.value
                                        ? 'bg-indigo-100 dark:bg-indigo-900'
                                        : 'bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600'"
                                >
                                    {{ lang.flag }}
                                </button>
                            </div>
                            <a
                                :href="pdfUrl"
                                target="_blank"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            >
                                <svg class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z" />
                                    <path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z" />
                                </svg>
                                PDF
                            </a>
                            <button
                                type="button"
                                @click="showPreviewModal = false"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                            >
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal body -->
                    <div class="flex-1 overflow-auto p-6 bg-gray-100 dark:bg-gray-900">
                        <div v-if="loadingPreview" class="flex items-center justify-center h-96">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                        </div>
                        <div
                            v-else
                            class="bg-white shadow-lg mx-auto"
                            style="width: 210mm; min-height: 297mm; transform: scale(1); transform-origin: top center;"
                            v-html="previewHtml"
                        ></div>
                    </div>

                    <!-- Modal footer -->
                    <div class="flex items-center justify-end px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                        <button
                            type="button"
                            @click="showPreviewModal = false"
                            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-600 dark:text-white dark:ring-gray-500"
                        >
                            {{ t('close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
