<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'default_quota',
        'requires_attachment',
        'max_consecutive_days',
        'is_paid',
        'is_active',
        'description',
    ];

    protected $casts = [
        'default_quota' => 'integer',
        'max_consecutive_days' => 'integer',
        'requires_attachment' => 'boolean',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Common leave type codes
     */
    public const CODE_ANNUAL = 'ANNUAL';
    public const CODE_SICK = 'SICK';
    public const CODE_UNPAID = 'UNPAID';
    public const CODE_MATERNITY = 'MATERNITY';
    public const CODE_PATERNITY = 'PATERNITY';

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
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

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getIsPaidLabelAttribute(): string
    {
        return $this->is_paid ? 'Dibayar' : 'Tidak Dibayar';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->is_active
            ? 'bg-green-100 text-green-700'
            : 'bg-gray-100 text-gray-600';
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Get active leave type by code
     */
    public static function getByCode(string $code): ?self
    {
        return self::active()->byCode($code)->first();
    }
}
