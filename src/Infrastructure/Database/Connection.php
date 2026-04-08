<?php

namespace Facchini\Infrastructure\Database;

use PDO;
use PDOException;
use Exception;

class Connection
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $user = getenv('DB_USER') ?: 'postgres';
            $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'F@cchini2025';
            $port = getenv('DB_PORT') ?: '5432';
            $db = getenv('DB_NAME') ?: 'epi_guard';

            try {
                $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=disable";
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new Exception("Falha na conexão PostgreSQL (PDO): " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
