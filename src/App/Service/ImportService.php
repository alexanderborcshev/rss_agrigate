<?php
namespace App\Service;

use App\Cache;
use App\Repository\CategoryRepository;
use App\Repository\NewsRepository;
use App\Rss\RssFetcher;

class ImportService
{
    private RssFetcher $fetcher;
    private CategoryRepository $categoryRepository;
    private NewsRepository $newsRepository;
    private Cache $cache;

    public function __construct(string $feedUrl)
    {
        $this->fetcher = new RssFetcher($feedUrl);
        $this->categoryRepository = new CategoryRepository();
        $this->newsRepository = new NewsRepository();
        $this->cache = new Cache();
    }

    public function run(): array
    {
        $items = $this->fetcher->fetch();
        $inserted = 0; $updated = 0;
        foreach ($items as $item) {
            $idBefore = $this->newsRepository->getByGuid($item['guid']);
            $id = $this->newsRepository->upsertByGuid($item);
            if ($id) {
                $catIds = [];
                foreach ($item['categories'] as $catName) {
                    if ($catName === '') {
                        continue;
                    }
                    $category = $this->categoryRepository->getOrCreateByName($catName);
                    if ($category) {
                        $catIds[] = (int)$category['id'];
                    }
                }
                if ($catIds) {
                    $this->newsRepository->linkCategories($id, $catIds);
                }
            }
            if ($idBefore) {
                $updated++;
            } else {
                $inserted++;
            }
        }
        $this->cache->bumpNamespace();
        return ['inserted' => $inserted, 'updated' => $updated, 'total' => count($items)];
    }
}
