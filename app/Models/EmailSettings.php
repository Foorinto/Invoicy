<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'default_message',
        'signature',
        'send_copy_to_self',
        'reminders_enabled',
        'reminder_levels',
    ];

    protected $casts = [
        'send_copy_to_self' => 'boolean',
        'reminders_enabled' => 'boolean',
        'reminder_levels' => 'array',
    ];

    /**
     * Default reminder levels configuration.
     */
    public static function defaultReminderLevels(): array
    {
        return [
            1 => [
                'enabled' => true,
                'days_after_due' => 7,
                'subject' => 'Rappel : Facture {numero} en attente de paiement',
                'body' => "Bonjour {client_nom},\n\nNous nous permettons de vous rappeler que la facture {numero} d'un montant de {montant} € est arrivée à échéance le {date_echeance}.\n\nNous vous remercions de bien vouloir procéder au règlement dans les meilleurs délais.\n\nCordialement,\n{entreprise}",
            ],
            2 => [
                'enabled' => true,
                'days_after_due' => 14,
                'subject' => 'Relance : Facture {numero} impayée',
                'body' => "Bonjour {client_nom},\n\nMalgré notre précédent rappel, nous constatons que la facture {numero} d'un montant de {montant} € reste impayée.\n\nLe retard de paiement est actuellement de {jours_retard} jours.\n\nNous vous prions de régulariser cette situation dans les plus brefs délais.\n\nCordialement,\n{entreprise}",
            ],
            3 => [
                'enabled' => true,
                'days_after_due' => 30,
                'subject' => 'Mise en demeure : Facture {numero}',
                'body' => "Bonjour {client_nom},\n\nMalgré nos relances précédentes, la facture {numero} d'un montant de {montant} € demeure impayée depuis {jours_retard} jours.\n\nSans règlement de votre part sous 8 jours, nous nous verrons contraints d'engager les procédures de recouvrement appropriées.\n\nCordialement,\n{entreprise}",
            ],
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get reminder level configuration.
     */
    public function getReminderLevel(int $level): ?array
    {
        $levels = $this->reminder_levels ?? self::defaultReminderLevels();
        return $levels[$level] ?? null;
    }

    /**
     * Get or create settings for a user.
     */
    public static function getOrCreate(User $user): self
    {
        return self::firstOrCreate(
            ['user_id' => $user->id],
            ['reminder_levels' => self::defaultReminderLevels()]
        );
    }
}
