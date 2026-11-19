# Paymentic PHP SDK

Official PHP SDK for [Paymentic](https://www.paymentic.com) payment gateway.

## Requirements

- PHP 8.2+
- PSR-18 HTTP Client (e.g., Guzzle)

## Installation

```bash
composer require paymentic/php-sdk
```

For HTTP client, install Guzzle (recommended):

```bash
composer require guzzlehttp/guzzle guzzlehttp/psr7
```

## Quick Start

### Initialize the Client

```php
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Sdk\Environment;

$client = PaymenticClientFactory::create('your-api-key')
    ->withSandbox() // or ->withProduction()
    ->build();
```

### Create a Transaction

```php
use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionRequest;
use Paymentic\Sdk\Shared\Enum\Currency;

$request = new CreateTransactionRequest(
    amount: '100.00',
    title: 'Order #12345',
    currency: Currency::PLN,
    description: 'Payment for order',
    externalReferenceId: 'order-12345',
);

$response = $client->payment()->transactions()->create(
    pointId: 'your-point-id',
    request: $request,
);

// Redirect user to payment page
$paymentUrl = $response->paymentUrl;
```

### Get Transaction by ID

```php
$transaction = $client->payment()->transactions()->get(
    pointId: 'your-point-id',
    transactionId: 'transaction-uuid',
);

echo $transaction->status->value;
```

### Get Available Payment Channels

```php
$channels = $client->payment()->points()->getChannels(
    pointId: 'your-point-id',
);

foreach ($channels as $channel) {
    echo $channel->name;
}
```

## Messaging

The Messaging module sends SMS and email and manages the email suppression list. It uses the same API key and
environment as the Payment module and is available through `$client->messaging()`.

All monetary values (`totalCost`, `netCost`, `vatRate`, `recipientsCost`, `attachmentsCost`) are returned as decimal
strings (for example `'0.086'`). They are never cast to float.

### Ping

```php
$ping = $client->messaging()->system()->ping();

echo $ping->message; // pong
```

### Send SMS

```php
use Paymentic\Sdk\Messaging\Application\DTO\SendSmsRequest;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;

$sms = $client->messaging()->sms()->send(
    new SendSmsRequest(
        recipient: '+48123123123',      // E.164
        text: 'Your code is 1234.',
        sender: 'Shop',                  // optional, max 11 chars; omit to use the client's default sender
        unicode: false,                  // optional
        externalReferenceId: 'order-1',  // optional, max 36 chars
        priority: MessagePriority::HIGH, // optional
    ),
    idempotencyKey: 'order-1-sms',       // optional, sent as the X-Idempotency-Key header
);

echo $sms->id;
echo $sms->status->value; // QUEUED
echo $sms->totalCost;     // "0.086"
```

Use an idempotency key whenever a request may be retried, so a retry cannot create a duplicate message. Reusing a key
with a different payload is rejected with `IDEMPOTENCY_CONFLICT`.

### Get SMS Status

```php
$sms = $client->messaging()->sms()->get('01JZX8K3M9QABCDEF0123456789');

echo $sms->status->value; // QUEUED, SENT, DELIVERED or FAILED
```

### Send Email

```php
use Paymentic\Sdk\Messaging\Application\DTO\SendEmailRequest;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailAttachmentDisposition;
use Paymentic\Sdk\Messaging\Domain\ValueObject\EmailAttachment;

$email = $client->messaging()->email()->send(
    new SendEmailRequest(
        from: 'invoices@example.com',
        to: ['jan@example.org'],
        subject: 'Invoice FV/2026/11/001',
        fromName: 'Example Ltd.',
        html: '<p>Your invoice is attached.</p>',
        text: 'Your invoice is attached.',
        cc: ['accounting@example.org'],
        bcc: ['archive@example.com'],
        replyTo: ['support@example.com'],
        attachments: [
            // Base64-encodes the raw content for you
            EmailAttachment::fromRawContent('invoice.pdf', file_get_contents('invoice.pdf'), 'application/pdf'),
            EmailAttachment::fromRawContent(
                'logo.png',
                file_get_contents('logo.png'),
                'image/png',
                EmailAttachmentDisposition::INLINE,
                contentId: 'logo',
            ),
        ],
        metadata: ['orderId' => '123'],
        externalReferenceId: 'order-1',
    ),
    idempotencyKey: 'order-1-invoice',
);
```

The SDK does not validate the payload on the client side (for example that `html` or `text` is present). The API answers
with a `422` error instead.

### Get Email

```php
$email = $client->messaging()->email()->get('01jzx8k3m9qabcdef0123456789');

echo $email->status->value;

foreach ($email->recipients as $recipient) {
    echo $recipient->email . ': ' . $recipient->status->value;
}
```

### Email Suppressions

```php
use Paymentic\Sdk\Messaging\Application\DTO\AddEmailSuppressionsRequest;
use Paymentic\Sdk\Messaging\Application\DTO\ListEmailSuppressionsRequest;

// Block addresses. Addresses that were already blocked are returned in "existing".
$result = $client->messaging()->emailSuppressions()->add(
    new AddEmailSuppressionsRequest(['jan@example.org', 'anna@example.org']),
);
$result->added;
$result->existing;

// List with filters and pagination
$list = $client->messaging()->emailSuppressions()->list(
    new ListEmailSuppressionsRequest(
        filterReason: 'BOUNCE,COMPLAINT',     // comma-separated for several values
        filterCreatedAtFrom: '2026-11-01',
        pageNumber: 1,
        pageSize: 50,                          // 1..100
    ),
);

foreach ($list->data as $suppression) {
    echo $suppression->email . ' (' . $suppression->reason->value . ')';
}

echo $list->meta->currentPage . '/' . $list->meta->lastPage; // pagination meta
echo $list->meta->total;
echo $list->links?->next;

// Unblock an address
$removed = $client->messaging()->emailSuppressions()->delete($suppression->id);
```

### Error Handling

Every API error is thrown as a subclass of `Paymentic\Sdk\Shared\Exception\PaymenticException`:

| HTTP status | Exception                  |
|-------------|----------------------------|
| 400         | `BadRequestException`      |
| 401         | `UnauthorizedException`    |
| 402         | `PaymentRequiredException` |
| 403         | `ForbiddenException`       |
| 404         | `NotFoundException`        |
| 409         | `ConflictException`        |
| 413         | `PayloadTooLargeException` |
| 422         | `ValidationException`      |
| other       | `PaymenticException`       |

```php
use Paymentic\Sdk\Shared\Enum\ApiErrorCode;
use Paymentic\Sdk\Shared\Exception\PaymentRequiredException;

try {
    $client->messaging()->sms()->send($request);
} catch (PaymentRequiredException $e) {
    $error = $e->getFirstError();

    if (ApiErrorCode::INSUFFICIENT_FUNDS === $error?->code) {
        // top up the wallet
    }
}
```

Use `getFirstError()->code` (an `ApiErrorCode`, or `rawCode` for codes the SDK does not know yet) to tell errors apart,
for example `IDEMPOTENCY_CONFLICT` and `EMAIL_SUPPRESSION_NOT_REMOVABLE` both arrive as `ConflictException`. Extra
context sent by the API is available in `getFirstError()->details`, for example the list of blocked addresses for
`EMAIL_RECIPIENT_SUPPRESSED`.

## Webhook Handling

Use `PaymentWebhookHandlerFactory` to process incoming webhooks securely:

```php
use Paymentic\Sdk\Payment\Webhook\PaymentWebhookHandlerFactory;

$handler = PaymentWebhookHandlerFactory::create('your-webhook-signature-key');

// Get headers and body from your framework (e.g., Laravel, Symfony)
$headers = new WebhookHeaders(
    event: $request->header('X-Paymentic-Event'),
    notificationId: $request->header('X-Paymentic-Notification-Id'),
    time: $request->header('X-Paymentic-Time'),
    signature: $request->header('X-Paymentic-Signature'),
    userAgent: $request->header('User-Agent'),
);
$rawBody = $request->getContent();

try {
    $webhook = $handler->handle($headers, $rawBody);
    
    // Access webhook data
    $event = $webhook->headers->event;
    $payload = $webhook->payload;
    
    // Handle based on event type
    match ($event) {
        \Paymentic\Sdk\Shared\Webhook\WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED => 
            handleTransactionStatusChanged($payload),
        \Paymentic\Sdk\Shared\Webhook\WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED => 
            handleRefundStatusChanged($payload),
        default => null,
    };
} catch (\Paymentic\Sdk\Shared\Webhook\Exception\InvalidWebhookSignatureException $e) {
    // Invalid signature - reject the request
    return response('Unauthorized', 401);
}
```

## Framework Integration

### Laravel

The SDK auto-registers via package discovery.

#### Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=paymentic-config
```

Add to your `.env`:

```env
PAYMENTIC_API_KEY=your-api-key
PAYMENTIC_SANDBOX=true
PAYMENTIC_WEBHOOK_SECRET=your-webhook-secret
PAYMENTIC_SMS_SENDER=Shop
```

#### Notification channels

The package registers a Laravel notification channel, `paymentic-sms`. It requires `illuminate/notifications` (included
in `laravel/framework`). Apps without it are unaffected. For email, see [Email (mail transport)](#email-mail-transport).

The default sender comes from `config/paymentic.php` (`paymentic.messaging.sms.sender`), filled from
`PAYMENTIC_SMS_SENDER`.

```php
use Illuminate\Notifications\Notification;
use Paymentic\Sdk\Integration\Laravel\Notifications\Messages\PaymenticSmsMessage;

final class InvoicePaid extends Notification
{
    public function via(object $notifiable): array
    {
        // or class-based: [PaymenticSmsChannel::class]
        return ['paymentic-sms'];
    }

    public function toPaymenticSms(object $notifiable): PaymenticSmsMessage
    {
        return PaymenticSmsMessage::create('Your invoice has been paid.')
            ->from('Shop')                      // optional, overrides the configured sender
            ->idempotencyKey("sms-{$this->id}");
    }
}
```

`toPaymenticSms()` may also return a plain string.

Tell Laravel where to send the notification by adding a routing method to your notifiable model:

```php
public function routeNotificationForPaymenticSms(Notification $notification): ?string
{
    return $this->phone;            // E.164, e.g. +48123123123
}
```

You can also use on-demand notifications:

```php
Notification::route('paymentic-sms', '+48123123123')
    ->notify(new InvoicePaid());
```

A recipient set on the message (`->to(...)`) takes precedence over routing. If no route is found, the channel silently
skips sending.

For queued notifications, set an idempotency key so retries do not send duplicates. A deterministic key derived from the
notification id (`"sms-{$this->id}"`) is recommended; it is sent as the `X-Idempotency-Key` header.

SDK errors (`PaymenticException` subclasses, for example `PaymentRequiredException`) propagate unchanged, so queued jobs
fail and retry as usual.

Test with Laravel's notification fake, no HTTP calls are made:

```php
Notification::fake();

$user->notify(new InvoicePaid());

Notification::assertSentTo($user, InvoicePaid::class);
```

#### Email (mail transport)

Email goes through Laravel's regular mail layer: the SDK registers a Symfony Mailer transport named `paymentic`
(requires `symfony/mailer`, included in `laravel/framework`). Mailables, `Mail::to()`, queued mail and notification
`mail` channels keep working unchanged.

Add a mailer to `config/mail.php`:

```php
'mailers' => [
    'paymentic' => [
        'transport' => 'paymentic',
    ],
],
```

Select it in `.env` (the API key and the sandbox flag come from `config/paymentic.php`):

```env
MAIL_MAILER=paymentic
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Example Shop"
```

The sender is taken from `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME` (or the Mailable's `from`). Recipients, cc, bcc,
reply-to, subject, html/text bodies, custom headers and attachments (including inline images) are mapped to the
Paymentic Email API.

Paymentic-specific options are passed as `X-Paymentic-*` headers and are removed from the payload. The header names are
available as constants on `Paymentic\Sdk\Integration\Mailer\PaymenticMailerHeaders`:

| Header                              | Meaning                                        |
|-------------------------------------|------------------------------------------------|
| `X-Paymentic-Idempotency-Key`       | Sent as the `X-Idempotency-Key` request header |
| `X-Paymentic-Priority`              | `LOW`, `MEDIUM` or `HIGH`                      |
| `X-Paymentic-External-Reference-Id` | Your own reference id                          |
| `X-Paymentic-Metadata-<Key>`        | Adds `metadata[<Key>]`                         |

Notes:

- Laravel's `->metadata($key, $value)` is mapped to `metadata[<key>]`. `->tag()` is ignored, because the Paymentic API
  has no tags.
- The envelope is authoritative for recipients (a `framework.mailer.envelope.recipients` override is respected),
  addresses are de-duplicated case-insensitively (To > Cc > Bcc) and at least one `To` recipient is required (Bcc-only
  messages are rejected with `Symfony\Component\Mailer\Exception\InvalidArgumentException`).
- Inline images are matched by `cid:`: the attachment content id is the part's content id or, if none is set, its file
  name (`->embed($img, 'logo')` is referenced as `cid:logo`).
- Duplicate custom headers are joined with `, `; `Return-Path` is never forwarded.
- An invalid `X-Paymentic-Priority` or a message without `To` recipients throws `InvalidArgumentException`, which is not
  a `TransportExceptionInterface`, so `failover` / `roundrobin` do not retry it. The `X-Paymentic-*` headers stay on the
  original message, so a failover SMTP transport would deliver them as regular headers.

```php
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Paymentic\Sdk\Integration\Mailer\PaymenticMailerHeaders;

final class InvoiceMail extends Mailable
{
    public function __construct(private int $invoiceId)
    {
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            PaymenticMailerHeaders::IDEMPOTENCY_KEY => "invoice-{$this->invoiceId}",
            PaymenticMailerHeaders::PRIORITY => 'HIGH',
            PaymenticMailerHeaders::EXTERNAL_REFERENCE_ID => (string) $this->invoiceId,
            PaymenticMailerHeaders::METADATA_PREFIX . 'InvoiceId' => (string) $this->invoiceId,
        ]);
    }

    // envelope(), content(), attachments() as usual
}
```

API errors are thrown as `Symfony\Component\Mailer\Exception\TransportException` with the original Paymentic exception
as `getPrevious()`, so the `failover` / `roundrobin` mailers fall through to the next transport.

Test with Laravel's mail fake, no HTTP calls are made:

```php
Mail::fake();

