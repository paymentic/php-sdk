<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\DTO;

use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;

final readonly class SendSmsRequest
{
    /**
     * @param string $recipient E.164 phone number
     * @param string|null $sender max 11 chars; null means the client's default sender (resolved by the API)
     * @param string|null $externalReferenceId max 36 chars
     */
    public function __construct(
        public string $recipient,
        public string $text,
        public ?string $sender = null,
        public ?bool $unicode = null,
        public ?string $externalReferenceId = null,
        public ?MessagePriority $priority = null,
    ) {
    }

    /**
     * @return array<string, string|bool>
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'recipient' => $this->recipient,
                'text' => $this->text,
                'sender' => $this->sender,
                'unicode' => $this->unicode,
                'externalReferenceId' => $this->externalReferenceId,
                'priority' => $this->priority?->value,
            ],
            static fn (string|bool|null $value): bool => null !== $value,
        );
    }
}
