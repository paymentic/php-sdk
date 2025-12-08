<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Enum;

enum PaymentMethod: string
{
    case BLIK = 'BLIK';
    case PBL = 'PBL';
    case BNPL = 'BNPL';
    case CARD = 'CARD';
    case MOBILE_WALLET = 'MOBILE_WALLET';
    case PAYSAFE = 'PAYSAFE';
}
