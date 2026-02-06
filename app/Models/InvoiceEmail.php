<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'type',
        'recipient_email',
        'subject',
        'body',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public const TYPE_INITIAL = 'initial';
    public const TYPE_MANUAL = 'manual';
    public const TYPE_REMINDER_1 = 'reminder_1';
    public const TYPE_REMINDER_2 = 'reminder_2';
    public const TYPE_REMINDER_3 = 'reminder_3';

    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get a human-readable label for the email type.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_INITIAL => 'Envoi initial',
            self::TYPE_MANUAL => 'Envoi manuel',
            self::TYPE_REMINDER_1 => 'Relance niveau 1',
            self::TYPE_REMINDER_2 => 'Relance niveau 2',
            self::TYPE_REMINDER_3 => 'Relance niveau 3',
            default => $this->type,
        };
    }

    /**
     * Check if this is a reminder email.
     */
    public function isReminder(): bool
    {
        return in_array($this->type, [
            self::TYPE_REMINDER_1,
            self::TYPE_REMINDER_2,
            self::TYPE_REMINDER_3,
        ]);
    }
}
