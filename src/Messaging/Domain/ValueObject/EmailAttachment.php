<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\ValueObject;

use Paymentic\Sdk\Messaging\Domain\Enum\EmailAttachmentDisposition;

final readonly class EmailAttachment
{
    /**
     * @param string $content already base64-encoded
     * @param string|null $contentId required by the API for INLINE disposition (not validated client-side)
     */
    public function __construct(
        public string $filename,
        public string $content,
        public string $contentType,
        public ?EmailAttachmentDisposition $disposition = null,
        public ?string $contentId = null,
    ) {
    }

    public static function fromRawContent(
        string $filename,
        string $rawContent,
        string $contentType,
        ?EmailAttachmentDisposition $disposition = null,
        ?string $contentId = null,
    ): self {
        return new self(
            filename: $filename,
            content: base64_encode($rawContent),
            contentType: $contentType,
            disposition: $disposition,
            contentId: $contentId,
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'filename' => $this->filename,
                'content' => $this->content,
                'contentType' => $this->contentType,
                'disposition' => $this->disposition?->value,
                'contentId' => $this->contentId,
            ],
            static fn (?string $value): bool => null !== $value,
        );
    }
}
