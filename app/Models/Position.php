<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Position extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'department_id',
        'name',
        'job_description',
    ];

    protected $casts = [
        'department_id' => 'integer',
    ];

    /**
     * Attributes to include in audit
     */
    protected $auditInclude = [
        'department_id',
        'name',
        'job_description',
    ];

    /**
     * Generate tags for the audit
     */
    public function generateTags(): array
    {
        return ['position', 'master-data'];
    }

    /**
     * Relationships
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Uncomment when Employee model is ready
    // public function employees(): HasMany
    // {
    //     return $this->hasMany(Employee::class);
    // }

    /**
     * Audit Transformations
     */
    public function transformAudit(array $data): array
    {
        try {
            // Add readable department name
            if (isset($data['old_values']['department_id']) && $data['old_values']['department_id']) {
                $oldDept = Department::find($data['old_values']['department_id']);
                $data['old_values']['department_name'] = $oldDept ? $oldDept->name : 'None';
            }

            if (isset($data['new_values']['department_id']) && $data['new_values']['department_id']) {
                $newDept = Department::find($data['new_values']['department_id']);
                $data['new_values']['department_name'] = $newDept ? $newDept->name : 'None';
            }

            // Truncate long job descriptions in audit
            if (isset($data['old_values']['job_description'])) {
                $data['old_values']['job_description_preview'] =
                    \Str::limit($data['old_values']['job_description'], 100);
            }

            if (isset($data['new_values']['job_description'])) {
                $data['new_values']['job_description_preview'] =
                    \Str::limit($data['new_values']['job_description'], 100);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to transform position audit data', [
                'error' => $e->getMessage()
            ]);
        }

        return $data;
    }

    /**
     * Accessors
     */
    public function getHasJobDescriptionAttribute(): bool
    {
        return !empty($this->job_description);
    }

    public function getJobDescriptionPreviewAttribute(): string
    {
        if (empty($this->job_description)) {
            return 'Belum ada deskripsi';
        }

        return \Str::limit($this->job_description, 100);
    }

    /**
     * Scopes
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeWithJobDescription($query)
    {
        return $query->whereNotNull('job_description')
            ->where('job_description', '!=', '');
    }

    public function scopeWithoutJobDescription($query)
    {
        return $query->whereNull('job_description')
            ->orWhere('job_description', '');
    }

    public function scopeRecentlyCreated($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get full position name with department
     */
    public function getFullNameAttribute(): string
    {
        return $this->name . ' - ' . ($this->department ? $this->department->name : 'No Department');
    }

    /**
     * Check if position has employees
     */
    public function hasEmployees(): bool
    {
        // Uncomment when Employee model is ready
        // return $this->employees()->exists();
        return false;
    }
}