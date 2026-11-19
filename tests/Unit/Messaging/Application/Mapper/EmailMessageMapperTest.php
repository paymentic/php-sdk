<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Messaging\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Messaging\Application\Mapper\EmailMessageMapper;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailBounceSubType;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailBounceType;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailFailureCode;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailRecipientStatus;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailRecipientType;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailStatus;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\Enum\MessageSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailMessageMapperTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[Test]
    public function mapsFullPayload(): void
    {
        $email = EmailMessageMapper::fromArray($this->payload([
            'fromName' => 'Firma Sp. z o.o.',
            'replyTo' => ['bok@firma.pl'],
            'metadata' => ['orderId' => '123'],
            'externalReferenceId' => 'order-1',
            'failureCode' => 'EXPIRED',
            'sentAt' => '2026-11-01T10:00:05+00:00',
            'failedAt' => '2026-11-01T11:00:00+00:00',
            'recipients' => [
                [
                    'email' => 'jan@example.org',
                    'type' => 'TO',
                    'status' => 'BOUNCED',
                    'isFree' => true,
                    'bounceType' => 'PERMANENT',
                    'bounceSubType' => 'NO_EMAIL',
                    'bouncedAt' => '2026-11-01T10:01:00+00:00',
                ],
                [
                    'email' => 'anna@example.org',
                    'type' => 'CC',
                    'status' => 'DELIVERED',
                    'isFree' => false,
                    'deliveredAt' => '2026-11-01T10:00:30+00:00',
                ],
            ],
        ]));

        $this->assertSame('01jzx8k3m9qabcdef0123456789', $email->id);
        $this->assertSame(EmailStatus::PARTIALLY_DELIVERED, $email->status);
        $this->assertSame('faktury@firma.pl', $email->from);
        $this->assertSame('Firma Sp. z o.o.', $email->fromName);
        $this->assertSame(['bok@firma.pl'], $email->replyTo);
        $this->assertSame('Faktura FV/2026/11/001', $email->subject);
        $this->assertSame(2, $email->recipientsCount);
        $this->assertSame(1, $email->freeRecipientsCount);
        $this->assertSame(1, $email->billableRecipientsCount);
        $this->assertSame(1, $email->attachmentsCount);
        $this->assertSame(1, $email->attachmentsBillableMb);
        $this->assertSame('0.005', $email->recipientsCost);
        $this->assertSame('0.010', $email->attachmentsCost);
        $this->assertSame('0.015', $email->netCost);
        $this->assertSame('23.00', $email->vatRate);
        $this->assertSame('0.018', $email->totalCost);
        $this->assertSame(MessageSource::API, $email->source);
        $this->assertSame(MessagePriority::LOW, $email->priority);
        $this->assertSame('order-1', $email->externalReferenceId);
        $this->assertSame(['orderId' => '123'], $email->metadata);
        $this->assertSame(EmailFailureCode::EXPIRED, $email->failureCode);
        $this->assertInstanceOf(DateTimeImmutable::class, $email->createdAt);
        $this->assertInstanceOf(DateTimeImmutable::class, $email->sentAt);
        $this->assertInstanceOf(DateTimeImmutable::class, $email->failedAt);

        $this->assertCount(2, $email->recipients);
        $first = $email->recipients[0];
        $this->assertSame('jan@example.org', $first->email);
        $this->assertSame(EmailRecipientType::TO, $first->type);
        $this->assertSame(EmailRecipientStatus::BOUNCED, $first->status);
        $this->assertTrue($first->isFree);
        $this->assertSame(EmailBounceType::PERMANENT, $first->bounceType);
        $this->assertSame(EmailBounceSubType::NO_EMAIL, $first->bounceSubType);
        $this->assertInstanceOf(DateTimeImmutable::class, $first->bouncedAt);
        $this->assertNull($first->deliveredAt);
        $this->assertNull($first->complainedAt);

        $second = $email->recipients[1];
        $this->assertSame(EmailRecipientType::CC, $second->type);
        $this->assertFalse($second->isFree);
        $this->assertNull($second->bounceType);
        $this->assertNull($second->bounceSubType);
        $this->assertInstanceOf(DateTimeImmutable::class, $second->deliveredAt);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsMinimalPayloadWithDefaults(): void
    {
        $email = EmailMessageMapper::fromArray($this->payload([
            'status' => 'QUEUED',
            'fromName' => null,
            'metadata' => null,
            'failureCode' => null,
            'recipients' => [],
        ]));

        $this->assertSame(EmailStatus::QUEUED, $email->status);
        $this->assertNull($email->fromName);
        $this->assertSame([], $email->replyTo);
        $this->assertNull($email->externalReferenceId);
        $this->assertNull($email->metadata);
        $this->assertNull($email->failureCode);
        $this->assertNull($email->sentAt);
        $this->assertNull($email->failedAt);
        $this->assertSame([], $email->recipients);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'id' => '01jzx8k3m9qabcdef0123456789',
            'status' => 'PARTIALLY_DELIVERED',
            'from' => 'faktury@firma.pl',
            'subject' => 'Faktura FV/2026/11/001',
            'recipients' => [],
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
            'createdAt' => '2026-11-01T10:00:00+00:00',
        ], $overrides);
    }
}
