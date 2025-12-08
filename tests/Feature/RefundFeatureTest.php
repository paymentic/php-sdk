<?php

declare(strict_types=1);

namespace Paymentic\Tests\Feature;

use JsonException;
use Paymentic\Sdk\Payment\Application\DTO\CreateRefundRequest;
use Paymentic\Sdk\Payment\Domain\Enum\RefundStatus;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Sdk\Shared\Exception\BadRequestException;
use Paymentic\Sdk\Shared\Exception\NotFoundException;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RefundFeatureTest extends TestCase
{
    /**
     * @throws JsonException
     */
    #[Test]
    public function createsRefundSuccessfully(): void
    {
        $responseBody = json_encode([
            'data' => [
                'id' => 'AB1-AB2-AB3',
                'status' => 'CREATED',
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 201);

        $request = new CreateRefundRequest(
            amount: '50.00',
            reason: 'Customer requested refund',
            externalReferenceId: 'EXT-REFUND-001',
        );

        $response = $client->payment()->refunds()->create('b8e6e2fc', 'ABCD-123-XYZ-9876', $request);

        $this->assertSame('AB1-AB2-AB3', $response->id);
        $this->assertSame(RefundStatus::CREATED, $response->status);
    }

    #[Test]
    public function getsRefundDetails(): void
    {
        $responseBody = json_encode([
            'data' => [
                'id' => 'AB1-AB2-AB3',
                'status' => 'DONE',
                'amount' => '50.00',
                'reason' => 'Customer requested refund',
                'externalReferenceId' => 'EXT-REFUND-001',
                'createdAt' => '2024-01-15T12:00:00+00:00',
                'updatedAt' => '2024-01-15T12:05:00+00:00',
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 200);

        $refund = $client->payment()->refunds()->get('b8e6e2fc', 'ABCD-123-XYZ-9876', 'AB1-AB2-AB3');

        $this->assertSame('AB1-AB2-AB3', $refund->id);
        $this->assertSame(RefundStatus::DONE, $refund->status);
        $this->assertSame('50.00', $refund->amount);
        $this->assertSame('Customer requested refund', $refund->reason);
        $this->assertSame('EXT-REFUND-001', $refund->externalReferenceId);
    }

    #[Test]
    public function throwsBadRequestWhenRefundsDisabled(): void
    {
        $responseBody = json_encode([
            'errors' => [
                [
                    'code' => 'POINT_REFUNDS_DISABLED',
                    'message' => 'Point refunds disabled.',
                    'docsUrl' => 'https://docs.paymentic.com/errors#POINT_REFUNDS_DISABLED',
                    'details' => null,
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 400);

        $this->expectException(BadRequestException::class);

        $request = new CreateRefundRequest(amount: '50.00');

        $client->payment()->refunds()->create('b8e6e2fc', 'ABCD-123-XYZ-9876', $request);
    }

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

        $client->payment()->refunds()->get('xxxxxxxx', 'ABCD-123-XYZ-9876', 'AB1-AB2-AB3');
    }

    #[Test]
    public function handlesAllRefundStatuses(): void
    {
        $statuses = ['CREATED', 'ACCEPTED', 'PENDING', 'DONE', 'REJECTED', 'CANCELLED'];

        foreach ($statuses as $status) {
            $responseBody = json_encode([
                'data' => [
                    'id' => 'AB1-AB2-AB3',
                    'status' => $status,
                    'amount' => '50.00',
                    'reason' => null,
                    'externalReferenceId' => null,
                    'createdAt' => '2024-01-15T12:00:00+00:00',
                    'updatedAt' => null,
                ],
            ], JSON_THROW_ON_ERROR);

            $client = $this->createClient($responseBody, 200);
            $refund = $client->payment()->refunds()->get('b8e6e2fc', 'ABCD-123-XYZ-9876', 'AB1-AB2-AB3');

            $this->assertSame(RefundStatus::from($status), $refund->status);
        }
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
