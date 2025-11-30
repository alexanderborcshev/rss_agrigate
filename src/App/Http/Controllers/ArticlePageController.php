<?php
namespace App\Http\Controllers;

use App\Http\Requests\RequestInterface;
use App\Repository\NewsRepository;

class ArticlePageController extends BaseController implements ControllerInterface
{
    public function run(RequestInterface $request): void
    {
        if ($request->getId() <= 0) {
            http_response_code(400);
            echo 'Bad request';
        }

        $item = new NewsRepository()->getById($request->getId());

        if (!$item) {
            http_response_code(404);
            echo 'Not found';
        }

        $this->render('article.php', [
            'item' => $item,
        ]);
    }
}
