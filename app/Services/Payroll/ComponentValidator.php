<?php

namespace App\Services\Payroll;

use App\Models\PayrollComponent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * ComponentValidator Service
 * 
 * Phase 3: Guardrail - Validates component configuration before use
 * Prevents misconfiguration that could cause calculation errors
 */
class ComponentValidator
{
    private array $errors = [];
    private array $warnings = [];

    /**
     * Validate a single component configuration
     */
    public function validate(PayrollComponent $component): bool
    {
        $this->errors = [];
        $this->warnings = [];

        // Validate based on calculation_type
        switch ($component->calculation_type) {
            case 'daily_rate':
                $this->validateDailyRate($component);
                break;
            case 'hourly_rate':
                $this->validateHourlyRate($component);
                break;
            case 'percentage':
                $this->validatePercentage($component);
                break;
            case 'formula':
                $this->validateFormula($component);
                break;
            case 'fixed':
                $this->validateFixed($component);
                break;
        }

        // Validate proration settings
        $this->validateProration($component);

        // Validate forfeit rules
        $this->validateForfeitRules($component);

        return empty($this->errors);
    }

    /**
     * Validate all components in the system
     */
    public function validateAll(): array
    {
        $components = PayrollComponent::where('is_active', true)->get();
        $results = [];

        foreach ($components as $component) {
            $isValid = $this->validate($component);
            $results[$component->code] = [
                'valid' => $isValid,
                'errors' => $this->errors,
                'warnings' => $this->warnings,
            ];
        }

        return $results;
    }

    /**
     * Validate employee component assignment
     */
    public function validateEmployeeComponent(
        int $employeeId,
        int $componentId,
        float $amount,
        ?string $effectiveFrom = null,
        ?string $effectiveTo = null
    ): bool {
        $this->errors = [];

        $component = PayrollComponent::find($componentId);
        if (!$component) {
            $this->errors[] = 'Component tidak ditemukan';
            return false;
        }

        // Validate amount based on calculation_type
        switch ($component->calculation_type) {
            case 'daily_rate':
            case 'hourly_rate':
                // Amount might be 0 if using rate_per_day from component
                break;
            case 'percentage':
                if ($amount < 0 || $amount > 100) {
                    $this->errors[] = 'Persentase harus antara 0 dan 100';
                }
                break;
            default:
                if ($amount < 0) {
                    $this->errors[] = 'Jumlah tidak boleh negatif';
                }
        }

        // Validate date range overlap
        if ($effectiveFrom || $effectiveTo) {
            $overlap = $this->checkDateOverlap(
                $employeeId,
                $componentId,
                $effectiveFrom,
                $effectiveTo
            );
            if ($overlap) {
                $this->errors[] = 'Tanggal efektif overlap dengan assignment yang sudah ada';
            }
        }

        return empty($this->errors);
    }

    /**
     * Check for date range overlap
     */
    private function checkDateOverlap(
        int $employeeId,
        int $componentId,
        ?string $effectiveFrom,
        ?string $effectiveTo
    ): bool {
        $query = \App\Models\EmployeePayrollComponent::where('employee_id', $employeeId)
            ->where('payroll_component_id', $componentId)
            ->where('is_active', true);

        if ($effectiveFrom && $effectiveTo) {
            // Check if new range overlaps with existing
            $query->where(function ($q) use ($effectiveFrom, $effectiveTo) {
                $q->whereBetween('effective_from', [$effectiveFrom, $effectiveTo])
                    ->orWhereBetween('effective_to', [$effectiveFrom, $effectiveTo])
                    ->orWhere(function ($q2) use ($effectiveFrom, $effectiveTo) {
                        $q2->where('effective_from', '<=', $effectiveFrom)
                            ->where('effective_to', '>=', $effectiveTo);
                    });
            });
        }

        return $query->exists();
    }

    /**
     * Validate daily_rate component
     */
    private function validateDailyRate(PayrollComponent $component): void
    {
        if (!$component->rate_per_day || $component->rate_per_day <= 0) {
            $this->errors[] = "Component {$component->code}: daily_rate requires rate_per_day > 0";
        }
    }

    /**
     * Validate hourly_rate component
     */
    private function validateHourlyRate(PayrollComponent $component): void
    {
        if (!$component->rate_per_hour || $component->rate_per_hour <= 0) {
            $this->warnings[] = "Component {$component->code}: hourly_rate tanpa rate_per_hour akan menggunakan formula dari basic salary";
        }
    }

    /**
     * Validate percentage component
     */
    private function validatePercentage(PayrollComponent $component): void
    {
        if ($component->percentage_value === null) {
            $this->errors[] = "Component {$component->code}: percentage requires percentage_value";
        } elseif ($component->percentage_value < 0 || $component->percentage_value > 100) {
            $this->errors[] = "Component {$component->code}: percentage_value must be 0-100";
        }
    }

    /**
     * Validate formula component
     */
    private function validateFormula(PayrollComponent $component): void
    {
        if (empty($component->calculation_formula)) {
            $this->errors[] = "Component {$component->code}: formula type requires calculation_formula";
        } else {
            // Basic syntax check - prevent dangerous patterns
            $dangerousPatterns = ['exec(', 'system(', 'shell_exec(', 'eval(', 'file_', 'fopen', 'mysql'];
            foreach ($dangerousPatterns as $pattern) {
                if (stripos($component->calculation_formula, $pattern) !== false) {
                    $this->errors[] = "Component {$component->code}: formula contains forbidden pattern: {$pattern}";
                }
            }
        }
    }

    /**
     * Validate fixed component (minimal rules)
     */
    private function validateFixed(PayrollComponent $component): void
    {
        // Fixed components are straightforward - just need amount in employee assignment
        // No specific validation needed here
    }

    /**
     * Validate proration settings
     */
    private function validateProration(PayrollComponent $component): void
    {
        $validTypes = ['none', 'daily', 'attendance'];
        $prorationValue = $component->proration_type ?? 'none';

        if (!in_array($prorationValue, $validTypes)) {
            $this->errors[] = "Component {$component->code}: invalid proration_type '{$prorationValue}'";
        }

        // If using proration, min_attendance_percent should be valid if set
        if ($component->min_attendance_percent !== null) {
            if ($component->min_attendance_percent < 0 || $component->min_attendance_percent > 100) {
                $this->errors[] = "Component {$component->code}: min_attendance_percent must be 0-100";
            }
        }
    }

    /**
     * Validate forfeit rules
     */
    private function validateForfeitRules(PayrollComponent $component): void
    {
        // Both forfeit flags set is unusual but allowed
        if ($component->forfeit_on_alpha && $component->forfeit_on_late) {
            $this->warnings[] = "Component {$component->code}: Both forfeit_on_alpha and forfeit_on_late are enabled";
        }
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get validation warnings
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Get first error message
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Throw validation exception if errors exist
     */
    public function throwIfInvalid(): void
    {
        if (!empty($this->errors)) {
            throw ValidationException::withMessages([
                'component' => $this->errors,
            ]);
        }
    }
}
