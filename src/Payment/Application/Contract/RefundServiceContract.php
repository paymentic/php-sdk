<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Contract;

use Paymentic\Sdk\Payment\Application\DTO\CreateRefundRequest;
use Paymentic\Sdk\Payment\Application\DTO\CreateRefundResponse;
use Paymentic\Sdk\Payment\Domain\Entity\Refund;

interface RefundServiceContract
{
    public function create(string $pointId, string $transactionId, CreateRefundRequest $request): CreateRefundResponse;

    public function get(string $pointId, string $transactionId, string $refundId): Refund;
}