Mail::to($user)->send(new InvoiceMail(1));

Mail::assertSent(InvoiceMail::class);
```

#### Using Dependency Injection (recommended)

```php
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionRequest;

class PaymentController extends Controller
{
    public function __construct(
        private PaymenticClient $paymentic,
    ) {
    }

    public function createPayment()
    {
        $request = new CreateTransactionRequest(
            amount: '100.00',
            title: 'Order #12345',
        );

        $response = $this->paymentic->payment()->transactions()->create(
            pointId: 'your-point-id',
            request: $request,
        );

        return redirect($response->paymentUrl);
    }
}
```

#### Using Container

```php
use Paymentic\Sdk\PaymenticClient;

// Via container
$client = app(PaymenticClient::class);

// Or using alias
$client = app('paymentic');

// Then use normally
$transaction = $client->payment()->transactions()->get(
    pointId: 'your-point-id',
    transactionId: 'transaction-uuid',
);
```

#### Manual Instantiation (without container)

```php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\Environment;

$httpFactory = new HttpFactory();

$client = new PaymenticClient(
    apiKey: 'your-api-key',
    httpClient: new Client(),
    requestFactory: $httpFactory,
    streamFactory: $httpFactory,
    environment: Environment::SANDBOX,
);

$channels = $client->payment()->points()->getChannels('your-point-id');
```

#### Webhook Controller Example

```php
use Illuminate\Http\Request;
use Paymentic\Sdk\Shared\Webhook\WebhookHandler;
use Paymentic\Sdk\Shared\Webhook\WebhookEvent;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookHandler $webhookHandler,
    ) {
    }

    public function handle(Request $request)
    {
        $webhook = $this->webhookHandler->handle(
            headers: $request->headers->all(),
            rawBody: $request->getContent(),
        );

        match ($webhook->headers->event) {
            WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED => 
                $this->handleTransactionStatusChanged($webhook->payload),
            default => null,
        };

        return response()->statusText('OK');
    }
}
```

### Symfony

Register the bundle in `config/bundles.php`:

```php
Paymentic\Sdk\Integration\Symfony\PaymenticBundle::class => ['all' => true],
```

#### Notifier (SMS)

The SDK ships a [Symfony Notifier](https://symfony.com/doc/current/notifier.html) texter transport for the Paymentic
Messaging API. The bundle registers the transport factory automatically when `symfony/notifier` is installed.

Requirements:

```bash
composer require symfony/notifier symfony/http-client
```

(`symfony/http-client` is required by Notifier's `AbstractTransport`, as for every Notifier bridge.)

Add the DSN to `.env` (URL-encode the API key):

```dotenv
PAYMENTIC_NOTIFIER_DSN=paymentic://API_KEY@default?from=Shop&sandbox=1
```

Register the transport in `config/packages/notifier.yaml`:

```yaml
framework:
  notifier:
    texter_transports:
      paymentic: '%env(PAYMENTIC_NOTIFIER_DSN)%'
