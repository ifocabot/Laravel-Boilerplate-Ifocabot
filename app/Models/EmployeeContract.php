<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class EmployeeContract extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'employee_id',
        'contract_number',
        'type',
        'start_date',
        'end_date',
        'document_path',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $auditInclude = [
        'employee_id',
        'contract_number',
        'type',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function generateTags(): array
    {
        return ['employee-contract', 'hr'];
    }

    /**
     * Relationships
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpiring($query, $days = 30)
    {
        return $query->where('is_active', true)
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'pkwt' => 'PKWT (Kontrak)',
            'pkwtt' => 'PKWTT (Tetap)',
            'internship' => 'Magang',
            'probation' => 'Probation',
            default => '-'
        };
    }

    public function getStatusAttribute(): string
    {
        if (!$this->end_date) {
            return 'permanent';
        }

        $daysRemaining = now()->diffInDays($this->end_date, false);

        if ($daysRemaining < 0) {
            return 'expired';
        } elseif ($daysRemaining <= 30) {
            return 'expiring_soon';
        } else {
            return 'active';
        }
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // When creating new active contract, deactivate previous ones
        static::creating(function ($contract) {
            if ($contract->is_active) {
                self::where('employee_id', $contract->employee_id)
                    ->where('is_active', true)
                    ->update([
                        'is_active' => false,
                        'end_date' => $contract->start_date->copy()->subDay(),
                    ]);
            }
        });
    }
}