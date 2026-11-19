<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Laravel\Notifications\Messages;

use Paymentic\Sdk\Integration\Laravel\Notifications\Messages\PaymenticSmsMessage;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaymenticSmsMessageTest extends TestCase
{
    #[Test]
    public function mapsBuilderToRequest(): void
    {
        $message = PaymenticSmsMessage::create('Hi')
            ->from('Shop')
            ->unicode()
            ->priority(MessagePriority::HIGH)
            ->externalReferenceId('ref-1')
            ->to('+48111111111')
            ->idempotencyKey('k1');

        $request = $message->toRequest('+48222222222', 'Default');

        $this->assertSame('+48222222222', $request->recipient);
        $this->assertSame('Hi', $request->text);
        $this->assertSame('Shop', $request->sender);
        $this->assertTrue($request->unicode);
        $this->assertSame(MessagePriority::HIGH, $request->priority);
        $this->assertSame('ref-1', $request->externalReferenceId);
        $this->assertSame('+48111111111', $message->getRecipient());
        $this->assertSame('k1', $message->getIdempotencyKey());
    }

    #[Test]
    public function fallsBackToDefaultSender(): void
    {
        $request = (new PaymenticSmsMessage('Hi'))->content('Changed')->toRequest('+48222222222', 'Default');

        $this->assertSame('Default', $request->sender);
        $this->assertSame('Changed', $request->text);
        $this->assertNull($request->unicode);
        $this->assertNull((new PaymenticSmsMessage())->getRecipient());
    }
}
