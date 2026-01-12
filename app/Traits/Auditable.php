<?php

namespace App\Traits;

use App\Models\PayrollAuditLog;

/**
 * Trait for models that should be audited
 * 
 * Automatically logs create, update, delete events.
 * Use logAction() for custom actions.
 */
trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            if ($model->shouldAudit('created')) {
                PayrollAuditLog::log($model, PayrollAuditLog::ACTION_CREATED, null, $model->getAuditableAttributes());
            }
        });

        static::updated(function ($model) {
            if ($model->shouldAudit('updated')) {
                $changes = $model->getChanges();
                $original = collect($model->getOriginal())->only(array_keys($changes))->toArray();

                // Only log if there are meaningful changes
                if (!empty($changes)) {
                    PayrollAuditLog::log($model, PayrollAuditLog::ACTION_UPDATED, $original, $changes);
                }
            }
        });

        static::deleted(function ($model) {
            if ($model->shouldAudit('deleted')) {
                PayrollAuditLog::log($model, PayrollAuditLog::ACTION_DELETED, $model->getAuditableAttributes());
            }
        });
    }

    /**
     * Check if action should be audited
     */
    protected function shouldAudit(string $action): bool
    {
        // Skip if in seeding mode
        if (app()->runningInConsole() && !property_exists($this, 'auditInConsole')) {
            return false;
        }

        // Check excluded actions
        $excluded = property_exists($this, 'excludeAuditActions')
            ? $this->excludeAuditActions
            : [];

        return !in_array($action, $excluded);
    }

    /**
     * Get attributes to include in audit log
     */
    protected function getAuditableAttributes(): array
    {
        // Use only specified attributes if defined
        if (property_exists($this, 'auditableAttributes')) {
            return collect($this->getAttributes())
                ->only($this->auditableAttributes)
                ->toArray();
        }

        // Exclude sensitive/large fields
        $excluded = ['password', 'remember_token', 'calculation_snapshot', 'earnings', 'deductions'];

        return collect($this->getAttributes())
            ->except($excluded)
            ->toArray();
    }

    /**
     * Log a custom action
     */
    public function logAction(string $action, ?array $oldValues = null, ?array $newValues = null, ?array $context = null): PayrollAuditLog
    {
        return PayrollAuditLog::log($this, $action, $oldValues, $newValues, $context);
    }

    /**
     * Log status change with old and new status
     */
    public function logStatusChange(string $oldStatus, string $newStatus, ?string $reason = null): PayrollAuditLog
    {
        return PayrollAuditLog::logStatusChange($this, $oldStatus, $newStatus, $reason);
    }

    /**
     * Get audit logs for this model
     */
    public function auditLogs()
    {
        return PayrollAuditLog::forModel($this)->orderBy('created_at', 'desc');
    }
}
