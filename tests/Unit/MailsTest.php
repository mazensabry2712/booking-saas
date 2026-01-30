<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\AppointmentBookedMail;
use App\Mail\AppointmentReminderMail;
use App\Mail\QueueUpdateMail;

class MailsTest extends TestCase
{
    /**
     * Test AppointmentBookedMail class exists
     */
    public function test_appointment_booked_mail_exists(): void
    {
        $this->assertTrue(class_exists(AppointmentBookedMail::class));
    }

    /**
     * Test AppointmentReminderMail class exists
     */
    public function test_appointment_reminder_mail_exists(): void
    {
        $this->assertTrue(class_exists(AppointmentReminderMail::class));
    }

    /**
     * Test QueueUpdateMail class exists
     */
    public function test_queue_update_mail_exists(): void
    {
        $this->assertTrue(class_exists(QueueUpdateMail::class));
    }

    /**
     * Test mails extend Mailable
     */
    public function test_mails_extend_mailable(): void
    {
        $appointmentBooked = new \ReflectionClass(AppointmentBookedMail::class);
        $appointmentReminder = new \ReflectionClass(AppointmentReminderMail::class);
        $queueUpdate = new \ReflectionClass(QueueUpdateMail::class);

        $this->assertTrue(
            $appointmentBooked->isSubclassOf(\Illuminate\Mail\Mailable::class),
            'AppointmentBookedMail should extend Mailable'
        );

        $this->assertTrue(
            $appointmentReminder->isSubclassOf(\Illuminate\Mail\Mailable::class),
            'AppointmentReminderMail should extend Mailable'
        );

        $this->assertTrue(
            $queueUpdate->isSubclassOf(\Illuminate\Mail\Mailable::class),
            'QueueUpdateMail should extend Mailable'
        );
    }
}
