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
use Paymentic\Sdk\Payment\Domain\ValueObject\ComplianceContent;
use Paymentic\Sdk\Payment\Domain\ValueObject\ComplianceItem;
use Paymentic\Sdk\Payment\Domain\ValueObject\ComplianceLink;

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
            aliases: $data['aliases'] ?? null,
            compliance: isset($data['compliance']) ? self::mapCompliance($data['compliance']) : null,
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

    /**
     * @param array<int, array<string, mixed>> $items
     * @return ComplianceItem[]
     */
    private static function mapCompliance(array $items): array
    {
        return array_map(static function (array $item): ComplianceItem {
            $content = isset($item['content']) ? new ComplianceContent(
                text: $item['content']['text'] ?? null,
                html: $item['content']['html'] ?? null,
                markdown: $item['content']['markdown'] ?? null,
            ) : null;

            $links = array_map(
                static fn (array $link): ComplianceLink => new ComplianceLink(
                    id: $link['id'] ?? null,
                    label: $link['label'] ?? null,
                    url: $link['url'] ?? null,
                ),
                $item['links'] ?? [],
            );

            return new ComplianceItem(
                id: $item['id'],
                type: $item['type'],
                required: $item['required'],
                checked: $item['checked'] ?? null,
                content: $content,
                links: $links,
            );
        }, $items);
    }
}
