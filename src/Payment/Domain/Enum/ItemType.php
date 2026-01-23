<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Enum;

enum ItemType: string
{
    case PRODUCT = 'PRODUCT';
    case SHIPPING = 'SHIPPING';
    case DISCOUNT = 'DISCOUNT';
    case SURCHARGE = 'SURCHARGE';
    case GIFT_CARD = 'GIFT_CARD';
}
