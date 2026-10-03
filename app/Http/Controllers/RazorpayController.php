<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\Admin\Event;
use Razorpay\Api\Api;

class RazorpayController extends Controller
{
    public function startRazorpay(Request $request)
    {
        $registration = Registration::findOrFail($request->registration_id);
        $event = Event::findOrFail($registration->event_id);
       
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $order = $api->order->create([
            'receipt'  => 'reg_'.$registration->id,
            'amount'   => $request->amount * 100,
            'currency' => 'INR',
            'payment_capture' => 1,
        ]);

        // Store order_id in database
        $registration->update([
            'razorpay_order_id' => $order['id']
        ]);

        return view('user.razorpay_checkout', [
            'registration' => $registration,
            'event'        => $event,
            'order_id'     => $order['id'],
            'amount'       => $request->amount * 100,
            'key'          => config('services.razorpay.key')
        ]);
    }

    /**
     * Testing page to check payment status by order ID
     */
    public function testPaymentStatus(Request $request)
    {
        $orderId = $request->input('order_id');
        $result = null;
        $error = null;

        if ($orderId) {
            try {
                $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
                
                // Fetch order details from Razorpay
                $order = $api->order->fetch($orderId);
                
                // Get registration from database
                $registration = Registration::where('razorpay_order_id', $orderId)->first();
                
                $result = [
                    'order' => [
                        'id' => $order['id'],
                        'amount' => $order['amount'] / 100, // Convert from paise to rupees
                        'amount_due' => isset($order['amount_due']) ? $order['amount_due'] / 100 : 0,
                        'amount_paid' => isset($order['amount_paid']) ? $order['amount_paid'] / 100 : 0,
                        'currency' => $order['currency'],
                        'status' => $order['status'],
                        'receipt' => $order['receipt'] ?? 'N/A',
                        'created_at' => date('Y-m-d H:i:s', $order['created_at']),
                    ],
                    'payments' => [],
                    'registration' => $registration ? [
                        'id' => $registration->id,
                        'registration_code' => $registration->registration_code,
                        'status' => $registration->status,
                        'payment_id' => $registration->payment_id,
                        'amount' => $registration->amount,
                        'event_id' => $registration->event_id,
                        'event' => $registration->event ? $registration->event->title : null,
                    ] : null,
                ];

                // Fetch payments for this order - try multiple methods
                $allPaymentIds = [];
                
                // Method 1: Get payments from order object
                try {
                    $orderObj = $api->order->fetch($orderId);
                    $paymentsData = $orderObj->payments();
                    
                    if (isset($paymentsData['items']) && is_array($paymentsData['items']) && count($paymentsData['items']) > 0) {
                        foreach ($paymentsData['items'] as $payment) {
                            $allPaymentIds[] = $payment['id'];
                            $result['payments'][] = [
                                'id' => $payment['id'],
                                'amount' => $payment['amount'] / 100,
                                'currency' => $payment['currency'],
                                'status' => $payment['status'],
                                'method' => $payment['method'] ?? 'N/A',
                                'captured' => isset($payment['captured']) && $payment['captured'] ? 'Yes' : 'No',
                                'authorized' => isset($payment['authorized_at']) ? 'Yes' : 'No',
                                'error_code' => $payment['error_code'] ?? null,
                                'error_description' => $payment['error_description'] ?? null,
                                'error_reason' => $payment['error_reason'] ?? null,
                                'created_at' => date('Y-m-d H:i:s', $payment['created_at']),
                                'authorized_at' => isset($payment['authorized_at']) ? date('Y-m-d H:i:s', $payment['authorized_at']) : null,
                                'captured_at' => isset($payment['captured_at']) ? date('Y-m-d H:i:s', $payment['captured_at']) : null,
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // Continue to other methods
                }

                // Method 2: If order has payments array, fetch each payment
                if (isset($order['payments']) && is_array($order['payments'])) {
                    foreach ($order['payments'] as $paymentId) {
                        if (!in_array($paymentId, $allPaymentIds)) {
                            try {
                                $payment = $api->payment->fetch($paymentId);
                                $allPaymentIds[] = $paymentId;
                                $result['payments'][] = [
                                    'id' => $payment['id'],
                                    'amount' => $payment['amount'] / 100,
                                    'currency' => $payment['currency'],
                                    'status' => $payment['status'],
                                    'method' => $payment['method'] ?? 'N/A',
                                    'captured' => isset($payment['captured']) && $payment['captured'] ? 'Yes' : 'No',
                                    'authorized' => isset($payment['authorized_at']) ? 'Yes' : 'No',
                                    'error_code' => $payment['error_code'] ?? null,
                                    'error_description' => $payment['error_description'] ?? null,
                                    'error_reason' => $payment['error_reason'] ?? null,
                                    'created_at' => date('Y-m-d H:i:s', $payment['created_at']),
                                    'authorized_at' => isset($payment['authorized_at']) ? date('Y-m-d H:i:s', $payment['authorized_at']) : null,
                                    'captured_at' => isset($payment['captured_at']) ? date('Y-m-d H:i:s', $payment['captured_at']) : null,
                                ];
                            } catch (\Exception $e2) {
                                // Skip if payment fetch fails
                            }
                        }
                    }
                }

                // Method 3: If registration has payment_id, fetch it
                if ($registration && $registration->payment_id && !in_array($registration->payment_id, $allPaymentIds)) {
                    try {
                        $payment = $api->payment->fetch($registration->payment_id);
                        $result['payments'][] = [
                            'id' => $payment['id'],
                            'amount' => $payment['amount'] / 100,
                            'currency' => $payment['currency'],
                            'status' => $payment['status'],
                            'method' => $payment['method'] ?? 'N/A',
                            'captured' => isset($payment['captured']) && $payment['captured'] ? 'Yes' : 'No',
                            'authorized' => isset($payment['authorized_at']) ? 'Yes' : 'No',
                            'error_code' => $payment['error_code'] ?? null,
                            'error_description' => $payment['error_description'] ?? null,
                            'error_reason' => $payment['error_reason'] ?? null,
                            'created_at' => date('Y-m-d H:i:s', $payment['created_at']),
                            'authorized_at' => isset($payment['authorized_at']) ? date('Y-m-d H:i:s', $payment['authorized_at']) : null,
                            'captured_at' => isset($payment['captured_at']) ? date('Y-m-d H:i:s', $payment['captured_at']) : null,
                        ];
                    } catch (\Exception $e2) {
                        // Payment fetch failed
                    }
                }

                // ✅ AUTO-UPDATE: Check if payment is successful but DB is still pending
                if ($registration) {
                    $isPaymentSuccessful = false;
                    $successfulPaymentId = null;
                    $paymentAmount = null;

                    // Check if order is paid
                    if ($order['status'] === 'paid' || $order['amount_due'] == 0) {
                        $isPaymentSuccessful = true;
                    }

                    // Check if any payment is captured
                    if (count($result['payments']) > 0) {
                        foreach ($result['payments'] as $payment) {
                            if ($payment['status'] === 'captured' || ($payment['status'] === 'authorized' && $payment['captured'] === 'Yes')) {
                                $isPaymentSuccessful = true;
                                $successfulPaymentId = $payment['id'];
                                $paymentAmount = $payment['amount'];
                                break;
                            }
                        }
                    }

                    // If payment is successful but DB status is pending, update it
                    if ($isPaymentSuccessful && $registration->status !== 'paid') {
                        $updateData = [
                            'status' => 'paid',
                        ];

                        // Update payment_id if not set or different
                        if ($successfulPaymentId && (!$registration->payment_id || $registration->payment_id !== $successfulPaymentId)) {
                            $updateData['payment_id'] = $successfulPaymentId;
                        }

                        // Update amount if not set
                        if ($paymentAmount && (!$registration->amount || $registration->amount == 0)) {
                            $updateData['amount'] = $paymentAmount;
                        }

                        // Update the registration
                        $registration->update($updateData);

                        // Refresh registration data for display
                        $registration->refresh();
                        
                        // Log the auto-update
                        \Log::info('Auto-updated registration status from pending to paid', [
                            'registration_id' => $registration->id,
                            'order_id' => $orderId,
                            'payment_id' => $successfulPaymentId,
                            'updated_fields' => array_keys($updateData)
                        ]);

                        // Update result with fresh registration data
                        $result['registration'] = [
                            'id' => $registration->id,
                            'registration_code' => $registration->registration_code,
                            'status' => $registration->status,
                            'payment_id' => $registration->payment_id,
                            'amount' => $registration->amount,
                            'event_id' => $registration->event_id,
                            'event' => $registration->event ? $registration->event->title : null,
                        ];

                        // Add success message
                        $result['auto_updated'] = true;
                        $result['update_message'] = 'Database status automatically updated from "pending" to "paid"!';
                    }
                }

            } catch (\Razorpay\Api\Errors\BadRequestError $e) {
                $error = 'Order not found: ' . $e->getMessage();
            } catch (\Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }

        return view('admin.test_payment_status', [
            'orderId' => $orderId,
            'result' => $result,
            'error' => $error,
        ]);
    }

    /**
     * Manually capture an authorized payment
     */
    public function capturePayment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required',
            'amount' => 'required|numeric',
        ]);

        try {
            $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            
            // Fetch payment to check status
            $payment = $api->payment->fetch($request->payment_id);
            
            if ($payment['status'] === 'captured') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is already captured'
                ]);
            }

