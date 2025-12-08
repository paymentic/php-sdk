<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\DTO;

use Paymentic\Sdk\Payment\Domain\Enum\BlikType;

final readonly class ProcessBlikRequest
{
    public function __construct(
        public string $code,
        public BlikType $type = BlikType::CODE,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'code' => $this->code,
        ];
    }
}
