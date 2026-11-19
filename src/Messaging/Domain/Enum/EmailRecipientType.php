<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Enum;

enum EmailRecipientType: string
{
    case TO = 'TO';
    case CC = 'CC';
    case BCC = 'BCC';
}
