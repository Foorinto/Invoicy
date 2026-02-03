<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Fields to exclude from audit logging (sensitive data).
     */
    protected static array $excludedFields = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Create an audit log entry.
     */
    public static function log(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $status = AuditLog::STATUS_SUCCESS,
        ?array $metadata = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => self::filterSensitiveData($oldValues),
            'new_values' => self::filterSensitiveData($newValues),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'status' => $status,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Filter out sensitive fields from audit data.
     */
    protected static function filterSensitiveData(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        return array_diff_key($data, array_flip(self::$excludedFields));
    }

    /**
     * Log a successful login.
     */
    public static function logLogin(): void
    {
        self::log(AuditLog::ACTION_LOGIN);
    }

    /**
     * Log a failed login attempt.
     */
    public static function logLoginFailed(?string $email = null): void
    {
        self::log(
            AuditLog::ACTION_LOGIN_FAILED,
            status: AuditLog::STATUS_FAILED,
            metadata: $email ? ['email' => $email] : null
        );
    }

    /**
     * Log a logout.
     */
    public static function logLogout(): void
    {
        self::log(AuditLog::ACTION_LOGOUT);
    }

    /**
     * Log a password change.
     */
    public static function logPasswordChanged(): void
    {
        self::log(AuditLog::ACTION_PASSWORD_CHANGED);
    }

    /**
     * Log 2FA enabled.
     */
    public static function log2FAEnabled(): void
    {
        self::log(AuditLog::ACTION_2FA_ENABLED);
    }

    /**
     * Log 2FA disabled.
     */
    public static function log2FADisabled(): void
    {
        self::log(AuditLog::ACTION_2FA_DISABLED);
    }

    /**
     * Log model creation.
     */
    public static function logModelCreated(Model $model): void
    {
        self::log(
            class_basename($model) . '.created',
            $model,
            newValues: self::getAuditableAttributes($model)
        );
    }

    /**
     * Log model update.
     */
    public static function logModelUpdated(Model $model, array $oldValues): void
    {
        $changes = $model->getChanges();

        // Only log if there are actual changes (excluding timestamps)
        $significantChanges = array_diff_key($changes, array_flip(self::$excludedFields));
        if (empty($significantChanges)) {
            return;
        }

        $changedFields = array_intersect_key($oldValues, $changes);

        self::log(
            class_basename($model) . '.updated',
            $model,
            oldValues: self::filterSensitiveData($changedFields),
            newValues: self::filterSensitiveData($changes)
        );
    }

    /**
     * Log model deletion.
     */
    public static function logModelDeleted(Model $model): void
    {
        self::log(
            class_basename($model) . '.deleted',
            $model,
            oldValues: self::getAuditableAttributes($model)
        );
    }

    /**
     * Log a custom action on a model.
     */
    public static function logCustomAction(
        string $action,
        Model $model,
        ?array $metadata = null
    ): void {
        self::log(
            class_basename($model) . '.' . $action,
            $model,
            metadata: $metadata
        );
    }

    /**
     * Log an export action.
     */
    public static function logExport(string $type, ?array $metadata = null): void
    {
        self::log('export.' . $type, metadata: $metadata);
    }

    /**
     * Get auditable attributes from model, filtering sensitive data.
     */
    protected static function getAuditableAttributes(Model $model): array
    {
        $attributes = $model->toArray();
        return self::filterSensitiveData($attributes);
    }
}
