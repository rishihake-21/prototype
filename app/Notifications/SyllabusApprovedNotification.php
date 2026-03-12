<?php

namespace App\Notifications;

use App\Models\Syllabus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SyllabusApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Syllabus $syllabus)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Syllabus Approved')
            ->line('Your syllabus has been approved.')
            ->line('Course: ' . $this->syllabus->title)
            ->line('Course Code: ' . $this->syllabus->course_code)
            ->action('View Syllabus', url('/syllabi/' . $this->syllabus->id))
            ->line('You can now download the approved syllabus in PDF or DOCX format.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'syllabus_id' => $this->syllabus->id,
            'title' => $this->syllabus->title,
            'course_code' => $this->syllabus->course_code,
            'message' => 'Your syllabus has been approved',
            'action_url' => '/syllabi/' . $this->syllabus->id,
        ];
    }
}
