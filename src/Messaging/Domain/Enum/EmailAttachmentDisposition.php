<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Enum;

enum EmailAttachmentDisposition: string
{
    case ATTACHMENT = 'ATTACHMENT';
    case INLINE = 'INLINE';
}
