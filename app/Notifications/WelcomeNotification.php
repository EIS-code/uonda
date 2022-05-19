<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\EmailTemplate;
use App\Email;

class WelcomeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $emailTemplate = EmailTemplate::find(EmailTemplate::WELCOME_EMAIL_ID);

        $fillableFields = $notifiable->getFillable();

        // Update email body variables.
        $emailBody = $emailTemplate->email_body;
        foreach ($fillableFields as $key => $field) {
            $customField = '{{' . $field . '}}';

            $emailBody   = str_ireplace($customField, $notifiable->$field, $emailBody);
        }

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

    public function toDatabase($notifiable)
    {}

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
