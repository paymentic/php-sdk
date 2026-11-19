<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Mailer;

final class PaymenticMailerHeaders
{
    public const IDEMPOTENCY_KEY = 'X-Paymentic-Idempotency-Key';
    public const PRIORITY = 'X-Paymentic-Priority';
    public const EXTERNAL_REFERENCE_ID = 'X-Paymentic-External-Reference-Id';
    public const METADATA_PREFIX = 'X-Paymentic-Metadata-';

    private function __construct()
    {
    }
}
