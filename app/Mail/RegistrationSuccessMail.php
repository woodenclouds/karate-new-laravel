<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; 
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;

class RegistrationSuccessMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $registration;

    public function __construct($registration)
    {
        $this->registration = $registration;
    }

    public function build()
{
    $registration = $this->registration;
    $event = $registration->event;

    // Decode submitted data
    $formData = $registration->submitted_data;
    if (is_string($formData)) {
        $formData = json_decode($formData, true);
    }

    // Attempt to get the user's name from form data
    $userName = $registration->name ?? null; // fallback to registration name field
    if (!$userName && is_array($formData)) {
        foreach ($formData as $field) {
            if (stripos($field['label'], 'name') !== false) {
                $userName = $field['value'];
                break;
            }
        }
    }
    $userName = $userName ?? 'Participant';

    // Generate PDF
    $pdf = PDF::loadView('user.pdf_template', [
        'registration' => $registration,
        'event' => $event,
        'formData' => $formData,
    ]);

    // Email content
    $htmlContent = "
    <h2 style='color:#2c3e50;'>Registration Successful</h2>
    <p>Dear {$userName},</p>

    <p>Thank you for completing your registration with <strong>MENTORS SPORTS KARATE DO</strong>. 
    We’re excited to have you with us!</p>

    <p><strong>Your Registration ID:</strong> {$registration->registration_code}</p>

    <p>For your convenience, we’ve attached your Registration Form (PDF). 
    Please download and keep it for your records.</p>


    <p>We look forward to seeing you and wish you the best in your martial arts journey.</p>

    <br>
    <p>Best regards,<br>
    <strong>Team Mentors Sports Karate Do</strong></p>";


    return $this->subject('🎉 Registration Confirmed – Welcome to Mentors Sports Karate Do')
                ->html($htmlContent)
                ->attachData(
                    $pdf->output(),
                    'Registration_'.$registration->id.'.pdf',
                    ['mime' => 'application/pdf']
                );
}

}
