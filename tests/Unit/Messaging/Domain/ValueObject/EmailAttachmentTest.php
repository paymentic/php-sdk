<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Messaging\Domain\ValueObject;

use Paymentic\Sdk\Messaging\Domain\Enum\EmailAttachmentDisposition;
use Paymentic\Sdk\Messaging\Domain\ValueObject\EmailAttachment;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailAttachmentTest extends TestCase
{
    #[Test]
    public function fromRawContentBase64EncodesContent(): void
    {
        $attachment = EmailAttachment::fromRawContent('f.txt', 'hello world', 'text/plain');

        $this->assertSame('f.txt', $attachment->filename);
        $this->assertSame(base64_encode('hello world'), $attachment->content);
        $this->assertSame('text/plain', $attachment->contentType);
        $this->assertNull($attachment->disposition);
        $this->assertNull($attachment->contentId);
    }

    #[Test]
    public function toArrayOmitsNulls(): void
    {
        $attachment = new EmailAttachment('f.pdf', 'QUJD', 'application/pdf');

        $this->assertSame([
            'filename' => 'f.pdf',
            'content' => 'QUJD',
            'contentType' => 'application/pdf',
        ], $attachment->toArray());
    }

    #[Test]
    public function toArrayIncludesInlineDispositionAndContentId(): void
    {
        $attachment = EmailAttachment::fromRawContent(
            'logo.png',
            'raw',
            'image/png',
            EmailAttachmentDisposition::INLINE,
            'logo-cid',
        );

        $this->assertSame([
            'filename' => 'logo.png',
            'content' => base64_encode('raw'),
            'contentType' => 'image/png',
            'disposition' => 'INLINE',
            'contentId' => 'logo-cid',
        ], $attachment->toArray());
    }
}
