<?php

declare(strict_types=1);

namespace App;

/**
 * Class UserService
 *
 * Provides user management operations exposed via SOAP Web Service.
 */
class UserService
{
    /**
     * In-memory mock database of users.
     *
     * @var array<int, array{id: int, name: string, email: string, role: string, active: bool}>
     */
    private static array $users = [
        [
            'id'     => 1,
            'name'   => 'Tolgahan',
            'email'  => 'tolgahan@example.com',
            'role'   => 'Administrator',
            'active' => true,
        ],
        [
            'id'     => 2,
            'name'   => 'Acar',
            'email'  => 'acar@example.com',
            'role'   => 'Lead Developer',
            'active' => true,
        ],
    ];

    /**
     * Retrieve a simple list of user names (required by WSDL contract).
     *
     * @return string[]
     */
    public function users(): array
    {
        return array_column(self::$users, 'name');
    }

    /**
     * Backward-compatible alias for users().
     *
     * @return string[]
     */
    public function getUsers(): array
    {
        return $this->users();
    }

    /**
     * Retrieve all detailed user records.
     *
     * @return array<int, array{id: int, name: string, email: string, role: string, active: bool}>
     */
    public function getAllUserDetails(): array
    {
        return self::$users;
    }

    /**
     * Find a single user by ID.
     *
     * @param int $id
     * @return array{id: int, name: string, email: string, role: string, active: bool}|null
     */
    public function getUserById(int $id): ?array
    {
        foreach (self::$users as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Return total number of active users.
     *
     * @return int
     */
    public function countUsers(): int
    {
        return count(self::$users);
    }
}

