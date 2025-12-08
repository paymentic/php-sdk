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

## License

MIT License. See [LICENSE](LICENSE) for details.