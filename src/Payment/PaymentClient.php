<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment;

use Paymentic\Sdk\Payment\Application\Contract\BlikServiceContract;
use Paymentic\Sdk\Payment\Application\Contract\PointServiceContract;
use Paymentic\Sdk\Payment\Application\Contract\RefundServiceContract;
use Paymentic\Sdk\Payment\Application\Contract\TransactionServiceContract;
use Paymentic\Sdk\Payment\Application\Service\BlikService;
use Paymentic\Sdk\Payment\Application\Service\PointService;
use Paymentic\Sdk\Payment\Application\Service\RefundService;
use Paymentic\Sdk\Payment\Application\Service\TransactionService;
use Paymentic\Sdk\Shared\Http\HttpClient;

final class PaymentClient
{
    private TransactionServiceContract $transactionService;
    private RefundServiceContract $refundService;
    private BlikServiceContract $blikService;
    private PointServiceContract $pointService;

    public function __construct(
        HttpClient $httpClient,
    ) {
        $this->transactionService = new TransactionService($httpClient);
        $this->refundService = new RefundService($httpClient);
        $this->blikService = new BlikService($httpClient);
        $this->pointService = new PointService($httpClient);
    }

    public function transactions(): TransactionServiceContract
    {
        return $this->transactionService;
    }

    public function refunds(): RefundServiceContract
    {
        return $this->refundService;
    }

    public function blik(): BlikServiceContract
    {
        return $this->blikService;
    }

    public function points(): PointServiceContract
    {
        return $this->pointService;
    }
}
