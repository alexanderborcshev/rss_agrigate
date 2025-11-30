<?php
namespace App\Repository;

use App\Bootstrap;
use PDO;

class NewsRepository extends BaseRepository
{
    protected ?string $tableName = 'news';
    public function upsertByGuid($item): ?int
    {
        $sql = 'INSERT INTO ' . $this->tableName . ' (guid, title, link, description, content, image_url, pub_date) 
                VALUES (:guid, :title, :link, :description, :content, :image_url, :pub_date)
                ON DUPLICATE KEY UPDATE 
                    title = VALUES(title),
                    link = VALUES(link),
                    description = VALUES(description),
                    content = VALUES(content),
                    image_url = VALUES(image_url),
                    pub_date = VALUES(pub_date),
                    updated_at = CURRENT_TIMESTAMP';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':guid' => $item['guid'],
            ':title' => $item['title'],
            ':link' => $item['link'],
            ':description' => $item['description'] ?? null,
            ':content' => $item['content'] ?? null,
            ':image_url' => $item['image_url'] ?? null,
            ':pub_date' => $item['pub_date'],
        ]);
        $row = $this->getByGuid($item['guid']);
        return $row ? (int)$row['id'] : null;
    }

    public function getByGuid($guid)
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . $this->tableName . ' WHERE guid = :guid');
        $stmt->execute([':guid' => $guid]);
        return $stmt->fetch();
    }

    public function linkCategories($newsId, $categoryIds): void
    {
        if (!$categoryIds) return;
        $sql = 'INSERT IGNORE INTO news_categories (news_id, category_id) VALUES (:nid, :cid)';
        $stmt = $this->db->prepare($sql);
        foreach ($categoryIds as $cid) {
            $stmt->execute([':nid' => $newsId, ':cid' => $cid]);
        }
    }

    public function findByFilters($filters, $page, $perPage): array
    {
        $params = [];
        $where = [];

        if (!empty($filters['date_from'])) {
            $where[] = 'n.pub_date >= :date_from';
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'n.pub_date <= :date_to';
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $joins = '';
        if (!empty($filters['category_id'])) {
            $joins = 'INNER JOIN news_categories nc ON nc.news_id = n.id AND nc.category_id = :cid';
            $params[':cid'] = (int)$filters['category_id'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $offset = max(0, ((int)$page - 1) * (int)$perPage);

        $sqlCount = 'SELECT COUNT(*) as cnt FROM ' . $this->tableName . ' n ' . $joins . ' ' . $whereSql;
        $stmt = $this->db->prepare($sqlCount);
        $stmt->execute($params);
        $total = (int)$stmt->fetch()['cnt'];

        $sql = 'SELECT n.* FROM ' . $this->tableName . ' n ' . $joins . ' ' . $whereSql . ' ORDER BY n.pub_date DESC, n.id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        return [
            'total' => $total,
            'items' => $items,
            'page' => (int)$page,
            'per_page' => (int)$perPage,
            'pages' => $perPage > 0 ? (int)ceil($total / $perPage) : 1,
        ];
    }
}
