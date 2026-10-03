<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestMail extends Command
{
    // Command signature
    protected $signature = 'mail:test';

    protected $description = 'Send a test mail via cron';

    public function handle()
    {
        Mail::raw('This is a test mail triggered by cron job.', function ($message) {
            $message->to('josmeejose.woodenclouds@gmail.com')
                    ->subject('Cron Job Test Mail');
        });

        $this->info('✅ Test mail sent successfully.');
        return 0;
    }
}
