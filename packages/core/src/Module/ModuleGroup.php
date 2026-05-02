<?php

declare(strict_types=1);

namespace Marko\Core\Module;

/**
 * Value object representing a module group for lazy loading and eviction.
 */
readonly class ModuleGroup
{
    /**
     * @param string $name Group identifier (e.g., "admin", "mail")
     * @param string $moduleName The module this group belongs to
     * @param array<string> $routes Route patterns (e.g., ["/admin/*"])
     * @param string|null $idleTimeout Idle timeout duration (e.g., "5m", "1h")
     * @param bool $isCore Whether this is a core group (never evicted)
     * @param \DateTimeImmutable $lastUsed Last time the group was activated
     */
    public function __construct(
        public string $name,
        public string $moduleName,
        public array $routes = [],
        public ?string $idleTimeout = null,
        public bool $isCore = false,
        public \DateTimeImmutable $lastUsed = new \DateTimeImmutable(),
    ) {}

    /**
     * Mark this group as used (update lastUsed timestamp).
     */
    public function markUsed(): self
    {
        return new self(
            name: $this->name,
            moduleName: $this->moduleName,
            routes: $this->routes,
            idleTimeout: $this->idleTimeout,
            isCore: $this->isCore,
            lastUsed: new \DateTimeImmutable(),
        );
    }

    /**
     * Check if this group is idle for longer than the given duration.
     */
    public function isIdle(string $duration): bool
    {
        $idleDuration = $this->parseDuration($duration);
        $now = new \DateTimeImmutable();
        
        return $this->lastUsed->add($idleDuration) <= $now;
    }

    /**
     * Parse duration string like "5m", "1h", "10m" to DateInterval.
     */
    private function parseDuration(string $duration): \DateInterval
    {
        // Handle shorthand: 5m = 5 minutes, 1h = 1 hour
        if (preg_match('/^(\d+)(m|h|d)$/', $duration, $matches)) {
            $value = (int) $matches[1];
            $unit = $matches[2];

            return match ($unit) {
                'm' => new \DateInterval("PT{$value}M"),
                'h' => new \DateInterval("PT{$value}H"),
                'd' => new \DateInterval("P{$value}D"),
                default => new \DateInterval("PT{$value}M"),
            };
        }

        // Fallback to standard parsing
        return new \DateInterval($duration);
    }

    /**
     * Get effective idle timeout (group-specific or default).
     */
    public function getEffectiveTimeout(?string $default = '5m'): string
    {
        return $this->idleTimeout ?? $default;
    }
}