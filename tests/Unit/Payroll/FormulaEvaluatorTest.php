<?php

namespace Tests\Unit\Payroll;

use Tests\TestCase;
use App\Services\Payroll\FormulaEvaluator;

/**
 * Unit tests for FormulaEvaluator
 */
class FormulaEvaluatorTest extends TestCase
{
    /** @test */
    public function it_evaluates_simple_addition()
    {
        $evaluator = new FormulaEvaluator();
        $evaluator->setVariables(['A' => 10, 'B' => 5]);

        $this->assertEquals(15, $evaluator->evaluate('A + B'));
    }

    /** @test */
    public function it_evaluates_multiplication()
    {
        $evaluator = new FormulaEvaluator();
        $evaluator->setVariables(['BASE' => 5000000, 'PRORATE_FACTOR' => 0.5]);

        $this->assertEquals(2500000, $evaluator->evaluate('BASE * PRORATE_FACTOR'));
    }

    /** @test */
    public function it_evaluates_overtime_formula()
    {
        $evaluator = new FormulaEvaluator();
        $evaluator->setVariables([
            'OT_HOURS' => 10,
            'OT_RATE' => 20000,
            'OT_MULT' => 1.5,
        ]);

        // 10 * 20000 * 1.5 = 300000
        $this->assertEquals(300000, $evaluator->evaluate('OT_HOURS * OT_RATE * OT_MULT'));
    }

    /** @test */
    public function it_handles_parentheses()
    {
        $evaluator = new FormulaEvaluator();
        $evaluator->setVariables(['A' => 10, 'B' => 5, 'C' => 2]);

        // (10 + 5) * 2 = 30
        $this->assertEquals(30, $evaluator->evaluate('(A + B) * C'));
    }

    /** @test */
    public function it_handles_division()
    {
        $evaluator = new FormulaEvaluator();
        $evaluator->setVariables([
            'BASIC_SALARY' => 5000000,
            'MONTHLY_HOURS' => 173,
        ]);

        // 5000000 / 173 ≈ 28901.73
        $result = $evaluator->evaluate('BASIC_SALARY / MONTHLY_HOURS');
        $this->assertEqualsWithDelta(28901.73, $result, 0.1);
    }

    /** @test */
    public function it_handles_division_by_zero()
    {
        $evaluator = new FormulaEvaluator();
        $evaluator->setVariables(['A' => 100, 'B' => 0]);

        // Should return 0 instead of error
        $this->assertEquals(0, $evaluator->evaluate('A / B'));
    }

    /** @test */
    public function it_validates_formulas()
    {
        $evaluator = new FormulaEvaluator();
        $evaluator->setVariables([
            'BASE' => 1000,
            'FACTOR' => 1,
        ]);

        // Valid formula
        $result = $evaluator->validate('BASE * FACTOR');
        $this->assertTrue($result['valid']);

        // Unknown variable
        $result = $evaluator->validate('BASE * UNKNOWN');
        $this->assertFalse($result['valid']);
        $this->assertContains('Unknown variable: UNKNOWN', $result['errors']);
    }

    /** @test */
    public function it_evaluates_complex_payroll_formula()
    {
        $evaluator = new FormulaEvaluator();
        $evaluator->setVariables(FormulaEvaluator::getPayrollContext([
            'base_amount' => 5000000,
            'working_days' => 22,
            'present_days' => 20,
        ]));

        // Prorate: BASE * (PRESENT_DAYS / WORKING_DAYS)
        $result = $evaluator->evaluate('BASE * (PRESENT_DAYS / WORKING_DAYS)');

        // 5000000 * (20/22) = 4545454.54...
        $this->assertEqualsWithDelta(4545454.54, $result, 1);
    }

    /** @test */
    public function it_rejects_invalid_expressions()
    {
        $evaluator = new FormulaEvaluator();
        $evaluator->setVariables(['A' => 10]);

        $this->expectException(\InvalidArgumentException::class);
        $evaluator->evaluate('A; DROP TABLE users;');
    }
}
