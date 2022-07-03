<?php

namespace Riverskies\LaravelNewsletterSubscription\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendNewsletterNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $subscriber;
    public string $subject;
    public string $greeting;
    public string $body;

    /**
     * @param $subscriber
     * @param $subject
     * @param $greeting
     * @param $body
     */
    public function __construct($subscriber, $subject, $greeting, $body)
    {
        $this->subscriber = $subscriber;
        $this->subject = $subject;
        $this->greeting = $greeting;
        $this->body = $body;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting($this->greeting)
            ->markdown($this->body)
            ->action('Unsubscribe', url($this->subscriber->unsubscribeUrl));
    }
}
