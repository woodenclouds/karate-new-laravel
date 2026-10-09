@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

@php
    use Illuminate\Support\Str;

    $classGroups = [
        'LKG-UKG' => ['LKG', 'UKG'],
        'I – II STD' => ['I STD', 'II STD'],
        'III – IV STD' => ['III STD', 'IV STD'],
        'V – VI STD' => ['V STD', 'VI STD'],
        'VII – VIII STD' => ['VII STD', 'VIII STD'],
        'IX - X STD' => ['IX STD', 'X STD'],
        'above' => ['above'],
    ];

    $weightRanges = [];
    $weightRanges['Below 25 kg'] = fn($w) => $w < 25;
    for ($start = 25; $start < 75; $start += 5) {
        $end = $start + 5;
        $label = "{$start}-{$end} kg";
        $weightRanges[$label] = fn($w) => $w >= $start && $w < $end;
    }
    $weightRanges['Above 75 kg'] = fn($w) => $w >= 75;

    $getField = function ($data, $keyword) {
        $item = collect($data ?? [])->first(function ($f) use ($keyword) {
            return isset($f['label']) && Str::contains(strtolower($f['label']), strtolower($keyword));
        });
        $val = $item['value'] ?? '';
        return is_array($val) ? implode(', ', $val) : $val;
    };

    $eventSearch = strtolower(request('event_search'));
    $studentSearch = strtolower(request('student_search'));
    $filterEventId = request('event_id');
@endphp

