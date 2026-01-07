<?php

namespace App\Services\Approval;

/**
 * Represents the result of an approver resolution attempt
 * 
 * Supports single or multiple approvers for future quorum/parallel approval
 */
class ApproverResolution
{
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_FAILED = 'failed';

    /**
     * @param string $status Resolution status: resolved, skipped, or failed
     * @param array<int> $approverIds Array of user IDs (supports multi-approver)
     * @param string|null $reason Human-readable reason for skip/failure
     * @param array<string, mixed> $meta Additional metadata for audit
     */
    public function __construct(
        public string $status,
        public array $approverIds = [],
        public ?string $reason = null,
        public array $meta = [],
    ) {
    }

    /**
     * Create a resolved result with single approver
     */
    public static function resolved(int $approverId, array $meta = []): self
    {
        return new self(self::STATUS_RESOLVED, [$approverId], null, $meta);
    }

    /**
     * Create a resolved result with multiple approvers
     */
    public static function resolvedMultiple(array $approverIds, array $meta = []): self
    {
        return new self(self::STATUS_RESOLVED, $approverIds, null, $meta);
    }

    /**
     * Create a skipped result
     */
    public static function skipped(string $reason, array $meta = []): self
    {
        return new self(self::STATUS_SKIPPED, [], $reason, $meta);
    }

    /**
     * Create a failed result
     */
    public static function failed(string $reason, array $meta = []): self
    {
        return new self(self::STATUS_FAILED, [], $reason, $meta);
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isSkipped(): bool
    {
        return $this->status === self::STATUS_SKIPPED;
    }

    /**
     * Get the first (primary) approver ID
     */
    public function getFirstApprover(): ?int
    {
        return $this->approverIds[0] ?? null;
    }

    /**
     * Check if resolution has any approvers
     */
    public function hasApprovers(): bool
    {
        return !empty($this->approverIds);
    }
}
