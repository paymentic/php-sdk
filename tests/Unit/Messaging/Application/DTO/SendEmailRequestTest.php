<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Messaging\Application\DTO;

use Paymentic\Sdk\Messaging\Application\DTO\SendEmailRequest;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailAttachmentDisposition;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\ValueObject\EmailAttachment;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SendEmailRequestTest extends TestCase
{
    #[Test]
    public function omitsNullValues(): void
    {
        $request = new SendEmailRequest(from: 'a@b.pl', to: ['x@y.pl'], subject: 'S', html: '<p>Hi</p>');

        $this->assertSame([
            'from' => 'a@b.pl',
            'to' => ['x@y.pl'],
            'subject' => 'S',
            'html' => '<p>Hi</p>',
        ], $request->toArray());
    }

    #[Test]
    public function omitsEmptyHeadersAndMetadata(): void
    {
        $request = new SendEmailRequest(
            from: 'a@b.pl',
            to: ['x@y.pl'],
            subject: 'S',
            headers: [],
            metadata: [],
        );

        $data = $request->toArray();

        $this->assertArrayNotHasKey('headers', $data);
        $this->assertArrayNotHasKey('metadata', $data);
    }

    #[Test]
    public function serializesAllFieldsInSpecOrder(): void
    {
        $request = new SendEmailRequest(
            from: 'a@b.pl',
            to: ['x@y.pl'],
            subject: 'S',
            fromName: 'Name',
            html: '<p>Hi</p>',
            text: 'Hi',
            cc: ['c@y.pl'],
            bcc: ['b@y.pl'],
            replyTo: ['r@y.pl'],
            headers: ['X-Custom' => 'v'],
            attachments: [
                new EmailAttachment('f.png', 'AAAA', 'image/png', EmailAttachmentDisposition::INLINE, 'cid-1'),
            ],
            metadata: ['orderId' => '1'],
            externalReferenceId: 'ref',
            priority: MessagePriority::LOW,
        );

        $data = $request->toArray();

        $this->assertSame(
            ['from', 'fromName', 'to', 'cc', 'bcc', 'replyTo', 'subject', 'html', 'text', 'headers', 'attachments', 'metadata', 'externalReferenceId', 'priority'],
            array_keys($data),
        );
        $this->assertSame([
            [
                'filename' => 'f.png',
                'content' => 'AAAA',
                'contentType' => 'image/png',
                'disposition' => 'INLINE',
                'contentId' => 'cid-1',
            ],
        ], $data['attachments']);
        $this->assertSame('LOW', $data['priority']);
        $this->assertSame(['X-Custom' => 'v'], $data['headers']);
        $this->assertSame(['orderId' => '1'], $data['metadata']);
    }

    #[Test]
    public function reindexesRecipientLists(): void
    {
        $request = new SendEmailRequest(
            from: 'a@b.pl',
            to: [3 => 'x@y.pl', 7 => 'z@y.pl'],
            subject: 'S',
            cc: [2 => 'c@y.pl'],
            bcc: [5 => 'b@y.pl'],
            replyTo: [9 => 'r@y.pl'],
        );

        $data = $request->toArray();

        $this->assertSame(['x@y.pl', 'z@y.pl'], $data['to']);
        $this->assertSame(['c@y.pl'], $data['cc']);
        $this->assertSame(['b@y.pl'], $data['bcc']);
        $this->assertSame(['r@y.pl'], $data['replyTo']);
    }
}
