<?php

namespace Facchini\Infrastructure\Database;

use mysqli;
use Exception;

class Connection
{
    private static ?mysqli $instance = null;

    public static function getInstance(): mysqli
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
            $port = getenv('DB_PORT') ?: '3306';
            $db = getenv('DB_NAME') ?: 'epi_guard';

            self::$instance = new mysqli($host, $user, $pass, $db, $port);

            if (self::$instance->connect_error) {
                throw new Exception("Falha na conexão MySQLi: " . self::$instance->connect_error);
            }

            self::$instance->set_charset("utf8mb4");
        }

        return self::$instance;
    }
}