<style>
    .pend-page-title {
        font-size: 1.65rem;
        letter-spacing: -0.02em;
        color: #146c43;
    }
    .pend-page-wrap {
        background: linear-gradient(180deg, #f7fcf9 0%, #ffffff 40%);
        border-radius: 0.5rem;
    }
    .pend-filter-panel {
        background: #f1faf4;
        border: 1px solid #cce8d6;
        border-radius: 0.75rem;
        padding: 1.15rem 1.25rem;
    }
    .pend-filter-panel .form-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 600;
        color: #3d6b4f;
        margin-bottom: 0.35rem;
    }
    .pend-event-card {
        border: 1px solid #b9e0c6;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(25, 135, 84, 0.08);
    }
    .event-header {
        background: linear-gradient(135deg, #e8f8ee 0%, #d1f0dc 55%, #eaf9f0 100%);
        color: #146c43;
        padding: 1.15rem 1.25rem 1rem;
    }
    .event-header h5 {
        color: #0f5132;
        font-weight: 700;
    }
    .event-header small,
    .event-header .fw-bold {
        color: #3d6b4f;
    }
    .pend-current-badge {
        background: #198754;
        color: #fff;
        border: 1px solid #146c43;
        font-weight: 600;
        font-size: 0.7rem;
    }
    .btn-custom {
        border: 1px solid #198754;
        color: #146c43;
        transition: all 0.3s ease;
    }
    .btn-custom:hover {
        background: #198754;
        color: #fff;
        border-color: #146c43;
    }
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .pend-empty {
        color: #5a7a66;
        background: #f8fcf9;
        text-align: center;
        padding: 1.5rem;
        border-radius: 0.5rem;
    }
</style>

<main class="page-content">
    <div class="container py-4 pend-page-wrap">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="pend-page-title fw-bold mb-0">Pending Registrations</h2>
                <p class="small mb-0" style="color:#5a7a66;">Unpaid registrations awaiting payment confirmation</p>
            </div>
            <div class="d-flex gap-2">
                @if(isset($eventId) && $eventId && $eventId !== 'all')
                    <a href="{{ route('admin.event.export.pending', ['eventId' => $eventId]) }}" 
                       class="btn btn-warning btn-sm text-dark fw-bold" 
                       title="Export Pending Registrations with Belt and Fee Details">
                        <i class="bi bi-file-earmark-arrow-down-fill"></i> Export Pending Excel
                    </a>
                @endif
                <a href="{{ route('admin.registrations') }}" class="btn btn-outline-success btn-sm">← Back to All Registrations</a>
            </div>
        </div>

        <!-- Filters & Event Selector -->
        <div class="pend-filter-panel mb-4">
            <form method="GET" action="{{ route('admin.pending') }}" id="pendingFilterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-5 col-md-6">
                        <label class="form-label">Academy Event</label>
                        <select name="event_id" class="form-select form-select-sm" onchange="document.getElementById('pendingFilterForm').submit();">
                            @if(isset($currentEvent) && $currentEvent)
                                <option value="{{ $currentEvent->id }}" {{ ($eventId == $currentEvent->id) ? 'selected' : '' }}>
                                    ★ Current Event: {{ $currentEvent->title }} ({{ \Carbon\Carbon::parse($currentEvent->event_date)->format('d M Y') }})
                                </option>
                            @endif
                            <option value="all" {{ ($eventId === 'all') ? 'selected' : '' }}>
                                -- Show All Events --
                            </option>
                            @if(isset($allEvents))
                                <optgroup label="Previous / Other Events">
                                    @foreach($allEvents as $ev)
                                        <option value="{{ $ev->id }}" {{ ($eventId == $ev->id) ? 'selected' : '' }}>
                                            {{ $ev->title }} ({{ \Carbon\Carbon::parse($ev->event_date)->format('d M Y') }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <label class="form-label">Search Student</label>
                        <input type="text" name="student_search" value="{{ request('student_search') }}"
                               placeholder="Student Name or Reg Code" class="form-control form-control-sm">
                    </div>
                    <div class="col-lg-3 col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-success flex-grow-1">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <a href="{{ route('admin.pending') }}" class="btn btn-sm btn-outline-success" title="Reset to Current Event">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Events Loop -->
        @forelse($events as $event)
            @php
                if ($filterEventId && $filterEventId !== 'all' && $event->id != $filterEventId) continue;
                if ($eventSearch && !str_contains(strtolower($event->title), $eventSearch)) continue;

                $registrations = $event->registrations->filter(function ($reg) use ($studentSearch) {
                    if (!$studentSearch) return true;
                    if (str_contains(strtolower($reg->registration_code), $studentSearch)) return true;
                    foreach ($reg->submitted_data ?? [] as $field) {
                        $val = $field['value'] ?? '';
                        $strVal = is_array($val) ? implode(' ', $val) : (string)$val;
                        if (str_contains(strtolower($strVal), $studentSearch)) return true;
                    }
                    return false;
                });

                $isFiltering = $eventSearch || $studentSearch || ($filterEventId && $filterEventId !== 'all');
                if ($isFiltering && $registrations->isEmpty()) continue;

                $catName = strtolower($event->category->name ?? '');
                $isCompetition = Str::contains($catName, 'competition');
                $isKyu = Str::contains($catName, 'kyu');
                $isCurrentEvent = isset($currentEvent) && $currentEvent && ($currentEvent->id == $event->id);
            @endphp

            <div class="card pend-event-card border-0 mb-4">
                <div class="card-header event-header border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="mb-0">{{ $event->title }}</h5>
                            @if($isCurrentEvent)
                                <span class="badge pend-current-badge rounded-pill"><i class="bi bi-star-fill"></i> Current Academy Event</span>
                            @endif
                        </div>
                        <small>{{ \Carbon\Carbon::parse($event->event_date)->format('d-m-Y') }} | {{ $event->venue }}</small><br>
                        <span class="fw-bold">Pending Registrations: {{ $registrations->count() }}</span>
                    </div>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <a href="{{ route('admin.events.registrations', $event->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-eye"></i> View Responses
                        </a>
                        <a href="{{ route('admin.event.export.pending', ['eventId' => $event->id]) }}" class="btn btn-warning btn-sm text-dark fw-bold" title="Export Pending with Belt Details">
                            <i class="bi bi-file-earmark-arrow-down-fill"></i> Export Pending Excel
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if ($registrations->isEmpty())
                        <div class="pend-empty">No pending registrations found for this search.</div>
                    @else
                        {{-- CLASS GROUPS LOOP --}}
                        @foreach ($classGroups as $groupLabel => $classes)
                            @php
                                $groupRegs = $registrations->filter(function ($reg) use ($classes, $getField) {
                                    $classVal = $getField($reg->submitted_data, 'class');
                                    return $classVal && in_array($classVal, $classes, true);
                                });
                            @endphp

                            @if ($groupRegs->isNotEmpty())
                                <div class="mb-4">
                                    <h6 class="fw-bold border-bottom pb-1" style="color:#3d6b4f;border-color:#cce8d6 !important;">{{ $groupLabel }}</h6>

                                    <!-- Competition -->
                                    @if ($isCompetition)
                                        @foreach ($weightRanges as $rangeLabel => $condition)
                                            @php
                                                $studentsInRange = $groupRegs->filter(function ($reg) use ($condition, $getField) {
                                                    $w = (float) $getField($reg->submitted_data, 'weight');
                                                    return $condition($w);
                                                });
                                            @endphp

                                            @if ($studentsInRange->isNotEmpty())
                                                <h6 class=" mt-3">{{ $rangeLabel }} ({{ $studentsInRange->count() }})</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered align-middle mt-2">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Registration ID</th>
                                                                <th>Student Name</th>
                                                                <th>Class</th>
                                                                <th>Weight (kg)</th>
                                                                <th>Status</th>
                                                                <th>Amount</th>
                                                                <th>Submitted At</th>
                                                                <th>Update Status</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($studentsInRange->values() as $index => $reg)
                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>{{ $reg->registration_code }}</td>
                                                                    <td>{{ $getField($reg->submitted_data, 'name') }}</td>
                                                                    <td>{{ $getField($reg->submitted_data, 'class') }}</td>
                                                                    <td>{{ $getField($reg->submitted_data, 'weight') }}</td>
                                                                    <td>
                                                                        <span class="status-pending">{{ ucfirst($reg->status) }}</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="text-muted">{{ $reg->amount ? '₹' . number_format($reg->amount, 2) : 'Not set' }}</span>
                                                                    </td>
                                                                    <td>{{ \App\Support\Ist::format($reg->created_at) }}</td>
                                                                    <td>
                                                                        <button type="button" class="btn btn-sm btn-success" 
                                                                                onclick="openAmountModal({{ $reg->id }}, '{{ $reg->registration_code }}', '{{ addslashes($getField($reg->submitted_data, 'name')) }}', {{ $reg->amount ?? 0 }})">
                                                                            <i class="bi bi-check-circle"></i> Mark Paid
                                                                        </button>
                                                                    </td>
                                                                    <td>
                                                                        <a href="{{ route('admin.registration.viewpdf', ['registrationId' => $reg->id]) }}" class="btn btn-sm btn-primary">View</a>
                                                                        <a href="{{ route('registration.download.pdf', $reg->id) }}" class="btn btn-sm btn-info">Download</a>
                                                                        <a href="{{ route('admin.registrations.edit', $reg->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif

                                    <!-- Kyu -->
                                    @if ($isKyu)
                                        <div class="table-responsive mt-3">
                                            <table class="table table-striped table-bordered align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Registration ID</th>
                                                        <th>Student Name</th>
                                                        <th>Class</th>
                                                        <th>Status</th>
                                                        <th>Amount</th>
                                                        <th>Submitted At</th>
                                                        <th>Update Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($groupRegs->values() as $index => $reg)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $reg->registration_code }}</td>
                                                            <td>{{ $getField($reg->submitted_data, 'name') }}</td>
                                                            <td>{{ $getField($reg->submitted_data, 'class') }}</td>
                                                            <td>
                                                                <span class="status-pending">{{ ucfirst($reg->status) }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="text-muted">{{ $reg->amount ? '₹' . number_format($reg->amount, 2) : 'Not set' }}</span>
                                                            </td>
                                                            <td>{{ \App\Support\Ist::format($reg->created_at) }}</td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-success" 
                                                                        onclick="openAmountModal({{ $reg->id }}, '{{ $reg->registration_code }}', '{{ addslashes($getField($reg->submitted_data, 'name')) }}', {{ $reg->amount ?? 0 }})">
                                                                    <i class="bi bi-check-circle"></i> Mark Paid
                                                                </button>
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('admin.registration.viewpdf', ['registrationId' => $reg->id]) }}" class="btn btn-sm btn-primary">View</a>
                                                                <a href="{{ route('registration.download.pdf', $reg->id) }}" class="btn btn-sm btn-info">Download</a>
                                                                <a href="{{ route('admin.registrations.edit', $reg->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        @empty
            <div class="pend-empty">No events found.</div>
        @endforelse
    </div>
