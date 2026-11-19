<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\DTO;

use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\ValueObject\EmailAttachment;

final readonly class SendEmailRequest
{
    /**
     * @param array<int, string> $to
     * @param array<int, string>|null $cc
     * @param array<int, string>|null $bcc
     * @param array<int, string>|null $replyTo
     * @param array<string, string>|null $headers
     * @param array<int, EmailAttachment>|null $attachments
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        public string $from,
        public array $to,
        public string $subject,
        public ?string $fromName = null,
        public ?string $html = null,
        public ?string $text = null,
        public ?array $cc = null,
        public ?array $bcc = null,
        public ?array $replyTo = null,
        public ?array $headers = null,
        public ?array $attachments = null,
        public ?array $metadata = null,
        public ?string $externalReferenceId = null,
        public ?MessagePriority $priority = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'from' => $this->from,
            'fromName' => $this->fromName,
            'to' => array_values($this->to),
            'cc' => null !== $this->cc ? array_values($this->cc) : null,
            'bcc' => null !== $this->bcc ? array_values($this->bcc) : null,
            'replyTo' => null !== $this->replyTo ? array_values($this->replyTo) : null,
            'subject' => $this->subject,
            'html' => $this->html,
            'text' => $this->text,
            'headers' => [] === $this->headers ? null : $this->headers,
            'attachments' => null !== $this->attachments
                ? array_map(
                    static fn (EmailAttachment $attachment): array => $attachment->toArray(),
                    array_values($this->attachments),
                )
                : null,
            'metadata' => [] === $this->metadata ? null : $this->metadata,
            'externalReferenceId' => $this->externalReferenceId,
            'priority' => $this->priority?->value,
        ];

        return array_filter($data, static fn (mixed $value): bool => null !== $value);
    }
}
