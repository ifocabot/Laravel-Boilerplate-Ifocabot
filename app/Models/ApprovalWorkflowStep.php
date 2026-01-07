<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflowStep extends Model
{
    protected $fillable = [
        'workflow_id',
        'step_order',
        'approver_type',
        'approver_value',
        'is_required',
        'can_skip_if_same',
        'conditions',
        'on_resolution_fail',
        'failure_message',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'is_required' => 'boolean',
        'can_skip_if_same' => 'boolean',
        'conditions' => 'array',
    ];

    /**
     * Approver types - expanded for ERP
     */
    public const TYPE_DIRECT_SUPERVISOR = 'direct_supervisor';
    public const TYPE_RELATIVE_LEVEL = 'relative_level';
    public const TYPE_SPECIFIC_USER = 'specific_user';
    public const TYPE_ROLE = 'role';
    public const TYPE_DEPARTMENT_HEAD = 'department_head';
    public const TYPE_COST_CENTER_OWNER = 'cost_center_owner';

    // Legacy types (for backward compatibility)
    public const TYPE_POSITION_LEVEL = 'position_level';
    public const TYPE_NEXT_LEVEL_UP = 'next_level_up';
    public const TYPE_SECOND_LEVEL_UP = 'second_level_up';

    /**
     * Resolution failure behaviors
     */
    public const ON_FAIL_FAIL_REQUEST = 'fail_request';
    public const ON_FAIL_SKIP_STEP = 'skip_step';

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Check if this step should be skipped when resolution fails
     */
    public function shouldSkipOnFail(): bool
    {
        return $this->on_resolution_fail === self::ON_FAIL_SKIP_STEP;
    }

    /**
     * Check if this step should fail the request when resolution fails
     */
    public function shouldFailRequestOnFail(): bool
    {
        return $this->on_resolution_fail === self::ON_FAIL_FAIL_REQUEST
            || $this->on_resolution_fail === null;
    }

    /**
     * Get the failure message with placeholders replaced
     */
    public function getFormattedFailureMessage(array $context = []): string
    {
        $message = $this->failure_message ?? 'Tidak dapat menentukan approver untuk step ' . $this->step_order;

        // Replace placeholders like {field_name}
        foreach ($context as $key => $value) {
            if (is_scalar($value)) {
                $message = str_replace('{' . $key . '}', (string) $value, $message);
            }
        }

        return $message;
    }

    /**
     * Get approver type label
     */
    public function getApproverTypeLabelAttribute(): string
    {
        return match ($this->approver_type) {
            self::TYPE_DIRECT_SUPERVISOR => 'Atasan Langsung',
            self::TYPE_RELATIVE_LEVEL => 'Berdasarkan Level Relatif',
            self::TYPE_POSITION_LEVEL => 'Berdasarkan Level',
            self::TYPE_SPECIFIC_USER => 'User Tertentu',
            self::TYPE_ROLE => 'Berdasarkan Role',
            self::TYPE_DEPARTMENT_HEAD => 'Kepala Departemen',
            self::TYPE_COST_CENTER_OWNER => 'Owner Cost Center',
            self::TYPE_NEXT_LEVEL_UP => 'Level +1 (Atasan)',
            self::TYPE_SECOND_LEVEL_UP => 'Level +2 (Skip-Level)',
            default => $this->approver_type,
        };
    }

    /**
     * Get available approver types
     */
    public static function getApproverTypes(): array
    {
        return [
            self::TYPE_DIRECT_SUPERVISOR => 'Atasan Langsung',
            self::TYPE_RELATIVE_LEVEL => 'Level Relatif (+N)',
            self::TYPE_ROLE => 'Berdasarkan Role',
            self::TYPE_SPECIFIC_USER => 'User Tertentu',
            self::TYPE_DEPARTMENT_HEAD => 'Kepala Departemen',
            self::TYPE_COST_CENTER_OWNER => 'Owner Cost Center',
        ];
    }
}
