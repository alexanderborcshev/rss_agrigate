<?php

namespace App\Http\Requests;

readonly class ListRequest implements RequestInterface
{
    public function __construct(
        private int $page,
        private string $category,
        private string $date_from,
        private string $date_to
    ) {
    }

    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'category' => $this->category,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to
        ];
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getDateFrom(): string
    {
        return $this->date_from;
    }

    public function getDateTo(): string
    {
        return $this->date_to;
    }
}