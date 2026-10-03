@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

<style>
    .payment-test-page {
        padding: 20px 0;
    }
    .payment-test-page .card {
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 8px;
    }
    .payment-test-page .card-header {
        padding: 15px 20px;
        font-weight: 600;
        border-bottom: 2px solid #e9ecef;
    }
    .payment-test-page .card-body {
        padding: 25px;
    }
    .payment-test-page .form-group {
        margin-bottom: 20px;
    }
    .payment-test-page .form-group label {
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
        color: #333;
    }
    .payment-test-page .form-control {
        padding: 12px 15px;
        font-size: 15px;
        border-radius: 5px;
    }
    .payment-test-page .table {
        margin-bottom: 0;
    }
    .payment-test-page .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        padding: 15px;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
    }
    .payment-test-page .table td {
        padding: 15px;
        vertical-align: middle;
    }
    .payment-test-page .table-bordered th,
    .payment-test-page .table-bordered td {
        border: 1px solid #dee2e6;
    }
    .payment-test-page .alert {
        margin: 20px 0;
        padding: 15px 20px;
        border-radius: 5px;
    }
    .payment-test-page code {
        background-color: #f4f4f4;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 13px;
        color: #e83e8c;
    }
    .payment-test-page .badge {
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .payment-test-page .btn {
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 5px;
    }
    .payment-test-page .table-responsive {
        border-radius: 5px;
        overflow-x: auto;
    }
    .payment-test-page .card-header.bg-primary,
    .payment-test-page .card-header.bg-success,
    .payment-test-page .card-header.bg-info,
    .payment-test-page .card-header.bg-secondary {
        color: white;
        border-bottom: none;
    }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h1 class="m-0">🧪 Test Payment Status by Order ID</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content payment-test-page">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Search Form -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-search"></i> Enter Razorpay Order ID
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.test.payment.status') }}">
                                <div class="form-group">
                                    <label for="order_id">Order ID</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="order_id" 
                                        name="order_id" 
                                        value="{{ $orderId }}" 
                                        placeholder="order_xxxxxxxxxxxxx"
                                        required
                                    >
                                    <small class="form-text text-muted mt-2">
                                        <i class="fas fa-info-circle"></i> Enter the Razorpay Order ID (e.g., order_ABC123XYZ)
                                    </small>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-search"></i> Check Payment Status
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($error)
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="fas fa-exclamation-circle"></i> Error:</strong> {{ $error }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(isset($result['auto_updated']) && $result['auto_updated'])
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-left: 4px solid #28a745;">
                            <h5 class="mb-2"><i class="fas fa-check-circle"></i> ✅ Database Auto-Updated!</h5>
                            <p class="mb-0"><strong>{{ $result['update_message'] ?? 'Database status has been automatically updated.' }}</strong></p>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($result)
                        <!-- Order Details -->
                        <div class="card">
                            <div class="card-header bg-primary">
                                <h3 class="card-title text-white mb-0">
                                    <i class="fas fa-shopping-cart"></i> Order Details
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <tbody>
                                            <tr>
                                                <th width="30%" style="min-width: 200px;">Order ID</th>
                                                <td><code>{{ $result['order']['id'] }}</code></td>
                                            </tr>
                                            <tr>
                                                <th>Receipt</th>
                                                <td><strong>{{ $result['order']['receipt'] }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Order Amount</th>
                                                <td><strong class="text-primary" style="font-size: 18px;">₹{{ number_format($result['order']['amount'], 2) }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Amount Paid</th>
                                                <td>
                                                    <span class="badge badge-success" style="font-size: 14px; padding: 8px 15px;">
                                                        ₹{{ number_format($result['order']['amount_paid'], 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Amount Due</th>
                                                <td>
                                                    <span class="badge badge-{{ $result['order']['amount_due'] > 0 ? 'warning' : 'success' }}" style="font-size: 14px; padding: 8px 15px;">
                                                        ₹{{ number_format($result['order']['amount_due'], 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Currency</th>
                                                <td><strong>{{ $result['order']['currency'] }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Order Status</th>
                                                <td>
                                                    @if($result['order']['status'] === 'paid')
                                                        <span class="badge badge-success" style="font-size: 14px; padding: 8px 15px;">✅ Paid</span>
                                                    @elseif($result['order']['status'] === 'attempted')
                                                        <span class="badge badge-warning" style="font-size: 14px; padding: 8px 15px;">⏳ Attempted</span>
                                                    @else
                                                        <span class="badge badge-danger" style="font-size: 14px; padding: 8px 15px;">❌ {{ ucfirst($result['order']['status']) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Created At</th>
                                                <td>{{ $result['order']['created_at'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        @if(count($result['payments']) > 0)
                            <div class="card">
                                <div class="card-header bg-success">
                                    <h3 class="card-title text-white mb-0">
                                        <i class="fas fa-credit-card"></i> Payment Details ({{ count($result['payments']) }} payment(s))
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="min-width: 200px;">Payment ID</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Method</th>
                                                    <th>Authorized</th>
                                                    <th>Captured</th>
                                                    <th style="min-width: 180px;">Timestamps</th>
                                                    <th style="min-width: 150px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($result['payments'] as $payment)
                                                    <tr>
                                                        <td><code style="font-size: 12px;">{{ $payment['id'] }}</code></td>
                                                        <td><strong class="text-primary" style="font-size: 16px;">₹{{ number_format($payment['amount'], 2) }}</strong></td>
                                                        <td>
                                                            @if($payment['status'] === 'captured')
                                                                <span class="badge badge-success" style="font-size: 12px; padding: 6px 10px;">✅ Captured</span>
                                                            @elseif($payment['status'] === 'authorized')
                                                                <span class="badge badge-warning" style="font-size: 12px; padding: 6px 10px;">
                                                                    ⏳ Authorized<br><small style="font-size: 10px;">Money deducted!</small>
                                                                </span>
                                                            @elseif($payment['status'] === 'failed')
                                                                <span class="badge badge-danger" style="font-size: 12px; padding: 6px 10px;">❌ Failed</span>
                                                                @if($payment['error_code'] || $payment['error_description'])
                                                                    <br><small class="text-danger" style="font-size: 10px;">{{ $payment['error_description'] ?? $payment['error_reason'] }}</small>
                                                                @endif
                                                            @else
                                                                <span class="badge badge-secondary" style="font-size: 12px; padding: 6px 10px;">{{ ucfirst($payment['status']) }}</span>
                                                            @endif
                                                        </td>
                                                        <td><strong>{{ ucfirst($payment['method']) }}</strong></td>
                                                        <td>
                                                            @if($payment['authorized'] === 'Yes')
                                                                <span class="badge badge-success" style="font-size: 11px;">Yes</span>
                                                                @if($payment['authorized_at'])
                                                                    <br><small style="font-size: 10px; color: #666;">{{ $payment['authorized_at'] }}</small>
                                                                @endif
                                                            @else
                                                                <span class="badge badge-secondary" style="font-size: 11px;">No</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($payment['captured'] === 'Yes')
                                                                <span class="badge badge-success" style="font-size: 11px;">Yes</span>
                                                                @if($payment['captured_at'])
                                                                    <br><small style="font-size: 10px; color: #666;">{{ $payment['captured_at'] }}</small>
                                                                @endif
                                                            @else
                                                                <span class="badge badge-danger" style="font-size: 11px;">No</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <small style="font-size: 11px; line-height: 1.6;">
                                                                <strong>Created:</strong> {{ $payment['created_at'] }}<br>
                                                                @if($payment['authorized_at'])
                                                                    <strong>Authorized:</strong> {{ $payment['authorized_at'] }}<br>
                                                                @endif
                                                                @if($payment['captured_at'])
                                                                    <strong>Captured:</strong> {{ $payment['captured_at'] }}
                                                                @endif
                                                            </small>
                                                        </td>
                                                        <td>
                                                            @if($payment['status'] === 'authorized' && $payment['captured'] === 'No')
                                                                <button 
                                                                    class="btn btn-sm btn-warning capture-payment-btn" 
                                                                    data-payment-id="{{ $payment['id'] }}"
                                                                    data-amount="{{ $payment['amount'] }}"
                                                                    style="white-space: nowrap;"
                                                                >
                                                                    <i class="fas fa-hand-holding-usd"></i> Capture
                                                                </button>
                                                            @elseif($payment['status'] === 'captured')
                                                                <span class="badge badge-success" style="font-size: 11px;">Already Captured</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    @if(collect($result['payments'])->contains(function($p) { return $p['status'] === 'authorized' && $p['captured'] === 'No'; }))
                                        <div class="alert alert-warning mt-4" style="border-left: 4px solid #ffc107;">
                                            <h5 class="mb-3"><i class="fas fa-exclamation-triangle"></i> ⚠️ Important: Authorized but Not Captured</h5>
                                            <p class="mb-2"><strong>Money has been deducted from the bank account, but payment is not yet captured.</strong></p>
                                            <p class="mb-2">This means:</p>
                                            <ul class="mb-2">
                                                <li>✅ Payment was authorized (money deducted)</li>
                                                <li>❌ Payment was NOT captured (merchant hasn't received it yet)</li>
                                                <li>⏰ You have 5-7 days to capture, otherwise it will be auto-refunded</li>
                                            </ul>
                                            <p class="mb-0"><strong>Solution:</strong> Click "Capture Payment" button above to capture the authorized payment.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> <strong>No payments found for this order.</strong>
                            </div>
                        @endif

                        <!-- Registration Details -->
                        @if($result['registration'])
                            <div class="card">
                                <div class="card-header bg-info">
                                    <h3 class="card-title text-white mb-0">
                                        <i class="fas fa-user"></i> Registration Details (Database)
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <tbody>
                                                <tr>
                                                    <th width="30%" style="min-width: 200px;">Registration ID</th>
                                                    <td><strong>{{ $result['registration']['id'] }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <th>Registration Code</th>
                                                    <td><code>{{ $result['registration']['registration_code'] }}</code></td>
                                                </tr>
                                                <tr>
                                                    <th>Event</th>
                                                    <td><strong>{{ $result['registration']['event'] ?? 'N/A' }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <th>Status in DB</th>
                                                    <td>
                                                        @if($result['registration']['status'] === 'paid')
                                                            <span class="badge badge-success" style="font-size: 14px; padding: 8px 15px;">✅ Paid</span>
                                                        @else
                                                            <span class="badge badge-warning" style="font-size: 14px; padding: 8px 15px;">⏳ {{ ucfirst($result['registration']['status']) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Payment ID (DB)</th>
                                                    <td>
                                                        @if($result['registration']['payment_id'])
                                                            <code>{{ $result['registration']['payment_id'] }}</code>
                                                        @else
                                                            <span class="text-muted">Not stored</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Amount (DB)</th>
                                                    <td>
                                                        @if($result['registration']['amount'])
                                                            <strong class="text-primary" style="font-size: 16px;">₹{{ number_format($result['registration']['amount'], 2) }}</strong>
                                                        @else
                                                            <span class="text-muted">Not set</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    @if($result['registration']['status'] !== 'paid' && ($result['order']['status'] === 'paid' || $result['order']['amount_due'] == 0))
                                        <div class="alert alert-warning mt-4" style="border-left: 4px solid #ffc107;">
                                            <h5 class="mb-2"><i class="fas fa-exclamation-triangle"></i> Warning: Payment Successful but DB Not Updated</h5>
                                            <p class="mb-3">Payment is successful in Razorpay but status is not updated in database.</p>
                                            <button 
                                                class="btn btn-warning sync-payment-btn" 
                                                data-order-id="{{ $result['order']['id'] }}"
                                            >
                                                <i class="fas fa-sync-alt"></i> Sync Payment Status Now
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Registration not found:</strong> No registration found in database with this order ID.
                            </div>
                        @endif

                        <!-- Summary -->
                        <div class="card">
                            <div class="card-header bg-secondary">
                                <h3 class="card-title text-white mb-0">
                                    <i class="fas fa-check-circle"></i> Payment Status Summary
                                </h3>
                            </div>
                            <div class="card-body">
                                @php
                                    $isFullyPaid = $result['order']['amount_due'] == 0;
                                    $hasPayments = count($result['payments']) > 0;
                                    $dbStatusPaid = $result['registration'] && $result['registration']['status'] === 'paid';
                                @endphp

                                @if($isFullyPaid && $hasPayments && $dbStatusPaid)
                                    <div class="alert alert-success" style="border-left: 4px solid #28a745;">
                                        <h5 class="mb-3"><i class="fas fa-check-circle"></i> ✅ Payment Verified Successfully!</h5>
                                        <ul class="mb-0" style="line-height: 2;">
                                            <li>Order is fully paid (₹{{ number_format($result['order']['amount_paid'], 2) }})</li>
                                            <li>Payment(s) captured successfully</li>
                                            <li>Database status is updated to "paid"</li>
                                        </ul>
                                    </div>
                                @elseif($isFullyPaid && $hasPayments && !$dbStatusPaid)
                                    <div class="alert alert-warning" style="border-left: 4px solid #ffc107;">
                                        <h5 class="mb-3"><i class="fas fa-exclamation-triangle"></i> ⚠️ Payment Successful but DB Not Updated</h5>
                                        <ul class="mb-3" style="line-height: 2;">
                                            <li>Order is fully paid in Razorpay</li>
                                            <li>Payment(s) captured successfully</li>
                                            <li><strong>Database status is still "{{ $result['registration']['status'] ?? 'pending' }}"</strong></li>
                                        </ul>
                                        <button 
                                            class="btn btn-warning sync-payment-btn" 
                                            data-order-id="{{ $result['order']['id'] }}"
                                        >
                                            <i class="fas fa-sync-alt"></i> Sync Payment Status Now
                                        </button>
                                    </div>
                                @elseif($isFullyPaid && !$hasPayments)
                                    <div class="alert alert-info" style="border-left: 4px solid #17a2b8;">
                                        <h5 class="mb-3"><i class="fas fa-info-circle"></i> ℹ️ Order Paid but No Payment Details</h5>
                                        <p class="mb-0">The order shows as paid but payment details are not available.</p>
                                    </div>
                                @else
                                    <div class="alert alert-danger" style="border-left: 4px solid #dc3545;">
                                        <h5 class="mb-3"><i class="fas fa-times-circle"></i> ❌ Payment Not Completed</h5>
                                        <ul class="mb-0" style="line-height: 2;">
                                            <li>Amount Due: ₹{{ number_format($result['order']['amount_due'], 2) }}</li>
                                            <li>Order Status: {{ $result['order']['status'] }}</li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

@include('admin.layout.footer')

<script>
$(document).ready(function() {
    // Capture payment button
    $('.capture-payment-btn').on('click', function() {
        const paymentId = $(this).data('payment-id');
        const amount = $(this).data('amount');
        const btn = $(this);
        
        if (!confirm(`Are you sure you want to capture payment ${paymentId} for ₹${amount}?`)) {
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Capturing...');
        
        $.ajax({
            url: '{{ route("admin.capture.payment") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                payment_id: paymentId,
                amount: amount
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ Payment captured successfully!');
                    location.reload();
                } else {
                    alert('❌ Error: ' + response.message);
                    btn.prop('disabled', false).html('<i class="fas fa-hand-holding-usd"></i> Capture');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'An error occurred';
                alert('❌ Error: ' + errorMsg);
                btn.prop('disabled', false).html('<i class="fas fa-hand-holding-usd"></i> Capture');
            }
        });
    });

    // Sync payment status button
    $('.sync-payment-btn').on('click', function() {
        const orderId = $(this).data('order-id');
        const btn = $(this);
        
        if (!confirm(`Are you sure you want to sync payment status for order ${orderId}?`)) {
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Syncing...');
        
        $.ajax({
            url: '{{ route("admin.sync.payment.status") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ ' + response.message);
                    location.reload();
                } else {
                    alert('❌ Error: ' + response.message);
                    btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Sync Payment Status Now');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'An error occurred';
                alert('❌ Error: ' + errorMsg);
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Sync Payment Status Now');
            }
        });
    });
});
</script>

