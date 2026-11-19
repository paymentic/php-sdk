<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Symfony\Notifier;

use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;

final class PaymenticOptions implements MessageOptionsInterface
{
    /**
     * @param array{priority?: MessagePriority, externalReferenceId?: string, unicode?: bool, idempotencyKey?: string} $options
     */
    public function __construct(private array $options = [])
    {
    }

    public function priority(MessagePriority $priority): static
    {
        $this->options['priority'] = $priority;

        return $this;
    }

    public function externalReferenceId(string $externalReferenceId): static
    {
        $this->options['externalReferenceId'] = $externalReferenceId;

        return $this;
    }

    public function unicode(bool $unicode = true): static
    {
        $this->options['unicode'] = $unicode;

        return $this;
    }

    public function idempotencyKey(string $idempotencyKey): static
    {
        $this->options['idempotencyKey'] = $idempotencyKey;

        return $this;
    }

    public function getPriority(): ?MessagePriority
    {
        return $this->options['priority'] ?? null;
    }

    public function getExternalReferenceId(): ?string
    {
        return $this->options['externalReferenceId'] ?? null;
    }

    public function isUnicode(): ?bool
    {
        return $this->options['unicode'] ?? null;
    }

    public function getIdempotencyKey(): ?string
    {
        return $this->options['idempotencyKey'] ?? null;
    }

    /**
     * @return array<string, string|bool>
     */
    public function toArray(): array
    {
        $options = $this->options;
        $array = [];

        if (isset($options['priority'])) {
            $array['priority'] = $options['priority']->value;
        }

        if (isset($options['externalReferenceId'])) {
            $array['externalReferenceId'] = $options['externalReferenceId'];
        }

        if (isset($options['unicode'])) {
            $array['unicode'] = $options['unicode'];
        }

        if (isset($options['idempotencyKey'])) {
            $array['idempotencyKey'] = $options['idempotencyKey'];
        }

        return $array;
    }

    public function getRecipientId(): ?string
    {
        return null;
    }
}
