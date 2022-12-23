<?php

namespace Config;

require_once 'headersConfig.php';

use App\Entities\DatabaseResult;
use PDO;
use PDOException;

class Database
{
    /**
     * Connection database object.
     *
     * @var PDO|null
     */
    private static ?PDO $connection = null;

    /**
     * Create a new connection or return an existing connection
     *
     * @return PDO|void|null
     */
    public static function getConnection()
    {
        try {
            if (is_null(self::$connection)) {
                self::$connection = new PDO(
                    "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_NAME'],
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASSWORD'],
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                );
            }
        } catch (PDOException $e) {
            if ($_ENV['APP_DEBUG']) {
                die("Could not connect to the database " . $_ENV['DB_NAME'] . ": " . $e->getMessage());
            }
            die("Could not connect to the server");
        }

        return self::$connection;
    }

    /**
     * Execute the given query. If the query has parameters, these must be represented by '?' symbol.
     * e.g: 'SELECT * FROM users WHERE id = ? AND email = ?'
     *
     * @param string $query
     * @param string|null $className
     * @param mixed ...$params
     * @return DatabaseResult
     */
    public static function execute(string $query, ?string $className = null, ...$params): DatabaseResult
    {
        $response = new DatabaseResult();

        $sentence = self::getConnection()->prepare($query);
        foreach ($params[0] as $index => $param) {
            $sentence->bindParam($index + 1, $params[0][$index]);
        }

        try {
            $response->setStatus($sentence->execute());
            if (!is_null($className)) {
                $result = $sentence->fetchAll(PDO::FETCH_CLASS, $className);
            } else {
                $result = $sentence->fetchAll(PDO::FETCH_OBJ);
            }
        } catch (PDOException $e) {
            $response->setStatus(false);
            $response->setError($e);
            return $response;
        }

        $response->setData($result);

        if (!sizeof($result))
            $response->setData(null);

        if (sizeof($result) == 1)
            $response->setData($result[0]);

        $response->setLastInsertId(Database::getConnection()->lastInsertId());
        $response->setRowsAffected($sentence->rowCount());

        return $response;
    }
}