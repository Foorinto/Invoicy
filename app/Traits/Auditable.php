<?php

namespace App\Traits;

use App\Services\AuditLogger;

trait Auditable
{
    /**
     * Store old values before update for audit logging.
     */
    protected array $auditOldValues = [];

    /**
     * Boot the auditable trait.
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLogger::logModelCreated($model);
        });

        static::updating(function ($model) {
            $model->auditOldValues = $model->getOriginal();
        });

        static::updated(function ($model) {
            if (!empty($model->auditOldValues)) {
                AuditLogger::logModelUpdated($model, $model->auditOldValues);
                $model->auditOldValues = [];
            }
        });

        static::deleted(function ($model) {
            AuditLogger::logModelDeleted($model);
        });
    }

    /**
     * Log a custom action on this model.
     */
    public function logAction(string $action, ?array $metadata = null): void
    {
        AuditLogger::logCustomAction($action, $this, $metadata);
    }
}
