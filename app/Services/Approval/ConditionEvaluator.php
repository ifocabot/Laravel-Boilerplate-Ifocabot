<?php

namespace App\Services\Approval;

use Illuminate\Support\Facades\Log;

/**
 * Evaluates step conditions against approval context
 * 
 * Supports operators: =, !=, >, <, >=, <=, in, not_in, exists, not_exists
 */
class ConditionEvaluator
{
    /**
     * Check if all conditions match the context
     * 
     * @param array|null $conditions Condition rules (null = always matches)
     * @param array $context Approval context
     * @return bool True if all conditions pass
     */
    public function matches(?array $conditions, array $context): bool
    {
        // No conditions = always matches
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($condition, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a single condition
     */
    protected function evaluateCondition(array $condition, array $context): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? null;

        if (!$field) {
            Log::warning('Condition missing field', ['condition' => $condition]);
            return false;
        }

        // Handle exists/not_exists operators (don't need context value)
        if ($operator === 'exists') {
            return array_key_exists($field, $context) && $context[$field] !== null;
        }

        if ($operator === 'not_exists') {
            return !array_key_exists($field, $context) || $context[$field] === null;
        }

        // Get context value using dot notation
        $contextValue = data_get($context, $field);

        return match ($operator) {
            '=', '==' => $contextValue == $value,
            '!=' => $contextValue != $value,
            '>' => is_numeric($contextValue) && $contextValue > $value,
            '<' => is_numeric($contextValue) && $contextValue < $value,
            '>=' => is_numeric($contextValue) && $contextValue >= $value,
            '<=' => is_numeric($contextValue) && $contextValue <= $value,
            'in' => is_array($value) && in_array($contextValue, $value),
            'not_in' => is_array($value) && !in_array($contextValue, $value),
            'contains' => is_string($contextValue) && str_contains($contextValue, $value),
            default => $contextValue == $value,
        };
    }

    /**
     * Get list of fields required by conditions that are missing from context
     */
    public function getMissingKeys(?array $conditions, array $context): array
    {
        if (empty($conditions)) {
            return [];
        }

        $missing = [];

        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? '=';

            if (!$field) {
                continue;
            }

            // exists/not_exists don't require the field to be present
            if (in_array($operator, ['exists', 'not_exists'])) {
                continue;
            }

            if (!array_key_exists($field, $context) && data_get($context, $field) === null) {
                $missing[] = $field;
            }
        }

        return array_unique($missing);
    }

    /**
     * Validate condition structure
     */
    public function validateConditions(?array $conditions): array
    {
        $errors = [];

        if (empty($conditions)) {
            return $errors;
        }

        $validOperators = ['=', '==', '!=', '>', '<', '>=', '<=', 'in', 'not_in', 'exists', 'not_exists', 'contains'];

        foreach ($conditions as $index => $condition) {
            if (!is_array($condition)) {
                $errors[] = "Condition at index {$index} must be an array";
                continue;
            }

            if (empty($condition['field'])) {
                $errors[] = "Condition at index {$index} missing 'field'";
            }

            $operator = $condition['operator'] ?? '=';
            if (!in_array($operator, $validOperators)) {
                $errors[] = "Condition at index {$index} has invalid operator: {$operator}";
            }

            if (in_array($operator, ['in', 'not_in']) && !is_array($condition['value'] ?? null)) {
                $errors[] = "Condition at index {$index} with operator '{$operator}' requires array value";
            }
        }

        return $errors;
    }
}
