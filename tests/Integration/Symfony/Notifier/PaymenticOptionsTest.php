<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Symfony\Notifier;

use Paymentic\Sdk\Integration\Symfony\Notifier\PaymenticOptions;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaymenticOptionsTest extends TestCase
{
    #[Test]
    public function fluentSettersAndGetters(): void
    {
        $options = (new PaymenticOptions())
            ->priority(MessagePriority::HIGH)
            ->externalReferenceId('ref-1')
            ->unicode()
            ->idempotencyKey('key-1');

        $this->assertSame(MessagePriority::HIGH, $options->getPriority());
        $this->assertSame('ref-1', $options->getExternalReferenceId());
        $this->assertTrue($options->isUnicode());
        $this->assertSame('key-1', $options->getIdempotencyKey());
    }

    #[Test]
    public function defaultsAreNull(): void
    {
        $options = new PaymenticOptions();

        $this->assertNull($options->getPriority());
        $this->assertNull($options->getExternalReferenceId());
        $this->assertNull($options->isUnicode());
        $this->assertNull($options->getIdempotencyKey());
        $this->assertSame([], $options->toArray());
    }

    #[Test]
    public function toArrayReturnsScalars(): void
    {
        $options = (new PaymenticOptions())
            ->priority(MessagePriority::LOW)
            ->unicode(false)
            ->externalReferenceId('ref-2');

        $this->assertSame([
            'priority' => 'LOW',
            'externalReferenceId' => 'ref-2',
            'unicode' => false,
        ], $options->toArray());
    }

    #[Test]
    public function recipientIdIsAlwaysNull(): void
    {
        $this->assertNull((new PaymenticOptions())->getRecipientId());
    }
}
