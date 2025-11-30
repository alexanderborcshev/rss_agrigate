<?php
namespace App\Setup;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseInitializer
{
    private mixed $cfg;

    public function __construct($config)
    {
        $this->cfg = $config['db'];
    }

    private function pdoServer(): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $this->cfg['host'], $this->cfg['port'],
            $this->cfg['charset'] ?? 'utf8mb4'
        );
        return new PDO($dsn, $this->cfg['user'], $this->cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function databaseExists(): bool
    {
        try {
            $pdo = $this->pdoServer();
            $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :db');
            $stmt->execute([':db' => $this->cfg['dbname']]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException) {
            return false;
        }
    }

    public function createDatabaseIfMissing(): true
    {
        if ($this->databaseExists()) {
            return true;
        }
        $pdo = $this->pdoServer();
        $db = $this->cfg['dbname'];
        $charset = $this->cfg['charset'] ?? 'utf8mb4';
        $collate = $charset === 'utf8mb4' ? 'utf8mb4_unicode_ci' : null;
        $sql = sprintf('CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s%s',
            str_replace('`','``',$db),
            $charset,
            $collate ? ' COLLATE ' . $collate : ''
        );
        $pdo->exec($sql);
        return true;
    }

    public function applySchema($schemaPath): void
    {
        if (!is_file($schemaPath)) {
            throw new RuntimeException('Schema file not found: ' . $schemaPath);
        }

        $sql = file_get_contents($schemaPath);

        if ($sql === false) {
            throw new RuntimeException('Failed to read schema file');
        }

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->cfg['host'],
            $this->cfg['port'],
            $this->cfg['dbname'],
            $this->cfg['charset'] ?? 'utf8mb4'
        );

        $pdo = new PDO($dsn, $this->cfg['user'], $this->cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $statements = array_filter(array_map('trim', preg_split('/;\s*\n|;\r?\n|;$/m', $sql)));
        foreach ($statements as $stmt) {
            if ($stmt === '' || str_starts_with(ltrim($stmt), '--')) {
                continue;
            }
            $pdo->exec($stmt);
        }
    }
}
