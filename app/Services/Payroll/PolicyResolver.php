<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\PayrollPolicy;
use App\Models\PayrollPeriod;

/**
 * Policy Resolver Service
 * 
 * Resolves payroll policies with hierarchical scope:
 * employee → level → department → branch → company
 * 
 * Most specific scope wins.
 */
class PolicyResolver
{
    private Employee $employee;
    private ?PayrollPeriod $period = null;
    private ?\DateTimeInterface $asOfDate = null;
    private array $cache = [];

    // Default fallback values when no policy is found
    private const DEFAULTS = [
        'late.penalty_per_minute' => 1000,
        'overtime.multiplier' => 1.5,
        'overtime.hourly_rate' => null,  // null = calculate from salary
        'work.standard_monthly_hours' => 173,
        'bpjs.jkk_risk_class' => 'low',
    ];

    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }

    /**
     * Set payroll period context for effective date
     */
    public function forPeriod(PayrollPeriod $period): self
    {
        $this->period = $period;
        $this->asOfDate = $period->end_date;
        $this->cache = []; // Clear cache when context changes
        return $this;
    }

    /**
     * Set specific effective date
     */
    public function asOf(\DateTimeInterface $date): self
    {
        $this->asOfDate = $date;
        $this->cache = [];
        return $this;
    }

    /**
     * Get policy value
     * 
     * Resolution order:
     * 1. Period-level config (if available)
     * 2. Employee-scoped policy
     * 3. Level-scoped policy  
     * 4. Department-scoped policy
     * 5. Branch-scoped policy
     * 6. Company-wide policy
     * 7. Default fallback
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Check cache first
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        // 1. Check period-level config first (for backward compatibility)
        if ($this->period) {
            $periodValue = $this->getPeriodConfig($key);
            if ($periodValue !== null) {
                $this->cache[$key] = $periodValue;
                return $periodValue;
            }
        }

        // 2-6. Check hierarchical policies
        $policy = PayrollPolicy::getForEmployee($key, $this->employee, $this->asOfDate);
        if ($policy) {
            $value = $policy->decoded_value;
            $this->cache[$key] = $value;
            return $value;
        }

        // 7. Use default
        $fallback = $default ?? (self::DEFAULTS[$key] ?? null);
        $this->cache[$key] = $fallback;
        return $fallback;
    }

    /**
     * Get period-level config (maps to existing period columns)
     */
    private function getPeriodConfig(string $key): mixed
    {
        if (!$this->period) {
            return null;
        }

        return match ($key) {
            'late.penalty_per_minute' => $this->period->late_penalty_per_minute,
            'overtime.multiplier' => $this->period->overtime_multiplier,
            'overtime.hourly_rate' => $this->period->overtime_hourly_rate,
            'work.standard_monthly_hours' => $this->period->standard_monthly_hours,
            default => null,
        };
    }

    /**
     * Convenience getters
     */
    public function getLatePenaltyPerMinute(): float
    {
        return (float) $this->get('late.penalty_per_minute', 1000);
    }

    public function getOvertimeMultiplier(): float
    {
        return (float) $this->get('overtime.multiplier', 1.5);
    }

    public function getOvertimeHourlyRate(): ?float
    {
        $value = $this->get('overtime.hourly_rate');
        return $value !== null ? (float) $value : null;
    }

    public function getStandardMonthlyHours(): int
    {
        return (int) $this->get('work.standard_monthly_hours', 173);
    }

    public function getJkkRiskClass(): string
    {
        return (string) $this->get('bpjs.jkk_risk_class', 'low');
    }

    /**
     * Get all resolved policies for debugging/audit
     */
    public function getAllResolved(): array
    {
        $result = [];
        foreach (array_keys(self::DEFAULTS) as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }
}
