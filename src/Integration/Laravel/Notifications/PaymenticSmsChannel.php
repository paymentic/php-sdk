<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Laravel\Notifications;

use Illuminate\Notifications\Notification;
use LogicException;
use Paymentic\Sdk\Integration\Laravel\Notifications\Messages\PaymenticSmsMessage;
use Paymentic\Sdk\Messaging\Domain\Entity\SmsMessage;
use Paymentic\Sdk\Messaging\MessagingClient;
use UnexpectedValueException;

final readonly class PaymenticSmsChannel
{
    public function __construct(
        private MessagingClient $messaging,
        private ?string $defaultSender = null,
    ) {
    }

    public function send(object $notifiable, Notification $notification): ?SmsMessage
    {
        if (! method_exists($notification, 'toPaymenticSms')) {
            throw new LogicException(sprintf('Notification [%s] is missing the toPaymenticSms() method.', $notification::class));
        }

        $message = $notification->toPaymenticSms($notifiable);

        if (is_string($message)) {
            $message = new PaymenticSmsMessage($message);
        }

        if (! $message instanceof PaymenticSmsMessage) {
            throw new UnexpectedValueException(sprintf(
                'Notification [%s]::toPaymenticSms() must return a string or an instance of [%s].',
                $notification::class,
                PaymenticSmsMessage::class,
            ));
        }

        $recipient = $message->getRecipient();
        if (null === $recipient && method_exists($notifiable, 'routeNotificationFor')) {
            $recipient = $notifiable->routeNotificationFor('paymentic-sms', $notification);
            if (null === $recipient || '' === $recipient) {
                $recipient = $notifiable->routeNotificationFor(self::class, $notification);
            }
        }

        if (null === $recipient || '' === $recipient) {
            return null;
        }

        /** @var string $recipient */
        return $this->messaging->sms()->send(
            $message->toRequest((string) $recipient, $this->defaultSender),
            $message->getIdempotencyKey(),
        );
    }
}
