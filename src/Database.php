<?php

namespace App;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database
{
    private $mysql;
    private $pgsql;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();

        $this->connectMySQL();
        $this->connectPgSQL();
    }

    private function connectMySQL()
    {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $db = $_ENV['DB_DATABASE'] ?? 'nqueue';
        $user = $_ENV['DB_USERNAME'] ?? 'root';
        $pass = $_ENV['DB_PASSWORD'] ?? '';

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

        try {
            $this->mysql = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            // Log error or handle gracefully
            // echo "MySQL Connection Error: " . $e->getMessage();
            $this->mysql = null;
        }
    }

    private function connectPgSQL()
    {
        $host = $_ENV['PG_HOST'] ?? '127.0.0.1';
        $port = $_ENV['PG_PORT'] ?? '5432';
        $db = $_ENV['PG_DATABASE'] ?? 'cpahdb';
        $user = $_ENV['PG_USERNAME'] ?? 'postgres';
        $pass = $_ENV['PG_PASSWORD'] ?? 'postgres';

        $dsn = "pgsql:host=$host;port=$port;dbname=$db";

        try {
            $this->pgsql = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            // Log error or handle gracefully
            // echo "PgSQL Connection Error: " . $e->getMessage();
            $this->pgsql = null;
        }
    }

    public function getMySQL(): ?PDO
    {
        return $this->mysql;
    }

    public function getPgSQL(): ?PDO
    {
        return $this->pgsql;
    }
}
