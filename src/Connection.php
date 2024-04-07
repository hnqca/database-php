<?php

namespace Hnqca\Database;

use PDO;
use PDOException;

class Connection
{
    private ?PDO  $pdo = null;
    private array $config;

    /**
     * Connection constructor.
     *
     * @param array $config Array with database connection configuration.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Initialize database connection.
     *
     * @throws PDOException
     */
    private function initConnection()
    {
        $dsn = $this->config['driver'] . ":dbname=" . $this->config['name'] . ";host=" . $this->config['host'] . ";port=" . $this->config['port'] . ";charset=" . $this->config['charset'];

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_CASE               => PDO::CASE_NATURAL
        ];

        try {
            $this->pdo = new PDO($dsn, $this->config['user'], $this->config['pass'], $options);
        } catch (PDOException $e) {
            throw new PDOException('ERROR DATABASE: ' . $e->getMessage());
        }
    }

    /**
     * Get PDO instance for the database connection.
     *
     * @return PDO
     * @throws PDOException
     */
    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $this->initConnection();
        }

        return $this->pdo;
    }
}