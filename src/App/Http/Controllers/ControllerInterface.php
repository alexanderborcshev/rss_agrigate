<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestInterface;

interface ControllerInterface
{
    public function run(RequestInterface $request);
}