<?php

declare(strict_types=1);

namespace Paymentic\Tests\Feature;

use JsonException;
use Paymentic\Sdk\Messaging\Application\DTO\SendEmailRequest;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailRecipientStatus;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailRecipientType;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailStatus;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\ValueObject\EmailAttachment;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Sdk\Shared\Enum\ApiErrorCode;
use Paymentic\Sdk\Shared\Exception\ConflictException;
use Paymentic\Sdk\Shared\Exception\PayloadTooLargeException;
use Paymentic\Sdk\Shared\Exception\ValidationException;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class EmailFeatureTest extends TestCase
{
    private ?MockHttpClient $http = null;

    /**
     * @throws JsonException
     */
    #[Test]
    public function sendsEmailWithAttachment(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [
                'id' => '01jzx8k3m9qabcdef0123456789',
                'status' => 'QUEUED',
                'from' => 'faktury@firma.pl',
                'fromName' => 'Firma',
                'replyTo' => ['bok@firma.pl'],
                'subject' => 'Faktura',
                'recipients' => [
                    ['email' => 'jan@example.org', 'type' => 'TO', 'status' => 'QUEUED', 'isFree' => true],
                    ['email' => 'anna@example.org', 'type' => 'BCC', 'status' => 'DELIVERED', 'isFree' => false, 'deliveredAt' => '2026-11-01T10:00:30+00:00'],
                ],
                'recipientsCount' => 2,
                'freeRecipientsCount' => 1,
                'billableRecipientsCount' => 1,
                'attachmentsCount' => 1,
                'attachmentsBillableMb' => 1,
                'recipientsCost' => '0.005',
                'attachmentsCost' => '0.010',
                'netCost' => '0.015',
                'vatRate' => '23.00',
                'totalCost' => '0.018',
                'source' => 'API',
                'priority' => 'LOW',
                'externalReferenceId' => 'order-1',
                'metadata' => ['orderId' => '123'],
                'failureCode' => null,
                'createdAt' => '2026-11-01T10:00:00+00:00',
            ],
        ], JSON_THROW_ON_ERROR), 202);

        $email = $client->messaging()->email()->send(
            new SendEmailRequest(
                from: 'faktury@firma.pl',
                to: ['jan@example.org'],
                subject: 'Faktura',
                fromName: 'Firma',
                html: '<p>Hi</p>',
                bcc: ['anna@example.org'],
                attachments: [EmailAttachment::fromRawContent('f.pdf', 'PDFDATA', 'application/pdf')],
                metadata: ['orderId' => '123'],
                priority: MessagePriority::LOW,
            ),
            idempotencyKey: 'email-key',
        );

        $request = $this->lastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringEndsWith('/v1_2/messaging/email/messages', (string) $request->getUri());
        $this->assertSame('email-key', $request->getHeaderLine('X-Idempotency-Key'));
        $this->assertSame([
            'from' => 'faktury@firma.pl',
            'fromName' => 'Firma',
            'to' => ['jan@example.org'],
            'bcc' => ['anna@example.org'],
            'subject' => 'Faktura',
            'html' => '<p>Hi</p>',
            'attachments' => [[
                'filename' => 'f.pdf',
                'content' => base64_encode('PDFDATA'),
                'contentType' => 'application/pdf',
            ]],
            'metadata' => ['orderId' => '123'],
            'priority' => 'LOW',
        ], $this->lastBody());

        $this->assertSame('01jzx8k3m9qabcdef0123456789', $email->id);
        $this->assertSame(EmailStatus::QUEUED, $email->status);
        $this->assertSame('0.018', $email->totalCost);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function doesNotSendIdempotencyHeaderByDefault(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [
                'id' => '01jzx8k3m9qabcdef0123456789',
                'status' => 'QUEUED',
                'from' => 'faktury@firma.pl',
                'fromName' => 'Firma',
                'replyTo' => ['bok@firma.pl'],
                'subject' => 'Faktura',
                'recipients' => [
                    ['email' => 'jan@example.org', 'type' => 'TO', 'status' => 'QUEUED', 'isFree' => true],
                    ['email' => 'anna@example.org', 'type' => 'BCC', 'status' => 'DELIVERED', 'isFree' => false, 'deliveredAt' => '2026-11-01T10:00:30+00:00'],
                ],
                'recipientsCount' => 2,
                'freeRecipientsCount' => 1,
                'billableRecipientsCount' => 1,
                'attachmentsCount' => 1,
                'attachmentsBillableMb' => 1,
                'recipientsCost' => '0.005',
                'attachmentsCost' => '0.010',
                'netCost' => '0.015',
                'vatRate' => '23.00',
                'totalCost' => '0.018',
                'source' => 'API',
                'priority' => 'LOW',
                'externalReferenceId' => 'order-1',
                'metadata' => ['orderId' => '123'],
                'failureCode' => null,
                'createdAt' => '2026-11-01T10:00:00+00:00',
            ],
        ], JSON_THROW_ON_ERROR), 202);

        $client->messaging()->email()->send(
            new SendEmailRequest(from: 'a@b.pl', to: ['x@y.pl'], subject: 'S', text: 'T'),
        );

        $this->assertFalse($this->lastRequest()->hasHeader('X-Idempotency-Key'));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function getsEmailWithRecipients(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [
                'id' => '01jzx8k3m9qabcdef0123456789',
                'status' => 'QUEUED',
                'from' => 'faktury@firma.pl',
                'fromName' => 'Firma',
                'replyTo' => ['bok@firma.pl'],
                'subject' => 'Faktura',
                'recipients' => [
                    ['email' => 'jan@example.org', 'type' => 'TO', 'status' => 'QUEUED', 'isFree' => true],
                    ['email' => 'anna@example.org', 'type' => 'BCC', 'status' => 'DELIVERED', 'isFree' => false, 'deliveredAt' => '2026-11-01T10:00:30+00:00'],
                ],
                'recipientsCount' => 2,
                'freeRecipientsCount' => 1,
                'billableRecipientsCount' => 1,
                'attachmentsCount' => 1,
                'attachmentsBillableMb' => 1,
                'recipientsCost' => '0.005',
                'attachmentsCost' => '0.010',
                'netCost' => '0.015',
                'vatRate' => '23.00',
                'totalCost' => '0.018',
                'source' => 'API',
                'priority' => 'LOW',
                'externalReferenceId' => 'order-1',
                'metadata' => ['orderId' => '123'],
                'failureCode' => null,
                'createdAt' => '2026-11-01T10:00:00+00:00',
            ],
        ], JSON_THROW_ON_ERROR), 200);

        $email = $client->messaging()->email()->get('01jzx8k3m9qabcdef0123456789');

        $this->assertSame('GET', $this->lastRequest()->getMethod());
        $this->assertStringEndsWith(
            '/v1_2/messaging/email/messages/01jzx8k3m9qabcdef0123456789',
            (string) $this->lastRequest()->getUri(),
        );
        $this->assertCount(2, $email->recipients);
        $this->assertSame(EmailRecipientType::BCC, $email->recipients[1]->type);
        $this->assertSame(EmailRecipientStatus::DELIVERED, $email->recipients[1]->status);
        $this->assertNotNull($email->recipients[1]->deliveredAt);
        $this->assertSame(['orderId' => '123'], $email->metadata);
        $this->assertSame(['bok@firma.pl'], $email->replyTo);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsConflictOnIdempotencyConflict(): void
    {
        $client = $this->createClient(json_encode([
            'errors' => [['code' => 'IDEMPOTENCY_CONFLICT', 'message' => 'Idempotency key was already used.']],
        ], JSON_THROW_ON_ERROR), 409);

        try {
            $client->messaging()->email()->send(
                new SendEmailRequest(from: 'a@b.pl', to: ['x@y.pl'], subject: 'S', text: 'T'),
                'dup',
            );
            $this->fail('Expected ConflictException.');
        } catch (ConflictException $e) {
            $this->assertSame(409, $e->getCode());
            $this->assertSame(ApiErrorCode::IDEMPOTENCY_CONFLICT, $e->getFirstError()?->code);
        }
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsPayloadTooLarge(): void
    {
        $client = $this->createClient(json_encode([
            'errors' => [['code' => 'PAYLOAD_TOO_LARGE', 'message' => 'Payload too large.']],
        ], JSON_THROW_ON_ERROR), 413);

        try {
            $client->messaging()->email()->send(
                new SendEmailRequest(from: 'a@b.pl', to: ['x@y.pl'], subject: 'S', text: 'T'),
            );
            $this->fail('Expected PayloadTooLargeException.');
        } catch (PayloadTooLargeException $e) {
            $this->assertSame(ApiErrorCode::PAYLOAD_TOO_LARGE, $e->getFirstError()?->code);
        }
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsValidationWithDetailsForSuppressedRecipient(): void
    {
        $client = $this->createClient(json_encode([
            'errors' => [['code' => 'EMAIL_RECIPIENT_SUPPRESSED', 'message' => 'Recipient is suppressed.', 'details' => ['recipients' => ['jan@example.org']]]],
        ], JSON_THROW_ON_ERROR), 422);

        try {
            $client->messaging()->email()->send(
                new SendEmailRequest(from: 'a@b.pl', to: ['jan@example.org'], subject: 'S', text: 'T'),
            );
            $this->fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            $error = $e->getFirstError();
            $this->assertNotNull($error);
            $this->assertSame(ApiErrorCode::EMAIL_RECIPIENT_SUPPRESSED, $error->code);
            $this->assertSame(['recipients' => ['jan@example.org']], $error->details);
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
