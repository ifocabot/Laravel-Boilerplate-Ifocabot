<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSlipItem extends Model
{
    protected $fillable = [
        'payroll_slip_id',
        'payroll_component_id',
        'component_code',
        'line_key',
        'component_name',
        'type',
        'category',
        'base_amount',
        'final_amount',
        'meta',
        'source_type',
        'source_id',
        'display_order',
        'is_taxable',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'meta' => 'array',
        'display_order' => 'integer',
        'is_taxable' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function slip(): BelongsTo
    {
        return $this->belongsTo(PayrollSlip::class, 'payroll_slip_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(PayrollComponent::class, 'payroll_component_id');
    }

    /**
     * Scopes
     */
    public function scopeEarnings($query)
    {
        return $query->where('type', 'earning');
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', 'deduction');
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('component_code', $code);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', true);
    }

    /**
     * Accessors
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->final_amount, 0, ',', '.');
    }

    /**
     * Create item from component array (used during slip generation)
     */
    public static function createFromArray(int $slipId, array $item, string $type): self
    {
        return self::create([
            'payroll_slip_id' => $slipId,
            'payroll_component_id' => $item['component_id'] ?? null,
            'component_code' => $item['code'],
            'line_key' => self::generateLineKey($item),
            'component_name' => $item['name'],
            'type' => $type,
            'category' => $item['category'] ?? null,
            'base_amount' => $item['base_amount'] ?? $item['amount'],
            'final_amount' => $item['amount'],
            'meta' => $item['meta'] ?? null,
            'source_type' => $item['meta']['adjustment_id'] ?? null ? 'adjustment' : 'component',
            'source_id' => $item['meta']['adjustment_id'] ?? $item['component_id'] ?? null,
            'display_order' => $item['display_order'] ?? 0,
            'is_taxable' => $item['is_taxable'] ?? false,
        ]);
    }

    /**
     * ⭐ Upsert item - prevents duplicates on rerun
     * Uses line_key for proper uniqueness (handles multiple items with same code)
     */
    public static function upsertFromArray(int $slipId, array $item, string $type, int $displayOrder = 0): self
    {
        $lineKey = self::generateLineKey($item);

        return self::updateOrCreate(
            [
                'payroll_slip_id' => $slipId,
                'line_key' => $lineKey,
            ],
            [
                'payroll_component_id' => $item['component_id'] ?? null,
                'component_code' => $item['code'],
                'component_name' => $item['name'],
                'type' => $type,
                'category' => $item['category'] ?? null,
                'base_amount' => $item['base_amount'] ?? $item['amount'],
                'final_amount' => $item['amount'],
                'meta' => $item['meta'] ?? null,
                'source_type' => isset($item['meta']['adjustment_id']) ? 'adjustment' : 'component',
                'source_id' => $item['meta']['adjustment_id'] ?? $item['component_id'] ?? null,
                'display_order' => $displayOrder,
                'is_taxable' => $item['is_taxable'] ?? false,
            ]
        );
    }

    /**
     * Generate unique line key from item data
     * Format: {code}_{source_type}_{source_id} or {code} for simple items
     */
    public static function generateLineKey(array $item): string
    {
        $code = $item['code'];

        // If item has adjustment_id in meta, use that for uniqueness
        if (isset($item['meta']['adjustment_id'])) {
            return $code . '_adj_' . $item['meta']['adjustment_id'];
        }

        // If item has component_id, add it
        if (isset($item['component_id'])) {
            return $code . '_cmp_' . $item['component_id'];
        }

        // Default: just use code (for BPJS, tax, etc)
        return $code;
    }
}

