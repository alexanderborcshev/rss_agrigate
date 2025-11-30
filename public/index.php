<?php
require_once __DIR__ . '/../src/autoload.php';

use App\Http\Controllers\IndexPageController;
use App\Http\Requests\ListRequest;

$controller = new IndexPageController();
$request = new ListRequest(
    $_GET['page'] ?? 1,
    $_GET['category'] ?? '',
    $_GET['date_from'] ?? '',
    $_GET['date_to'] ?? ''
);
$controller->run($request);
