<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Messaging\Application\DTO;

use Paymentic\Sdk\Messaging\Application\DTO\AddEmailSuppressionsRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AddEmailSuppressionsRequestTest extends TestCase
{
    #[Test]
    public function buildsArray(): void
    {
        $request = new AddEmailSuppressionsRequest(['a@b.pl', 'c@d.pl']);

        $this->assertSame(['emails' => ['a@b.pl', 'c@d.pl']], $request->toArray());
    }

    #[Test]
    public function reindexesEmails(): void
    {
        $request = new AddEmailSuppressionsRequest([4 => 'a@b.pl']);

        $this->assertSame(['emails' => ['a@b.pl']], $request->toArray());
    }
}