</main>

<!-- Amount Modal -->
<div class="modal fade" id="amountModal" tabindex="-1" aria-labelledby="amountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="amountModalLabel">Update Registration Status</h5>
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
                <button type="button" class="btn btn-warning" onclick="alert('Test button clicked!')">Test</button>
                <button type="button" class="btn btn-success" onclick="updateRegistrationStatus()">Mark as Paid</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentRegistrationId = null;

function openAmountModal(registrationId, registrationCode, studentName, currentAmount) {
    console.log('openAmountModal called with:', registrationId, registrationCode, studentName, currentAmount);
    
    currentRegistrationId = registrationId;
    
    document.getElementById('modal-registration-id').textContent = registrationCode;
    document.getElementById('modal-student-name').textContent = studentName;
    document.getElementById('amount-input').value = currentAmount || '';
    
    const modal = new bootstrap.Modal(document.getElementById('amountModal'));
    modal.show();
}

function updateRegistrationStatus() {
    console.log('updateRegistrationStatus function called');
    
    const amount = document.getElementById('amount-input').value;
    
    if (!amount || amount <= 0) {
        alert('Please enter a valid amount.');
        return;
    }
    
    if (!currentRegistrationId) {
        alert('No registration selected.');
        return;
    }
    
    console.log('Updating registration:', currentRegistrationId, 'with amount:', amount);
    
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
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Registration status updated successfully!');
            // Close modal first
            const modal = bootstrap.Modal.getInstance(document.getElementById('amountModal'));
            modal.hide();
            // Then reload page
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
</script>

@include('admin.layout.footer')
