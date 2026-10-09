@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')
<style>
    .evt-page-title {
        font-size: 1.65rem;
        letter-spacing: -0.02em;
        color: #1a1a1a;
    }
    .evt-page-subtitle {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 0.15rem;
    }
    .btn-evt-create {
        font-weight: 600;
    }
    .evt-cat-competition { background: #0d6efd; border-color: #0d6efd; color: #fff; }
    .evt-cat-camping { background: #198754; border-color: #198754; color: #fff; }
    .evt-cat-kyu { background: #fd7e14; border-color: #fd7e14; color: #fff; }
    .evt-cat-default { background: #0d6efd; border-color: #0d6efd; color: #fff; }
    .evt-cat-competition:hover,
    .evt-cat-camping:hover,
    .evt-cat-kyu:hover,
    .evt-cat-default:hover {
        filter: brightness(0.92);
        color: #fff;
    }
    .evt-filter-panel {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
        padding: 1.15rem 1.25rem;
    }
    .evt-filter-panel .form-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.35rem;
    }
    .evt-table-card {
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }
    .evt-table {
        margin-bottom: 0;
        border-color: #e9ecef;
    }
    .evt-table thead th {
        background: #f8f9fa;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        font-weight: 700;
        border-bottom-width: 1px;
        white-space: nowrap;
        vertical-align: middle;
    }
    .evt-table tbody tr:hover {
        background: #fafafa;
    }
    .evt-table td {
        font-size: 0.875rem;
        vertical-align: middle;
    }
    .evt-cat-badge {
        font-weight: 600;
        font-size: 0.72rem;
    }
    .evt-status-cell {
        min-width: 120px;
    }
    .evt-action-group .btn {
        font-weight: 600;
        font-size: 0.78rem;
        white-space: nowrap;
    }
    .evt-empty-state {
        text-align: center;
        padding: 2.75rem 1.5rem;
        color: #6c757d;
    }
    .evt-empty-state i {
        font-size: 2rem;
        color: #adb5bd;
        display: block;
        margin-bottom: 0.75rem;
    }
    .switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        margin-bottom: 0;
        vertical-align: middle;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ced4da;
        transition: 0.3s;
        border-radius: 30px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
    }
    input:checked + .slider {
        background-color: #198754;
    }
    input:checked + .slider:before {
        transform: translateX(20px);
    }
</style>

<main class="page-content">
    <div class="container py-4">

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h2 class="evt-page-title fw-bold mb-0">Events</h2>
                <p class="evt-page-subtitle mb-0">Create and manage academy competitions, camps, and grading</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                @foreach ($categories as $cat)
                    @php
                        $catKey = strtolower($cat->name ?? '');
                        $createBtnClass = str_contains($catKey, 'competition') ? 'evt-cat-competition'
                            : (str_contains($catKey, 'camp') ? 'evt-cat-camping'
                            : (str_contains($catKey, 'kyu') || str_contains($catKey, 'belt') ? 'evt-cat-kyu' : 'evt-cat-default'));
                    @endphp
                    <button type="button" class="btn btn-evt-create {{ $createBtnClass }} btn-sm"
                        onclick="openEventModalWithCategory({{ $cat->id }}, '{{ $cat->name }}')">
                        <i class="bi bi-plus-lg"></i> {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Event Filter & Search Bar -->
        <div class="evt-filter-panel mb-4">
            <form method="GET" action="{{ route('admin.event') }}" id="eventFilterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Search Event / Venue</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search title, venue..." class="form-control form-control-sm">
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select form-select-sm" onchange="document.getElementById('eventFilterForm').submit();">
                            <option value="all">-- All Categories --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label">Timeframe</label>
                        <select name="timeframe" class="form-select form-select-sm" onchange="document.getElementById('eventFilterForm').submit();">
                            <option value="all" {{ request('timeframe', 'all') === 'all' ? 'selected' : '' }}>All Events</option>
                            <option value="last" {{ request('timeframe') === 'last' ? 'selected' : '' }}>Last Events (Past)</option>
                            <option value="upcoming" {{ request('timeframe') === 'upcoming' ? 'selected' : '' }}>Upcoming Events</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm" onchange="document.getElementById('eventFilterForm').submit();">
                            <option value="all">All Statuses</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active Only</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive Only</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-3">
                        <label class="form-label">Sort</label>
                        <select name="sort" class="form-select form-select-sm" onchange="document.getElementById('eventFilterForm').submit();">
                            <option value="desc" {{ request('sort', 'desc') === 'desc' ? 'selected' : '' }}>Newest</option>
                            <option value="asc" {{ request('sort') === 'asc' ? 'selected' : '' }}>Oldest</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-dark flex-grow-1">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.event') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Event Table -->
        <div class="card evt-table-card border-0">
            <div class="card-body p-0">
                @if ($events->isEmpty())
                    <div class="evt-empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <p class="fw-semibold text-secondary mb-1">No events found</p>
                        <p class="small mb-0">Try clearing filters or create a new event above.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered evt-table align-middle">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Venue</th>
                                    <th>Fee</th>
                                    <th>Additional Fee</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($events as $event)
                                    @php
                                        $rowCat = strtolower($event->category->name ?? '');
                                        $rowBadge = str_contains($rowCat, 'competition') ? 'bg-primary'
                                            : (str_contains($rowCat, 'camp') ? 'bg-success'
                                            : (str_contains($rowCat, 'kyu') || str_contains($rowCat, 'belt') ? 'bg-warning text-dark' : 'bg-info text-dark'));
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $event->title }}</td>
                                        <td>
                                            <span class="badge evt-cat-badge rounded-pill {{ $rowBadge }}">{{ $event->category->name ?? '-' }}</span>
                                        </td>
                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}</td>
                                        <td class="text-nowrap text-muted">{{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</td>
                                        <td>{{ $event->venue }}</td>
                                        <td class="text-nowrap">{{ $event->fee ? '₹' . number_format($event->fee, 2) : 'Free' }}</td>
                                        <td class="text-nowrap">{{ $event->additional_fee ? '₹' . number_format($event->additional_fee, 2) : '—' }}</td>
                                        <td class="evt-status-cell">
                                            <div class="d-flex align-items-center gap-2">
                                                <form action="{{ route('admin.event.toggleStatus', $event->id) }}" method="POST" class="mb-0">
                                                    @csrf
                                                    @method('PATCH')
                                                    <label class="switch" title="{{ $event->is_active ? 'Active' : 'Inactive' }}">
                                                        <input type="checkbox" name="is_active" onchange="this.form.submit()"
                                                            {{ $event->is_active ? 'checked' : '' }}>
                                                        <span class="slider round"></span>
                                                    </label>
                                                </form>
                                                <span class="badge {{ $event->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $event->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap align-items-center gap-1 evt-action-group">
                                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                    data-bs-target="#editEventModal{{ $event->id }}" title="Edit">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-info text-white btn-open-event-qr"
                                                    title="Registration QR Code"
                                                    data-event-id="{{ $event->id }}"
                                                    data-event-title="{{ $event->title }}"
                                                    data-register-url="{{ route('user.form.show', $event->id) }}">
                                                    <i class="bi bi-upc-scan"></i> QR Code
                                                </button>
                                                <a href="{{ route('admin.events.registrations', $event->id) }}"
                                                    class="btn btn-sm btn-success" title="View Responses">
                                                    <i class="bi bi-eye"></i> View Responses
                                                </a>
                                                <form method="POST" action="{{ route('admin.event.delete', $event->id) }}"
                                                    class="d-inline mb-0" onsubmit="return confirm('Delete this event?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>

<!-- Event Registration QR Modal -->
<div class="modal fade" id="eventQrModal" tabindex="-1" aria-labelledby="eventQrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventQrModalLabel">Registration QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fw-semibold mb-1" id="eventQrTitle">—</p>
                <p class="small text-muted mb-3">Scan to open the public registration form.</p>
                <div class="d-flex justify-content-center mb-3">
                    <div id="eventQrCanvasWrap" class="p-3 bg-white border rounded">
                        <div id="eventQrCode"></div>
                    </div>
                </div>
                <label class="form-label small fw-bold text-muted">Registration link</label>
                <div class="input-group input-group-sm mb-2">
                    <input type="text" class="form-control" id="eventQrUrl" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="eventQrCopyBtn" title="Copy link">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <p class="small text-muted mb-0" id="eventQrCopyStatus" style="min-height: 1.25rem;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary btn-sm" id="eventQrDownloadBtn" download="event-registration-qr.png">
                    <i class="bi bi-download"></i> Download PNG
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Edit Event Modals -->
@foreach ($events as $event)
<div class="modal fade" id="editEventModal{{ $event->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.event.update', $event->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Event - {{ $event->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ $event->title }}">
                        <div class="form-text">Shows on: public Events page, registration form, payment checkout, and admin lists.</div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ $event->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Shows on: public Events page (category badge) and controls which registration form fields are used.</div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Event Date</label>
                        <input type="date" name="event_date" class="form-control"
                            value="{{ $event->event_date }}" required>
                        <div class="form-text">Shows on: public Events page, registration PDF, and admin event lists.</div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Event Time</label>
                        <input type="time" name="event_time" class="form-control"
                            value="{{ $event->event_time }}">
                        <div class="form-text">Shows on: public Events page and registration PDF.</div>
                    </div>
                    <div class="mb-3 col-12">
                        <label class="form-label">Venue</label>
                        <input type="text" name="venue" class="form-control"
                            value="{{ $event->venue }}" required>
                        <div class="form-text">Shows on: public Events page, registration PDF, and certificate <strong>PLACE</strong> line (exact spelling).</div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Registration Fee (INR)</label>
                        <input type="number" step="0.01" name="fee" class="form-control"
                            value="{{ $event->fee }}">
                        <div class="form-text">Shows on: public Events page, registration form, and Razorpay checkout amount.</div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Additional Fee (INR)</label>
                        <input type="number" step="0.01" name="additional_fee"
                            class="form-control" value="{{ $event->additional_fee }}">
                        <div class="form-text">Shows on: public Events page as Team Fee, and is added at registration when participation is team.</div>
                    </div>
                    <div class="mb-3 col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ $event->description }}</textarea>
                        <div class="form-text">Shows on: public Events page event card.</div>
                    </div>
                    <div class="mb-3 col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="editCustomizeBeltFeesCheck{{ $event->id }}" name="customize_belt_fees">
                            <label class="form-check-label" for="editCustomizeBeltFeesCheck{{ $event->id }}">
                                Customize belt fees for this event
                            </label>
                        </div>
                    </div>
                    <div class="mb-3 col-12" id="editBeltFeesOverrideSection{{ $event->id }}" style="display:none;">
                        <div class="card">
                            <div class="card-header">Belt Fees (override defaults)</div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 25%">From Belt</th>
                                                <th style="width: 25%">To Belt</th>
                                                <th style="width: 25%">Default Fee</th>
                                                <th style="width: 25%">Override Fee</th>
                                            </tr>
                                        </thead>
                                        <tbody id="editBeltFeesRows{{ $event->id }}">
                                            <tr><td colspan="4" class="text-center p-3">Loading...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-muted small p-2">Leave blank to use default fee.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Update Event</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.event.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="titleField" class="form-control">
                        <div class="form-text">Shows on: public Events page, registration form, payment checkout, and admin lists.</div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="add-event-category-select" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Shows on: public Events page (category badge) and controls which registration form fields are used.</div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Event Date</label>
                        <input type="date" name="event_date" class="form-control" required>
                        <div class="form-text">Shows on: public Events page, registration PDF, and admin event lists.</div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Event Time</label>
                        <input type="time" name="event_time" class="form-control">
                        <div class="form-text">Shows on: public Events page and registration PDF.</div>
                    </div>
                    <div class="mb-3 col-12">
                        <label class="form-label">Venue</label>
                        <input type="text" name="venue" class="form-control" required>
                        <div class="form-text">Shows on: public Events page, registration PDF, and certificate <strong>PLACE</strong> line (exact spelling).</div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Registration Fee (INR)</label>
                        <input type="number" step="0.01" name="fee" class="form-control"
                            placeholder="e.g. 250.00">
                        <div class="form-text">Shows on: public Events page, registration form, and Razorpay checkout amount.</div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Additional Fee (INR)</label>
                        <input type="number" step="0.01" name="additional_fee" class="form-control"
                            placeholder="e.g. 50.00">
                        <div class="form-text">Shows on: public Events page as Team Fee, and is added at registration when participation is team.</div>
                    </div>
                    <div class="mb-3 col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                        <div class="form-text">Shows on: public Events page event card.</div>
                    </div>
                    <div class="mb-3 col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="customizeBeltFeesCheck" name="customize_belt_fees">
                            <label class="form-check-label" for="customizeBeltFeesCheck">
                                Customize belt fees for this event
                            </label>
                        </div>
                    </div>
                    <div class="mb-3 col-12" id="beltFeesOverrideSection" style="display:none;">
                        <div class="card">
                            <div class="card-header">Belt Fees (override defaults)</div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 25%">From Belt</th>
                                                <th style="width: 25%">To Belt</th>
                                                <th style="width: 25%">Default Fee</th>
                                                <th style="width: 25%">Override Fee</th>
                                            </tr>
                                        </thead>
                                        <tbody id="beltFeesRows">
                                            <tr><td colspan="4" class="text-center p-3">Loading...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-muted small p-2">Leave blank to use default fee.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Create Event</button>
                </div>
            </div>
        </form>
    </div>
</div>



<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    function openEventModalWithCategory(categoryId, categoryName) {
        const select = document.getElementById('add-event-category-select');
        if (select) {
            select.value = categoryId;
        }
        const modal = new bootstrap.Modal(document.getElementById('addEventModal'));
        modal.show();
    }

    (function () {
        const modalEl = document.getElementById('eventQrModal');
        if (!modalEl) return;

        const titleEl = document.getElementById('eventQrTitle');
        const urlEl = document.getElementById('eventQrUrl');
        const copyBtn = document.getElementById('eventQrCopyBtn');
        const copyStatus = document.getElementById('eventQrCopyStatus');
        const downloadBtn = document.getElementById('eventQrDownloadBtn');
        const canvasWrap = document.getElementById('eventQrCanvasWrap');
        let qrInstance = null;

        function clearQr() {
            if (canvasWrap) {
                canvasWrap.innerHTML = '<div id="eventQrCode"></div>';
            }
            qrInstance = null;
        }

        function renderQr(url) {
            clearQr();
            const host = document.getElementById('eventQrCode');
            if (!host || typeof QRCode === 'undefined') return;

            qrInstance = new QRCode(host, {
                text: url,
                width: 220,
                height: 220,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });

            // Wait a tick for QRCode.js to paint img/canvas, then wire download
            setTimeout(function () {
                const img = host.querySelector('img');
                const canvas = host.querySelector('canvas');
                let dataUrl = '';
                if (img && img.src) {
                    dataUrl = img.src;
                } else if (canvas) {
                    dataUrl = canvas.toDataURL('image/png');
                }
                if (dataUrl && downloadBtn) {
                    downloadBtn.href = dataUrl;
                }
            }, 80);
        }

        function openEventQr(title, url, eventId) {
            if (titleEl) titleEl.textContent = title || 'Event registration';
            if (urlEl) urlEl.value = url || '';
            if (copyStatus) copyStatus.textContent = '';
            if (downloadBtn) {
                const safeName = (title || 'event').toString().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
                downloadBtn.download = (safeName || 'event') + '-registration-qr.png';
            }
            renderQr(url);
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }

        document.querySelectorAll('.btn-open-event-qr').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openEventQr(
                    btn.getAttribute('data-event-title'),
                    btn.getAttribute('data-register-url'),
                    btn.getAttribute('data-event-id')
                );
            });
        });

        if (copyBtn && urlEl) {
            copyBtn.addEventListener('click', async function () {
                const value = urlEl.value;
                if (!value) return;
                try {
                    await navigator.clipboard.writeText(value);
                    if (copyStatus) copyStatus.textContent = 'Link copied.';
                } catch (e) {
                    urlEl.select();
                    document.execCommand('copy');
                    if (copyStatus) copyStatus.textContent = 'Link copied.';
                }
            });
        }

        // Auto-open after creating an event
        @if (session('qr_event_id'))
            document.addEventListener('DOMContentLoaded', function () {
                openEventQr(
                    @json(session('qr_event_title')),
                    @json(session('qr_register_url')),
                    @json(session('qr_event_id'))
                );
            });
        @endif

        window.openEventQr = openEventQr;
    })();
