<?php
require_once __DIR__ . '/../src/autoload.php';

use App\Http\Controllers\ArticlePageController;
use App\Http\Requests\ArticleRequest;

$controller = new ArticlePageController();
$request = new ArticleRequest((int) $_GET['id']);
$controller->run($request);
