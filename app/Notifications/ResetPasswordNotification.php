<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\EmailTemplate;
use App\Email;

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
        // Get email template.
        $emailTemplate = EmailTemplate::find(EmailTemplate::RESET_EMAIL_ID);

        if (empty($emailTemplate)) {
            return (new MailMessage)
                    ->subject('Password Reset Request')
                    ->greeting('Hello, '.$notifiable->name)
                    ->line('You are receiving this email because we received a password reset request for your account. Click the button below to reset your password:')
                    ->action('Reset Password', url('password/reset', $this->token).'?email='.urlencode($notifiable->email))
                    ->line('If you did not request a password reset, no further action is required.')
                    ->line('Thank you for using '. config('app.name'));
        }

        $fillableFields = $notifiable->getFillable();

        // Update email body variables.
        $emailBody = $emailTemplate->email_body;
        foreach ($fillableFields as $key => $field) {
            $customField = '{{' . $field . '}}';

            $emailBody   = str_ireplace($customField, $notifiable->$field, $emailBody);
        }

        $actionUrl  = url('password/reset', $this->token).'?email='.urlencode($notifiable->email);

        $emailBody  = str_ireplace('{{reset_link_url}}', $actionUrl, $emailBody);

        // Update email subject variables.
        $emailSubject = $emailTemplate->email_subject;
        foreach ($fillableFields as $key => $field) {
            $customField = '{{' . $field . '}}';

            $emailSubject   = str_ireplace($customField, $notifiable->$field, $emailSubject);
        }

        $this->storeToDatabase($notifiable, $emailSubject, $emailBody);

        return (new MailMessage)
                ->subject($emailSubject)
                ->view('pages.settings.email-templates.email-templates', compact('emailBody'));
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

    public function storeToDatabase($notifiable, string $emailSubject, string $emailBody)
    {
        Email::store([$notifiable->email], $emailSubject, $emailBody);
    }
}
