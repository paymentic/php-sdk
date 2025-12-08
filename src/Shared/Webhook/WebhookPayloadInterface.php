<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\Webhook;

interface WebhookPayloadInterface
{
    public static function getEventType(): WebhookEvent;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self;
}
