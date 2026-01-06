<?php

namespace Services;

use PDO;
use PDOException;

class Database {

    private static ?self $instance = null;
    private ?PDO $connection = null;

    // O construtor é privado para prevenir a criação de instâncias diretas
    private function __construct()
    {
        try {
            $drive   = getenv('DB_CONNECTION', 'mysql');
            $host    = getenv('DB_HOST', 'localhost');
            $port    = getenv('DB_PORT', '3306');
            $dbname  = getenv('DB_DATABASE', '');
            $charset = 'utf8mb4';
            $user    = getenv('DB_USERNAME', 'root');
            $pass    = getenv('DB_PASSWORD', '');

            $dsn = "$drive:host=$host;port=$port;dbname=$dbname;charset=$charset";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Em um app real, isso seria logado em vez de exibido
            throw new PDOException("Connection Error: " . $e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Pega a instância única da classe Database (Singleton).
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna o objeto de conexão PDO.
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function query($args)
    {
        return $this->connection->query($args);
    }

    public function prepare($args)
    {
        return $this->connection->prepare($args);
    }

    public function exec($args)
    {
        return $this->connection->exec($args);
    }

    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    // Previne a clonagem da instância
    private function __clone() { }

    // Previne a desserialização da instância
    public function __wakeup() { }
}