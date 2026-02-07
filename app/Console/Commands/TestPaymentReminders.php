<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentReminders;
use App\Models\EmailSettings;
use App\Models\Invoice;
use Illuminate\Console\Command;

class TestPaymentReminders extends Command
{
    protected $signature = 'reminders:test
                            {--user= : ID de l\'utilisateur à tester}
                            {--invoice= : ID de la facture spécifique à tester}
                            {--dry-run : Afficher ce qui serait envoyé sans envoyer}';

    protected $description = 'Teste l\'envoi des relances de paiement';

    public function handle(): int
    {
        $userId = $this->option('user');
        $invoiceId = $this->option('invoice');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Mode dry-run activé - aucun email ne sera envoyé');
            $this->newLine();
        }

        // Afficher les factures éligibles aux relances
        $query = Invoice::query()
            ->whereIn('status', [Invoice::STATUS_FINALIZED, Invoice::STATUS_SENT])
            ->where('type', Invoice::TYPE_INVOICE)
            ->where('exclude_from_reminders', false)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now()->startOfDay())
            ->whereHas('client', fn($q) => $q->where('exclude_from_reminders', false))
            ->with(['client', 'emails', 'user.emailSettings']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($invoiceId) {
            $query->where('id', $invoiceId);
        }

        $invoices = $query->get();

        $this->line("Date actuelle : " . now()->format('d/m/Y'));
        $this->newLine();

        if ($invoices->isEmpty()) {
            $this->warn('Aucune facture éligible aux relances trouvée.');
            $this->newLine();
            $this->info('Critères :');
            $this->line('  - Statut : finalisée ou envoyée');
            $this->line('  - Type : facture (pas avoir)');
            $this->line('  - Non exclue des relances');
            $this->line('  - Date d\'échéance passée');
            $this->line('  - Client non exclu des relances');
            return 0;
        }

        $this->info("📋 {$invoices->count()} facture(s) éligible(s) aux relances :");
        $this->newLine();

        $tableData = [];
        foreach ($invoices as $invoice) {
            // Calcul correct : nombre de jours depuis l'échéance (positif si en retard)
            $daysOverdue = $invoice->due_at->startOfDay()->diffInDays(now()->startOfDay(), false);
            $settings = $invoice->user->emailSettings;
            $remindersEnabled = $settings?->reminders_enabled ?? false;
            $levels = $settings?->reminder_levels ?? EmailSettings::defaultReminderLevels();

            // Déterminer le niveau à envoyer
            $levelToSend = null;
            foreach ([3, 2, 1] as $level) {
                $levelConfig = $levels[$level] ?? null;
                if (!$levelConfig || !$levelConfig['enabled']) continue;

                $daysAfterDue = $levelConfig['days_after_due'] ?? ($level * 7);
                if ($daysOverdue >= $daysAfterDue) {
                    // Vérifier si déjà envoyé
                    $type = match($level) {
                        1 => 'reminder_1',
                        2 => 'reminder_2',
                        3 => 'reminder_3',
                    };
                    $alreadySent = $invoice->emails()->where('type', $type)->where('status', 'sent')->exists();
                    if (!$alreadySent) {
                        $levelToSend = $level;
                        break;
                    }
                }
            }

            $sentReminders = $invoice->emails()
                ->whereIn('type', ['reminder_1', 'reminder_2', 'reminder_3'])
                ->where('status', 'sent')
                ->pluck('type')
                ->map(fn($t) => str_replace('reminder_', 'N', $t))
                ->join(', ');

            $tableData[] = [
                $invoice->number,
                $invoice->client->name ?? 'N/A',
                $invoice->due_at->format('d/m/Y'),
                "{$daysOverdue} jours",
                $remindersEnabled ? '✅' : '❌',
                $sentReminders ?: '-',
                $levelToSend ? "Niveau {$levelToSend}" : ($remindersEnabled ? 'Aucun (délai non atteint ou déjà envoyé)' : 'Relances désactivées'),
            ];
        }

        $this->table(
            ['Facture', 'Client', 'Échéance', 'Retard', 'Relances actives', 'Déjà envoyées', 'Action'],
            $tableData
        );

        if (!$dryRun) {
            $this->newLine();
            if ($this->confirm('Voulez-vous envoyer les relances maintenant ?')) {
                $this->info('📧 Envoi des relances...');
                SendPaymentReminders::dispatchSync();
                $this->info('✅ Terminé !');
            }
        }

        return 0;
    }
}