```

Send an SMS:

```php
use Paymentic\Sdk\Integration\Symfony\Notifier\PaymenticOptions;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;

final class OtpSender
{
    public function __construct(private TexterInterface $texter)
    {
    }

    public function send(string $phone, string $code): void
    {
        $options = (new PaymenticOptions())
            ->priority(MessagePriority::HIGH)
            ->externalReferenceId('otp-123')
            ->idempotencyKey('otp-123');

        $sent = $this->texter->send(new SmsMessage($phone, "Your code is {$code}.", '', $options));

        $messageId = $sent?->getMessageId(); // Paymentic SMS message id
    }
}
```

The third `SmsMessage` argument is the sender and overrides the DSN `from` option. `PaymenticOptions` supports
`priority()`, `externalReferenceId()`, `unicode()` and `idempotencyKey()`.

DSN options:

| Part             | Description                                                                                                                                                                 |
|------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `API_KEY@`       | Your Paymentic API key (required, URL-encoded)                                                                                                                              |
| `default` (host) | Use the official host. Any other host overrides the API URL, e.g. `paymentic://API_KEY@custom.host:8443` uses `https://custom.host:8443/v1_2` (a DSN path replaces `/v1_2`) |
| `from`           | Default SMS sender (max 11 characters)                                                                                                                                      |
| `sandbox`        | `1`/`true` to use the sandbox environment (`api.sandbox.paymentic.com`)                                                                                                     |

