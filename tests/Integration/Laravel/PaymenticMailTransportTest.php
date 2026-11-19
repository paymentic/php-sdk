<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Laravel;

use Illuminate\Http\Client\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Orchestra\Testbench\TestCase;
use Paymentic\Sdk\Integration\Laravel\PaymenticServiceProvider;
use Paymentic\Sdk\Integration\Mailer\PaymenticMailerHeaders;
use PHPUnit\Framework\Attributes\Test;

final class PaymenticMailTransportTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [PaymenticServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['view']->addNamespace('paymentic-fixtures', __DIR__ . '/Fixtures');
        $app['config']->set('paymentic.api_key', 'test-api-key');
        $app['config']->set('paymentic.sandbox', true);
        $app['config']->set('mail.mailers.paymentic', ['transport' => 'paymentic']);
        $app['config']->set('mail.default', 'paymentic');
        $app['config']->set('mail.from', ['address' => 'shop@example.org', 'name' => 'My Shop']);
    }

    #[Test]
    public function sendsMailableThroughPaymenticApi(): void
    {
        $this->fakeApi();

        Mail::mailer('paymentic')->to('jan@example.org')->send(new TestMailable());

        Http::assertSentCount(1);
        Http::assertSent(function (Request $request): bool {
            $data = $request->data();

            return 'POST' === $request->method()
                && str_ends_with($request->url(), '/messaging/email/messages')
                && 'shop@example.org' === $data['from']
                && 'My Shop' === $data['fromName']
                && ['jan@example.org'] === $data['to']
                && 'Welcome' === $data['subject']
                && '<p>Hi</p>' === $data['html'];
        });
    }

    #[Test]
    public function usesMailFromConfiguration(): void
    {
        $this->app['config']->set('mail.from', ['address' => 'other@example.org', 'name' => null]);
        $this->fakeApi();

        Mail::mailer('paymentic')->to('jan@example.org')->send(new TestMailable());

        Http::assertSent(static fn (Request $request): bool => 'other@example.org' === $request->data()['from']
            && ! array_key_exists('fromName', $request->data()));
    }

    #[Test]
    public function mapsBccAndPaymenticHeaders(): void
    {
        $this->fakeApi();

        Mail::mailer('paymentic')
            ->to('jan@example.org')
            ->bcc('hidden@example.org')
            ->send(new TestMailable([
                PaymenticMailerHeaders::IDEMPOTENCY_KEY => 'key-1',
                PaymenticMailerHeaders::PRIORITY => 'LOW',
                PaymenticMailerHeaders::METADATA_PREFIX . 'OrderId' => '5',
            ]));

        Http::assertSent(static function (Request $request): bool {
            $data = $request->data();

            return ['hidden@example.org'] === $data['bcc']
                && 'LOW' === $data['priority']
                && ['OrderId' => '5'] === $data['metadata']
                && ! isset($data['headers'][PaymenticMailerHeaders::PRIORITY])
                && 'key-1' === $request->header('X-Idempotency-Key')[0];
        });
    }

    #[Test]
    public function embedDataContentIdMatchesCidInHtml(): void
    {
        $this->fakeApi();

        Mail::mailer('paymentic')->to('jan@example.org')->send(new EmbedMailable());

        Http::assertSent(static function (Request $request): bool {
            $data = $request->data();
            $cid = $data['attachments'][0]['contentId'] ?? null;

            return 'INLINE' === $data['attachments'][0]['disposition']
                && is_string($cid)
                && str_contains($data['html'], 'cid:' . $cid . '"');
        });
    }

    #[Test]
    public function mapsTagAndMetadataFromMailable(): void
    {
        $this->fakeApi();

        Mail::mailer('paymentic')->to('jan@example.org')->send(new TagMailable());

        Http::assertSent(static function (Request $request): bool {
            $data = $request->data();

            return ['order' => '42'] === $data['metadata']
                && ! isset($data['headers']['X-Tag'])
                && ! isset($data['headers']['X-Metadata-order']);
        });
    }

    #[Test]
    public function mailFakeStillInterceptsMail(): void
    {
        Mail::fake();

        Mail::to('jan@example.org')->send(new TestMailable());

        Mail::assertSent(TestMailable::class);
    }

    private function fakeApi(): void
    {
        Http::fake([
            '*/messaging/email/messages' => Http::response([
                'data' => [
                    'id' => '01JZX8K3M9QABCDEF0123456789',
                    'status' => 'QUEUED',
                    'from' => 'shop@example.org',
                    'subject' => 'Welcome',
                    'recipients' => [],
                    'recipientsCount' => 1,
                    'freeRecipientsCount' => 1,
                    'billableRecipientsCount' => 0,
                    'attachmentsCount' => 0,
                    'attachmentsBillableMb' => 0,
                    'recipientsCost' => '0.000',
                    'attachmentsCost' => '0.000',
                    'netCost' => '0.000',
                    'vatRate' => '23.00',
                    'totalCost' => '0.000',
                    'source' => 'API',
                    'priority' => 'MEDIUM',
                ],
            ], 202),
        ]);
    }
}

final class TestMailable extends Mailable
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(private readonly array $headers = [])
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome');
    }

    public function content(): Content
    {
        return new Content(htmlString: '<p>Hi</p>');
    }

    public function headers(): Headers
    {
        return new Headers(text: $this->headers);
    }
}

final class EmbedMailable extends Mailable
{
    public function build(): self
    {
        return $this->subject('Embed')->view('paymentic-fixtures::embed');
    }
}

final class TagMailable extends Mailable
{
    public function build(): self
    {
        return $this->subject('Tag')->html('<p>Hi</p>')->tag('welcome')->tag('second')->metadata('order', '42');
    }
}
