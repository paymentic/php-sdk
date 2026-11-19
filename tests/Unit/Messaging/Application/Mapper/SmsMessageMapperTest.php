<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Messaging\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Messaging\Application\Mapper\SmsMessageMapper;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\Enum\MessageSource;
use Paymentic\Sdk\Messaging\Domain\Enum\SmsStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SmsMessageMapperTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[Test]
    public function mapsFullPayload(): void
    {
        $sms = SmsMessageMapper::fromArray([
            'id' => '01JZX8K3M9QABCDEF0123456789',
            'status' => 'DELIVERED',
            'sender' => 'Paymentic',
            'recipient' => '+48123123123',
            'text' => 'Your code is 1234.',
            'segmentsCount' => 1,
            'totalCost' => '0.086',
            'netCost' => '0.070',
            'vatRate' => '23.00',
            'source' => 'API',
            'priority' => 'LOW',
            'externalReferenceId' => 'order-2026-000123',
            'createdAt' => '2026-11-01T10:00:00+00:00',
            'sentAt' => '2026-11-01T10:00:05+00:00',
            'deliveredAt' => '2026-11-01T10:00:09+00:00',
        ]);

        $this->assertSame('01JZX8K3M9QABCDEF0123456789', $sms->id);
        $this->assertSame(SmsStatus::DELIVERED, $sms->status);
        $this->assertSame('Paymentic', $sms->sender);
        $this->assertSame('+48123123123', $sms->recipient);
        $this->assertSame('Your code is 1234.', $sms->text);
        $this->assertSame(1, $sms->segmentsCount);
        $this->assertSame('0.086', $sms->totalCost);
        $this->assertSame('0.070', $sms->netCost);
        $this->assertSame('23.00', $sms->vatRate);
        $this->assertSame(MessageSource::API, $sms->source);
        $this->assertSame(MessagePriority::LOW, $sms->priority);
        $this->assertSame('order-2026-000123', $sms->externalReferenceId);
        $this->assertInstanceOf(DateTimeImmutable::class, $sms->createdAt);
        $this->assertInstanceOf(DateTimeImmutable::class, $sms->sentAt);
        $this->assertInstanceOf(DateTimeImmutable::class, $sms->deliveredAt);
        $this->assertSame('2026-11-01T10:00:09+00:00', $sms->deliveredAt->format('c'));
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsNullableFieldsAsNull(): void
    {
        $sms = SmsMessageMapper::fromArray([
            'id' => 'ID-1',
            'status' => 'QUEUED',
            'sender' => null,
            'recipient' => '+48123123123',
            'text' => 'Hi',
            'segmentsCount' => 1,
            'totalCost' => '0.086',
            'netCost' => null,
            'vatRate' => null,
            'source' => 'WEB',
            'priority' => 'MEDIUM',
            'externalReferenceId' => null,
            'sentAt' => null,
            'deliveredAt' => null,
        ]);

        $this->assertNull($sms->sender);
        $this->assertNull($sms->netCost);
        $this->assertNull($sms->vatRate);
        $this->assertNull($sms->externalReferenceId);
        $this->assertNull($sms->createdAt);
        $this->assertNull($sms->sentAt);
        $this->assertNull($sms->deliveredAt);
        $this->assertSame(SmsStatus::QUEUED, $sms->status);
    }
}
