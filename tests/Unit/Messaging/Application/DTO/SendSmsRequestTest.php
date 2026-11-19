<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Messaging\Application\DTO;

use Paymentic\Sdk\Messaging\Application\DTO\SendSmsRequest;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SendSmsRequestTest extends TestCase
{
    #[Test]
    public function buildsMinimalArray(): void
    {
        $request = new SendSmsRequest(recipient: '+48123123123', text: 'Hi');

        $this->assertSame(['recipient' => '+48123123123', 'text' => 'Hi'], $request->toArray());
    }

    #[Test]
    public function buildsFullArray(): void
    {
        $request = new SendSmsRequest(
            recipient: '+48123123123',
            text: 'Hi',
            sender: 'Shop',
            unicode: false,
            externalReferenceId: 'ref-1',
            priority: MessagePriority::HIGH,
        );

        $this->assertSame([
            'recipient' => '+48123123123',
            'text' => 'Hi',
            'sender' => 'Shop',
            'unicode' => false,
            'externalReferenceId' => 'ref-1',
            'priority' => 'HIGH',
        ], $request->toArray());
    }
}
