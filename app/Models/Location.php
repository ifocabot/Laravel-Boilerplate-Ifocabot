<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Location extends Model implements AuditableContract
{
    use Auditable;

    /**
     * Available location types
     */
    public const TYPES = [
        'site',
        'office',
        'warehouse',
        'zone',
        'rack',
        'bin',
        'room',
        'floor',
        'store',
    ];

    /**
     * Hierarchy rules: defines allowed child types for each parent type
     * Empty array means the type is a leaf node (no children allowed)
     */
    public const ALLOWED_CHILDREN = [
        'site' => ['office', 'warehouse', 'store'],
        'office' => ['room', 'floor'],
        'warehouse' => ['zone', 'rack', 'bin'],
        'zone' => ['rack', 'bin'],
        'rack' => ['bin'],
        'bin' => [], // leaf node
        'room' => [], // leaf node
        'floor' => ['room'],
        'store' => ['warehouse', 'bin'],
    ];

    /**
     * Types that are considered leaf nodes (cannot have children)
     */
    public const LEAF_TYPES = ['bin', 'room'];

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'type',
        'address',
        'latitude',
        'longitude',
        'radius_meters',
        'is_geofence_enabled',
        'is_stock_location',
        'is_assignable_to_employee',
        'allowed_child_types',
        'is_active',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius_meters' => 'integer',
        'is_geofence_enabled' => 'boolean',
        'is_stock_location' => 'boolean',
        'is_assignable_to_employee' => 'boolean',
        'allowed_child_types' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Attributes to include in audit
     */
    protected $auditInclude = [
        'parent_id',
        'name',
        'code',
        'type',
        'address',
        'latitude',
        'longitude',
        'radius_meters',
        'is_geofence_enabled',
        'is_stock_location',
        'is_assignable_to_employee',
        'allowed_child_types',
        'is_active',
    ];

    /**
     * Generate tags for the audit
     */
    public function generateTags(): array
    {
        return ['location', 'master-data'];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    /**
     * Get all descendants recursively
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors up to root
     */
    public function getAncestorsAttribute(): \Illuminate\Support\Collection
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope for active locations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for locations by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for locations with geofence enabled
     */
    public function scopeGeofenceEnabled($query)
    {
        return $query->where('is_geofence_enabled', true);
    }

    /**
     * Scope for stock locations (Inventory/Warehouse module)
     */
    public function scopeStockLocations($query)
    {
        return $query->where('is_stock_location', true);
    }

    /**
     * Scope for locations assignable to employees (HRIS module)
     */
    public function scopeAssignableToEmployee($query)
    {
        return $query->where('is_assignable_to_employee', true);
    }

    /**
     * Scope for root locations (no parent)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    // =========================================================================
    // GEOFENCE METHODS
    // =========================================================================

    /**
     * Check if location has geofence enabled and properly configured
     */
    public function hasGeofence(): bool
    {
        return $this->is_geofence_enabled
            && !is_null($this->latitude)
            && !is_null($this->longitude)
            && !is_null($this->radius_meters)
            && $this->radius_meters > 0;
    }

    /**
     * Check if coordinates are within this location's geofence
     */
    public function isWithinGeofence(float $latitude, float $longitude): bool
    {
        if (!$this->hasGeofence()) {
            return false;
        }

        $distance = $this->calculateDistance($latitude, $longitude);
        return $distance <= $this->radius_meters;
    }

    /**
     * Calculate distance from given coordinates to this location (in meters)
     */
    public function calculateDistance(float $latitude, float $longitude): float
    {
        if (!$this->latitude || !$this->longitude) {
            return PHP_FLOAT_MAX;
        }

        $earthRadius = 6371000; // meters

        $dLat = deg2rad($this->latitude - $latitude);
        $dLon = deg2rad($this->longitude - $longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($latitude)) * cos(deg2rad($this->latitude)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get formatted coordinates
     */
    public function getFormattedCoordinatesAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return number_format($this->latitude, 6) . ', ' . number_format($this->longitude, 6);
        }
        return null;
    }

    /**
     * Get Google Maps URL
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        return null;
    }

    // =========================================================================
    // HIERARCHY VALIDATION
    // =========================================================================

    /**
     * Check if this location type can have children
     */
    public function canHaveChildren(): bool
    {
        return !in_array($this->type, self::LEAF_TYPES);
    }

    /**
     * Check if a given type is allowed as a child of this location
     */
    public function isAllowedChildType(string $childType): bool
    {
        // Check custom allowed_child_types first (per-location override)
        if (!empty($this->allowed_child_types)) {
            return in_array($childType, $this->allowed_child_types);
        }

        // Fall back to default hierarchy rules
        $allowedTypes = self::ALLOWED_CHILDREN[$this->type] ?? [];
        return in_array($childType, $allowedTypes);
    }

    /**
     * Validate hierarchy rules for a potential child
     * Returns array with 'valid' boolean and 'message' string
     */
    public static function validateHierarchy(?int $parentId, string $childType): array
    {
        // No parent = root level, always allowed
        if (!$parentId) {
            return ['valid' => true, 'message' => 'OK'];
        }

        $parent = self::find($parentId);
        if (!$parent) {
            return ['valid' => false, 'message' => 'Parent location not found.'];
        }

        // Check if parent type can have children
        if (!$parent->canHaveChildren()) {
            return [
                'valid' => false,
                'message' => "Lokasi tipe '{$parent->type}' tidak dapat memiliki sub-lokasi."
            ];
        }

        // Check if child type is allowed under parent
        if (!$parent->isAllowedChildType($childType)) {
            $allowedTypes = self::ALLOWED_CHILDREN[$parent->type] ?? [];
            $allowedList = implode(', ', $allowedTypes);
            return [
                'valid' => false,
                'message' => "Tipe '{$childType}' tidak valid sebagai sub dari '{$parent->type}'. Tipe yang diizinkan: {$allowedList}"
            ];
        }

        return ['valid' => true, 'message' => 'OK'];
    }

    /**
     * Get the hierarchy path (breadcrumb)
     */
    public function getHierarchyPathAttribute(): string
    {
        $path = collect([$this->name]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }

        return $path->implode(' > ');
    }

    /**
     * Get depth level in hierarchy (0 = root)
     */
    public function getDepthAttribute(): int
    {
        return $this->ancestors->count();
    }

    // =========================================================================
    // AUDIT TRANSFORMATIONS
    // =========================================================================

    /**
     * Audit Transformations
     */
    public function transformAudit(array $data): array
    {
        try {
            // Add readable parent name
            if (isset($data['old_values']['parent_id']) && $data['old_values']['parent_id']) {
                $oldParent = Location::find($data['old_values']['parent_id']);
                $data['old_values']['parent_location'] = $oldParent ? $oldParent->name : 'None';
            }

            if (isset($data['new_values']['parent_id']) && $data['new_values']['parent_id']) {
                $newParent = Location::find($data['new_values']['parent_id']);
                $data['new_values']['parent_location'] = $newParent ? $newParent->name : 'None';
            }

            // Add readable type labels
            $typeLabels = [
                'site' => 'Site',
                'office' => 'Kantor',
                'warehouse' => 'Gudang',
                'zone' => 'Zone',
                'rack' => 'Rak',
                'bin' => 'Bin',
                'room' => 'Ruangan',
                'floor' => 'Lantai',
                'store' => 'Retail',
            ];

            if (isset($data['old_values']['type'])) {
                $data['old_values']['type_label'] = $typeLabels[$data['old_values']['type']] ?? $data['old_values']['type'];
            }

            if (isset($data['new_values']['type'])) {
                $data['new_values']['type_label'] = $typeLabels[$data['new_values']['type']] ?? $data['new_values']['type'];
            }

            // Add readable status
            if (isset($data['old_values']['is_active'])) {
                $data['old_values']['status_label'] = $data['old_values']['is_active'] ? 'Aktif' : 'Nonaktif';
            }

            if (isset($data['new_values']['is_active'])) {
                $data['new_values']['status_label'] = $data['new_values']['is_active'] ? 'Aktif' : 'Nonaktif';
            }

            // Add readable flag labels
            $flagLabels = [
                'is_geofence_enabled' => 'Geofence',
                'is_stock_location' => 'Lokasi Stok',
                'is_assignable_to_employee' => 'Dapat Ditugaskan ke Karyawan',
            ];

            foreach ($flagLabels as $flag => $label) {
                if (isset($data['old_values'][$flag])) {
                    $data['old_values']["{$flag}_label"] = $data['old_values'][$flag] ? "{$label}: Ya" : "{$label}: Tidak";
                }
                if (isset($data['new_values'][$flag])) {
                    $data['new_values']["{$flag}_label"] = $data['new_values'][$flag] ? "{$label}: Ya" : "{$label}: Tidak";
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to transform location audit data', [
                'error' => $e->getMessage()
            ]);
        }

        return $data;
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Get type label for display
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'site' => 'Site',
            'office' => 'Kantor',
            'warehouse' => 'Gudang',
            'zone' => 'Zone',
            'rack' => 'Rak',
            'bin' => 'Bin',
            'room' => 'Ruangan',
            'floor' => 'Lantai',
            'store' => 'Retail',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Get all available types as options for dropdowns
     */
    public static function getTypeOptions(): array
    {
        return [
            'site' => 'Site',
            'office' => 'Kantor',
            'warehouse' => 'Gudang',
            'zone' => 'Zone',
            'rack' => 'Rak',
            'bin' => 'Bin',
            'room' => 'Ruangan',
            'floor' => 'Lantai',
            'store' => 'Retail',
        ];
    }
}