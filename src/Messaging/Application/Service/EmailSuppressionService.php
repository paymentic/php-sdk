<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\Service;

use Exception;
use JsonException;
use Paymentic\Sdk\Messaging\Application\Contract\EmailSuppressionServiceContract;
use Paymentic\Sdk\Messaging\Application\DTO\AddEmailSuppressionsRequest;
use Paymentic\Sdk\Messaging\Application\DTO\AddEmailSuppressionsResponse;
use Paymentic\Sdk\Messaging\Application\DTO\ListEmailSuppressionsRequest;
use Paymentic\Sdk\Messaging\Application\DTO\ListEmailSuppressionsResponse;
use Paymentic\Sdk\Messaging\Application\Mapper\EmailSuppressionMapper;
use Paymentic\Sdk\Messaging\Domain\Entity\EmailSuppression;
use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Paymentic\Sdk\Shared\Http\HttpClient;
use Paymentic\Sdk\Shared\ValueObject\PaginationLinks;
use Paymentic\Sdk\Shared\ValueObject\PaginationMeta;

final readonly class EmailSuppressionService implements EmailSuppressionServiceContract
{
    public function __construct(
        private HttpClient $httpClient,
    ) {
    }

    /**
     * @throws PaymenticException
     * @throws JsonException
     * @throws Exception
     */
    public function add(AddEmailSuppressionsRequest $request): AddEmailSuppressionsResponse
    {
        $response = $this->httpClient->post(
            uri: '/messaging/email/suppressions',
            data: $request->toArray(),
        );

        return AddEmailSuppressionsResponse::fromArray($response['data']);
    }

    /**
     * @throws PaymenticException
     * @throws JsonException
     * @throws Exception
     */
    public function list(?ListEmailSuppressionsRequest $request = null): ListEmailSuppressionsResponse
    {
        $query = $request?->toQueryString() ?? '';

        $response = $this->httpClient->get(
            uri: '/messaging/email/suppressions' . $query,
        );

        return new ListEmailSuppressionsResponse(
            data: EmailSuppressionMapper::fromArrayCollection($response['data']),
            meta: PaginationMeta::fromArray($response['meta']),
            links: isset($response['links']) ? PaginationLinks::fromArray($response['links']) : null,
        );
    }

    /**
     * @throws PaymenticException
     * @throws JsonException
     * @throws Exception
     */
    public function delete(string $suppressionId): EmailSuppression
    {
        $response = $this->httpClient->delete(
            uri: "/messaging/email/suppressions/{$suppressionId}",
        );

        return EmailSuppressionMapper::fromArray($response['data']);
    }
}
