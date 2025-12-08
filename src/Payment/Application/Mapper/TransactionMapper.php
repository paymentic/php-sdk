<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Payment\Domain\Entity\Transaction;
use Paymentic\Sdk\Payment\Domain\Enum\CustomerType;
use Paymentic\Sdk\Payment\Domain\Enum\ShippingMethod;
use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;
use Paymentic\Sdk\Payment\Domain\ValueObject\Address;
use Paymentic\Sdk\Payment\Domain\ValueObject\CartItem;
use Paymentic\Sdk\Payment\Domain\ValueObject\Customer;
use Paymentic\Sdk\Payment\Domain\ValueObject\Order;
use Paymentic\Sdk\Payment\Domain\ValueObject\Redirect;
use Paymentic\Sdk\Shared\Enum\Currency;

final readonly class TransactionMapper
{
    /**
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public static function fromArray(array $data): Transaction
    {
        return new Transaction(
            id: $data['id'],
            status: TransactionStatus::from($data['status']),
            amount: $data['amount'],
            currency: Currency::from($data['currency']),
            title: $data['title'],
            commission: $data['commission'] ?? null,
            description: $data['description'] ?? null,
            customer: isset($data['customer']) ? new Customer(...$data['customer']) : null,
            order: isset($data['order']) ? new Order(
                id: $data['order']['id'] ?? null,
                shippingMethod: isset($data['order']['shippingMethod'])
                    ? ShippingMethod::from($data['order']['shippingMethod'])
                    : null,
                trackingNumber: $data['order']['trackingNumber'] ?? null,
                customerType: isset($data['order']['customerType'])
                    ? CustomerType::from($data['order']['customerType'])
                    : null
            ) : null,
            billingAddress: isset($data['billingAddress']) ? new Address(...$data['billingAddress']) : null,
            shippingAddress: isset($data['shippingAddress']) ? new Address(...$data['shippingAddress']) : null,
            externalReferenceId: $data['externalReferenceId'] ?? null,
            redirect: isset($data['redirect']) ? new Redirect(...$data['redirect']) : null,
            paymentMethod: $data['paymentMethod'] ?? null,
            paymentChannel: $data['paymentChannel'] ?? null,
            whitelabel: $data['whitelabel'] ?? null,
            cart: isset($data['cart']) ? array_map(static fn ($item): CartItem => new CartItem(...$item), $data['cart']) : null,
            autoCapture: $data['autoCapture'] ?? null,
            isCaptured: $data['isCaptured'] ?? null,
            capturedAt: isset($data['capturedAt']) ? new DateTimeImmutable($data['capturedAt']) : null,
            paidAt: isset($data['paidAt']) ? new DateTimeImmutable($data['paidAt']) : null,
            createdAt: isset($data['createdAt']) ? new DateTimeImmutable($data['createdAt']) : null,
            expiresAt: isset($data['expiresAt']) ? new DateTimeImmutable($data['expiresAt']) : null,
        );
    }
}
