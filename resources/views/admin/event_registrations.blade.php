@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

@php
    use Illuminate\Support\Str;
    
    $getField = function ($data, $keyword) {
        $item = collect($data ?? [])->first(function ($f) use ($keyword) {
            return isset($f['label']) && Str::contains(strtolower($f['label']), strtolower($keyword));
        });
        $val = $item['value'] ?? '';
        return is_array($val) ? implode(', ', $val) : $val;
    };
@endphp

<style>
    .pending-header {
        background-color: #dc3545;
        color: white;
    }
    .paid-header {
        background-color: #28a745;
        color: white;
    }
    .badge-pending {
        background-color: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    .badge-paid {
        background-color: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    .badge-gender-boys {
        background-color: #0d6efd;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    .badge-gender-girls {
        background-color: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    .action-buttons a, .action-buttons button {
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .attendance-btn-attended {
        background-color: #198754;
        color: white;
        border: 1px solid #198754;
        cursor: pointer;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .attendance-btn-attended:hover {
        background-color: #157347;
        color: white;
    }
    .attendance-btn-absent {
        background-color: #f8f9fa;
        color: #dc3545;
        border: 1px solid #dc3545;
        cursor: pointer;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .attendance-btn-absent:hover {
        background-color: #dc3545;
        color: white;
    }
    /* Constrain pagination SVGs in case default Tailwind pagination renders */
    nav[role="navigation"] svg {
        width: 1.25rem !important;
        height: 1.25rem !important;
        max-width: 1.25rem !important;
        max-height: 1.25rem !important;
        display: inline-block !important;
    }
    .pagination svg {
        width: 1rem !important;
        height: 1rem !important;
    }
    .pagination {
        margin-bottom: 0;
        justify-content: center;
    }
</style>

<main class="page-content">
    <div class="container py-4">
        
        <!-- Event Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="mb-2">{{ $event->title }}</h2>
                        <p class="text-muted mb-1">
                            <strong>Category:</strong> {{ $event->category->name ?? 'N/A' }} | 
                            <strong>Date:</strong> {{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }} |
                            <strong>Venue:</strong> {{ $event->venue ?? 'N/A' }}
                        </p>
                        <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-dark" style="font-size: 0.85rem; padding: 6px 10px;">
                                <i class="bi bi-wallet2"></i> Total: {{ $paidRegistrations->total() + $pendingRegistrations->count() }} | ₹{{ number_format($totalFullAmount, 2) }}
                            </span>
                            <span class="badge bg-primary" style="font-size: 0.85rem; padding: 6px 10px;">
                                <i class="bi bi-check-circle-fill"></i> {{ $paidRegistrations->total() }} Paid | ₹{{ number_format($totalPaidAmount, 2) }}
                            </span>
                            <span class="badge bg-success" style="font-size: 0.85rem; padding: 6px 10px;">
                                <i class="bi bi-check2-circle"></i> <span id="attendedCountBadge">{{ $attendedCount ?? 0 }}</span> Attended
                            </span>
                            <span class="badge bg-warning text-dark" style="font-size: 0.85rem; padding: 6px 10px;">
                                <i class="bi bi-clock-history"></i> {{ $pendingRegistrations->count() }} Pending | ₹{{ number_format($totalPendingAmount, 2) }}
                            </span>
                            @if(isset($allEvents) && count($allEvents) > 1)
                                <div class="ms-md-3 d-inline-flex align-items-center gap-2">
                                    <label class="text-muted small fw-bold mb-0">Switch Venue / Event:</label>
                                    <select class="form-select form-select-sm" style="max-width: 320px;" onchange="if(this.value) window.location.href=this.value;">
                                        @foreach($allEvents as $ev)
                                            @php
                                                $displayText = !empty($ev->venue) ? $ev->venue : $ev->title;
                                            @endphp
                                            <option value="{{ route('admin.events.registrations', $ev->id) }}" {{ $ev->id == $event->id ? 'selected' : '' }}>
                                                {{ $displayText }} ({{ \Carbon\Carbon::parse($ev->event_date)->format('d M Y') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <!-- Download Attended Certificates (PDFs/ZIP) -->
                        <a href="{{ route('admin.event.certificates.download', ['eventId' => $event->id]) }}" class="btn btn-primary btn-sm" title="Download certificate PDFs for all attended participants">
                            <i class="bi bi-award-fill"></i> Download Certificates
                        </a>
                        <!-- Export Attended Excel -->
                        <a href="{{ route('admin.event.export.attended', ['eventId' => $event->id]) }}" class="btn btn-outline-primary btn-sm" title="Export attended participants formatted for Excel">
                            <i class="bi bi-file-earmark-excel"></i> Export Attended (Excel)
                        </a>
                        <!-- Export Paid Excel -->
                        <a href="{{ route('admin.event.export', ['eventId' => $event->id]) }}" class="btn btn-success btn-sm" title="Export paid registrations">
                            <i class="bi bi-file-earmark-excel"></i> Export Paid
                        </a>
                        <!-- Export Pending Excel -->
                        <a href="{{ route('admin.event.export.pending', ['eventId' => $event->id]) }}" class="btn btn-warning btn-sm text-dark fw-bold" title="Export pending belt registrations">
                            <i class="bi bi-file-earmark-arrow-down"></i> Export Pending
                        </a>
                        @if ($paidRegistrations->total() > 0)
                            <a href="{{ route('admin.event.pdf.all', ['eventId' => $event->id]) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-download"></i> Download PDFs
                            </a>
                        @endif
                        <a href="{{ route('admin.registrations', ['event_id' => $event->id]) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-list"></i> All Registrations
                        </a>
                        <a href="{{ route('admin.event') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-calendar"></i> Back to Events
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Participant Search -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.events.registrations', $event->id) }}" class="row g-2 align-items-end">
                    <div class="col-md-8 col-lg-9">
                        <label class="form-label small fw-bold text-muted mb-1">Search Participants</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control form-control-sm"
                               placeholder="Name, registration ID, school, class, belt...">
                    </div>
                    <div class="col-md-4 col-lg-3 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-dark flex-grow-1">
                            <i class="bi bi-search"></i> Search
                        </button>
                        @if(request()->filled('search'))
                            <a href="{{ route('admin.events.registrations', $event->id) }}" class="btn btn-sm btn-outline-secondary" title="Clear search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </form>
                @if(request()->filled('search'))
                    <p class="small text-muted mb-0 mt-2">
                        Showing matches for <strong>{{ request('search') }}</strong>
                        — {{ $paidRegistrations->total() }} paid, {{ $pendingRegistrations->count() }} pending
                    </p>
                @endif
            </div>
        </div>

        <!-- Belt-wise Participant Count Breakdown -->
        @if(isset($beltCounts) && count($beltCounts) > 0)
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-diagram-3-fill text-primary"></i>
                    <h6 class="mb-0 fw-bold">Belt-wise Participant Breakdown</h6>
                </div>
                <small class="text-muted">Total Participants by Belt</small>
            </div>
            <div class="card-body p-3">
                <div class="row g-2">
                    @foreach($beltCounts as $beltInfo)
                        @if($beltInfo['total'] > 0)
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark small">{{ $beltInfo['name'] }}</div>
                                        <div class="d-flex gap-1 mt-1">
                                            <span class="badge bg-success" style="font-size: 0.72rem;">{{ $beltInfo['paid'] }} Paid</span>
                                            @if($beltInfo['pending'] > 0)
                                                <span class="badge bg-warning text-dark" style="font-size: 0.72rem;">{{ $beltInfo['pending'] }} Pending</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end ps-2">
                                        <span class="badge bg-dark fs-6">{{ $beltInfo['total'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Pending Registrations Section -->
        @if($pendingRegistrations->count() > 0 || request()->filled('search'))
        <div class="card mb-4">
            <div class="card-header pending-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-clock-history"></i>
                    <h5 class="mb-0">Pending Registrations</h5>
                    <span class="badge bg-light text-dark">{{ $pendingRegistrations->count() }}</span>
                    <span class="badge bg-dark">Total Amount: ₹{{ number_format($totalPendingAmount, 2) }}</span>
                </div>
                <a href="{{ route('admin.event.export.pending', ['eventId' => $event->id]) }}" class="btn btn-sm btn-light text-danger fw-bold">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i> Export Pending Registrations
                </a>
            </div>
            <div class="card-body">
                @if($pendingRegistrations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Registration ID</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>School</th>
                                    <th>Gender</th>
                                    <th>Payment Status</th>
                                    <th>Amount</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRegistrations as $index => $reg)
                                    @php
                                        $regCode = $reg->registration_code;
                                        if (!$regCode || $regCode === 'TEMP') {
                                            $regCode = 'TEMP-' . $event->id . '-' . strtotime($reg->created_at) . '-' . $reg->id;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $regCode }}</td>
                                        <td>{{ $getField($reg->submitted_data, 'name') ?: $getField($reg->submitted_data, 'student name') }}</td>
                                        <td>{{ $getField($reg->submitted_data, 'class') }}</td>
                                        <td>{{ $getField($reg->submitted_data, 'school') ?: $getField($reg->submitted_data, 'school name') }}</td>
                                        <td>
                                            @php
                                                $gender = strtolower($getField($reg->submitted_data, 'gender') ?? '');
                                            @endphp
                                            @if(str_contains($gender, 'boy') || str_contains($gender, 'male'))
                                                <span class="badge-gender-boys">Boys</span>
                                            @elseif(str_contains($gender, 'girl') || str_contains($gender, 'female'))
                                                <span class="badge-gender-girls">Girls</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($gender) ?: 'N/A' }}</span>
                                            @endif
                                        </td>
                                        <td><span class="badge-pending">Pending</span></td>
                                        <td>₹{{ number_format($reg->amount ?? 0, 2) }}</td>
                                        <td>{{ $reg->created_at->format('d M Y h:i A') }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn btn-sm btn-success" 
                                                        onclick="openAmountModal({{ $reg->id }}, '{{ $regCode }}', '{{ addslashes($getField($reg->submitted_data, 'name') ?: $getField($reg->submitted_data, 'student name')) }}', {{ $reg->amount ?? 0 }})"
                                                        title="Mark as Paid">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                                <a href="{{ route('admin.registration.viewpdf', ['registrationId' => $reg->id]) }}" 
                                                   class="btn btn-sm btn-primary" target="_blank" title="View PDF">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('registration.download.pdf', $reg->id) }}" 
                                                   class="btn btn-sm btn-success" title="Download PDF">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <a href="{{ route('admin.registrations.edit', $reg->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4 mb-0">
                        No pending participants match “{{ request('search') }}”.
                    </p>
                @endif
            </div>
        </div>
        @endif

        <!-- Paid Registrations Section -->
        <div class="card mb-4">
            <div class="card-header paid-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-check-circle"></i>
                    <h5 class="mb-0">Paid Registrations</h5>
                    <span class="badge bg-light text-dark">{{ $paidRegistrations->total() }} Total</span>
                    <span class="badge bg-dark">Total Amount: ₹{{ number_format($totalPaidAmount, 2) }}</span>
                    <span class="badge bg-white text-success fw-bold">
                        <i class="bi bi-person-check-fill"></i> <span id="paidAttendedCount">{{ $attendedCount ?? 0 }}</span> Attended
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light text-success fw-bold" onclick="markAllAttendance(true)">
                        <i class="bi bi-check-all"></i> Mark All Attended
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="markAllAttendance(false)">
                        <i class="bi bi-x-circle"></i> Mark All Absent
                    </button>
                    <a href="{{ route('admin.event.certificates.download', ['eventId' => $event->id]) }}" class="btn btn-sm btn-warning text-dark fw-bold" title="Download certificate PDFs for all attended participants">
                        <i class="bi bi-award-fill"></i> Download Certificates
                    </a>
                    <a href="{{ route('admin.event.export.attended', ['eventId' => $event->id]) }}" class="btn btn-sm btn-outline-light" title="Export attended participants to Excel">
                        <i class="bi bi-file-earmark-excel"></i> Export Attended (Excel)
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($paidRegistrations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Registration ID</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>School</th>
                                    <th>Gender</th>
                                    <th>Attendance</th>
                                    <th>Payment Status</th>
                                    <th>Amount</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paidRegistrations as $index => $reg)
                                    @php
                                        $regCode = $reg->registration_code;
                                        if (!$regCode || $regCode === 'TEMP') {
                                            $regCode = 'TEMP-' . $event->id . '-' . strtotime($reg->created_at) . '-' . $reg->id;
                                        }
                                        $isAttended = $reg->is_attended ?? true;
                                    @endphp
                                    <tr id="reg-row-{{ $reg->id }}" class="{{ !$isAttended ? 'table-light text-muted' : '' }}">
                                        <td>{{ ($paidRegistrations->currentPage() - 1) * $paidRegistrations->perPage() + $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('admin.registration.viewpdf', ['registrationId' => $reg->id]) }}" 
                                               class="text-primary fw-bold" target="_blank" title="View PDF">
                                                {{ $regCode }}
                                            </a>
                                        </td>
                                        <td>{{ $getField($reg->submitted_data, 'name') ?: $getField($reg->submitted_data, 'student name') }}</td>
                                        <td>{{ $getField($reg->submitted_data, 'class') }}</td>
                                        <td>{{ $getField($reg->submitted_data, 'school') ?: $getField($reg->submitted_data, 'school name') }}</td>
                                        <td>
                                            @php
                                                $gender = strtolower($getField($reg->submitted_data, 'gender') ?? '');
                                            @endphp
                                            @if(str_contains($gender, 'boy') || str_contains($gender, 'male'))
                                                <span class="badge-gender-boys">Boys</span>
                                            @elseif(str_contains($gender, 'girl') || str_contains($gender, 'female'))
                                                <span class="badge-gender-girls">Girls</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($gender) ?: 'N/A' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="attendance-toggle-btn {{ $isAttended ? 'attendance-btn-attended' : 'attendance-btn-absent' }}"
                                                    data-id="{{ $reg->id }}"
                                                    data-attended="{{ $isAttended ? '1' : '0' }}"
                                                    onclick="toggleAttendance(this, {{ $reg->id }})"
                                                    title="Click to toggle attendance status">
                                                <i class="bi {{ $isAttended ? 'bi-check-circle-fill' : 'bi-x-circle' }}"></i>
                                                <span>{{ $isAttended ? 'Attended' : 'Absent' }}</span>
                                            </button>
                                        </td>
                                        <td><span class="badge-paid">Paid</span></td>
                                        <td>₹{{ number_format($reg->amount ?? 0, 2) }}</td>
                                        <td>{{ $reg->created_at->format('d M Y h:i A') }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('admin.certificate.layout', $reg->id) }}" 
                                                   class="btn btn-sm btn-info text-white" target="_blank" title="View Certificate">
                                                    <i class="bi bi-award"></i>
                                                </a>
                                                <a href="{{ route('admin.registration.viewpdf', ['registrationId' => $reg->id]) }}" 
                                                   class="btn btn-sm btn-primary" target="_blank" title="View PDF">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('registration.download.pdf', $reg->id) }}" 
                                                   class="btn btn-sm btn-success" title="Download PDF">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <a href="{{ route('admin.registrations.edit', $reg->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $paidRegistrations->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <p class="text-muted text-center py-4">
                        @if(request()->filled('search'))
                            No paid participants match “{{ request('search') }}”.
                        @else
                            No paid registrations found.
                        @endif
                    </p>
                @endif
            </div>
        </div>

    </div>
</main>

<!-- Amount Modal for Mark as Paid -->
<div class="modal fade" id="amountModal" tabindex="-1" aria-labelledby="amountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="amountModalLabel">Mark Registration as Paid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><strong>Registration ID:</strong></label>
                    <p id="modal-registration-id" class="form-control-plaintext"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Student Name:</strong></label>
                    <p id="modal-student-name" class="form-control-plaintext"></p>
                </div>
                <div class="mb-3">
                    <label for="amount-input" class="form-label"><strong>Amount Received (₹):</strong></label>
                    <input type="number" class="form-control" id="amount-input" step="0.01" min="0" placeholder="Enter amount">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="updateRegistrationStatus()">Mark as Paid</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentRegistrationId = null;

function openAmountModal(registrationId, registrationCode, studentName, currentAmount) {
    currentRegistrationId = registrationId;
    
    document.getElementById('modal-registration-id').textContent = registrationCode;
    document.getElementById('modal-student-name').textContent = studentName;
    document.getElementById('amount-input').value = currentAmount || '';
    
    const modal = new bootstrap.Modal(document.getElementById('amountModal'));
    modal.show();
}

function updateRegistrationStatus() {
    const amount = document.getElementById('amount-input').value;
    
    if (!amount || amount <= 0) {
        alert('Please enter a valid amount.');
        return;
    }
    
    if (!currentRegistrationId) {
        alert('No registration selected.');
        return;
    }
    
    // Send AJAX request to update status
    fetch('{{ route("admin.registrations.update-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            registration_id: currentRegistrationId,
            amount: parseFloat(amount)
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Registration status updated successfully!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('amountModal'));
            modal.hide();
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            alert('Error updating status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status: ' + error.message);
    });
}

function toggleAttendance(btn, regId) {
    const isCurrentlyAttended = btn.getAttribute('data-attended') === '1';
    const nextStatus = !isCurrentlyAttended;

    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

    fetch('{{ route("admin.registrations.toggle-attendance") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            registration_id: regId,
            is_attended: nextStatus
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            btn.setAttribute('data-attended', data.is_attended ? '1' : '0');
            if (data.is_attended) {
                btn.className = 'attendance-toggle-btn attendance-btn-attended';
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> <span>Attended</span>';
            } else {
                btn.className = 'attendance-toggle-btn attendance-btn-absent';
                btn.innerHTML = '<i class="bi bi-x-circle"></i> <span>Absent</span>';
            }
            updateAttendedBadgeCounts();
        } else {
            btn.innerHTML = originalHtml;
            alert('Failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        alert('Error toggling attendance: ' + err.message);
    });
}

function markAllAttendance(attended) {
    const actionText = attended ? 'Attended' : 'Absent';
    if (!confirm(`Are you sure you want to mark all paid participants for this event as ${actionText}?`)) {
        return;
    }

    fetch('{{ route("admin.event.mark-all-attendance", ["eventId" => $event->id]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: attended ? 'attended' : 'absent'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Error marking all attendance: ' + err.message);
    });
}

function updateAttendedBadgeCounts() {
    const attendedBtns = document.querySelectorAll('.attendance-toggle-btn[data-attended="1"]');
    const count = attendedBtns.length;
    const badge1 = document.getElementById('attendedCountBadge');
    if (badge1) badge1.textContent = count;
    const badge2 = document.getElementById('paidAttendedCount');
    if (badge2) badge2.textContent = count;
}
</script>

@include('admin.layout.footer')

