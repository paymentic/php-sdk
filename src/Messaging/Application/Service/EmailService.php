<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\Service;

use Exception;
use JsonException;
use Paymentic\Sdk\Messaging\Application\Contract\EmailServiceContract;
use Paymentic\Sdk\Messaging\Application\DTO\SendEmailRequest;
use Paymentic\Sdk\Messaging\Application\Mapper\EmailMessageMapper;
use Paymentic\Sdk\Messaging\Domain\Entity\EmailMessage;
use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Paymentic\Sdk\Shared\Http\HttpClient;

final readonly class EmailService implements EmailServiceContract
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
    public function send(SendEmailRequest $request, ?string $idempotencyKey = null): EmailMessage
    {
        $headers = (null !== $idempotencyKey && '' !== $idempotencyKey)
            ? ['X-Idempotency-Key' => $idempotencyKey]
            : [];

        $response = $this->httpClient->post(
            uri: '/messaging/email/messages',
            data: $request->toArray(),
            headers: $headers,
        );

        return EmailMessageMapper::fromArray($response['data']);
    }

    /**
     * @throws PaymenticException
     * @throws JsonException
     * @throws Exception
     */
    public function get(string $messageId): EmailMessage
    {
        $response = $this->httpClient->get(
            uri: "/messaging/email/messages/{$messageId}",
        );

        return EmailMessageMapper::fromArray($response['data']);
    }
}
