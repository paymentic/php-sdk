<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Payment\Application\Mapper\TransactionListItemMapper;
use Paymentic\Sdk\Payment\Domain\Entity\TransactionListItem;
use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TransactionListItemMapperTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[Test]
    public function mapsBasicTransactionListItem(): void
    {
        $data = [
            'id' => 'ABCD-123-XYZ-9876',
            'status' => 'PAID',
            'amount' => '123.45',
            'title' => 'Order #12345',
        ];

        $item = TransactionListItemMapper::fromArray($data);

        $this->assertInstanceOf(TransactionListItem::class, $item);
        $this->assertSame('ABCD-123-XYZ-9876', $item->id);
        $this->assertSame(TransactionStatus::PAID, $item->status);
        $this->assertSame('123.45', $item->amount);
        $this->assertSame('Order #12345', $item->title);
        $this->assertNull($item->commission);
        $this->assertNull($item->customerName);
        $this->assertNull($item->customerEmail);
        $this->assertNull($item->externalReferenceId);
        $this->assertNull($item->paymentMethod);
        $this->assertNull($item->paymentChannel);
        $this->assertNull($item->orderId);
        $this->assertNull($item->blikId);
        $this->assertNull($item->cardBin);
        $this->assertNull($item->paidAt);
        $this->assertNull($item->createdAt);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsTransactionListItemWithAllFields(): void
    {
        $data = [
            'id' => 'ABCD-123-XYZ-9876',
            'status' => 'PAID',
            'amount' => '123.45',
            'title' => 'Order #12345',
            'commission' => '1.23',
            'customerName' => 'John Doe',
            'customerEmail' => 'john@example.com',
            'externalReferenceId' => 'EXT-REF-123',
            'paymentMethod' => 'BLIK',
            'paymentChannel' => 'blik-psp',
            'orderId' => 'ORD-12345',
            'blikId' => 'BLIK123',
            'cardBin' => '411111',
            'paidAt' => '2024-01-15T11:55:00+00:00',
            'createdAt' => '2024-01-15T11:50:00+00:00',
        ];

        $item = TransactionListItemMapper::fromArray($data);

        $this->assertSame('ABCD-123-XYZ-9876', $item->id);
        $this->assertSame(TransactionStatus::PAID, $item->status);
        $this->assertSame('123.45', $item->amount);
        $this->assertSame('Order #12345', $item->title);
        $this->assertSame('1.23', $item->commission);
        $this->assertSame('John Doe', $item->customerName);
        $this->assertSame('john@example.com', $item->customerEmail);
        $this->assertSame('EXT-REF-123', $item->externalReferenceId);
        $this->assertSame('BLIK', $item->paymentMethod);
        $this->assertSame('blik-psp', $item->paymentChannel);
        $this->assertSame('ORD-12345', $item->orderId);
        $this->assertSame('BLIK123', $item->blikId);
        $this->assertSame('411111', $item->cardBin);
        $this->assertInstanceOf(DateTimeImmutable::class, $item->paidAt);
        $this->assertInstanceOf(DateTimeImmutable::class, $item->createdAt);
        $this->assertSame('2024-01-15', $item->paidAt->format('Y-m-d'));
        $this->assertSame('2024-01-15', $item->createdAt->format('Y-m-d'));
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsArrayCollection(): void
    {
        $data = [
            [
                'id' => 'TXN1-123-ABC-4567',
                'status' => 'PAID',
                'amount' => '100.00',
                'title' => 'Order #1',
            ],
            [
                'id' => 'TXN2-456-DEF-8901',
                'status' => 'CREATED',
                'amount' => '200.00',
                'title' => 'Order #2',
            ],
        ];

        $items = TransactionListItemMapper::fromArrayCollection($data);

        $this->assertCount(2, $items);
        $this->assertSame('TXN1-123-ABC-4567', $items[0]->id);
        $this->assertSame(TransactionStatus::PAID, $items[0]->status);
        $this->assertSame('TXN2-456-DEF-8901', $items[1]->id);
        $this->assertSame(TransactionStatus::CREATED, $items[1]->status);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsEmptyCollection(): void
    {
        $items = TransactionListItemMapper::fromArrayCollection([]);

        $this->assertCount(0, $items);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsAllTransactionStatuses(): void
    {
        $statuses = ['CREATED', 'PENDING', 'PAID', 'FAILED', 'EXPIRED'];
        $expectedEnums = [
            TransactionStatus::CREATED,
            TransactionStatus::PENDING,
            TransactionStatus::PAID,
            TransactionStatus::FAILED,
            TransactionStatus::EXPIRED,
        ];

        foreach ($statuses as $index => $status) {
            $data = [
                'id' => 'TXN-' . $index,
                'status' => $status,
                'amount' => '100.00',
                'title' => 'Test',
            ];

            $item = TransactionListItemMapper::fromArray($data);

            $this->assertSame($expectedEnums[$index], $item->status);
        }
    }
}
