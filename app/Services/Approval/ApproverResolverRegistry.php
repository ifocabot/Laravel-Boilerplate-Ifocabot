<?php

namespace App\Services\Approval;

use App\Contracts\ApproverResolverInterface;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

/**
 * Registry for approver resolvers
 * 
 * Uses config-based mapping for deterministic, debuggable resolver lookup.
 */
class ApproverResolverRegistry
{
    /**
     * @var array<string, ApproverResolverInterface>
     */
    protected array $resolvers = [];

    /**
     * @var array<string, class-string<ApproverResolverInterface>>
     */
    protected array $resolverMap;

    public function __construct()
    {
        $this->resolverMap = config('approval.resolvers', []);
    }

    /**
     * Get resolver for the given approver type
     */
    public function get(string $approverType): ?ApproverResolverInterface
    {
        // Return cached instance if available
        if (isset($this->resolvers[$approverType])) {
            return $this->resolvers[$approverType];
        }

        // Look up in config map
        $resolverClass = $this->resolverMap[$approverType] ?? null;

        if (!$resolverClass) {
            Log::warning('No resolver registered for approver type', [
                'approver_type' => $approverType,
                'available_types' => array_keys($this->resolverMap),
            ]);
            return null;
        }

        // Instantiate and cache
        try {
            $resolver = app($resolverClass);

            if (!$resolver instanceof ApproverResolverInterface) {
                Log::error('Resolver does not implement ApproverResolverInterface', [
                    'approver_type' => $approverType,
                    'resolver_class' => $resolverClass,
                ]);
                return null;
            }

            $this->resolvers[$approverType] = $resolver;
            return $resolver;

        } catch (\Throwable $e) {
            Log::error('Failed to instantiate resolver', [
                'approver_type' => $approverType,
                'resolver_class' => $resolverClass,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Resolve approver using the appropriate resolver
     */
    public function resolve(string $approverType, Employee $requester, array $context, ?string $value): ApproverResolution
    {
        $resolver = $this->get($approverType);

        if (!$resolver) {
            return ApproverResolution::failed(
                'Resolver not found for type: ' . $approverType,
                ['approver_type' => $approverType]
            );
        }

        try {
            return $resolver->resolve($requester, $context, $value);
        } catch (\Throwable $e) {
            Log::error('Resolver threw exception', [
                'approver_type' => $approverType,
                'error' => $e->getMessage(),
                'requester_id' => $requester->id,
            ]);

            return ApproverResolution::failed(
                'Resolver error: ' . $e->getMessage(),
                ['exception' => get_class($e)]
            );
        }
    }

    /**
     * Check if a resolver exists for the given type
     */
    public function has(string $approverType): bool
    {
        return isset($this->resolverMap[$approverType]);
    }

    /**
     * Get all registered approver types
     */
    public function getTypes(): array
    {
        return array_keys($this->resolverMap);
    }

    /**
     * Register a resolver at runtime (for testing)
     */
    public function register(string $approverType, ApproverResolverInterface $resolver): void
    {
        $this->resolvers[$approverType] = $resolver;
        $this->resolverMap[$approverType] = get_class($resolver);
    }
}
