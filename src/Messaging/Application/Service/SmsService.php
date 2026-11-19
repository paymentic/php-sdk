<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\Service;

use Exception;
use JsonException;
use Paymentic\Sdk\Messaging\Application\Contract\SmsServiceContract;
use Paymentic\Sdk\Messaging\Application\DTO\SendSmsRequest;
use Paymentic\Sdk\Messaging\Application\Mapper\SmsMessageMapper;
use Paymentic\Sdk\Messaging\Domain\Entity\SmsMessage;
use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Paymentic\Sdk\Shared\Http\HttpClient;

final readonly class SmsService implements SmsServiceContract
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
    public function send(SendSmsRequest $request, ?string $idempotencyKey = null): SmsMessage
    {
        $headers = (null !== $idempotencyKey && '' !== $idempotencyKey)
            ? ['X-Idempotency-Key' => $idempotencyKey]
            : [];

        $response = $this->httpClient->post(
            uri: '/messaging/sms/messages',
            data: $request->toArray(),
            headers: $headers,
        );

        return SmsMessageMapper::fromArray($response['data']);
    }

    /**
     * @throws PaymenticException
     * @throws JsonException
     * @throws Exception
     */
    public function get(string $messageId): SmsMessage
    {
        $response = $this->httpClient->get(
            uri: "/messaging/sms/messages/{$messageId}",
        );

        return SmsMessageMapper::fromArray($response['data']);
    }
}
