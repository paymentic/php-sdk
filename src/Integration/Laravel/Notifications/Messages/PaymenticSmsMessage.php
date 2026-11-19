<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Laravel\Notifications\Messages;

use Paymentic\Sdk\Messaging\Application\DTO\SendSmsRequest;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;

final class PaymenticSmsMessage
{
    private ?string $sender = null;
    private ?string $recipient = null;
    private ?bool $unicode = null;
    private ?MessagePriority $priority = null;
    private ?string $externalReferenceId = null;
    private ?string $idempotencyKey = null;

    public function __construct(
        private string $content = '',
    ) {
    }

    public static function create(string $content = ''): self
    {
        return new self($content);
    }

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function from(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function to(string $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function unicode(bool $unicode = true): self
    {
        $this->unicode = $unicode;

        return $this;
    }

    public function priority(MessagePriority $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function externalReferenceId(string $id): self
    {
        $this->externalReferenceId = $id;

        return $this;
    }

    public function idempotencyKey(string $key): self
    {
        $this->idempotencyKey = $key;

        return $this;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function getIdempotencyKey(): ?string
    {
        return $this->idempotencyKey;
    }

    public function toRequest(string $recipient, ?string $defaultSender = null): SendSmsRequest
    {
        return new SendSmsRequest(
            recipient: $recipient,
            text: $this->content,
            sender: $this->sender ?? $defaultSender,
            unicode: $this->unicode,
            externalReferenceId: $this->externalReferenceId,
            priority: $this->priority,
        );
    }
}
