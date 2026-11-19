<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Laravel\Fixtures;

use Illuminate\Notifications\Notifiable;

final class TestNotifiable
{
    use Notifiable;

    public function __construct(
        private readonly ?string $phone = null,
    ) {
    }

    public function routeNotificationForPaymenticSms(): ?string
    {
        return $this->phone;
    }
}
