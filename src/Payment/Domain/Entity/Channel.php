<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Entity;

use DateTimeInterface;
use Paymentic\Sdk\Payment\Domain\Enum\PaymentMethod;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelAmount;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelAuthorization;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelCommission;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelImage;

final readonly class Channel
{
    /**
     * @param string[] $currencies
     */
    public function __construct(
        public string $id,
        public bool $available,
        public PaymentMethod $method,
        public string $name,
        public ChannelImage $image,
        public ChannelAmount $amount,
        public array $currencies,
        public ChannelCommission $commission,
        public ChannelAuthorization $authorization,
        public string $paymentType,
        public ?DateTimeInterface $enablingAt = null,
        public ?DateTimeInterface $disablingAt = null,
    ) {
    }
}
