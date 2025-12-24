<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalWorkflow extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Workflow types
     */
    public const TYPE_LEAVE = 'leave';
    public const TYPE_OVERTIME = 'overtime';
    public const TYPE_REIMBURSEMENT = 'reimbursement';

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalWorkflowStep::class, 'workflow_id')->orderBy('step_order');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'workflow_id');
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Get ordered steps
     */
    public function getStepsOrdered()
    {
        return $this->steps()->orderBy('step_order')->get();
    }

    /**
     * Get total steps count
     */
    public function getTotalStepsAttribute(): int
    {
        return $this->steps()->count();
    }

    /**
     * Get active workflow for type
     */
    public static function getActiveForType(string $type): ?self
    {
        return self::active()->forType($type)->first();
    }
}
