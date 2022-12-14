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
    private static ?int $currentUser = null;

    /**
     * Current logged user roles
     *
     * @var array|null
     */

    private static ?array $currentUserRoles = null;

    /**
     * Generate a JTW with the user id, username and roles in the payload. The token expires in 20 minutes
     *
     * @param User $user
     * @return string
     */
    public static function generateJWT(User $user): string
    {
        self::$currentUser = $user->getId();

        $roles = $user->getRoles();
        self::$currentUserRoles = array();

        foreach ($roles as $role) {
            self::$currentUserRoles[] = $role->getDescription();
        }

        $issuedAt = time();
        $expirationTime = $issuedAt + (60) * 20; //20 minutes
        $payload = array(
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => array(
                'userId' => $user->getId(),
                'username' => $user->getUser(),
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

        self::$currentUser = $decoded->data->userId;
        self::$currentUserRoles = $decoded->data->roles;

        return true;
    }

    /**
     * Get the id user of the current logged user
     *
     * @return int|null
     */
    public static function getLoggedUser(): ?int
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

}