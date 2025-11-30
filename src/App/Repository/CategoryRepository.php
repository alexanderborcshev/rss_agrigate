<?php
namespace App\Repository;

use App\Bootstrap;
use App\Cache;
use App\Util;
use PDO;

class CategoryRepository extends BaseRepository
{
    protected ?string $tableName = 'category';

    public function getOrCreateByName($name): array
    {
        $slug = Util::slugify($name);
        $stmt = $this->db->prepare(
            'INSERT INTO ' . $this->tableName . ' (name, slug)
             VALUES (:name, :slug)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
        );
        $stmt->execute([':name' => $name, ':slug' => $slug]);
        $id = (int)$this->db->lastInsertId();

        return ['id' => $id, 'name' => $name, 'slug' => $slug];
    }

    public function getBySlug($slug)
    {
        $stmt = $this->db->prepare('SELECT id, name, slug FROM ' . $this->tableName . ' WHERE slug = :slug');
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch();
    }
}
