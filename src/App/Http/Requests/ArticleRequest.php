<?php

namespace App\Http\Requests;

readonly class ArticleRequest implements RequestInterface
{
    public function __construct(
        private int $id,
    ) {
    }
    public function toArray(): array
    {
        return [
            'id' => $this->id
        ];
    }
    public function getId(): int
    {
        return $this->id;
    }
}