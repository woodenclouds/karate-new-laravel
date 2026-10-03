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

    $eventSearch = strtolower(request('event_search') ?? '');
    $studentSearch = strtolower(request('student_search') ?? '');
    $filterEventId = request('event_id');
@endphp

<style>
    .reg-page-title {
        font-size: 1.65rem;
        letter-spacing: -0.02em;
        color: #146c43;
    }
    .reg-page-subtitle {
        font-size: 0.9rem;
        color: #5a7a66;
        margin-top: 0.15rem;
    }
    .btn-reg-primary {
        font-weight: 600;
    }
    .reg-filter-panel {
        background: #f1faf4;
        border: 1px solid #cce8d6;
        border-radius: 0.75rem;
        padding: 1.15rem 1.25rem;
    }
    .reg-filter-panel .form-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 600;
        color: #3d6b4f;
        margin-bottom: 0.35rem;
    }
    .reg-event-card {
        border: 1px solid #b9e0c6;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(25, 135, 84, 0.08);
    }
    .reg-event-header {
        background: linear-gradient(135deg, #e8f8ee 0%, #d1f0dc 55%, #eaf9f0 100%);
        color: #146c43;
        padding: 1.15rem 1.25rem 1rem;
    }
    .reg-event-header h5 {
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: #0f5132;
    }
    .reg-event-meta {
        color: #3d6b4f;
        font-size: 0.85rem;
    }
    .reg-current-badge {
        background: #198754;
        color: #fff;
        border: 1px solid #146c43;
        font-weight: 600;
        font-size: 0.7rem;
        padding: 0.35em 0.65em;
    }
    .reg-stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        border: 1px solid transparent;
        text-decoration: none;
    }
    .reg-stat-chip.paid {
        background: #cff4fc;
        color: #055160;
        border-color: #9eeaf9;
    }
    .reg-stat-chip.attended {
        background: #d1e7dd;
        color: #0f5132;
        border-color: #a3cfbb;
    }
    .reg-stat-chip.pending {
        background: #fff3cd;
        color: #664d03;
        border-color: #ffecb5;
    }
    .reg-stat-chip.pending:hover {
        color: #664d03;
        background: #ffe69c;
    }
    .reg-event-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        margin-top: 1rem;
        padding-top: 0.9rem;
        border-top: 1px solid #b9e0c6;
    }
    .reg-event-actions .btn {
        border-radius: 0.4rem;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .reg-empty-state {
        text-align: center;
        padding: 2.5rem 1.5rem;
        color: #5a7a66;
        background: #f8fcf9;
    }
    .reg-empty-state i {
        font-size: 2rem;
        color: #75b798;
        display: block;
        margin-bottom: 0.75rem;
    }
    .reg-empty-state p {
        margin: 0;
        font-size: 0.95rem;
    }
    .reg-group-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #3d6b4f;
        border-bottom: 1px solid #cce8d6;
        padding-bottom: 0.45rem;
        margin-bottom: 0.85rem;
    }
    .reg-table {
        margin-bottom: 0;
        border-color: #cce8d6;
    }
    .reg-table thead th {
        background: #eaf7ef;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #3d6b4f;
        font-weight: 700;
        border-bottom-width: 1px;
        white-space: nowrap;
    }
    .reg-table tbody tr:hover {
        background: #f3fbf6;
    }
    .reg-table td {
        font-size: 0.875rem;
        vertical-align: middle;
    }
    .reg-action-group .btn {
        padding: 0.25rem 0.45rem;
        line-height: 1;
    }
    .reg-action-group .btn i {
        font-size: 0.9rem;
    }
    .reg-page-wrap {
        background: linear-gradient(180deg, #f7fcf9 0%, #ffffff 40%);
        border-radius: 0.5rem;
    }
</style>

<main class="page-content">
    <div class="container py-4 reg-page-wrap">

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h2 class="reg-page-title fw-bold mb-0">Event Registrations</h2>
                <p class="reg-page-subtitle mb-0">Manage paid registrations by academy event</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.registrations.create') }}" class="btn btn-success btn-reg-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Create Registration
                </a>
                <a href="{{ route('admin.event') }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Events
                </a>
            </div>
        </div>

        <!-- Filters & Event Selector -->
        <div class="reg-filter-panel mb-4">
            <form method="GET" action="{{ route('admin.registrations') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-5 col-md-6">
                        <label class="form-label">Academy Event</label>
                        <select name="event_id" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit();">
                            @if(isset($currentEvent) && $currentEvent)
                                <option value="{{ $currentEvent->id }}" {{ ($selectedEventId == $currentEvent->id) ? 'selected' : '' }}>
                                    ★ Current Event: {{ $currentEvent->title }} ({{ \Carbon\Carbon::parse($currentEvent->event_date)->format('d M Y') }})
                                </option>
                            @endif
                            <option value="all" {{ ($selectedEventId === 'all') ? 'selected' : '' }}>
                                -- Show All Events --
                            </option>
                            @if(isset($allEvents))
                                <optgroup label="Select Event (Previous / Other)">
                                    @foreach($allEvents as $ev)
                                        <option value="{{ $ev->id }}" {{ ($selectedEventId == $ev->id) ? 'selected' : '' }}>
                                            {{ $ev->title }} ({{ \Carbon\Carbon::parse($ev->event_date)->format('d M Y') }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label">Sort Events</label>
                        <select name="sort" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit();">
                            <option value="desc" {{ ($sort ?? 'desc') === 'desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="asc" {{ ($sort ?? '') === 'asc' ? 'selected' : '' }}>Oldest First</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-5">
                        <label class="form-label">Search Participant</label>
                        <input type="text" name="student_search" value="{{ request('student_search') }}"
                               placeholder="Student name or reg code" class="form-control form-control-sm">
                    </div>
                    <div class="col-lg-2 col-md-4 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-success flex-grow-1">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <a href="{{ route('admin.registrations') }}" class="btn btn-sm btn-outline-success" title="Reset to Current Event">
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

            <div class="card reg-event-card border-0 mb-4">
                <div class="reg-event-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h5 class="mb-0">{{ $event->title }}</h5>
                                @if($isCurrentEvent)
                                    <span class="badge reg-current-badge rounded-pill">
                                        <i class="bi bi-star-fill"></i> Current Academy Event
                                    </span>
                                @endif
                            </div>
                            <div class="reg-event-meta">
                                <i class="bi bi-calendar3"></i>
                                {{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}
                                <span class="mx-1">·</span>
                                <i class="bi bi-geo-alt"></i>
                                {{ $event->venue }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                        <span class="reg-stat-chip paid">
                            <i class="bi bi-check-circle-fill"></i> {{ $event->paid_count ?? $registrations->count() }} Paid
                        </span>
                        <span class="reg-stat-chip attended">
                            <i class="bi bi-person-check-fill"></i> {{ $event->attended_count ?? 0 }} Attended
                        </span>
                        @if (($event->pending_count ?? 0) > 0)
                            <a href="{{ route('admin.pending', ['event_id' => $event->id]) }}" class="reg-stat-chip pending">
                                <i class="bi bi-clock-history"></i> {{ $event->pending_count }} Pending
                            </a>
                        @endif
                    </div>

                    <div class="reg-event-actions">
                        <a href="{{ route('admin.events.registrations', $event->id) }}" class="btn btn-sm btn-success" title="View, manage attendance, and responses">
                            <i class="bi bi-eye"></i> View Responses
                        </a>
                        <a href="{{ route('admin.event.certificates.download', ['eventId' => $event->id]) }}" class="btn btn-sm btn-outline-success" title="Download certificate PDFs for all attended participants">
                            <i class="bi bi-award-fill"></i> Download Certificates
                        </a>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-file-earmark-arrow-down"></i> Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.event.export', ['eventId' => $event->id]) }}">
                                        <i class="bi bi-file-earmark-excel text-success me-2"></i> Export Paid
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.event.export.pending', ['eventId' => $event->id]) }}">
                                        <i class="bi bi-file-earmark-excel text-warning me-2"></i> Export Pending
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.event.export.attended', ['eventId' => $event->id]) }}">
                                        <i class="bi bi-file-earmark-excel text-primary me-2"></i> Export Attended (Excel)
                                    </a>
                                </li>
                                @if ($registrations->count())
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.event.pdf.all', ['eventId' => $event->id]) }}" target="_blank">
                                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i> Download PDFs
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if ($registrations->isEmpty())
                        <div class="reg-empty-state">
                            <i class="bi bi-inbox"></i>
                            <p class="fw-semibold text-secondary">No registrations found</p>
                            <p class="small">Try another event or clear the participant search.</p>
                        </div>
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
                                    <h6 class="reg-group-title">{{ $groupLabel }}</h6>

                                    <!-- Competition -->
                                    @if ($isCompetition)
                                        @foreach ($weightRanges as $rangeLabel => $condition)
                                            @php
                                                $subRegs = $groupRegs->filter(function ($reg) use ($condition, $getField) {
                                                    $w = (float) $getField($reg->submitted_data, 'weight');
                                                    return $condition($w);
                                                });
                                            @endphp

                                            @if ($subRegs->isNotEmpty())
                                                <div class="ps-1 mb-3">
                                                    <span class="badge bg-danger mb-2">{{ $rangeLabel }}</span>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm align-middle reg-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Registration ID</th>
                                                                    <th>Student Name</th>
                                                                    <th>Class</th>
                                                                    <th>Weight (kg)</th>
                                                                    <th>Payment Status</th>
                                                                    <th>Amount</th>
                                                                    <th>Submitted At</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($subRegs as $reg)
                                                                    <tr>
                                                                        <td><code class="small">{{ $reg->registration_code }}</code></td>
                                                                        <td class="fw-semibold">{{ $getField($reg->submitted_data, 'name') }}</td>
                                                                        <td>{{ $getField($reg->submitted_data, 'class') }}</td>
                                                                        <td>{{ $getField($reg->submitted_data, 'weight') }}</td>
                                                                        <td><span class="badge bg-success">Paid</span></td>
                                                                        <td><strong>₹{{ number_format($reg->amount ?? 0, 2) }}</strong></td>
                                                                        <td class="text-muted small">{{ $reg->created_at->format('d M Y h:i A') }}</td>
                                                                        <td>
                                                                            <div class="btn-group reg-action-group" role="group">
                                                                                <a href="{{ route('admin.certificate.layout', $reg->id) }}" class="btn btn-sm btn-success" target="_blank" title="Certificate"><i class="bi bi-award"></i></a>
                                                                                <a href="{{ route('admin.registration.viewpdf', ['registrationId' => $reg->id]) }}" class="btn btn-sm btn-primary" title="View"><i class="bi bi-eye"></i></a>
                                                                                <a href="{{ route('registration.download.pdf', $reg->id) }}" class="btn btn-sm btn-info text-white" title="Download"><i class="bi bi-download"></i></a>
                                                                                <a href="{{ route('admin.registrations.edit', $reg->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach

                                    <!-- Kyu / Normal -->
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm align-middle reg-table">
                                                <thead>
                                                    <tr>
                                                        <th>Registration ID</th>
                                                        <th>Student Name</th>
                                                        <th>Class</th>
                                                        <th>Payment Status</th>
                                                        <th>Amount</th>
                                                        <th>Submitted At</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($groupRegs as $reg)
                                                        <tr>
                                                            <td><code class="small">{{ $reg->registration_code }}</code></td>
                                                            <td class="fw-semibold">{{ $getField($reg->submitted_data, 'name') }}</td>
                                                            <td>{{ $getField($reg->submitted_data, 'class') }}</td>
                                                            <td><span class="badge bg-success">Paid</span></td>
                                                            <td><strong>₹{{ number_format($reg->amount ?? 0, 2) }}</strong></td>
                                                            <td class="text-muted small">{{ $reg->created_at->format('d M Y h:i A') }}</td>
                                                            <td>
                                                                <div class="btn-group reg-action-group" role="group">
                                                                    <a href="{{ route('admin.certificate.layout', $reg->id) }}" class="btn btn-sm btn-success" target="_blank" title="Certificate"><i class="bi bi-award"></i></a>
                                                                    <a href="{{ route('admin.registration.viewpdf', ['registrationId' => $reg->id]) }}" class="btn btn-sm btn-primary" title="View"><i class="bi bi-eye"></i></a>
                                                                    <a href="{{ route('registration.download.pdf', $reg->id) }}" class="btn btn-sm btn-info text-white" title="Download"><i class="bi bi-download"></i></a>
                                                                    <a href="{{ route('admin.registrations.edit', $reg->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                                                </div>
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
            <div class="reg-empty-state border rounded bg-white">
                <i class="bi bi-calendar-x"></i>
                <p class="fw-semibold text-secondary">No events found</p>
                <p class="small">Create or activate an academy event to see registrations here.</p>
            </div>
        @endforelse
    </div>
</main>

@include('admin.layout.footer')
