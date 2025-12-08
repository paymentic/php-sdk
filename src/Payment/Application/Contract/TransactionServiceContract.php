<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Contract;

use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionRequest;
use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionResponse;
use Paymentic\Sdk\Payment\Domain\Entity\Transaction;

interface TransactionServiceContract
{
    public function create(string $pointId, CreateTransactionRequest $request): CreateTransactionResponse;

    public function get(string $pointId, string $transactionId): Transaction;

    public function capture(string $pointId, string $transactionId): void;
}
