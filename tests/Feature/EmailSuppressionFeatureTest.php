<?php

declare(strict_types=1);

namespace Paymentic\Tests\Feature;

use JsonException;
use Paymentic\Sdk\Messaging\Application\DTO\AddEmailSuppressionsRequest;
use Paymentic\Sdk\Messaging\Application\DTO\ListEmailSuppressionsRequest;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailSuppressionReason;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Sdk\Shared\Enum\ApiErrorCode;
use Paymentic\Sdk\Shared\Exception\ConflictException;
use Paymentic\Sdk\Shared\Exception\NotFoundException;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class EmailSuppressionFeatureTest extends TestCase
{
    private ?MockHttpClient $http = null;

    /**
     * @throws JsonException
     */
    #[Test]
    public function addsSuppressionsWith201(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [
                'added' => [['id' => 'ID-1', 'email' => 'a@b.pl', 'reason' => 'MANUAL', 'isAddedByClient' => true, 'isRemovable' => true, 'createdAt' => '2026-11-01T10:00:00+00:00']],
                'existing' => [],
            ],
        ], JSON_THROW_ON_ERROR), 201);

        $response = $client->messaging()->emailSuppressions()->add(new AddEmailSuppressionsRequest(['a@b.pl']));

        $request = $this->lastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringEndsWith('/v1_2/messaging/email/suppressions', (string) $request->getUri());
        $this->assertSame(['emails' => ['a@b.pl']], $this->lastBody());
        $this->assertCount(1, $response->added);
        $this->assertSame('ID-1', $response->added[0]->id);
        $this->assertSame(EmailSuppressionReason::MANUAL, $response->added[0]->reason);
        $this->assertSame([], $response->existing);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function addsSuppressionsWith200WhenAllExisted(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [
                'added' => [],
                'existing' => [['id' => 'ID-1', 'email' => 'a@b.pl', 'reason' => 'MANUAL', 'isAddedByClient' => true, 'isRemovable' => true, 'createdAt' => '2026-11-01T10:00:00+00:00']],
            ],
        ], JSON_THROW_ON_ERROR), 200);

        $response = $client->messaging()->emailSuppressions()->add(new AddEmailSuppressionsRequest(['a@b.pl']));

        $this->assertSame([], $response->added);
        $this->assertCount(1, $response->existing);
        $this->assertSame('a@b.pl', $response->existing[0]->email);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function listsSuppressionsWithFilters(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [['id' => 'ID-1', 'email' => 'a@b.pl', 'reason' => 'MANUAL', 'isAddedByClient' => true, 'isRemovable' => true, 'createdAt' => '2026-11-01T10:00:00+00:00'], ['id' => 'ID-2', 'email' => 'c@d.pl', 'reason' => 'MANUAL', 'isAddedByClient' => true, 'isRemovable' => true, 'createdAt' => '2026-11-01T10:00:00+00:00']],
            'meta' => ['currentPage' => 2, 'perPage' => 2, 'total' => 5, 'lastPage' => 3],
            'links' => ['first' => 'https://x/?page=1', 'prev' => 'https://x/?page=1', 'next' => 'https://x/?page=3', 'last' => 'https://x/?page=3'],
        ], JSON_THROW_ON_ERROR), 200);

        $response = $client->messaging()->emailSuppressions()->list(new ListEmailSuppressionsRequest(
            filterReason: 'MANUAL',
            filterCreatedAtFrom: '2026-11-01',
            pageNumber: 2,
            pageSize: 2,
        ));

        $request = $this->lastRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringEndsWith(
            '/v1_2/messaging/email/suppressions?filter%5Breason%5D=MANUAL&filter%5BcreatedAt%5D%5Bfrom%5D=2026-11-01&page%5Bnumber%5D=2&page%5Bsize%5D=2',
            (string) $request->getUri(),
        );
        $this->assertCount(2, $response->data);
        $this->assertSame('c@d.pl', $response->data[1]->email);
        $this->assertSame(2, $response->meta->currentPage);
        $this->assertSame(2, $response->meta->perPage);
        $this->assertSame(5, $response->meta->total);
        $this->assertSame(3, $response->meta->lastPage);
        $this->assertNotNull($response->links);
        $this->assertSame('https://x/?page=3', $response->links->next);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function listsSuppressionsWithoutRequestOrLinks(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [],
            'meta' => ['currentPage' => 1, 'perPage' => 50, 'total' => 0, 'lastPage' => 1],
        ], JSON_THROW_ON_ERROR), 200);

        $response = $client->messaging()->emailSuppressions()->list();

        $this->assertStringEndsWith('/v1_2/messaging/email/suppressions', (string) $this->lastRequest()->getUri());
        $this->assertSame([], $response->data);
        $this->assertNull($response->links);
        $this->assertSame(0, $response->meta->total);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function deletesSuppression(): void
    {
        $client = $this->createClient(json_encode([
            'data' => ['id' => 'ID-1', 'email' => 'a@b.pl', 'reason' => 'MANUAL', 'isAddedByClient' => true, 'isRemovable' => true, 'createdAt' => '2026-11-01T10:00:00+00:00'],
        ], JSON_THROW_ON_ERROR), 200);

        $suppression = $client->messaging()->emailSuppressions()->delete('ID-1');

        $request = $this->lastRequest();
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertStringEndsWith('/v1_2/messaging/email/suppressions/ID-1', (string) $request->getUri());
        $this->assertSame('ID-1', $suppression->id);
        $this->assertSame('a@b.pl', $suppression->email);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsConflictWhenSuppressionNotRemovable(): void
    {
        $client = $this->createClient(json_encode([
            'errors' => [['code' => 'EMAIL_SUPPRESSION_NOT_REMOVABLE', 'message' => 'Suppression cannot be removed.']],
        ], JSON_THROW_ON_ERROR), 409);

        try {
            $client->messaging()->emailSuppressions()->delete('ID-1');
            $this->fail('Expected ConflictException.');
        } catch (ConflictException $e) {
            $this->assertSame(ApiErrorCode::EMAIL_SUPPRESSION_NOT_REMOVABLE, $e->getFirstError()?->code);
        }
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsNotFoundWhenSuppressionMissing(): void
    {
        $client = $this->createClient(json_encode([
            'errors' => [['code' => 'EMAIL_SUPPRESSION_NOT_FOUND', 'message' => 'Suppression not found.']],
        ], JSON_THROW_ON_ERROR), 404);

        try {
            $client->messaging()->emailSuppressions()->delete('missing');
            $this->fail('Expected NotFoundException.');
        } catch (NotFoundException $e) {
            $this->assertSame(ApiErrorCode::EMAIL_SUPPRESSION_NOT_FOUND, $e->getFirstError()?->code);
        }
    }

    private function createClient(string $responseBody, int $statusCode): PaymenticClient
    {
        $this->http = new MockHttpClient($responseBody, $statusCode);

        return PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient($this->http)
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build();
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->http?->getLastRequest();
        $this->assertNotNull($request);

        return $request;
    }

    /**
     * @return array<string, mixed>
     * @throws JsonException
     */
    private function lastBody(): array
    {
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) $this->lastRequest()->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