</script>
<script>
    // Fix title requirement based on category - only if elements exist
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('add-event-category-select');
        const titleField = document.getElementById('titleField');

        if (!categorySelect || !titleField) {
            return; // Elements don't exist on this page, skip
        }

        function updateTitleRequirement() {
            if (!categorySelect.selectedIndex) return;
            
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const categoryName = selectedOption ? selectedOption.text.toLowerCase() : '';

            if (categoryName.includes('kyu')) {
                titleField.removeAttribute('required');
            } else {
                titleField.setAttribute('required', 'required');
            }
        }

        // Run on category change
        categorySelect.addEventListener('change', updateTitleRequirement);
    });
</script>
<script>
    // Handle belt fees for create modal
    document.addEventListener('DOMContentLoaded', function() {
        const customizeCheck = document.getElementById('customizeBeltFeesCheck');
        const overrideSection = document.getElementById('beltFeesOverrideSection');
        const rowsTbody = document.getElementById('beltFeesRows');

        function fetchAndRenderBeltFees() {
            if (!rowsTbody) {
                console.error('beltFeesRows element not found');
                return;
            }
            
            rowsTbody.innerHTML = '<tr><td colspan="4" class="text-center p-3">Loading...</td></tr>';
            const url = "{{ route('admin.events.belt_fees.defaults') }}";
            
            fetch(url, { 
                method: 'GET',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
                .then(async r => {
                    if (!r.ok) {
                        const errorText = await r.text();
                        console.error('Server error:', r.status, errorText);
                        throw new Error(`HTTP ${r.status}: ${errorText}`);
                    }
                    return r.json();
                })
                .then(json => {
                    console.log('Belt fees response:', json);
                    const belts = json.data || [];
                    if (!belts.length) {
                        rowsTbody.innerHTML = '<tr><td colspan="4" class="text-center p-3">No belts found in database. Please add belts first.</td></tr>';
                        return;
                    }
                    rowsTbody.innerHTML = belts.map(b => `
                        <tr>
                            <td>${b.from_belt || '-'}</td>
                            <td>${b.to_belt || '-'}</td>
                            <td><span class="text-muted">${Number(b.fees || 0).toFixed(2)}</span></td>
                            <td>
                                <input name="belt_fee[${b.id}]" type="number" step="0.01" class="form-control form-control-sm" placeholder="Leave blank to use ${Number(b.fees || 0).toFixed(2)}">
                            </td>
                        </tr>
                    `).join('');
                })
                .catch(error => {
                    console.error('Error fetching belt fees:', error);
                    rowsTbody.innerHTML = '<tr><td colspan="4" class="text-center p-3 text-danger">Failed to load belts. Please check console for details and ensure you are logged in.</td></tr>';
                });
        }

        if (customizeCheck && overrideSection && rowsTbody) {
            customizeCheck.addEventListener('change', function () {
                if (this.checked) {
                    overrideSection.style.display = '';
                    fetchAndRenderBeltFees();
                } else {
                    overrideSection.style.display = 'none';
                    rowsTbody.innerHTML = '';
                }
            });
        } else {
            console.error('Belt fee customization elements not found');
        }
    });
</script>
<script>
    // Handle belt fees for edit modals - fetch from event_belt_fees table
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[id^="editEventModal"]').forEach(modalElement => {
            const modalId = modalElement.id;
            const eventId = modalId.replace('editEventModal', '');
            const customizeCheck = document.getElementById('editCustomizeBeltFeesCheck' + eventId);
            const overrideSection = document.getElementById('editBeltFeesOverrideSection' + eventId);
            const rowsTbody = document.getElementById('editBeltFeesRows' + eventId);

            if (!customizeCheck || !overrideSection || !rowsTbody) {
                console.error('Edit modal belt fee elements not found for event:', eventId);
                return;
            }

            function fetchAndRenderEventBeltFees(eventId) {
                if (!rowsTbody) {
                    console.error('beltFeesRows element not found for event:', eventId);
                    return;
                }
                
                rowsTbody.innerHTML = '<tr><td colspan="4" class="text-center p-3">Loading...</td></tr>';
                const url = "{{ route('admin.events.belt_fees.event', ':id') }}".replace(':id', eventId);
                fetch(url, { 
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    } 
                })
                    .then(r => {
                        if (!r.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return r.json();
                    })
                    .then(json => {
                        const belts = json.data || [];
                        const hasOverrides = json.has_overrides || false;
                        
                        if (!belts.length) {
                            rowsTbody.innerHTML = '<tr><td colspan="4" class="text-center p-3">No belts found</td></tr>';
                            return;
                        }
                        
                        rowsTbody.innerHTML = belts.map(b => {
                            // Use override_fee from event_belt_fees table if it exists
                            const overrideValue = b.override_fee !== null && b.override_fee !== undefined 
                                ? Number(b.override_fee).toFixed(2) 
                                : '';
                            return `
                                <tr>
                                    <td>${b.from_belt || '-'}</td>
                                    <td>${b.to_belt || '-'}</td>
                                    <td><span class="text-muted">${Number(b.fees || 0).toFixed(2)}</span></td>
                                    <td>
                                        <input name="belt_fee[${b.id}]" type="number" step="0.01" class="form-control form-control-sm" 
                                            value="${overrideValue}" 
                                            placeholder="Leave blank to use ${Number(b.fees || 0).toFixed(2)}">
                                    </td>
                                </tr>
                            `;
                        }).join('');
                        
                        // Check the checkbox if there are existing overrides from event_belt_fees table
                        if (hasOverrides && customizeCheck) {
                            customizeCheck.checked = true;
                            if (overrideSection) {
                                overrideSection.style.display = '';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching event belt fees:', error);
                        if (rowsTbody) {
                            rowsTbody.innerHTML = '<tr><td colspan="4" class="text-center p-3 text-danger">Failed to load belts. Please try again.</td></tr>';
                        }
                    });
            }

            // Handle checkbox toggle for edit modal
            customizeCheck.addEventListener('change', function () {
                if (this.checked) {
                    if (overrideSection) {
                        overrideSection.style.display = '';
                    }
                    fetchAndRenderEventBeltFees(eventId);
                } else {
                    if (overrideSection) {
                        overrideSection.style.display = 'none';
                    }
                    if (rowsTbody) {
                        rowsTbody.innerHTML = '';
                    }
                }
            });

            // Load belt fees from event_belt_fees table when modal is shown
            modalElement.addEventListener('show.bs.modal', function () {
                fetchAndRenderEventBeltFees(eventId);
            });
        });
    });
</script>
@include('admin.layout.footer')