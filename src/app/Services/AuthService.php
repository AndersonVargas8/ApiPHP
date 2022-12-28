<?php

namespace App\Services;

use App\Models\User;
use DomainException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

class AuthService
{
    /**
     * Current logged id user
     *
     * @var int|null
     */
    private static ?int $currentIdUser = null;

    /**
     * Current logged user
     *
     * @var string|null
     */
    private static ?string $currentUser = null;

    /**
     * Current logged user roles
     *
     * @var array|null
     */

    private static ?array $currentUserRoles = null;

    /**
     * Set the current credentials
     *
     * @param User $user
     * @return void
     */
    public static function openSession(User $user): void
    {
        self::$currentIdUser = $user->getId();
        self::$currentUser = $user->getUser();

        $roles = $user->getRoles();
        self::$currentUserRoles = array();

        foreach ($roles as $role) {
            self::$currentUserRoles[] = $role->getDescription();
        }
    }

    /**
     * Remove the current credentials
     *
     * @return void
     */
    public static function closeSession(): void
    {
        self::$currentAppName = null;
        self::$currentUserRoles = null;
        self::$currentUser = null;
    }

    /**
     * Current app's name
     *
     * @var string|null
     */
    private static ?string $currentAppName = null;

    /**
     * Generate a JTW with the user id, username and roles in the payload.
     *
     * @param User|null $user If null the token is created with current credentials
     * @return string
     */
    public static function generateJWT(?User $user = null): string
    {
        if (!is_null($user)) {
            self::$currentIdUser = $user->getId();
            self::$currentUser = $user->getUser();

            $roles = $user->getRoles();
            self::$currentUserRoles = array();

            foreach ($roles as $role) {
                self::$currentUserRoles[] = $role->getDescription();
            }
        }

        $issuedAt = time();
        $expirationTime = $issuedAt + (60) * SESSION_MINUTES;
        $payload = array(
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => array(
                'app' => self::$currentAppName,
                'userId' => self::$currentIdUser,
                'username' => self::$currentUser,
                'roles' => self::$currentUserRoles
            )
        );

        $key = API_JWT_SECRET;
        $alg = 'HS256';

        return JWT::encode($payload, $key, $alg);
    }

    /**
     * Verify if the given token is valid with signature and expiration time
     *
     * @param string $jwt
     * @return bool
     */
    public static function validateToken(string $jwt): bool
    {
        $key = API_JWT_SECRET;
        $alg = 'HS256';

        try {
            $decoded = JWT::decode($jwt, new Key($key, $alg));
        } catch (SignatureInvalidException|ExpiredException|DomainException $e) {
            if ($_ENV['APP_DEBUG']) {
                print($e->getMessage());
            }
            return false;
        }

        self::$currentAppName = $decoded->data->app;
        self::$currentIdUser = $decoded->data->userId;
        self::$currentUser = $decoded->data->username;
        self::$currentUserRoles = $decoded->data->roles;

        return true;
    }

    /**
     * @return bool
     */
    public static function isLoggedUser(): bool
    {
        return !is_null(self::$currentUser);
    }

    /**
     * Get the id user of the current logged user
     *
     * @return int|null
     */
    public static function getLoggedUser(): ?int
    {
        return self::$currentIdUser;
    }

    /**
     * Get the current user's username
     *
     * @return string|null
     */
    public static function getCurrentUser(): ?string
    {
        return self::$currentUser;
    }

    /**
     * Get the id user of the current logged user
     *
     * @return array|null
     */
    public static function getLoggedUserRoles(): ?array
    {
        return self::$currentUserRoles;
    }

    /**
     * Get the current app's name
     *
     * @return string|null
     */
    public static function getAppName(): ?string
    {
        return self::$currentAppName;
    }

    public static function setAppName(string $appName): void
    {
        self::$currentAppName = $appName;
    }
}