<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PayrollPeriod;
use App\Models\PayrollSlip;
use App\Models\PayrollSlipItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayrollAntiDuplicationTest extends TestCase
{
    /**
     * Test that PayrollPeriod isLocked() returns correct values
     */
    public function test_period_is_locked_returns_correct_values(): void
    {
        $draft = new PayrollPeriod(['status' => 'draft']);
        $processing = new PayrollPeriod(['status' => 'processing']);
        $approved = new PayrollPeriod(['status' => 'approved']);
        $paid = new PayrollPeriod(['status' => 'paid']);
        $closed = new PayrollPeriod(['status' => 'closed']);

        $this->assertFalse($draft->isLocked(), 'Draft should NOT be locked');
        $this->assertFalse($processing->isLocked(), 'Processing should NOT be locked');
        $this->assertFalse($approved->isLocked(), 'Approved should NOT be locked');
        $this->assertTrue($paid->isLocked(), 'Paid SHOULD be locked');
        $this->assertTrue($closed->isLocked(), 'Closed SHOULD be locked');
    }

    /**
     * Test that guardAgainstLock throws exception for locked periods
     */
    public function test_guard_against_lock_throws_exception_for_paid_period(): void
    {
        $paid = new PayrollPeriod([
            'status' => 'paid',
            'period_name' => 'Test Period'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Cannot.*Period.*is paid/');

        $paid->guardAgainstLock('test action');
    }

    /**
     * Test that guardAgainstLock does NOT throw for draft period
     */
    public function test_guard_against_lock_allows_draft_period(): void
    {
        $draft = new PayrollPeriod([
            'status' => 'draft',
            'period_name' => 'Draft Period'
        ]);

        // Should not throw
        $draft->guardAgainstLock('test action');
        $this->assertTrue(true, 'guardAgainstLock did not throw for draft period');
    }

    /**
     * Test that upsertFromArray method exists on PayrollSlipItem
     */
    public function test_upsert_from_array_method_exists(): void
    {
        $this->assertTrue(
            method_exists(PayrollSlipItem::class, 'upsertFromArray'),
            'upsertFromArray method should exist on PayrollSlipItem'
        );
    }
}
