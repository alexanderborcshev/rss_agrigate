<?php

namespace App\Repository;

use App\Bootstrap;
use PDO;

class BaseRepository
{
    protected ?PDO $db;
    protected ?string $tableName = null;

    public function __construct()
    {
        $this->db = Bootstrap::db();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . $this->tableName . ' WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getAll(): array
    {
        return $this->db->query('SELECT id, name, slug FROM ' . $this->tableName . ' ORDER BY name')->fetchAll();
    }
}