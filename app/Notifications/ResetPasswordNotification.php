<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        dd($notifiable);
        return (new MailMessage)
                    ->subject('Password Reset Request')
                    ->greeting('Hello, '.$notifiable->name)
                    ->line('You are receiving this email because we received a password reset request for your account. Click the button below to reset your password:')
                    ->action('Reset Password', url('password/reset', $this->token).'?email='.urlencode($notifiable->email))
                    ->line('If you did not request a password reset, no further action is required.')
                    ->line('Thank you for using '. config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
