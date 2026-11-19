<?php

declare(strict_types=1);

namespace Paymentic\Tests\Feature;

use JsonException;
use Paymentic\Sdk\Messaging\Application\DTO\SendSmsRequest;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\Enum\MessageSource;
use Paymentic\Sdk\Messaging\Domain\Enum\SmsStatus;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Sdk\Shared\Enum\ApiErrorCode;
use Paymentic\Sdk\Shared\Exception\BadRequestException;
use Paymentic\Sdk\Shared\Exception\ForbiddenException;
use Paymentic\Sdk\Shared\Exception\NotFoundException;
use Paymentic\Sdk\Shared\Exception\PaymentRequiredException;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class SmsFeatureTest extends TestCase
{
    private ?MockHttpClient $http = null;

    /**
     * @throws JsonException
     */
    #[Test]
    public function sendsSms(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [
                'id' => '01JZX8K3M9QABCDEF0123456789',
                'status' => 'QUEUED',
                'sender' => 'Shop',
                'recipient' => '+48123123123',
                'text' => 'Your code is 1234.',
                'segmentsCount' => 1,
                'totalCost' => '0.086',
                'netCost' => '0.070',
                'vatRate' => '23.00',
                'source' => 'API',
                'priority' => 'HIGH',
                'externalReferenceId' => 'ref-1',
                'createdAt' => '2026-11-01T10:00:00+00:00',
                'sentAt' => null,
                'deliveredAt' => null,
            ],
        ], JSON_THROW_ON_ERROR), 202);

        $sms = $client->messaging()->sms()->send(new SendSmsRequest(
            recipient: '+48123123123',
            text: 'Your code is 1234.',
            sender: 'Shop',
            unicode: false,
            externalReferenceId: 'ref-1',
            priority: MessagePriority::HIGH,
        ));

        $request = $this->lastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringEndsWith('/v1_2/messaging/sms/messages', (string) $request->getUri());
        $this->assertSame('Bearer test-api-key', $request->getHeaderLine('Authorization'));
        $this->assertFalse($request->hasHeader('X-Idempotency-Key'));
        $this->assertSame([
            'recipient' => '+48123123123',
            'text' => 'Your code is 1234.',
            'sender' => 'Shop',
            'unicode' => false,
            'externalReferenceId' => 'ref-1',
            'priority' => 'HIGH',
        ], $this->lastBody());

        $this->assertSame('01JZX8K3M9QABCDEF0123456789', $sms->id);
        $this->assertSame(SmsStatus::QUEUED, $sms->status);
        $this->assertSame('0.086', $sms->totalCost);
        $this->assertSame(MessageSource::API, $sms->source);
        $this->assertSame(MessagePriority::HIGH, $sms->priority);
        $this->assertNull($sms->sentAt);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function sendsIdempotencyKeyHeader(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [
                'id' => '01JZX8K3M9QABCDEF0123456789',
                'status' => 'QUEUED',
                'sender' => 'Shop',
                'recipient' => '+48123123123',
                'text' => 'Your code is 1234.',
                'segmentsCount' => 1,
                'totalCost' => '0.086',
                'netCost' => '0.070',
                'vatRate' => '23.00',
                'source' => 'API',
                'priority' => 'HIGH',
                'externalReferenceId' => 'ref-1',
                'createdAt' => '2026-11-01T10:00:00+00:00',
                'sentAt' => null,
                'deliveredAt' => null,
            ],
        ], JSON_THROW_ON_ERROR), 202);

        $client->messaging()->sms()->send(
            new SendSmsRequest(recipient: '+48123123123', text: 'Hi'),
            idempotencyKey: 'key-123',
        );

        $this->assertSame('key-123', $this->lastRequest()->getHeaderLine('X-Idempotency-Key'));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function doesNotSendEmptyIdempotencyKey(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [
                'id' => '01JZX8K3M9QABCDEF0123456789',
                'status' => 'QUEUED',
                'sender' => 'Shop',
                'recipient' => '+48123123123',
                'text' => 'Your code is 1234.',
                'segmentsCount' => 1,
                'totalCost' => '0.086',
                'netCost' => '0.070',
                'vatRate' => '23.00',
                'source' => 'API',
                'priority' => 'HIGH',
                'externalReferenceId' => 'ref-1',
                'createdAt' => '2026-11-01T10:00:00+00:00',
                'sentAt' => null,
                'deliveredAt' => null,
            ],
        ], JSON_THROW_ON_ERROR), 202);

        $client->messaging()->sms()->send(
            new SendSmsRequest(recipient: '+48123123123', text: 'Hi'),
            idempotencyKey: '',
        );

        $this->assertFalse($this->lastRequest()->hasHeader('X-Idempotency-Key'));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function getsSms(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [
                'id' => '01JZX8K3M9QABCDEF0123456789',
                'status' => 'QUEUED',
                'sender' => 'Shop',
                'recipient' => '+48123123123',
                'text' => 'Your code is 1234.',
                'segmentsCount' => 1,
                'totalCost' => '0.086',
                'netCost' => '0.070',
                'vatRate' => '23.00',
                'source' => 'API',
                'priority' => 'HIGH',
                'externalReferenceId' => 'ref-1',
                'createdAt' => '2026-11-01T10:00:00+00:00',
                'sentAt' => null,
                'deliveredAt' => null,
            ],
        ], JSON_THROW_ON_ERROR), 200);

        $sms = $client->messaging()->sms()->get('01JZX8K3M9QABCDEF0123456789');

        $this->assertSame('GET', $this->lastRequest()->getMethod());
        $this->assertStringEndsWith(
            '/v1_2/messaging/sms/messages/01JZX8K3M9QABCDEF0123456789',
            (string) $this->lastRequest()->getUri(),
        );
        $this->assertSame('01JZX8K3M9QABCDEF0123456789', $sms->id);
        $this->assertSame('+48123123123', $sms->recipient);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsPaymentRequiredWhenFundsAreInsufficient(): void
    {
        $client = $this->createClient(json_encode([
            'errors' => [['code' => 'INSUFFICIENT_FUNDS', 'message' => 'Insufficient funds.']],
        ], JSON_THROW_ON_ERROR), 402);

        try {
            $client->messaging()->sms()->send(new SendSmsRequest(recipient: '+48123123123', text: 'Hi'));
            $this->fail('Expected PaymentRequiredException.');
        } catch (PaymentRequiredException $e) {
            $this->assertSame(402, $e->getCode());
            $this->assertSame(ApiErrorCode::INSUFFICIENT_FUNDS, $e->getFirstError()?->code);
        }
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsForbiddenWhenSmsServiceNotActive(): void
    {
        $client = $this->createClient(json_encode([
            'errors' => [['code' => 'SMS_SERVICE_NOT_ACTIVE', 'message' => 'SMS service is not active.']],
        ], JSON_THROW_ON_ERROR), 403);

        try {
            $client->messaging()->sms()->send(new SendSmsRequest(recipient: '+48123123123', text: 'Hi'));
            $this->fail('Expected ForbiddenException.');
        } catch (ForbiddenException $e) {
            $this->assertSame(ApiErrorCode::SMS_SERVICE_NOT_ACTIVE, $e->getFirstError()?->code);
        }
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsBadRequestWhenPriceNotConfigured(): void
    {
        $client = $this->createClient(json_encode([
            'errors' => [['code' => 'PRICE_NOT_CONFIGURED', 'message' => 'Price is not configured.']],
        ], JSON_THROW_ON_ERROR), 400);

        $this->expectException(BadRequestException::class);

        $client->messaging()->sms()->send(new SendSmsRequest(recipient: '+48123123123', text: 'Hi'));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsNotFoundWhenMessageMissing(): void
    {
        $client = $this->createClient(json_encode([
            'errors' => [['code' => 'MESSAGE_NOT_FOUND', 'message' => 'Message not found.']],
        ], JSON_THROW_ON_ERROR), 404);

        try {
            $client->messaging()->sms()->get('missing');
            $this->fail('Expected NotFoundException.');
        } catch (NotFoundException $e) {
            $this->assertSame(ApiErrorCode::MESSAGE_NOT_FOUND, $e->getFirstError()?->code);
        }
    }

    private function createClient(string $responseBody, int $statusCode): PaymenticClient
    {
        $this->http = new MockHttpClient($responseBody, $statusCode);

        return PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient($this->http)
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build();
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->http?->getLastRequest();
        $this->assertNotNull($request);

        return $request;
    }

    /**
     * @return array<string, mixed>
     * @throws JsonException
     */
    private function lastBody(): array
    {
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) $this->lastRequest()->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
