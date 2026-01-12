<?php

namespace App\Services\Payroll;

/**
 * Safe Formula Evaluator
 * 
 * Evaluates simple mathematical expressions with variable substitution.
 * Uses a whitelist approach for security - no eval(), no arbitrary code.
 */
class FormulaEvaluator
{
    // Whitelisted operators
    private const OPERATORS = ['+', '-', '*', '/', '(', ')', '.'];

    // Available variable context
    private array $variables = [];

    /**
     * Set variables for formula evaluation
     */
    public function setVariables(array $variables): self
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * Add a variable
     */
    public function addVariable(string $name, float|int $value): self
    {
        $this->variables[strtoupper($name)] = $value;
        return $this;
    }

    /**
     * Standard payroll context variables
     */
    public static function getPayrollContext(array $data): array
    {
        return [
            // Base amounts
            'BASE' => $data['base_amount'] ?? 0,
            'BASIC_SALARY' => $data['basic_salary'] ?? 0,
            'COMPONENT_AMOUNT' => $data['component_amount'] ?? 0,

            // Time factors
            'WORKING_DAYS' => $data['working_days'] ?? 22,
            'PRESENT_DAYS' => $data['present_days'] ?? 0,
            'PAID_DAYS' => $data['paid_days'] ?? 0,
            'ABSENT_DAYS' => $data['absent_days'] ?? 0,
            'LATE_DAYS' => $data['late_days'] ?? 0,

            // Proration factors
            'PRORATE_FACTOR' => $data['prorate_factor'] ?? 1,
            'ATTENDANCE_RATE' => $data['attendance_rate'] ?? 1,

            // Overtime
            'OT_HOURS' => $data['ot_hours'] ?? 0,
            'OT_MINUTES' => $data['ot_minutes'] ?? 0,
            'OT_RATE' => $data['ot_rate'] ?? 0,
            'OT_MULT' => $data['ot_multiplier'] ?? 1.5,

            // Late
            'LATE_MINUTES' => $data['late_minutes'] ?? 0,
            'LATE_RATE' => $data['late_rate'] ?? 1000,

            // Policy
            'MONTHLY_HOURS' => $data['monthly_hours'] ?? 173,
            'HOURLY_RATE' => $data['hourly_rate'] ?? 0,
            'DAILY_RATE' => $data['daily_rate'] ?? 0,
        ];
    }

    /**
     * Evaluate a formula expression
     * 
     * @throws \InvalidArgumentException if formula is invalid
     */
    public function evaluate(string $formula): float
    {
        // Normalize formula
        $formula = strtoupper(trim($formula));

        // Substitute variables
        $expression = $this->substituteVariables($formula);

        // Validate expression (only numbers, operators, whitespace)
        if (!$this->isValidExpression($expression)) {
            throw new \InvalidArgumentException("Invalid formula expression: {$formula}");
        }

        // Evaluate using safe method
        return $this->safeEvaluate($expression);
    }

    /**
     * Substitute variables with their values
     */
    private function substituteVariables(string $formula): string
    {
        // Sort by length descending to avoid partial replacements
        $vars = $this->variables;
        uksort($vars, fn($a, $b) => strlen($b) - strlen($a));

        foreach ($vars as $name => $value) {
            $formula = str_replace($name, (string) $value, $formula);
        }

        return $formula;
    }

    /**
     * Validate expression contains only safe characters
     */
    private function isValidExpression(string $expression): bool
    {
        // Remove whitespace
        $clean = preg_replace('/\s+/', '', $expression);

        // Only allow: digits, decimal point, operators, parentheses
        return preg_match('/^[\d\.\+\-\*\/\(\)]+$/', $clean) === 1;
    }

    /**
     * Safely evaluate a mathematical expression
     * Uses a simple recursive descent parser
     */
    private function safeEvaluate(string $expression): float
    {
        $expression = preg_replace('/\s+/', '', $expression);
        $pos = 0;

        return $this->parseExpression($expression, $pos);
    }

    private function parseExpression(string $expr, int &$pos): float
    {
        $result = $this->parseTerm($expr, $pos);

        while ($pos < strlen($expr)) {
            $op = $expr[$pos] ?? '';

            if ($op === '+') {
                $pos++;
                $result += $this->parseTerm($expr, $pos);
            } elseif ($op === '-') {
                $pos++;
                $result -= $this->parseTerm($expr, $pos);
            } else {
                break;
            }
        }

        return $result;
    }

    private function parseTerm(string $expr, int &$pos): float
    {
        $result = $this->parseFactor($expr, $pos);

        while ($pos < strlen($expr)) {
            $op = $expr[$pos] ?? '';

            if ($op === '*') {
                $pos++;
                $result *= $this->parseFactor($expr, $pos);
            } elseif ($op === '/') {
                $pos++;
                $divisor = $this->parseFactor($expr, $pos);
                if ($divisor == 0) {
                    return 0; // Avoid division by zero
                }
                $result /= $divisor;
            } else {
                break;
            }
        }

        return $result;
    }

    private function parseFactor(string $expr, int &$pos): float
    {
        // Handle parentheses
        if (($expr[$pos] ?? '') === '(') {
            $pos++; // Skip (
            $result = $this->parseExpression($expr, $pos);
            $pos++; // Skip )
            return $result;
        }

        // Handle negative numbers
        $negative = false;
        if (($expr[$pos] ?? '') === '-') {
            $negative = true;
            $pos++;
        }

        // Parse number
        $numStr = '';
        while ($pos < strlen($expr) && (ctype_digit($expr[$pos]) || $expr[$pos] === '.')) {
            $numStr .= $expr[$pos];
            $pos++;
        }

        $value = $numStr !== '' ? (float) $numStr : 0;

        return $negative ? -$value : $value;
    }

    /**
     * Validate formula syntax without evaluating
     */
    public function validate(string $formula): array
    {
        $errors = [];

        try {
            // Check for balanced parentheses
            $open = substr_count($formula, '(');
            $close = substr_count($formula, ')');
            if ($open !== $close) {
                $errors[] = 'Unbalanced parentheses';
            }

            // Check for unknown variables
            $normalized = strtoupper($formula);
            $knownVars = array_keys($this->variables);

            // Extract potential variable names (alphabetic sequences)
            preg_match_all('/[A-Z_]+/', $normalized, $matches);
            foreach ($matches[0] as $var) {
                if (!in_array($var, $knownVars)) {
                    $errors[] = "Unknown variable: {$var}";
                }
            }

            // Try to evaluate with dummy values
            $testEvaluator = new self();
            $testVars = array_fill_keys($knownVars, 1);
            $testEvaluator->setVariables($testVars);
            $testEvaluator->evaluate($formula);

        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
