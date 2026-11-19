<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Laravel\Fixtures;

use Illuminate\Notifications\Notification;
use Paymentic\Sdk\Integration\Laravel\Notifications\Messages\PaymenticSmsMessage;

final class SmsNotification extends Notification
{
    /**
     * @param list<string> $channels
     */
    public function __construct(
        private readonly array $channels = ['paymentic-sms'],
        private readonly PaymenticSmsMessage|string|null $message = null,
    ) {
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toPaymenticSms(object $notifiable): PaymenticSmsMessage|string
    {
        return $this->message ?? PaymenticSmsMessage::create('Your code is 1234.');
    }
}
