@extends('admin.layout.app')

@section('content')
<main class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Payment Amount Management</h4>
                </div>
            </div>
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

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $registrations->count() }}</h4>
                                <p class="card-text">Pending Amount Updates</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $allRegistrations->total() }}</h4>
                                <p class="card-text">Total Registrations</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $allRegistrations->total() - $registrations->count() }}</h4>
                                <p class="card-text">Amounts Updated</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-tools"></i> Payment Amount Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-primary" onclick="selectAllPending()">
                                    <i class="fas fa-check-square"></i> Select All Pending
                                </button>
                                <button type="button" class="btn btn-success" onclick="processSelected()">
                                    <i class="fas fa-sync"></i> Process Selected
                                </button>
                                <button type="button" class="btn btn-warning" onclick="processAllPending()">
                                    <i class="fas fa-magic"></i> Process All Pending
                                </button>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ route('admin.registrations') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Registrations
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Amount Updates -->
        @if($registrations->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5><i class="fas fa-exclamation-triangle"></i> Registrations Pending Amount Updates</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAllPending" onchange="toggleAllPending(this)">
                                        </th>
                                        <th>ID</th>
                                        <th>Registration Code</th>
                                        <th>Event</th>
                                        <th>Payment ID</th>
                                        <th>Current Amount</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($registrations as $registration)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="pending-checkbox" value="{{ $registration->id }}">
                                        </td>
                                        <td>{{ $registration->id }}</td>
                                        <td>{{ $registration->registration_code }}</td>
                                        <td>{{ $registration->event->title ?? 'N/A' }}</td>
                                        <td>
                                            <code>{{ $registration->payment_id }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">₹{{ $registration->amount ?? '0.00' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $registration->status === 'paid' ? 'success' : 'warning' }}">
                                                {{ ucfirst($registration->status) }}
                                            </span>
                                        </td>
                                        <td>{{ \App\Support\Ist::format($registration->created_at) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h4>All Amounts Updated!</h4>
                        <p class="text-muted">No registrations are pending amount updates.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- All Registrations -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> All Registrations</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Registration Code</th>
                                        <th>Event</th>
                                        <th>Payment ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Entered By</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allRegistrations as $registration)
                                    <tr class="{{ $registration->amount == 0 || $registration->amount == null ? 'table-warning' : '' }}">
                                        <td>{{ $registration->id }}</td>
                                        <td>{{ $registration->registration_code }}</td>
                                        <td>{{ $registration->event->title ?? 'N/A' }}</td>
                                        <td>
                                            @if($registration->payment_id)
                                                <code>{{ $registration->payment_id }}</code>
                                            @else
                                                <span class="text-muted">No Payment ID</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($registration->amount > 0)
                                                <span class="badge bg-success">₹{{ number_format($registration->amount, 2) }}</span>
                                            @else
                                                <span class="badge bg-warning">₹0.00</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $registration->status === 'paid' ? 'success' : 'warning' }}">
                                                {{ ucfirst($registration->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ ucfirst($registration->entered_by ?? 'user') }}</span>
                                        </td>
                                        <td>{{ \App\Support\Ist::format($registration->created_at) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $allRegistrations->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>Processing Payment Amounts...</h5>
                <p class="text-muted">Please wait while we fetch amounts from Razorpay.</p>
            </div>
        </div>
    </div>
</div>

<script>
function selectAllPending() {
    const checkboxes = document.querySelectorAll('.pending-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selectAllPending').checked = true;
}

function toggleAllPending(checkbox) {
    const checkboxes = document.querySelectorAll('.pending-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

function processSelected() {
    const selectedIds = [];
    const checkboxes = document.querySelectorAll('.pending-checkbox:checked');
    
    checkboxes.forEach(checkbox => {
        selectedIds.push(checkbox.value);
    });
    
    if (selectedIds.length === 0) {
        alert('Please select at least one registration to process.');
        return;
    }
    
    processPayments(selectedIds);
}

function processAllPending() {
    const allIds = [];
    const checkboxes = document.querySelectorAll('.pending-checkbox');
    
    checkboxes.forEach(checkbox => {
        allIds.push(checkbox.value);
    });
    
    if (allIds.length === 0) {
        alert('No pending registrations found.');
        return;
    }
    
    if (confirm(`Are you sure you want to process all ${allIds.length} pending registrations?`)) {
        processPayments(allIds);
    }
}

function processPayments(registrationIds) {
    // Show processing modal
    const modal = new bootstrap.Modal(document.getElementById('processingModal'));
    modal.show();
    
    // Send AJAX request
    fetch('{{ route("admin.payment.amounts.process") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            registration_ids: registrationIds
        })
    })
    .then(response => response.json())
    .then(data => {
        modal.hide();
        
        if (data.success) {
            alert(`Success! ${data.message}\n\nUpdated: ${data.updated_count} registrations`);
            
            if (data.errors && data.errors.length > 0) {
                alert('Errors encountered:\n' + data.errors.join('\n'));
            }
            
            // Reload page to show updated data
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        modal.hide();
        console.error('Error:', error);
        alert('Error processing payments: ' + error.message);
    });
}
</script>
@endsection
