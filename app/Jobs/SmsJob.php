<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SmsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $tires = 5;
    public $user;
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //twilio code to send sms

        try {
            // Simulate sending SMS
            Log::info("Sending SMS to user: " . $this->user->name);
            // If the SMS sending fails, throw an exception to trigger a retry
            // throw new \Exception("Failed to send SMS");
        } catch (\Exception $e) {
            Log::error("Error sending SMS: " . $e->getMessage());
            // The job will automatically be retried based on the $tires property
            throw $e;
        }
    }
}
