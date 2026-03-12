<?php

namespace App\Notifications;

use App\Models\Syllabus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SyllabusSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject('New Syllabus Submitted for Review')
            ->line('A new syllabus has been submitted for your review.')
            ->line('Course: ' . $this->syllabus->title)
            ->line('Course Code: ' . $this->syllabus->course_code)
            ->line('Instructor: ' . $this->syllabus->instructor_name)
            ->action('Review Syllabus', url('/syllabi/' . $this->syllabus->id))
            ->line('Please review at your earliest convenience.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'syllabus_id' => $this->syllabus->id,
            'title' => $this->syllabus->title,
            'course_code' => $this->syllabus->course_code,
            'message' => 'New syllabus submitted for review',
            'action_url' => '/syllabi/' . $this->syllabus->id,
        ];
    }
}
