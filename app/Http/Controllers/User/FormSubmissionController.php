<?php

namespace App\Http\Controllers\User;
use App\Mail\RegistrationSuccessMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\RegistrationExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Razorpay\Api\Api;
use Illuminate\Support\Str;




class FormSubmissionController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:event,id',
        ]);

        $event = Event::with('category.form.formFields')->findOrFail($request->event_id);
        $form = $event->category->form;

        if (!$form) {
            return redirect()->back()->with('error', 'No form configured for this event\'s category.');
        }

        $fields = $form->formFields->sortBy('order') ?? collect();
        $submittedData = [];

        $participationType = null;
        $beltName = null;

        foreach ($fields as $field) {
            $fieldKey = "fields.{$field->id}";
            $value = $request->input($fieldKey);

            if ($field->type === 'file' && $request->hasFile($fieldKey)) {
                $storedPath = $request->file($fieldKey)->store('uploads', 'public');
                $value = Storage::url($storedPath);
            }

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            // Detect participation type field
            if (strtolower($field->label) === 'participation type') {
                $participationType = strtolower($value);
            }

            // Detect belt field
            // detect belt field
                if (strtolower($field->type) === 'belt') {
                    $beltID = $value;  // storing belt name instead of id
                }

            $submittedData[] = [
                'label' => $field->label,
                'value' => $value,
                'type'  => $field->type,
            ];
        }

        // --------------------------
        // Calculate total amount
        // -------------------------- // base registration fee
        $totalAmount = 0;
        $additionalCharge = $event->additional_fee ?? 0;
        $beltFee = 0;

        if (strtolower($event->category->name) === 'competition') {
            $totalAmount = $event->fee;
            if ($participationType === 'team') {
                $totalAmount += $additionalCharge;
            }
        }

        elseif (Str::contains(Str::lower($event->category->name), 'kyu')) {
            if ($beltID) {
                // Check event-specific override first
                $override = DB::table('event_belt_fees')
                    ->where('event_id', $event->id)
                    ->where('belt_id', $beltID)
                    ->value('fee');

                if ($override !== null) {
                    $beltFee = (float) $override;
                    $totalAmount += $beltFee;
                } else {
                    // Fallback to default belt fee
                    $belt = DB::table('tbl_belt')->where('id', $beltID)->first();
                    if ($belt) {
                        $beltFee = (float) $belt->fees;
                        $totalAmount += $beltFee;
                    }
                }
            }
        }
        else{
            $totalAmount += $event->fee;
        }
         
        // --------------------------
        // Save registration
        // --------------------------
        $registrationId = DB::table('tbl_registration')->insertGetId([
            'event_id'       => $event->id,
            'submitted_data' => json_encode($submittedData),
            'registration_code'=>'TEMP',
            'status'         => 'pending',
            'amount'         => $totalAmount,
            'entered_by'     => 'user',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        // Generate code from ID
            $prefix = 'MENTORS';
            $registrationCode = $prefix . str_pad($registrationId, 4, '0', STR_PAD_LEFT);

            // Update the same row with code
            DB::table('tbl_registration')->where('id', $registrationId)->update([
                'registration_code' => $registrationCode,
            ]);

        $registration = Registration::findOrFail($registrationId);

        // ✅ Create Razorpay Order
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $order = $api->order->create([
            'receipt'         => 'reg_'.$registration->id,
            'amount'          => $totalAmount * 100, // amount in paisa
            'currency'        => 'INR',
            'payment_capture' => 1,
        ]);

        // ✅ Store order_id in database
        $registration->update([
            'razorpay_order_id' => $order['id']
        ]);

        return view('user.razorpay_checkout', [
            'registration'      => $registration,
            'event'             => $event,
            'submittedData'     => $submittedData,
            'totalAmount'       => $totalAmount,
            'additionalCharge'  => $additionalCharge,
            'beltFee'           => $beltFee,
            'beltName'          => $beltName,

            // ✅ Pass these to blade
            'order_id'          => $order['id'],
            'key'               => config('services.razorpay.key'),
            'amount'            => $totalAmount * 100,
        ]);

    }
    public function Razorepaysuccess(Request $request)
    {
        // Validate required fields
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
            'registration_id' => 'required|exists:tbl_registration,id',
        ]);

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $attributes = [
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature
        ];

        try {
            // ✅ Verify payment signature
            $api->utility->verifyPaymentSignature($attributes);
            
            $registration = Registration::findOrFail($request->registration_id);
            
            // ✅ Verify order_id matches (security check)
            if ($registration->razorpay_order_id !== $request->razorpay_order_id) {
                \Log::error('Order ID mismatch', [
                    'registration_id' => $registration->id,
                    'stored_order_id' => $registration->razorpay_order_id,
                    'received_order_id' => $request->razorpay_order_id
                ]);
                return redirect()->route('user.form.show', ['event' => $registration->event_id])
                    ->with('error', 'Payment verification failed: Order ID mismatch.');
            }

            // ✅ Update registration status, amount, and payment details
            $updateData = [
                'status' => 'paid',
                'payment_id' => $request->razorpay_payment_id,
                'razorpay_order_id' => $request->razorpay_order_id,
            ];
            try {
                $payment = $api->payment->fetch($request->razorpay_payment_id);
                if ($payment && isset($payment->amount)) {
                    $updateData['amount'] = $payment->amount / 100;
                }
            } catch (\Exception $e) {
                // If amount is already set from form submission, keep it
            }

            $registration->update($updateData);

            // Extract user email from submitted_data
            $formData = $registration->submitted_data;
            $userEmail = null;
            foreach ($formData as $field) {
                if (stripos($field['label'], 'email') !== false) {
                    $userEmail = $field['value'];
                    break;
                }
            }

            // ✅ Send email
            if ($userEmail) {
                Mail::to($userEmail)->queue(new RegistrationSuccessMail($registration));
            }

            // ✅ Store registration_id in session for success page
            Session::put('registration_id', $request->registration_id);

            // ✅ Log successful payment
            \Log::info('Payment successful', [
                'registration_id' => $registration->id,
                'payment_id' => $request->razorpay_payment_id,
                'order_id' => $request->razorpay_order_id
            ]);

            // Redirect to success page
            return redirect()->route('user.registration_success', ['id' => $registration->id]);
        } 
        catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            // Signature verification failed
            \Log::error('Payment signature verification failed', [
                'registration_id' => $request->registration_id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('user.form.show', ['event' => Registration::find($request->registration_id)?->event_id ?? 1])
                ->with('error', 'Payment verification failed. Please contact support if payment was deducted.');
        }
        catch (\Exception $e) {
            // Other errors
            \Log::error('Payment processing error', [
                'registration_id' => $request->registration_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('user.form.show', ['event' => Registration::find($request->registration_id)?->event_id ?? 1])
                ->with('error', 'An error occurred while processing your payment. Please contact support.');
        }
    }
    

    /**
     * Razorpay Webhook Handler
     * Handles payment.captured event from Razorpay
     * This is a safety net in case the frontend handler fails
     */
    public function razorpayWebhook(Request $request)
    {
        $webhookSecret = config('services.razorpay.webhook_secret'); // Add this to your .env
        
        if (!$webhookSecret) {
            \Log::warning('Razorpay webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        $webhookSignature = $request->header('X-Razorpay-Signature');
        $webhookBody = $request->getContent();

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        try {
            // Verify webhook signature
            $api->utility->verifyWebhookSignature($webhookBody, $webhookSignature, $webhookSecret);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            \Log::error('Webhook signature verification failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $payload = json_decode($webhookBody, true);
        $event = $payload['event'] ?? null;
        $payment = $payload['payload']['payment']['entity'] ?? null;

        // Handle payment.captured event
        if ($event === 'payment.captured' && $payment) {
            $paymentId = $payment['id'];
            $orderId = $payment['order_id'] ?? null;

            if (!$orderId) {
                \Log::warning('Webhook: payment.captured event missing order_id', ['payment_id' => $paymentId]);
                return response()->json(['error' => 'Order ID missing'], 400);
            }

            // Find registration by order_id
            $registration = Registration::where('razorpay_order_id', $orderId)->first();

            if (!$registration) {
                \Log::warning('Webhook: Registration not found for order_id', ['order_id' => $orderId, 'payment_id' => $paymentId]);
                return response()->json(['error' => 'Registration not found'], 404);
            }

            // Update registration if not already updated
            if ($registration->status !== 'paid' || $registration->payment_id !== $paymentId) {
                $webhookUpdateData = [
                    'status' => 'paid',
                    'payment_id' => $paymentId,
                ];
                if (isset($payment['amount'])) {
                    $webhookUpdateData['amount'] = $payment['amount'] / 100;
                }
                $registration->update($webhookUpdateData);

                // Send email if not sent already
                $formData = $registration->submitted_data;
                $userEmail = null;
                foreach ($formData as $field) {
                    if (stripos($field['label'], 'email') !== false) {
                        $userEmail = $field['value'];
                        break;
                    }
                }

                if ($userEmail) {
                    Mail::to($userEmail)->queue(new RegistrationSuccessMail($registration));
                }

                \Log::info('Webhook: Payment updated via webhook', [
                    'registration_id' => $registration->id,
                    'payment_id' => $paymentId,
                    'order_id' => $orderId
                ]);
            }

            return response()->json(['status' => 'success'], 200);
        }

        // Log other events for debugging
        \Log::info('Razorpay webhook received', ['event' => $event]);
        return response()->json(['status' => 'received'], 200);
    }

    public function downloadPDF()
    {
        $registrationId = Session::get('registration_id');

        if (!$registrationId) {
            abort(404, 'Registration ID not found in session.');
        }

        $registration = Registration::with('event')->findOrFail($registrationId);
        $event = $registration->event;

        $formData = is_array($registration->submitted_data)
            ? $registration->submitted_data
            : json_decode($registration->submitted_data, true);

        if (!is_array($formData)) {
            abort(500, 'Submitted data could not be processed.');
        }

        return Pdf::loadView('user.pdf_template', [
            'registration' => $registration,
            'event' => $event,
            'formData' => $formData,
        ])->download('karate_participation_form_' . $registrationId . '.pdf');
    }

    public function showAllRegistrations()
    {
        $events = Event::with('registrations')->get();
        return view('admin.registrations', compact('events'));
    }

    public function export($eventId)
    {
        $event = Event::with('registrations')->findOrFail($eventId);
        return Excel::download(new RegistrationExport($event), 'event_' . $eventId . '_registrations.xlsx');
    }
}