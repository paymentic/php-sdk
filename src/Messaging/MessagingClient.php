<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging;

use Paymentic\Sdk\Messaging\Application\Contract\EmailServiceContract;
use Paymentic\Sdk\Messaging\Application\Contract\EmailSuppressionServiceContract;
use Paymentic\Sdk\Messaging\Application\Contract\SmsServiceContract;
use Paymentic\Sdk\Messaging\Application\Contract\SystemServiceContract;
use Paymentic\Sdk\Messaging\Application\Service\EmailService;
use Paymentic\Sdk\Messaging\Application\Service\EmailSuppressionService;
use Paymentic\Sdk\Messaging\Application\Service\SmsService;
use Paymentic\Sdk\Messaging\Application\Service\SystemService;
use Paymentic\Sdk\Shared\Http\HttpClient;

final class MessagingClient
{
    private SmsServiceContract $smsService;
    private EmailServiceContract $emailService;
    private EmailSuppressionServiceContract $emailSuppressionService;
    private SystemServiceContract $systemService;

    public function __construct(
        HttpClient $httpClient,
    ) {
        $this->smsService = new SmsService($httpClient);
        $this->emailService = new EmailService($httpClient);
        $this->emailSuppressionService = new EmailSuppressionService($httpClient);
        $this->systemService = new SystemService($httpClient);
    }

    public function sms(): SmsServiceContract
    {
        return $this->smsService;
    }

    public function email(): EmailServiceContract
    {
        return $this->emailService;
    }

    public function emailSuppressions(): EmailSuppressionServiceContract
    {
        return $this->emailSuppressionService;
    }

    public function system(): SystemServiceContract
    {
        return $this->systemService;
    }
}
