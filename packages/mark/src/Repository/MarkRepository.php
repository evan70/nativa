<?php

declare(strict_types=1);

namespace Marko\Mark\Repository;

use Marko\Mark\Entity\Mark;
use Marko\Mark\Entity\Role;
use Marko\Mark\Events\MarkCreated;
use Marko\Mark\Events\MarkUpdated;
use Marko\Database\Entity\Entity;
use Marko\Database\Exceptions\EntityException;
use Marko\Database\Exceptions\RepositoryException;
use Marko\Database\Repository\Repository;

/**
 * @extends Repository<Mark>
 */
class MarkRepository extends Repository implements MarkRepositoryInterface
{
    protected const string ENTITY_CLASS = Mark::class;

    /**
     * Find a mark by email address.
     */
    public function findByEmail(
        string $email,
    ): ?Mark {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Get all roles for a user.
     *
     * @return array<Role>
     * @throws EntityException
     */
    public function getRolesForUser(
        int $userId,
    ): array {
        $sql = 'SELECT r.* FROM roles r
            INNER JOIN mark_roles aur ON r.id = aur.role_id
            WHERE aur.user_id = ?';

        $rows = $this->connection->query($sql, [$userId]);

        $roleMetadata = $this->metadataFactory->parse(Role::class);

        return array_map(
            fn (array $row): Role => $this->hydrator->hydrate(
                Role::class,
                $row,
                $roleMetadata,
            ),
            $rows,
        );
    }

    /**
     * Sync roles for a user, replacing all existing.
     *
     * @param array<int> $roleIds
     */
    public function syncRoles(
        int $userId,
        array $roleIds,
    ): void {
        // Remove all existing roles for this user
        $sql = 'DELETE FROM mark_roles WHERE user_id = ?';
        $this->connection->execute($sql, [$userId]);

        // Attach the new roles
        foreach ($roleIds as $roleId) {
            $sql = 'INSERT INTO mark_roles (user_id, role_id) VALUES (?, ?)';
            $this->connection->execute($sql, [$userId, $roleId]);
        }
    }

    /**
     * Save a mark, dispatching appropriate events.
     *
     * @throws RepositoryException
     */
    public function save(
        Entity $entity,
    ): void {
        if (!$entity instanceof Mark) {
            parent::save($entity);

            return;
        }

        $isNew = $entity->id === null;

        parent::save($entity);

        $this->dispatchSaveEvent($entity, $isNew);
    }

    private function dispatchSaveEvent(
        Mark $user,
        bool $isNew,
    ): void {
        if ($this->eventDispatcher === null) {
            return;
        }

        if ($isNew) {
            $this->eventDispatcher->dispatch(new MarkCreated(
                user: $user,
            ));
        } else {
            $this->eventDispatcher->dispatch(new MarkUpdated(
                user: $user,
            ));
        }
    }
}
