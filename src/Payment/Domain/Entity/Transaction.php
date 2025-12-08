<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Entity;

use DateTimeInterface;
use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;
use Paymentic\Sdk\Payment\Domain\ValueObject\Address;
use Paymentic\Sdk\Payment\Domain\ValueObject\CartItem;
use Paymentic\Sdk\Payment\Domain\ValueObject\Customer;
use Paymentic\Sdk\Payment\Domain\ValueObject\Order;
use Paymentic\Sdk\Payment\Domain\ValueObject\Redirect;
use Paymentic\Sdk\Shared\Enum\Currency;

final readonly class Transaction
{
    /**
     * @param array<CartItem>|null $cart
     */
    public function __construct(
        public string $id,
        public TransactionStatus $status,
        public string $amount,
        public Currency $currency,
        public string $title,
        public ?string $commission = null,
        public ?string $description = null,
        public ?Customer $customer = null,
        public ?Order $order = null,
        public ?Address $billingAddress = null,
        public ?Address $shippingAddress = null,
        public ?string $externalReferenceId = null,
        public ?Redirect $redirect = null,
        public ?string $paymentMethod = null,
        public ?string $paymentChannel = null,
        public ?bool $whitelabel = null,
        public ?array $cart = null,
        public ?bool $autoCapture = null,
        public ?bool $isCaptured = null,
        public ?DateTimeInterface $capturedAt = null,
        public ?DateTimeInterface $paidAt = null,
        public ?DateTimeInterface $createdAt = null,
        public ?DateTimeInterface $expiresAt = null,
    ) {
    }
}
