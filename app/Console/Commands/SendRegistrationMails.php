<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Registration;
use App\Mail\RegistrationSuccessMail;
use Illuminate\Support\Facades\Mail;

class SendRegistrationMails extends Command
{
    // Command name (used in scheduler/cron)
    protected $signature = 'mails:send-registrations';

    // Description (for php artisan list)
    protected $description = 'Send registration success mails with PDF attachment';

    public function handle()
    {
        // Get registrations that still need an email
        $registrations = Registration::where('mail_sent', false)->get();

        foreach ($registrations as $registration) {
            Mail::to($registration->email)->queue(new RegistrationSuccessMail($registration));

            // Prevent duplicate sending
            $registration->update(['mail_sent' => true]);

            $this->info("✅ Mail sent to: " . $registration->email);
        }

        return 0;
    }
}
