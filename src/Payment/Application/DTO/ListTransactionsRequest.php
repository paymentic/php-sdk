<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\DTO;

final readonly class ListTransactionsRequest
{
    public function __construct(
        public ?string $filterStatus = null,
        public ?string $filterAmount = null,
        public ?string $filterExternalReferenceId = null,
        public ?string $filterOrderId = null,
        public ?string $filterCustomerName = null,
        public ?string $filterCustomerEmail = null,
        public ?string $filterBlikId = null,
        public ?string $filterCardBin = null,
        public ?string $filterProvider = null,
        public ?string $filterCreatedAt = null,
        public ?string $filterPaidAt = null,
        public ?string $queryFull = null,
        public ?string $queryCustomerName = null,
        public ?string $queryCustomerEmail = null,
        public ?string $queryTitle = null,
        public ?int $pageNumber = null,
        public ?int $pageSize = null,
    ) {
    }

    public function toQueryString(): string
    {
        $params = [];

        $this->addIfNotNull($params, 'filter[status]', $this->filterStatus);
        $this->addIfNotNull($params, 'filter[amount]', $this->filterAmount);
        $this->addIfNotNull($params, 'filter[externalReferenceId]', $this->filterExternalReferenceId);
        $this->addIfNotNull($params, 'filter[orderId]', $this->filterOrderId);
        $this->addIfNotNull($params, 'filter[customerName]', $this->filterCustomerName);
        $this->addIfNotNull($params, 'filter[customerEmail]', $this->filterCustomerEmail);
        $this->addIfNotNull($params, 'filter[blikId]', $this->filterBlikId);
        $this->addIfNotNull($params, 'filter[cardBin]', $this->filterCardBin);
        $this->addIfNotNull($params, 'filter[provider]', $this->filterProvider);
        $this->addIfNotNull($params, 'filter[createdAt]', $this->filterCreatedAt);
        $this->addIfNotNull($params, 'filter[paidAt]', $this->filterPaidAt);
        $this->addIfNotNull($params, 'query[full]', $this->queryFull);
        $this->addIfNotNull($params, 'query[customerName]', $this->queryCustomerName);
        $this->addIfNotNull($params, 'query[customerEmail]', $this->queryCustomerEmail);
        $this->addIfNotNull($params, 'query[title]', $this->queryTitle);
        $this->addIfNotNull($params, 'page[number]', $this->pageNumber !== null ? (string) $this->pageNumber : null);
        $this->addIfNotNull($params, 'page[size]', $this->pageSize !== null ? (string) $this->pageSize : null);

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
