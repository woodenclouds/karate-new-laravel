@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

<main class="page-content">
    <div class="container py-4">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Edit Registration</h2>
            <a href="{{ route('admin.registrations') }}" class="btn btn-secondary btn-sm">← Back to Registrations</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.registrations.update', $registration->id) }}" id="registrationForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Event & Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="event_id" class="form-label">Event <span class="text-danger">*</span></label>
                                <select class="form-select" id="event_id" name="event_id" required>
                                    <option value="">Select Event</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ $event->id == $registration->event_id ? 'selected' : '' }}>
                                            {{ $event->title }} - {{ $event->event_date }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending" {{ $registration->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ $registration->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (₹)</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" 
                                       value="{{ $registration->amount ?? '' }}" placeholder="0.00">
                            </div>

                            <div class="mb-3">
                                <label for="payment_id" class="form-label">Payment ID (Optional)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="payment_id" 
                                           value="{{ $registration->payment_id ?? '' }}" placeholder="Enter Razorpay Payment ID">
                                    <button type="button" class="btn btn-outline-secondary" onclick="fetchPaymentAmount()">Fetch Amount</button>
                                </div>
                                <small class="form-text text-muted">Enter Razorpay payment ID to automatically fetch the amount</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Registration Code</label>
                                <input type="text" class="form-control" value="{{ $registration->registration_code }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Entered By</label>
                                <input type="text" class="form-control" value="{{ ucfirst($registration->entered_by ?? 'user') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Registration Data</h5>
                        </div>
                        <div class="card-body">
                            <div id="formFields">
                                @if($registration->event->category->form && $registration->event->category->form->formFields)
                                    @php
                                        // Show Next Belt for Competition and KYU Grading / Belt Test categories
                                        $categoryName = strtolower($registration->event->category->name ?? '');
                                        $isBeltCompetition = str_contains($categoryName, 'competition') || 
                                                             str_contains($categoryName, 'kyu') || 
                                                             str_contains($categoryName, 'belt test');
                                    @endphp
                                    @foreach($registration->event->category->form->formFields->sortBy('order') as $index => $field)
                                        @php
                                            // Skip Date of Birth field
                                            if (str_contains(strtolower($field->label), 'date of birth') || 
                                                str_contains(strtolower($field->label), 'dob')) {
                                                continue;
                                            }
                                        @endphp
                                        <div class="mb-3">
                                            <label for="field_{{ $index }}" class="form-label">
                                                {{ $field->label }}@if($field->required) <span class="text-danger">*</span>@endif
                                            </label>
                                            
                                            @php
                                                $currentValue = '';
                                                foreach($registration->submitted_data as $data) {
                                                    if($data['label'] == $field->label) {
                                                        $currentValue = $data['value'];
                                                        break;
                                                    }
                                                }
                                            @endphp

                                            @switch($field->type)
                                                @case('text')
                                                @case('email')
                                                @case('tel')
                                                    <input type="hidden" name="registration_data[{{ $index }}][label]" value="{{ $field->label }}">
                                                    <input type="hidden" name="registration_data[{{ $index }}][type]" value="{{ $field->type }}">
                                                    <input type="{{ $field->type }}" class="form-control" 
                                                           name="registration_data[{{ $index }}][value]" 
                                                           value="{{ $currentValue }}" 
                                                           {{ $field->required ? 'required' : '' }}>
                                                    @break
                                                @case('select')
                                                    <input type="hidden" name="registration_data[{{ $index }}][label]" value="{{ $field->label }}">
                                                    <input type="hidden" name="registration_data[{{ $index }}][type]" value="{{ $field->type }}">
                                                    <select class="form-select" name="registration_data[{{ $index }}][value]" {{ $field->required ? 'required' : '' }}>
                                                        <option value="">Select {{ $field->label }}</option>
                                                        @if($field->options)
                                                            @foreach(json_decode($field->options) as $option)
                                                                <option value="{{ $option }}" {{ $currentValue == $option ? 'selected' : '' }}>
                                                                    {{ $option }}
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    @break
                                                @case('belt')
                                                    <input type="hidden" name="registration_data[{{ $index }}][label]" value="{{ $field->label }}">
                                                    <input type="hidden" name="registration_data[{{ $index }}][type]" value="{{ $field->type }}">
                                                    <select class="form-select" name="registration_data[{{ $index }}][value]" {{ $field->required ? 'required' : '' }}>
                                                        <option value="">Select Belt</option>
                                                        @foreach(\App\Models\Admin\belt::all() as $belt)
                                                            <option value="{{ $belt->id }}" {{ $currentValue == $belt->id ? 'selected' : '' }}>
                                                                {{ $belt->from_belt }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @break
                                            @endswitch
                                        </div>
                                    @endforeach
                                    
                                    {{-- Add Next Belt field for belt competition events if it doesn't exist --}}
                                    @if($isBeltCompetition)
                                        @php
                                            $hasNextBelt = false;
                                            foreach($registration->event->category->form->formFields as $field) {
                                                if (str_contains(strtolower($field->label), 'next belt')) {
                                                    $hasNextBelt = true;
                                                    break;
                                                }
                                            }
                                            
                                            if (!$hasNextBelt) {
                                                $nextBeltValue = '';
                                                foreach($registration->submitted_data as $data) {
                                                    if(str_contains(strtolower($data['label']), 'next belt')) {
                                                        $nextBeltValue = $data['value'];
                                                        break;
                                                    }
                                                }
                                                $nextBeltIndex = $registration->event->category->form->formFields->count();
                                            }
                                        @endphp
                                        
                                        @if(!$hasNextBelt)
                                            <div class="mb-3">
                                                <label for="field_next_belt" class="form-label">
                                                    Next Belt <span class="text-danger">*</span>
                                                </label>
                                                <input type="hidden" name="registration_data[{{ $nextBeltIndex }}][label]" value="Next Belt">
                                                <input type="hidden" name="registration_data[{{ $nextBeltIndex }}][type]" value="belt">
                                                <select class="form-select" name="registration_data[{{ $nextBeltIndex }}][value]" id="field_next_belt" required>
                                                    <option value="">Select Belt</option>
                                                    @foreach(\App\Models\Admin\belt::all() as $belt)
                                                        <option value="{{ $belt->id }}" {{ $nextBeltValue == $belt->id ? 'selected' : '' }}>
                                                            {{ $belt->from_belt }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    @endif
                                @else
                                    <p class="text-muted">No form fields configured for this event.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Update Registration</button>
                        <a href="{{ route('admin.registrations') }}" class="btn btn-secondary">Cancel</a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete Registration</button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Delete Form -->
        <form id="deleteForm" method="POST" action="{{ route('admin.registrations.delete', $registration->id) }}" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</main>

<script>
function fetchPaymentAmount() {
    const paymentId = document.getElementById('payment_id').value;
    if (!paymentId) {
        alert('Please enter a payment ID');
        return;
    }

    fetch('{{ route("admin.registrations.fetch-payment-amount") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            payment_id: paymentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('amount').value = data.amount;
            alert('Amount fetched successfully: ₹' + data.amount);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error fetching payment amount');
    });
}

function confirmDelete() {
    if (confirm('Are you sure you want to delete this registration? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>

@include('admin.layout.footer')
