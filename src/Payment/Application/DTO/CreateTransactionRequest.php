<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\DTO;

use DateTimeInterface;
use Paymentic\Sdk\Payment\Domain\Enum\PaymentMethod;
use Paymentic\Sdk\Payment\Domain\ValueObject\Address;
use Paymentic\Sdk\Payment\Domain\ValueObject\CartItem;
use Paymentic\Sdk\Payment\Domain\ValueObject\Customer;
use Paymentic\Sdk\Payment\Domain\ValueObject\Order;
use Paymentic\Sdk\Payment\Domain\ValueObject\Redirect;
use Paymentic\Sdk\Shared\Enum\Currency;

final readonly class CreateTransactionRequest
{
    /**
     * @param array<CartItem> $cart
     */
    public function __construct(
        public string $amount,
        public string $title,
        public ?Currency $currency = null,
        public ?string $description = null,
        public ?string $externalReferenceId = null,
        public ?Redirect $redirect = null,
        public ?Customer $customer = null,
        public ?Order $order = null,
        public ?Address $billingAddress = null,
        public ?Address $shippingAddress = null,
        public ?array $cart = null,
        public ?PaymentMethod $paymentMethod = null,
        public ?string $paymentChannel = null,
        public ?bool $createRegistration = null,
        public ?bool $whitelabel = null,
        public ?bool $autoCapture = null,
        public ?DateTimeInterface $expiresAt = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'amount' => $this->amount,
            'title' => $this->title,
        ];

        $this->addIfNotNull($data, 'currency', $this->currency?->value);
        $this->addIfNotNull($data, 'description', $this->description);
        $this->addIfNotNull($data, 'externalReferenceId', $this->externalReferenceId);
        $this->addIfNotNull($data, 'redirect', $this->redirect?->toArray());
        $this->addIfNotNull($data, 'customer', $this->customer?->toArray());
        $this->addIfNotNull($data, 'order', $this->order?->toArray());
        $this->addIfNotNull($data, 'billingAddress', $this->billingAddress?->toArray());
        $this->addIfNotNull($data, 'shippingAddress', $this->shippingAddress?->toArray());
        $this->addIfNotNull($data, 'cart', null !== $this->cart ? array_map(static fn (CartItem $item): array => $item->toArray(), $this->cart) : null);
        $this->addIfNotNull($data, 'paymentMethod', $this->paymentMethod?->value);
        $this->addIfNotNull($data, 'paymentChannel', $this->paymentChannel);
        $this->addIfNotNull($data, 'createRegistration', $this->createRegistration);
        $this->addIfNotNull($data, 'whitelabel', $this->whitelabel);
        $this->addIfNotNull($data, 'autoCapture', $this->autoCapture);
        $this->addIfNotNull($data, 'expiresAt', $this->expiresAt?->format('c'));

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function addIfNotNull(array &$data, string $key, mixed $value): void
    {
        if (null !== $value) {
            $data[$key] = $value;
        }
    }
}