            if ($payment['status'] !== 'authorized') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment status is: ' . $payment['status'] . '. Only authorized payments can be captured.'
                ]);
            }

            // Capture the payment
            $capturedPayment = $api->payment->fetch($request->payment_id)->capture([
                'amount' => $request->amount * 100 // Convert to paise
            ]);

            // Find registration and update
            $registration = Registration::where('payment_id', $request->payment_id)
                ->orWhere('razorpay_order_id', $payment['order_id'])
                ->first();

            if ($registration) {
                $registration->update([
                    'status' => 'paid',
                    'payment_id' => $request->payment_id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment captured successfully!',
                'payment' => [
                    'id' => $capturedPayment['id'],
                    'status' => $capturedPayment['status'],
                    'amount' => $capturedPayment['amount'] / 100,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error capturing payment: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Manually sync payment status for a specific order
     * Updates DB if payment is successful in Razorpay but DB shows pending
     */
    public function syncPaymentStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
        ]);

        try {
            $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            
            // Fetch order from Razorpay
            $order = $api->order->fetch($request->order_id);
            
            // Find registration
            $registration = Registration::where('razorpay_order_id', $request->order_id)->first();
            
            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration not found for this order ID'
                ], 404);
            }

            // Check if payment is successful
            $isPaymentSuccessful = false;
            $successfulPaymentId = null;
            $paymentAmount = null;

            // Check order status
            if ($order['status'] === 'paid' || $order['amount_due'] == 0) {
                $isPaymentSuccessful = true;
            }

            // Check payments
            try {
                $paymentsData = $api->order->fetch($request->order_id)->payments();
                if (isset($paymentsData['items']) && is_array($paymentsData['items']) && count($paymentsData['items']) > 0) {
                    foreach ($paymentsData['items'] as $payment) {
                        if ($payment['status'] === 'captured' || ($payment['status'] === 'authorized' && isset($payment['captured']) && $payment['captured'])) {
                            $isPaymentSuccessful = true;
                            $successfulPaymentId = $payment['id'];
                            $paymentAmount = $payment['amount'] / 100;
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Try alternative method
                if (isset($order['payments']) && is_array($order['payments'])) {
                    foreach ($order['payments'] as $paymentId) {
                        try {
                            $payment = $api->payment->fetch($paymentId);
                            if ($payment['status'] === 'captured' || ($payment['status'] === 'authorized' && isset($payment['captured']) && $payment['captured'])) {
                                $isPaymentSuccessful = true;
                                $successfulPaymentId = $payment['id'];
                                $paymentAmount = $payment['amount'] / 100;
                                break;
                            }
                        } catch (\Exception $e2) {
                            continue;
                        }
                    }
                }
            }

            if (!$isPaymentSuccessful) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is not successful in Razorpay. Order status: ' . $order['status']
                ]);
            }

            // Update registration if needed
            if ($registration->status !== 'paid') {
                $updateData = [
                    'status' => 'paid',
                ];

                if ($successfulPaymentId && (!$registration->payment_id || $registration->payment_id !== $successfulPaymentId)) {
                    $updateData['payment_id'] = $successfulPaymentId;
                }

                if ($paymentAmount && (!$registration->amount || $registration->amount == 0)) {
                    $updateData['amount'] = $paymentAmount;
                }

                $registration->update($updateData);

                \Log::info('Manually synced payment status', [
                    'registration_id' => $registration->id,
                    'order_id' => $request->order_id,
                    'payment_id' => $successfulPaymentId,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment status synced successfully! Database updated to "paid".',
                    'registration' => [
                        'id' => $registration->id,
                        'status' => $registration->status,
                        'payment_id' => $registration->payment_id,
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment status is already up to date. Status: ' . $registration->status
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing payment: ' . $e->getMessage()
            ], 400);
        }
    }
}
