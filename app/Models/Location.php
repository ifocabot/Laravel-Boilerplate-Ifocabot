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

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'type',
        'address',
        'latitude',
        'longitude',
        'radius_meters',
        'is_active',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius_meters' => 'integer',
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
        'is_active',
    ];

    /**
     * Generate tags for the audit
     */
    public function generateTags(): array
    {
        return ['location', 'master-data'];
    }

    /**
     * Relationships
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

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
                'office' => 'Kantor',
                'warehouse' => 'Gudang',
                'store' => 'Retail',
                'bin' => 'Bin/Rak',
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
        } catch (\Exception $e) {
            \Log::warning('Failed to transform location audit data', [
                'error' => $e->getMessage()
            ]);
        }

        return $data;
    }

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
     * Check if location has geofence configured
     */
    public function hasGeofence(): bool
    {
        return !is_null($this->latitude)
            && !is_null($this->longitude)
            && !is_null($this->radius_meters);
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
}