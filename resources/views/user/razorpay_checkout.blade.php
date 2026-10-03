@include('user.layout.header')
@include('user.layout.navbar')
<style>
    /* Razorpay Payment Card */
    .payment-card {
        max-width: 500px;
        margin: 150px auto;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        font-family: 'Segoe UI', Roboto, sans-serif;
    }

    .payment-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.18);
    }

    /* Header */
    .payment-header {
        background: linear-gradient(135deg, #f44336, #d32f2f);
        color: #fff;
        padding: 20px 15px;
        text-align: center;
        font-weight: 600;
        font-size: 20px;
    }

    .payment-header i {
        margin-right: 8px;
    }

    /* Body */
    .payment-body {
        padding: 20px 25px;
    }

    .payment-body h5 {
        font-size: 16px;
        color: #333;
        margin-bottom: 15px;
        font-weight: 500;
    }

    /* Table */
    .payment-table td {
        font-size: 14px;
        padding: 10px 8px;
        vertical-align: middle;
        color: #444;
    }

    .payment-table tr td:first-child {
        font-weight: 500;
        color: #222;
    }

    .payment-table tr td:last-child {
        text-align: right;
        font-weight: 600;
        color: #111;
    }

    /* Total row */
    .payment-total td {
        background: #fce4ec;
        font-size: 15px;
        font-weight: 600;
        border-radius: 8px;
        color: #d32f2f;
    }

    /* Razorpay Button */
    .btn-razorpay {
        width: 100%;
        font-size: 16px;
        border-radius: 50px;
        padding: 12px 0;
        background: linear-gradient(135deg, #d32f2f, #f44336);
        border: none;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 6px 15px rgba(211, 47, 47, 0.3);
        transition: all 0.3s ease;
        margin-top: 20px;
    }

    .btn-razorpay:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #b71c1c, #d32f2f);
    }
</style>

<div class="container">
    <div class="payment-card">
        

        <!-- Header -->
        <div class="payment-header">
            <i class="fa fa-credit-card"></i> Complete Your Payment
        </div>

        <!-- Body -->
        <div class="payment-body">
           
            <!-- Payment Instruction -->
            <p class="mt-2 text-muted" style="font-size: 14px;color:#111;">
                <em>⚠️ After completing your payment, please wait while your registration ID is generated.
                    Once ready, you will be able to download your registration form.</em>
            </p><br>
            <h5>Event: <strong>{{ $event->title }}</strong></h5>
            <h5>Category: <strong>{{ $event->category->name }}</strong></h5>

            <table class="table payment-table">
                <tbody>

                    @if (strtolower($event->category->name) === 'competition' && isset($submittedData))
                        <tr>
                            <td>Registration Fee</td>
                            <td>₹{{ number_format($event->fee, 2) }}</td>
                        </tr>
                        @php
                            $typeField = collect($submittedData)->firstWhere('label', 'Participation Type');
                        @endphp
                        @if ($typeField && strtolower($typeField['value']) === 'team')
                            <tr>
                                <td>Team Fee</td>
                                <td>₹{{ number_format($additionalCharge, 2) }}</td>
                            </tr>
                        @endif
                    @endif

                    @if (Str::contains(Str::lower($event->category->name), 'kyu') && $beltFee > 0)
                        <tr>
                            <td>Belt Fee</td>
                            <td>₹{{ number_format($beltFee, 2) }}</td>
                        </tr>
                    @endif

                    <tr class="payment-total">
                        <td>Total Payable</td>
                        <td>₹{{ number_format($totalAmount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Razorpay Button -->
            <form action="{{ route('user.razorpay.start') }}" method="POST">
                @csrf
                <input type="hidden" name="registration_id" value="{{ $registration->id }}">
                <input type="hidden" name="amount" value="{{ $totalAmount }}">
                <button type="submit" class="btn btn-razorpay mt-3">
                    Pay Now ₹{{ number_format($totalAmount, 2) }}
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Razorpay Script --}}
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.querySelector(".btn-razorpay").addEventListener("click", function(e) {
        e.preventDefault();

        var options = {
            "key": "{{ $key }}",
            "amount": "{{ $amount }}",
            "currency": "INR",
            "name": "{{ $event->title }}",
            "description": "Event Registration Payment",
            "order_id": "{{ $order_id }}",
            "handler": function(response) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('user.razorpay.Razorepaysuccess') }}";
                form.innerHTML = `
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="razorpay_payment_id" value="${response.razorpay_payment_id}">
        <input type="hidden" name="razorpay_order_id" value="${response.razorpay_order_id}">
        <input type="hidden" name="razorpay_signature" value="${response.razorpay_signature}">
        <input type="hidden" name="registration_id" value="{{ $registration->id }}">
    `;
                document.body.appendChild(form);
                form.submit();
            },
            "prefill": {
                "name": "{{ auth()->user()->name ?? '' }}",
                "email": "{{ auth()->user()->email ?? '' }}",
                "contact": "{{ auth()->user()->phone ?? '' }}"
            },
            "theme": {
                "color": "#d32f2f"
            }
        };

        var rzp1 = new Razorpay(options);
        rzp1.open();
    });
</script>

@include('user.layout.footer')
