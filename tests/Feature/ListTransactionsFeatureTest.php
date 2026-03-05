<?php

declare(strict_types=1);

namespace Paymentic\Tests\Feature;

use JsonException;
use Paymentic\Sdk\Payment\Application\DTO\ListTransactionsRequest;
use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Sdk\Shared\Exception\NotFoundException;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListTransactionsFeatureTest extends TestCase
{
    /**
     * @throws JsonException
     */
    #[Test]
    public function listsTransactionsSuccessfully(): void
    {
        $responseBody = json_encode([
            'data' => [
                [
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
                    'cardBin' => null,
                    'paidAt' => '2024-01-15T11:55:00+00:00',
                    'createdAt' => '2024-01-15T11:50:00+00:00',
                ],
                [
                    'id' => 'EFGH-456-UVW-3210',
                    'status' => 'CREATED',
                    'amount' => '50.00',
                    'title' => 'Order #67890',
                    'commission' => null,
                    'customerName' => null,
                    'customerEmail' => null,
                    'externalReferenceId' => null,
                    'paymentMethod' => null,
                    'paymentChannel' => null,
                    'orderId' => null,
                    'blikId' => null,
                    'cardBin' => null,
                    'paidAt' => null,
                    'createdAt' => '2024-01-16T09:00:00+00:00',
                ],
            ],
            'pagination' => [
                'page' => 1,
                'pageSize' => 25,
                'total' => 2,
                'totalPages' => 1,
                'from' => 1,
                'to' => 2,
                'links' => [
                    'first' => 'https://api.paymentic.com/v1_2/payment/points/b8e6e2fc/transactions?page[number]=1',
                    'prev' => null,
                    'next' => null,
                    'last' => 'https://api.paymentic.com/v1_2/payment/points/b8e6e2fc/transactions?page[number]=1',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 200);

        $response = $client->payment()->transactions()->list('b8e6e2fc');

        $this->assertCount(2, $response->data);

        $first = $response->data[0];
        $this->assertSame('ABCD-123-XYZ-9876', $first->id);
        $this->assertSame(TransactionStatus::PAID, $first->status);
        $this->assertSame('123.45', $first->amount);
        $this->assertSame('Order #12345', $first->title);
        $this->assertSame('1.23', $first->commission);
        $this->assertSame('John Doe', $first->customerName);
        $this->assertSame('john@example.com', $first->customerEmail);
        $this->assertSame('BLIK', $first->paymentMethod);
        $this->assertSame('blik-psp', $first->paymentChannel);
        $this->assertSame('ORD-12345', $first->orderId);
        $this->assertSame('BLIK123', $first->blikId);
        $this->assertNotNull($first->paidAt);
        $this->assertNotNull($first->createdAt);

        $second = $response->data[1];
        $this->assertSame('EFGH-456-UVW-3210', $second->id);
        $this->assertSame(TransactionStatus::CREATED, $second->status);
        $this->assertNull($second->customerName);

        $this->assertSame(1, $response->pagination->page);
        $this->assertSame(25, $response->pagination->pageSize);
        $this->assertSame(2, $response->pagination->total);
        $this->assertSame(1, $response->pagination->totalPages);
        $this->assertSame(1, $response->pagination->from);
        $this->assertSame(2, $response->pagination->to);
        $this->assertNotNull($response->pagination->links);
        $this->assertNotNull($response->pagination->links->first);
        $this->assertNull($response->pagination->links->prev);
        $this->assertNull($response->pagination->links->next);
        $this->assertNotNull($response->pagination->links->last);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function listsTransactionsWithFilters(): void
    {
        $responseBody = json_encode([
            'data' => [
                [
                    'id' => 'ABCD-123-XYZ-9876',
                    'status' => 'PAID',
                    'amount' => '123.45',
                    'title' => 'Order #12345',
                    'paidAt' => '2024-01-15T11:55:00+00:00',
                    'createdAt' => '2024-01-15T11:50:00+00:00',
                ],
            ],
            'pagination' => [
                'page' => 1,
                'pageSize' => 10,
                'total' => 1,
                'totalPages' => 1,
                'from' => 1,
                'to' => 1,
                'links' => [
                    'first' => null,
                    'prev' => null,
                    'next' => null,
                    'last' => null,
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $mockHttpClient = new MockHttpClient($responseBody, 200);

        $client = PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient($mockHttpClient)
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build();

        $request = new ListTransactionsRequest(
            filterStatus: 'PAID',
            filterProvider: 'blik',
            pageNumber: 1,
            pageSize: 10,
        );

        $response = $client->payment()->transactions()->list('b8e6e2fc', $request);

        $this->assertCount(1, $response->data);
        $this->assertSame(1, $response->pagination->page);

        $lastRequest = $mockHttpClient->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('GET', $lastRequest->getMethod());
        $uri = (string) $lastRequest->getUri();
        $this->assertStringContainsString('/payment/points/b8e6e2fc/transactions', $uri);
        $this->assertStringContainsString('filter%5Bstatus%5D=PAID', $uri);
        $this->assertStringContainsString('filter%5Bprovider%5D=blik', $uri);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function listsEmptyTransactions(): void
    {
        $responseBody = json_encode([
            'data' => [],
            'pagination' => [
                'page' => 1,
                'pageSize' => 25,
                'total' => 0,
                'totalPages' => 0,
                'from' => null,
                'to' => null,
                'links' => [
                    'first' => null,
                    'prev' => null,
                    'next' => null,
                    'last' => null,
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 200);

        $response = $client->payment()->transactions()->list('b8e6e2fc');

        $this->assertCount(0, $response->data);
        $this->assertSame(0, $response->pagination->total);
        $this->assertNull($response->pagination->from);
        $this->assertNull($response->pagination->to);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsNotFoundWhenPointNotFound(): void
    {
        $responseBody = json_encode([
            'errors' => [
                [
                    'code' => 'POINT_NOT_FOUND',
                    'message' => 'Point not found.',
                    'docsUrl' => 'https://docs.paymentic.com/errors#POINT_NOT_FOUND',
                    'details' => null,
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 404);

        $this->expectException(NotFoundException::class);

        $client->payment()->transactions()->list('xxxxxxxx');
    }

    private function createClient(string $responseBody, int $statusCode): PaymenticClient
    {
        return PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient(new MockHttpClient($responseBody, $statusCode))
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build();
    }
}
