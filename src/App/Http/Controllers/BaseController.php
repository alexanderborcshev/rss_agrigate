<?php

namespace App\Http\Controllers;

use App\Cache;
use App\View\Renderer;

class BaseController
{
    private Renderer $renderer;
    protected Cache $cache;
    public function __construct()
    {
        $this->renderer = new Renderer();
        $this->cache = new Cache();
    }

    protected function render($view, $params = []): void
    {
        echo $this->renderer->render($view, $params);
    }
}