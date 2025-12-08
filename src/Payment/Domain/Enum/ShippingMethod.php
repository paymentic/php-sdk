<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Enum;

enum ShippingMethod: string
{
    case VIRTUAL = 'VIRTUAL';
    case TRACKED_DELIVERY = 'TRACKED_DELIVERY';
    case UNTRACKED_DELIVERY = 'UNTRACKED_DELIVERY';
    case IN_STORE_PICKUP = 'IN_STORE_PICKUP';
    case PARCEL_PICKUP = 'PARCEL_PICKUP';
    case LOCKER_PICKUP = 'LOCKER_PICKUP';
    case HYBRID = 'HYBRID';
    case OTHER = 'OTHER';
}
