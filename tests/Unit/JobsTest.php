<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Jobs\SendAppointmentNotification;
use App\Jobs\SendQueueNotification;
use Illuminate\Support\Facades\Queue;

class JobsTest extends TestCase
{
    /**
     * Test SendAppointmentNotification job can be instantiated
     */
    public function test_send_appointment_notification_job_exists(): void
    {
        $this->assertTrue(class_exists(SendAppointmentNotification::class));
    }

    /**
     * Test SendQueueNotification job can be instantiated
     */
    public function test_send_queue_notification_job_exists(): void
    {
        $this->assertTrue(class_exists(SendQueueNotification::class));
    }

    /**
     * Test jobs are queueable
     */
    public function test_jobs_implement_shouldqueue(): void
    {
        $appointmentJob = new \ReflectionClass(SendAppointmentNotification::class);
        $queueJob = new \ReflectionClass(SendQueueNotification::class);

        $this->assertTrue(
            $appointmentJob->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class),
            'SendAppointmentNotification should implement ShouldQueue'
        );

        $this->assertTrue(
            $queueJob->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class),
            'SendQueueNotification should implement ShouldQueue'
        );
    }
}
