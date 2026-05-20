<?php

declare(strict_types=1);

namespace Marko\Mark;

use Marko\Mark\Entity\Mark;
use Marko\Mark\Repository\MarkRepositoryInterface;
use Marko\Mark\Repository\RoleRepositoryInterface;
use Marko\Authentication\AuthenticatableInterface;
use Marko\Authentication\Contracts\PasswordHasherInterface;
use Marko\Authentication\Contracts\UserProviderInterface;

readonly class MarkProvider implements UserProviderInterface
{
    /**
     * @param MarkRepositoryInterface<Mark> $userRepository
     * @param RoleRepositoryInterface<\Marko\Mark\Entity\Role> $roleRepository
     */
    public function __construct(
        private MarkRepositoryInterface $userRepository,
        /** @var RoleRepositoryInterface<\Marko\Mark\Entity\Role> */
        private RoleRepositoryInterface $roleRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {}

    public function retrieveById(
        int|string $identifier,
    ): ?AuthenticatableInterface {
        $user = $this->userRepository->find((int) $identifier);

        if (!$user instanceof Mark) {
            return null;
        }

        if ($user->isActive !== '1') {
            return null;
        }

        $this->loadRolesAndPermissions($user);

        return $user;
    }

    public function retrieveByCredentials(
        array $credentials,
    ): ?AuthenticatableInterface {
        $emailRaw = $credentials['email'] ?? null;
        $email = is_string($emailRaw) ? $emailRaw : null;

        if ($email === null) {
            return null;
        }

        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            return null;
        }

        if ($user->isActive !== '1') {
            return null;
        }

        $this->loadRolesAndPermissions($user);

        return $user;
    }

    public function validateCredentials(
        AuthenticatableInterface $user,
        array $credentials,
    ): bool {
        $passwordRaw = $credentials['password'] ?? '';
        $password = is_string($passwordRaw) ? $passwordRaw : '';

        return $this->passwordHasher->verify($password, $user->getAuthPassword());
    }

    public function retrieveByRememberToken(
        int|string $identifier,
        string $token,
    ): ?AuthenticatableInterface {
        $user = $this->userRepository->find((int) $identifier);

        if (!$user instanceof Mark) {
            return null;
        }

        if ($user->isActive !== '1') {
            return null;
        }

        if ($user->getRememberToken() !== $token) {
            return null;
        }

        $this->loadRolesAndPermissions($user);

        return $user;
    }

    public function updateRememberToken(
        AuthenticatableInterface $user,
        ?string $token,
    ): void {
        if (!$user instanceof Mark) {
            return;
        }

        $user->setRememberToken($token);

        $this->userRepository->save($user);
    }

    private function loadRolesAndPermissions(
        Mark $user,
    ): void {
        $roles = $this->userRepository->getRolesForUser($user->id ?? 0);

        $permissionKeys = [];

        foreach ($roles as $role) {
            if ($role->id === null) {
                continue;
            }

            $permissions = $this->roleRepository->getPermissionsForRole($role->id);

            foreach ($permissions as $permission) {
                $permissionKeys[] = $permission->key;
            }
        }

        $user->setRoles(
            roles: $roles,
            permissionKeys: array_unique($permissionKeys),
        );
    }
}
