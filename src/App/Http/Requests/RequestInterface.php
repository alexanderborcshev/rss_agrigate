<?php

namespace App\Http\Requests;

interface RequestInterface
{
    public function toArray(): array;
}