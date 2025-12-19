<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class EmployeeFamily extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'employee_id',
        'name',
        'relation',
        'is_emergency_contact',
        'phone',
        'is_bpjs_dependent',
    ];

    protected $casts = [
        'is_emergency_contact' => 'boolean',
        'is_bpjs_dependent' => 'boolean',
    ];

    /**
     * Attributes to include in audit
     */
    protected $auditInclude = [
        'employee_id',
        'name',
        'relation',
        'is_emergency_contact',
        'phone',
        'is_bpjs_dependent',
    ];

    /**
     * Generate tags for the audit
     */
    public function generateTags(): array
    {
        return ['employee-family', 'hr'];
    }

    /**
     * Relationships
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Accessors
     */
    public function getRelationLabelAttribute(): string
    {
        return match ($this->relation) {
            'spouse' => 'Suami/Istri',
            'child' => 'Anak',
            'parent' => 'Orang Tua',
            'sibling' => 'Saudara Kandung',
            default => '-'
        };
    }

    public function getRelationColorAttribute(): string
    {
        return match ($this->relation) {
            'spouse' => 'pink',
            'child' => 'blue',
            'parent' => 'green',
            'sibling' => 'purple',
            default => 'gray'
        };
    }

    /**
     * Scopes
     */
    public function scopeByRelation($query, $relation)
    {
        return $query->where('relation', $relation);
    }

    public function scopeEmergencyContacts($query)
    {
        return $query->where('is_emergency_contact', true);
    }

    public function scopeBpjsDependents($query)
    {
        return $query->where('is_bpjs_dependent', true);
    }

    public function scopeSpouses($query)
    {
        return $query->where('relation', 'spouse');
    }

    public function scopeChildren($query)
    {
        return $query->where('relation', 'child');
    }

    /**
     * Audit Transformations
     */
    public function transformAudit(array $data): array
    {
        try {
            // Add readable employee name
            if (isset($data['old_values']['employee_id']) && $data['old_values']['employee_id']) {
                $oldEmp = Employee::find($data['old_values']['employee_id']);
                $data['old_values']['employee_name'] = $oldEmp ? $oldEmp->full_name : 'None';
            }

            if (isset($data['new_values']['employee_id']) && $data['new_values']['employee_id']) {
                $newEmp = Employee::find($data['new_values']['employee_id']);
                $data['new_values']['employee_name'] = $newEmp ? $newEmp->full_name : 'None';
            }

            // Add readable relation labels
            if (isset($data['old_values']['relation'])) {
                $data['old_values']['relation_label'] = $this->getRelationLabel($data['old_values']['relation']);
            }

            if (isset($data['new_values']['relation'])) {
                $data['new_values']['relation_label'] = $this->getRelationLabel($data['new_values']['relation']);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to transform employee family audit data', [
                'error' => $e->getMessage()
            ]);
        }

        return $data;
    }

    private function getRelationLabel($relation): string
    {
        return match ($relation) {
            'spouse' => 'Suami/Istri',
            'child' => 'Anak',
            'parent' => 'Orang Tua',
            'sibling' => 'Saudara Kandung',
            default => '-'
        };
    }
}