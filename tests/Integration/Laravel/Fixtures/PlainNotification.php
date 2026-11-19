<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Laravel\Fixtures;

use Illuminate\Notifications\Notification;

final class PlainNotification extends Notification
{
    /**
     * @return list<class-string>
     */
    public function via(object $notifiable): array
    {
        return [];
    }
}
