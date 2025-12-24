<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EmployeeCertification extends Model
{
    protected $fillable = [
        'employee_id',
        'certification_id',
        'certification_number',
        'issue_date',
        'expiry_date',
        'file_path',
        'status',
        'verified_by',
        'verified_at',
        'cost',
        'company_sponsored',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
        'cost' => 'decimal:2',
        'company_sponsored' => 'boolean',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_PENDING = 'pending_verification';

    public const STATUS_LABELS = [
        'active' => 'Aktif',
        'expired' => 'Kadaluarsa',
        'revoked' => 'Dicabut',
        'pending_verification' => 'Menunggu Verifikasi',
    ];

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopePendingVerification($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-100 text-green-700',
            'expired' => 'bg-red-100 text-red-700',
            'revoked' => 'bg-gray-100 text-gray-600',
            'pending_verification' => 'bg-yellow-100 text-yellow-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getFormattedCostAttribute(): string
    {
        return 'Rp ' . number_format($this->cost ?? 0, 0, ',', '.');
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        $daysUntilExpiry = $this->days_until_expiry;
        return $daysUntilExpiry !== null && $daysUntilExpiry >= 0 && $daysUntilExpiry <= 30;
    }

    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    public function getExpiryBadgeClassAttribute(): string
    {
        if (!$this->expiry_date) {
            return 'bg-gray-100 text-gray-600';
        }

        $days = $this->days_until_expiry;

        if ($days < 0) {
            return 'bg-red-100 text-red-700';
        } elseif ($days <= 30) {
            return 'bg-yellow-100 text-yellow-700';
        } elseif ($days <= 90) {
            return 'bg-blue-100 text-blue-700';
        }
        return 'bg-green-100 text-green-700';
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    public function verify(int $verifierId): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'verified_by' => $verifierId,
            'verified_at' => now(),
        ]);
    }

    public function markExpired(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    public function revoke(): void
    {
        $this->update(['status' => self::STATUS_REVOKED]);
    }

    /**
     * Check and update status based on expiry date
     */
    public function checkExpiry(): void
    {
        if ($this->is_expired && $this->status === self::STATUS_ACTIVE) {
            $this->markExpired();
        }
    }
}
