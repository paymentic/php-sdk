<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Payment\Domain\Entity\Channel;
use Paymentic\Sdk\Payment\Domain\Enum\AuthorizationType;
use Paymentic\Sdk\Payment\Domain\Enum\PaymentMethod;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelAmount;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelAuthorization;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelCommission;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelImage;

final readonly class ChannelMapper
{
    /**
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public static function fromArray(array $data): Channel
    {
        return new Channel(
            id: $data['id'],
            available: $data['available'],
            method: PaymentMethod::from($data['method']),
            name: $data['name'],
            image: new ChannelImage(
                default: $data['image']['default'] ?? null,
            ),
            amount: new ChannelAmount(
                minimum: $data['amount']['minimum'],
                maximum: $data['amount']['maximum'],
            ),
            currencies: $data['currencies'] ?? [],
            commission: new ChannelCommission(
                value: $data['commission']['value'] ?? null,
                minimum: $data['commission']['minimum'] ?? null,
                fixed: $data['commission']['fixed'] ?? null,
            ),
            authorization: new ChannelAuthorization(
                type: array_map(
                    static fn (string $type): AuthorizationType => AuthorizationType::from($type),
                    $data['authorization']['type'] ?? [],
                ),
            ),
            paymentType: $data['paymentType'],
            enablingAt: isset($data['enablingAt']) ? new DateTimeImmutable($data['enablingAt']) : null,
            disablingAt: isset($data['disablingAt']) ? new DateTimeImmutable($data['disablingAt']) : null,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @return Channel[]
     * @throws Exception
     */
    public static function fromArrayCollection(array $data): array
    {
        return array_map(
            static fn (array $item): Channel => self::fromArray($item),
            $data,
        );
    }
}
