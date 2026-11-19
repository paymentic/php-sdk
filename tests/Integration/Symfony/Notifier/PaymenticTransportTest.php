<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Symfony\Notifier;

use JsonException;
use Paymentic\Sdk\Integration\Symfony\Notifier\PaymenticOptions;
use Paymentic\Sdk\Integration\Symfony\Notifier\PaymenticTransport;
use Paymentic\Sdk\Integration\Symfony\Notifier\PaymenticTransportException;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Sdk\Shared\Exception\PaymentRequiredException;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Notifier\Exception\RuntimeException;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\FailoverTransport;
use Symfony\Component\Notifier\Transport\TransportInterface;

final class PaymenticTransportTest extends TestCase
{
    private ?MockHttpClient $http = null;

    /**
     * @throws JsonException
     */
    #[Test]
    public function sendsSmsMessage(): void
    {
        $transport = $this->createTransport($this->smsResponse(), 202, 'Shop');

        $sent = $transport->send(new SmsMessage('+48123123123', 'Your code is 1234.'));

        $request = $this->lastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringEndsWith('/messaging/sms/messages', (string) $request->getUri());
        $this->assertSame([
            'recipient' => '+48123123123',
            'text' => 'Your code is 1234.',
            'sender' => 'Shop',
        ], $this->lastBody());
        $this->assertSame('01JZX8K3M9QABCDEF0123456789', $sent->getMessageId());
        $this->assertSame((string) $transport, $sent->getTransport());
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function messageFromOverridesDsnFrom(): void
    {
        $transport = $this->createTransport($this->smsResponse(), 202, 'Shop');

        $transport->send(new SmsMessage('+48123123123', 'Hi', 'Other'));

        $this->assertSame('Other', $this->lastBody()['sender']);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function omitsSenderWhenNoneConfigured(): void
    {
        $transport = $this->createTransport($this->smsResponse(), 202);

        $transport->send(new SmsMessage('+48123123123', 'Hi'));

        $this->assertArrayNotHasKey('sender', $this->lastBody());
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function mapsPaymenticOptions(): void
    {
        $transport = $this->createTransport($this->smsResponse(), 202);
        $options = (new PaymenticOptions())
            ->priority(MessagePriority::HIGH)
            ->unicode()
            ->externalReferenceId('ref-1')
            ->idempotencyKey('key-123');

        $transport->send(new SmsMessage('+48123123123', 'Hi', '', $options));

        $this->assertSame([
            'recipient' => '+48123123123',
            'text' => 'Hi',
            'unicode' => true,
            'externalReferenceId' => 'ref-1',
            'priority' => 'HIGH',
        ], $this->lastBody());
        $this->assertSame('key-123', $this->lastRequest()->getHeaderLine('X-Idempotency-Key'));
    }

    #[Test]
    public function supportsOnlySmsMessages(): void
    {
        $transport = $this->createTransport('{}', 200);
        $foreignOptions = new class () implements MessageOptionsInterface {
            /**
             * @return array<string, mixed>
             */
            public function toArray(): array
            {
                return [];
            }

            public function getRecipientId(): ?string
            {
                return null;
            }
        };

        $this->assertFalse($transport->supports(new ChatMessage('x')));
        $this->assertFalse($transport->supports(new SmsMessage('+48123123123', 'Hi', '', $foreignOptions)));
        $this->assertTrue($transport->supports(new SmsMessage('+48123123123', 'Hi')));
        $this->assertTrue($transport->supports(new SmsMessage('+48123123123', 'Hi', '', new PaymenticOptions())));
    }

    #[Test]
    public function throwsUnsupportedMessageTypeForChatMessage(): void
    {
        $transport = $this->createTransport('{}', 200);

        $this->expectException(UnsupportedMessageTypeException::class);

        $transport->send(new ChatMessage('x'));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function wrapsApiErrorsInNotifierRuntimeException(): void
    {
        $transport = $this->createTransport(json_encode([
            'errors' => [['code' => 'INSUFFICIENT_FUNDS', 'message' => 'Insufficient funds.']],
        ], JSON_THROW_ON_ERROR), 402);

        try {
            $transport->send(new SmsMessage('+48123123123', 'Hi'));
            $this->fail('Expected RuntimeException.');
        } catch (RuntimeException $e) {
            $this->assertInstanceOf(PaymenticTransportException::class, $e);
            $this->assertInstanceOf(TransportExceptionInterface::class, $e);
            $this->assertInstanceOf(PaymentRequiredException::class, $e->getPrevious());
            $this->assertSame(402, $e->getCode());
        }
    }

    #[Test]
    public function apiErrorsImplementTransportExceptionInterface(): void
    {
        $transport = $this->createTransport(json_encode([
            'errors' => [['code' => 'INSUFFICIENT_FUNDS', 'message' => 'Insufficient funds.']],
        ], JSON_THROW_ON_ERROR), 402);

        try {
            $transport->send(new SmsMessage('+48123123123', 'Hi'));
            $this->fail('Expected exception.');
        } catch (TransportExceptionInterface $e) {
            $this->assertInstanceOf(PaymentRequiredException::class, $e->getPrevious());
        }
    }

    #[Test]
    public function clientExceptionsImplementTransportExceptionInterface(): void
    {
        $previous = new class ('network down') extends \RuntimeException implements ClientExceptionInterface {
        };
        $transport = $this->createTransportWithClient(new class ($previous) implements \Psr\Http\Client\ClientInterface {
            public function __construct(private readonly \Throwable $e)
            {
            }

            public function sendRequest(\Psr\Http\Message\RequestInterface $request): ResponseInterface
            {
                throw $this->e;
            }
        });

        try {
            $transport->send(new SmsMessage('+48123123123', 'Hi'));
            $this->fail('Expected exception.');
        } catch (TransportExceptionInterface $e) {
            $this->assertSame($previous, $e->getPrevious());
        }
    }

    #[Test]
    public function jsonExceptionsImplementTransportExceptionInterface(): void
    {
        $previous = new JsonException('Syntax error');
        $transport = $this->createTransportWithClient(new class ($previous) implements \Psr\Http\Client\ClientInterface {
            public function __construct(private readonly \Throwable $e)
            {
            }

            public function sendRequest(\Psr\Http\Message\RequestInterface $request): ResponseInterface
            {
                throw $this->e;
            }
        });

        try {
            $transport->send(new SmsMessage('+48123123123', 'Hi'));
            $this->fail('Expected exception.');
        } catch (TransportExceptionInterface $e) {
            $this->assertSame($previous, $e->getPrevious());
        }
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function failoverFallsThroughToSecondTransport(): void
    {
        $failing = $this->createTransport(json_encode([
            'errors' => [['code' => 'INSUFFICIENT_FUNDS', 'message' => 'Insufficient funds.']],
        ], JSON_THROW_ON_ERROR), 402);
        $failingHttp = $this->http;
        $working = $this->createTransport($this->smsResponse(), 202);
        $workingHttp = $this->http;

        $failover = new FailoverTransport([$failing, $working]);
        $sent = $failover->send(new SmsMessage('+48123123123', 'Hi'));

        $this->assertNotNull($failingHttp?->getLastRequest());
        $this->assertNotNull($workingHttp?->getLastRequest());
        $this->assertSame('01JZX8K3M9QABCDEF0123456789', $sent->getMessageId());
        $this->assertInstanceOf(TransportInterface::class, $failover);
    }

    #[Test]
    public function toStringUrlEncodesFrom(): void
    {
        $transport = $this->createTransport('{}', 200, 'My Shop');

        $this->assertSame('paymentic://api.paymentic.com?from=My%20Shop', (string) $transport);
    }

    private function createTransport(string $body, int $status, ?string $from = null): PaymenticTransport
    {
        $this->http = new MockHttpClient($body, $status);

        return $this->createTransportWithClient($this->http, $from);
    }

    private function createTransportWithClient(\Psr\Http\Client\ClientInterface $client, ?string $from = null): PaymenticTransport
    {
        $messaging = PaymenticClientFactory::create('test-api-key')
            ->withHttpClient($client)
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build()
            ->messaging();

        return new PaymenticTransport($messaging, $from);
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
        $decoded = json_decode((string) $this->lastRequest()->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);

        return $decoded;
    }

    /**
     * @throws JsonException
     */
    private function smsResponse(): string
    {
        return json_encode([
            'data' => [
                'id' => '01JZX8K3M9QABCDEF0123456789',
                'status' => 'QUEUED',
                'sender' => 'Shop',
                'recipient' => '+48123123123',
                'text' => 'Hi',
                'segmentsCount' => 1,
                'totalCost' => '0.086',
                'netCost' => '0.070',
                'vatRate' => '23.00',
                'source' => 'API',
                'priority' => 'MEDIUM',
                'externalReferenceId' => null,
                'createdAt' => '2026-11-01T10:00:00+00:00',
                'sentAt' => null,
                'deliveredAt' => null,
            ],
        ], JSON_THROW_ON_ERROR);
    }
}
