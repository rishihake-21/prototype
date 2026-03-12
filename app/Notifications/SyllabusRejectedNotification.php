<?php

namespace App\Notifications;

use App\Models\Syllabus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SyllabusRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Syllabus $syllabus,
        public string $reason
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Syllabus Revision Required')
            ->line('Your syllabus requires revisions before approval.')
            ->line('Course: ' . $this->syllabus->title)
            ->line('Course Code: ' . $this->syllabus->course_code)
            ->line('Feedback: ' . $this->reason)
            ->action('Edit Syllabus', url('/syllabi/' . $this->syllabus->id . '/edit'))
            ->line('Please address the feedback and resubmit.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'syllabus_id' => $this->syllabus->id,
            'title' => $this->syllabus->title,
            'course_code' => $this->syllabus->course_code,
            'message' => 'Syllabus requires revisions: ' . substr($this->reason, 0, 100) . '...',
            'action_url' => '/syllabi/' . $this->syllabus->id . '/edit',
        ];
    }
}
