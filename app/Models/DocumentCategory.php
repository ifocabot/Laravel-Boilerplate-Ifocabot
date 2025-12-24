<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentCategory extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'description',
        'allowed_file_types',
        'max_file_size_mb',
        'is_required_for_employees',
        'is_confidential',
        'access_roles',
        'retention_period_months',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'allowed_file_types' => 'array',
        'access_roles' => 'array',
        'is_required_for_employees' => 'boolean',
        'is_confidential' => 'boolean',
        'is_active' => 'boolean',
        'max_file_size_mb' => 'integer',
        'retention_period_months' => 'integer',
        'display_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(DocumentCategory::class, 'parent_id')->orderBy('display_order');
    }

    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required_for_employees', true);
    }

    public function scopeConfidential($query)
    {
        return $query->where('is_confidential', true);
    }

    public function getFullPathAttribute(): string
    {
        $path = [];
        $category = $this;

        while ($category) {
            array_unshift($path, $category->name);
            $category = $category->parent;
        }

        return implode(' > ', $path);
    }

    public function getAllowedFileTypesStringAttribute(): string
    {
        if (empty($this->allowed_file_types)) {
            return 'Semua tipe file';
        }

        return implode(', ', array_map('strtoupper', $this->allowed_file_types));
    }

    public function getMaxFileSizeFormattedAttribute(): string
    {
        return $this->max_file_size_mb . ' MB';
    }

    public function isFileTypeAllowed(string $extension): bool
    {
        if (empty($this->allowed_file_types)) {
            return true;
        }

        return in_array(strtolower($extension), array_map('strtolower', $this->allowed_file_types));
    }

    public function canUserAccess($user): bool
    {
        if (empty($this->access_roles)) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $userRoles = $user->getRoleNames()->toArray();
        return !empty(array_intersect($userRoles, $this->access_roles));
    }
}
