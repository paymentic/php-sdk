<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\DTO;

final readonly class ListEmailSuppressionsRequest
{
    /**
     * @param string|null $filterEmail exact address, comma-separated for several
     * @param string|null $filterReason BOUNCE|COMPLAINT|MANUAL|UNSUBSCRIBE, comma-separated for several
     * @param string|null $filterCreatedAtFrom for example "2026-11-01"
     * @param int|null $pageSize 1..100, API default 50
     */
    public function __construct(
        public ?string $filterEmail = null,
        public ?string $filterReason = null,
        public ?string $filterCreatedAtFrom = null,
        public ?string $filterCreatedAtTo = null,
        public ?int $pageNumber = null,
        public ?int $pageSize = null,
    ) {
    }

    public function toQueryString(): string
    {
        $params = [];

        $this->addIfNotNull($params, 'filter[email]', $this->filterEmail);
        $this->addIfNotNull($params, 'filter[reason]', $this->filterReason);
        $this->addIfNotNull($params, 'filter[createdAt][from]', $this->filterCreatedAtFrom);
        $this->addIfNotNull($params, 'filter[createdAt][to]', $this->filterCreatedAtTo);
        $this->addIfNotNull($params, 'page[number]', null !== $this->pageNumber ? (string) $this->pageNumber : null);
        $this->addIfNotNull($params, 'page[size]', null !== $this->pageSize ? (string) $this->pageSize : null);

        if ([] === $params) {
            return '';
        }

        return '?' . http_build_query($params);
    }

    /**
     * @param array<string, string> $params
     */
    private function addIfNotNull(array &$params, string $key, ?string $value): void
    {
        if (null !== $value) {
            $params[$key] = $value;
        }
    }
}
