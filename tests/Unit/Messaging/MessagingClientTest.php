<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Messaging;

use Paymentic\Sdk\Messaging\Application\Contract\EmailServiceContract;
use Paymentic\Sdk\Messaging\Application\Contract\EmailSuppressionServiceContract;
use Paymentic\Sdk\Messaging\Application\Contract\SmsServiceContract;
use Paymentic\Sdk\Messaging\Application\Contract\SystemServiceContract;
use Paymentic\Sdk\Messaging\MessagingClient;
use Paymentic\Sdk\Shared\Http\HttpClient;
use Paymentic\Tests\Support\MockPsrClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MessagingClientTest extends TestCase
{
    #[Test]
    public function returnsServiceContracts(): void
    {
        $client = $this->createClient();

        $this->assertInstanceOf(SmsServiceContract::class, $client->sms());
        $this->assertInstanceOf(EmailServiceContract::class, $client->email());
        $this->assertInstanceOf(EmailSuppressionServiceContract::class, $client->emailSuppressions());
        $this->assertInstanceOf(SystemServiceContract::class, $client->system());
    }

    #[Test]
    public function returnsSameInstancesOnRepeatedCalls(): void
    {
        $client = $this->createClient();

        $this->assertSame($client->sms(), $client->sms());
        $this->assertSame($client->email(), $client->email());
        $this->assertSame($client->emailSuppressions(), $client->emailSuppressions());
        $this->assertSame($client->system(), $client->system());
    }

    private function createClient(): MessagingClient
    {
        return new MessagingClient(new HttpClient(
            new MockPsrClient(),
            new MockRequestFactory(),
            new MockStreamFactory(),
            'test-api-key',
            'https://api.sandbox.paymentic.com/v1_2',
        ));
    }
}
