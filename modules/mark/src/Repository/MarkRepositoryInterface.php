<?php

declare(strict_types=1);

namespace Marko\Mark\Repository;

use Marko\Mark\Entity\Mark;
use Marko\Mark\Entity\Role;
use Marko\Database\Repository\RepositoryInterface;

/**
 * Interface for Mark entity repository.
 *
 * @extends RepositoryInterface<Mark>
 */
interface MarkRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a mark by email address.
     */
    public function findByEmail(
        string $email,
    ): ?Mark;

    /**
     * Get all roles for a user.
     *
     * @return array<Role>
     */
    public function getRolesForUser(
        int $userId,
    ): array;

    /**
     * Sync roles for a user, replacing all existing.
     *
     * @param array<int> $roleIds
     */
    public function syncRoles(
        int $userId,
        array $roleIds,
    ): void;
}
