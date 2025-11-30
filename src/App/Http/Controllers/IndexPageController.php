<?php
namespace App\Http\Controllers;

use App\Bootstrap;
use App\Http\Requests\RequestInterface;
use App\Repository\CategoryRepository;
use App\Repository\NewsRepository;

class IndexPageController extends BaseController implements ControllerInterface
{
    public function run(RequestInterface $request): void
    {
        $cfg = Bootstrap::config();
        $perPage = (int)$cfg['app']['items_per_page'];

        $categoriesRepository = new CategoryRepository();
        $newsRepository = new NewsRepository();

        $categorySlug = $request->getCategory();
        $categoryId = null;
        if ($categorySlug !== '') {
            $categoryRow = $categoriesRepository->getBySlug($categorySlug);
            if ($categoryRow && isset($categoryRow['id'])) {
                $categoryId = (int)$categoryRow['id'];
            }
        }

        $filters = [
            'category_id' => $categoryId,
            'date_from' => $request->getDateFrom(),
            'date_to' => $request->getDateTo(),
        ];

        $cacheKey = 'list_' . md5(json_encode([$filters, $request->getPage(), $perPage]));

        $result = $this->cache->get($cacheKey);

        if (!is_array($result)) {
            $list = $newsRepository->findByFilters($filters, $request->getPage(), $perPage);
            $categories = $categoriesRepository->getAll();
            $result = [
                'list' => $list,
                'categories' => $categories,
            ];
            if ($list && $categories) {
                $this->cache->set($cacheKey, $result, 120);
            } else {
                $this->cache->abort($cacheKey);
            }
        }

        $this->render('index.php', [
            'categories' => $result['categories'],
            'catSlug' => $request->getCategory(),
            'dateFrom' => $request->getDateFrom(),
            'dateTo' => $request->getDateTo(),
            'result' => $result['list'],
            'urlWith' => function (array $overrides = []) use ($request): string {
                $query = array_filter($request->toArray(), static function ($v) {
                    return $v !== '' && $v !== null;
                });
                $query = array_merge($query, $overrides);
                return '?' . http_build_query($query);
            },
        ]);
    }

}
