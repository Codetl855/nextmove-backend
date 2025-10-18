<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;

final class VerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    public $connection = 'database';

    /**
     * The queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'default';

    /**
     * The delay (in seconds) before the job should be processed.
     *
     * @var int|null
     */
    public $delay = null;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying a failed job.
     *
     * @var int
     */
    public $backoff = 10;

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $backendSignedUrl = $this->verificationUrl($notifiable);

        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        $verificationUrl = sprintf(
            '%s/verify-email?redirect=%s',
            $frontendUrl,
            urlencode($backendSignedUrl)
        );

        return (new MailMessage)
            ->subject(__('Verify Email Address'))
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $verificationUrl)
            ->line(__('If you did not create an account, no further action is required.'));
    }
}
