<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\DTO;

use Paymentic\Sdk\Payment\Application\DTO\ProcessBlikRequest;
use Paymentic\Sdk\Payment\Domain\Enum\BlikType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProcessBlikRequestTest extends TestCase
{
    #[Test]
    public function createsWithDefaultCodeType(): void
    {
        $request = new ProcessBlikRequest(code: '123456');

        $this->assertSame('123456', $request->code);
        $this->assertSame(BlikType::CODE, $request->type);
    }

    #[Test]
    public function createsWithAliasType(): void
    {
        $request = new ProcessBlikRequest(
            code: 'alias-token-123',
            type: BlikType::ALIAS,
        );

        $this->assertSame('alias-token-123', $request->code);
        $this->assertSame(BlikType::ALIAS, $request->type);
    }

    #[Test]
    public function convertsToArrayWithCodeType(): void
    {
        $request = new ProcessBlikRequest(code: '654321');

        $array = $request->toArray();

        $this->assertSame([
            'type' => 'CODE',
            'code' => '654321',
        ], $array);
    }

    #[Test]
    public function convertsToArrayWithAliasType(): void
    {
        $request = new ProcessBlikRequest(
            code: 'my-alias',
            type: BlikType::ALIAS,
        );

        $array = $request->toArray();

        $this->assertSame([
            'type' => 'ALIAS',
            'code' => 'my-alias',
        ], $array);
    }
}