API errors are thrown as `Paymentic\Sdk\Integration\Symfony\Notifier\PaymenticTransportException`, which implements
`TransportExceptionInterface` (so `failover()`/`roundrobin()` fall through to the next transport); the original
Paymentic exception is available via `getPrevious()`.

#### Mailer (email)

The same transport is available for [Symfony Mailer](https://symfony.com/doc/current/mailer.html). The bundle registers
the transport factory automatically when `symfony/mailer` is installed.

```bash
composer require symfony/mailer
```

Add the DSN to `.env` (URL-encode the API key):

```dotenv
MAILER_DSN=paymentic+api://API_KEY@default?sandbox=1
```

(`paymentic://` works as an alias.) Then send mail as usual with `MailerInterface`. The sender, recipients, cc, bcc,
reply-to, bodies, headers and attachments are mapped to the Paymentic Email API, and the Paymentic message id is set on
the `SentMessage` (`MailerInterface::send()` returns `void`, so read it from
`SentMessageEvent::getMessage()->getMessageId()` or call the transport's `send()` directly).

Paymentic-specific options use the same `X-Paymentic-*` headers as in Laravel (see the table above, constants in
`PaymenticMailerHeaders`):

```php
use Paymentic\Sdk\Integration\Mailer\PaymenticMailerHeaders;
use Symfony\Component\Mime\Email;

$email = (new Email())
    ->from('noreply@example.com')
    ->to('jan@example.org')
    ->subject('Invoice paid')
    ->html('<p>Thank you!</p>');

$headers = $email->getHeaders();
$headers->addTextHeader(PaymenticMailerHeaders::IDEMPOTENCY_KEY, 'invoice-42');
$headers->addTextHeader(PaymenticMailerHeaders::PRIORITY, 'HIGH');
$headers->addTextHeader(PaymenticMailerHeaders::METADATA_PREFIX . 'InvoiceId', '42');

$mailer->send($email);
```

DSN options: `sandbox` (`1`/`true` for the sandbox environment) and a custom host/port (e.g.
`paymentic+api://API_KEY@custom.host:8443` uses `https://custom.host:8443/v1_2`); `default` uses the official host.

API errors are thrown as `Symfony\Component\Mailer\Exception\TransportException` with the original Paymentic exception
as `getPrevious()`, so `failover()` / `roundrobin()` fall through to the next transport.

## License

MIT License. See [LICENSE](LICENSE) for details.