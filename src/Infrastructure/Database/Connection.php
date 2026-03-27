<?php

namespace epiGuard\Infrastructure\Database;

use mysqli;
use Exception;

class Connection
{
    private static ?mysqli $instance = null;

    public static function getInstance(): mysqli
    {
        if (self::$instance === null) {
            // Tenta carregar do ambiente ou usa padrões do XAMPP
            $host = getenv('DB_HOST') ?: 'localhost';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
            $port = getenv('DB_PORT') ?: '3306'; // Porta do MySQL do XAMPP
            $db = getenv('DB_NAME') ?: 'epi_guard';

            try {
                self::$instance = new mysqli($host, $user, $pass, $db, $port);
            } catch (\mysqli_sql_exception $e) {
                // Se a máquina destino recusou (2002), o MySQL pode não estar iniciado no XAMPP
                $extraMsg = strpos($e->getMessage(), 'recusou ativamente') !== false 
                            ? " Dica: O MySQL está iniciado no painel do XAMPP? A porta é $port?" : "";
                throw new Exception("Falha na conexão MySQLi: " . $e->getMessage() . $extraMsg);
            }

            if (self::$instance->connect_error) {
                throw new Exception("Falha na conexão MySQLi: " . self::$instance->connect_error);
            }

            self::$instance->set_charset("utf8mb4");
        }

        return self::$instance;
    }
}
