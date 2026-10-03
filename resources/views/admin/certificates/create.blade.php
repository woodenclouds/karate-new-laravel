@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

@php
use Illuminate\Support\Facades\Storage;
@endphp

<style>
    .create-certificate-page {
        background: #f5f5f5;
        min-height: 100vh;
        padding: 30px 0;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .back-btn {
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.3s;
    }

    .back-btn:hover {
        background: #5c636a;
        color: white;
    }

    .create-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-top: 20px;
    }

    .form-card, .instructions-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 0;
        overflow: hidden;
    }

    .section-header {
        background: #0d6efd;
        color: white;
        padding: 15px 20px;
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }

    .form-body {
        padding: 25px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-label .required {
        color: #dc3545;
    }

    .form-control, select.form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    .form-control:focus, select.form-control:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    .form-control::placeholder {
        color: #999;
    }

    input[type="date"].form-control {
        position: relative;
    }

    input[type="file"].form-control {
        padding: 8px;
        cursor: pointer;
    }

    .helper-text {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    .template-selection {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .template-item {
        position: relative;
        border: 2px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s;
        background: white;
    }

    .template-item:hover {
        border-color: #0d6efd;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
    }

    .template-item.selected {
        border-color: #0d6efd;
        border-width: 3px;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }

    .template-item input[type="radio"] {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 20px;
        height: 20px;
        cursor: pointer;
        z-index: 10;
    }

    .template-preview-img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        background: #f9f9f9;
    }

    .template-name-label {
        padding: 10px;
        text-align: center;
        font-weight: 500;
        font-size: 14px;
        color: #333;
        background: #f9f9f9;
    }

    .template-instruction {
        font-size: 12px;
        color: #666;
        margin-top: 10px;
        font-style: italic;
    }

    .generate-btn {
        background: #28a745;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 5px;
        font-weight: 600;
        font-size: 16px;
        width: 100%;
        cursor: pointer;
        transition: background 0.3s;
        margin-top: 20px;
    }

    .generate-btn:hover {
        background: #218838;
    }

    .instructions-header {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        padding: 0 25px;
        padding-top: 25px;
    }

    .instructions-body {
        padding: 0 25px 25px 25px;
    }

    .excel-format-section {
        margin-bottom: 25px;
    }

    .excel-format-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
        font-size: 14px;
    }

    .excel-columns-list {
        list-style: none;
        padding: 0;
        margin: 0;
        background: #f9f9f9;
        padding: 15px;
        border-radius: 5px;
    }

    .excel-columns-list li {
        padding: 6px 0;
        padding-left: 20px;
        position: relative;
        color: #555;
        font-size: 13px;
        border-bottom: 1px solid #eee;
    }

    .excel-columns-list li:last-child {
        border-bottom: none;
    }

    .excel-columns-list li:before {
        content: counter(item);
        counter-increment: item;
        position: absolute;
        left: 0;
        color: #0d6efd;
        font-weight: bold;
        font-size: 12px;
    }

    .excel-columns-list {
        counter-reset: item;
    }

    .important-tag {
        background: #ffc107;
        color: #000;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 5px;
    }

    .certificate-types-box, .tip-box, .note-box {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }

    .certificate-types-box {
        background: #d4edda;
        border-left: 4px solid #28a745;
    }

    .tip-box {
        background: #e7f3ff;
        border-left: 4px solid #0d6efd;
    }

    .note-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
    }

    .certificate-types-box strong, .tip-box strong, .note-box strong {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-size: 14px;
    }

    .certificate-types-box ul, .tip-box p, .note-box p {
        margin: 0;
        color: #555;
        font-size: 13px;
        line-height: 1.6;
    }

    .certificate-types-box ul {
        list-style: none;
        padding-left: 0;
    }

    .certificate-types-box ul li {
        padding: 4px 0;
        padding-left: 20px;
        position: relative;
    }

    .certificate-types-box ul li:before {
        content: "•";
        position: absolute;
        left: 0;
        color: #28a745;
        font-weight: bold;
    }

    @media (max-width: 768px) {
        .create-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="page-content create-certificate-page">
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Create Certificate Batch</h1>
            <a href="{{ route('admin.certificates.index') }}" class="back-btn">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('cert_import_skipped'))
            <div class="alert alert-warning">
                <strong>Skipped rows ({{ count(session('cert_import_skipped')) }}):</strong>
                <ul class="mb-0 mt-2" style="max-height: 180px; overflow-y: auto;">
                    @foreach(session('cert_import_skipped') as $skip)
                        <li>
                            Row/ID {{ $skip['row'] ?? '—' }}
                            — {{ $skip['name'] ?? '—' }}:
                            {{ $skip['reason'] ?? '—' }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="create-container">
            <!-- Left Column - Form -->
            <div class="form-card">
                <div class="section-header">Upload Certificate Data</div>
                <div class="form-body">
                    <form action="{{ route('admin.certificates.store') }}" method="POST" enctype="multipart/form-data" id="certificateForm">
                        @csrf

                        <div class="form-group">
                            <label for="event_title" class="form-label">
                                Event Title <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('event_title') is-invalid @enderror" 
                                   id="event_title" 
                                   name="event_title" 
                                   value="{{ old('event_title') }}" 
                                   placeholder="e.g., THRISSUR MEET"
                                   required>
                            @error('event_title')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">This will appear in the green banner at the top.</div>
                        </div>

                        <div class="form-group">
                            <label for="event_date" class="form-label">
                                Event Date <span class="required">*</span>
                            </label>
                            <div style="position: relative;">
                                <input type="date" 
                                       class="form-control @error('event_date') is-invalid @enderror" 
                                       id="event_date" 
                                       name="event_date" 
                                       value="{{ old('event_date') }}" 
                                       required>
                                <i class="bi bi-calendar" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #999;"></i>
                            </div>
                            @error('event_date')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">Date when the event was conducted.</div>
                        </div>

                        <div class="form-group">
                            <label for="event_id" class="form-label">
                                Event <span class="required">*</span>
                            </label>
                            <select class="form-control @error('event_id') is-invalid @enderror"
                                    id="event_id"
                                    name="event_id"
                                    required>
                                <option value="">Select Event</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}"
                                            data-venue="{{ $event->venue }}"
                                            {{ (string) old('event_id') === (string) $event->id ? 'selected' : '' }}>
                                        {{ $event->title }}@if($event->event_date) — {{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('event_id')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">The certificate uses this event’s venue.</div>
                        </div>

                        <div class="form-group">
                            <label for="venue_display" class="form-label">Venue</label>
                            <input type="text"
                                   class="form-control"
                                   id="venue_display"
                                   value=""
                                   readonly>
                            <div class="helper-text">Same spelling as the event. To change it, edit the event.</div>
                        </div>

                        <div class="form-group">
                            <label for="certificate_type" class="form-label">
                                Certificate Type <span class="required">*</span>
                            </label>
                            <select class="form-control @error('certificate_type') is-invalid @enderror" 
                                    id="certificate_type" 
                                    name="certificate_type" 
                                    required>
                                <option value="">Select Type</option>
                                <option value="belt" {{ old('certificate_type', 'belt') === 'belt' ? 'selected' : '' }}>Belt Certificate</option>
                                <option value="competition" {{ old('certificate_type') === 'competition' ? 'selected' : '' }}>Competition Certificate</option>
                            </select>
                            @error('certificate_type')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">Select the type of certificate to generate.</div>
                        </div>

                        <p class="helper-text text-info" id="belt_template_helper" style="display: none;">For Belt events, the certificate template is chosen automatically from each row’s <strong>Next Belt</strong> column in the Excel (Yellow → yellow template, Orange → orange template, etc.).</p>

                        <div class="form-group" id="template_selection_group" style="display: none;">
                            <label class="form-label">
                                Select Certificate Template <span class="required">*</span>
                            </label>
                            <input type="hidden" name="template_id" id="selected_template_id" value="{{ old('template_id') }}">
                            <div class="template-selection">
                                @forelse($templates->where('certificate_type', 'competition') as $template)
                                    @php
                                        $imageUrl = $template->background_image && Storage::disk('public')->exists($template->background_image) 
                                            ? asset('storage/' . $template->background_image) 
                                            : null;
                                        $isSelected = old('template_id') == $template->id;
                                    @endphp
                                    <div class="template-item {{ $isSelected ? 'selected' : '' }}" 
                                         data-template-id="{{ $template->id }}">
                                        <input type="radio" 
                                               name="template_radio" 
                                               value="{{ $template->id }}" 
                                               {{ $isSelected ? 'checked' : '' }}
                                               style="display: none;">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" 
                                                 alt="{{ $template->name }}" 
                                                 class="template-preview-img"
                                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'250\'%3E%3Crect fill=\'%23f0f0f0\' width=\'200\' height=\'250\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' fill=\'%23999\' font-family=\'Arial\' font-size=\'14\'%3ENo Preview%3C/text%3E%3C/svg%3E';">
                                        @else
                                            <div class="template-preview-img" style="display: flex; align-items: center; justify-content: center; background: #f9f9f9; color: #999;">
                                                <i class="bi bi-image" style="font-size: 48px; opacity: 0.3;"></i>
                                            </div>
                                        @endif
                                        <div class="template-name-label">{{ $template->name }}</div>
                                    </div>
                                @empty
                                    <div style="grid-column: 1 / -1; padding: 20px; text-align: center; color: #999;">
                                        No competition templates available. <a href="{{ route('admin.certificates.templates.create') }}">Create a competition template first</a>.
                                    </div>
                                @endforelse
                            </div>
                            <div class="template-instruction">Click on a template to select it. The selected template will be highlighted.</div>
                            @error('template_id')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="excel_file" class="form-label">
                                Excel File with Participant Data <span class="required">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('excel_file') is-invalid @enderror" 
                                   id="excel_file" 
                                   name="excel_file" 
                                   accept=".xlsx,.csv"
                                   required>
                            @error('excel_file')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">Upload Excel file with participant details (XLSX or CSV)</div>
                        </div>

                        <button type="submit" class="generate-btn">
                            <i class="bi bi-check-circle"></i> Generate Certificates
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column - Instructions -->
            <div class="instructions-card">
                <div class="instructions-header">Instructions</div>
                <div class="instructions-body">
                    <div class="excel-format-section">
                        <div class="excel-format-title">Excel File Format:</div>
                        <ol class="excel-columns-list" style="counter-reset: item;">
                            <li><strong>Registration ID</strong> - Unique ID (used for Reg. No on certificate)</li>
                            <li><strong>Name</strong> - Participant name (required, used for NAME on certificate)</li>
                            <li><strong>Class</strong> - Age group/Class</li>
                            <li><strong>School</strong> - School name (not printed as the certificate venue)</li>
                            <li><strong>Current Belt</strong> (Belt events) - e.g. Yellow, Orange</li>
                            <li><strong>Next Belt</strong> (Belt events) - <span class="important-tag">Required</span> per row; used to pick template (Yellow, Orange, Green, Blue, Purple, Brown)</li>
                            <li><strong>Participation</strong> - Event type</li>
                            <li><strong>Gender</strong> - Boys/Girls</li>
                            <li><strong>Payment Status</strong> - paid/unpaid</li>
                            <li><strong>Amount Paid</strong> - Fee amount</li>
                            <li><strong>Submitted At</strong> - Registration date (used for DATE OF REG on certificate)</li>
                            <li><strong>Rank</strong> - Achievement/Position <span class="important-tag">Important!</span></li>
                            <li><strong>Place</strong> (Optional) - Ignored. The certificate venue always comes from the selected event.</li>
                        </ol>
                        <p style="font-size: 12px; color: #666; margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 3px;">
                            <strong>Note:</strong> For certificates, columns used include Registration ID, Name, and Submitted At. The venue is taken from the selected event, not from School or Place. For <strong>Belt events</strong>, each row must have <strong>Next Belt</strong>; the certificate template is chosen automatically from that value.
                        </p>
                    </div>

                    <div class="certificate-types-box">
                        <strong>Certificate Types:</strong>
                        <ul>
                            <li>If Rank column is filled → Shows "MERIT / PARTICIPATION" with ranking details</li>
                            <li>If Rank is empty → Shows "PARTICIPATION" only</li>
                        </ul>
                    </div>

                    <div class="tip-box">
                        <strong>Tip:</strong>
                        <p>Export registrations from any event, add a "Rank" column at the end with values like "1st Place", "Gold Medal", "2nd Runner Up", etc., then upload it here!</p>
                    </div>

                    <div class="note-box">
                        <strong>Note:</strong>
                        <p>Certificate generation may take a few moments. For <strong>Competition</strong> events, the selected template is used for all certificates. For <strong>Belt</strong> events, the template is chosen per participant from the Excel <strong>Next Belt</strong> column.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Template selection
    const templateItems = document.querySelectorAll('.template-item');
    const hiddenInput = document.getElementById('selected_template_id');
    
    templateItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove selected class from all items
            templateItems.forEach(t => t.classList.remove('selected'));
            
            // Add selected class to clicked item
            this.classList.add('selected');
            
            // Update hidden input and radio button
            const templateId = this.getAttribute('data-template-id');
            hiddenInput.value = templateId;
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });

    function toggleTemplateSection() {
        const certType = document.getElementById('certificate_type').value;
        const group = document.getElementById('template_selection_group');
        const beltHelper = document.getElementById('belt_template_helper');
        if (certType === 'competition') {
            if (group) group.style.display = 'block';
            if (beltHelper) beltHelper.style.display = 'none';
            hiddenInput.setAttribute('required', 'required');
        } else {
            if (group) group.style.display = 'none';
            if (beltHelper) beltHelper.style.display = 'block';
            hiddenInput.removeAttribute('required');
            hiddenInput.value = '';
        }
    }
    document.getElementById('certificate_type').addEventListener('change', toggleTemplateSection);
    toggleTemplateSection();

    const eventSelect = document.getElementById('event_id');
    const venueDisplay = document.getElementById('venue_display');
    function syncVenueFromEvent() {
        if (!eventSelect || !venueDisplay || !eventSelect.value) return;
        const option = eventSelect.options[eventSelect.selectedIndex];
        venueDisplay.value = option ? (option.getAttribute('data-venue') || '') : '';
    }
    if (eventSelect) {
        eventSelect.addEventListener('change', syncVenueFromEvent);
        syncVenueFromEvent();
    }

    const form = document.getElementById('certificateForm');
    form.addEventListener('submit', function(e) {
        const certType = document.getElementById('certificate_type').value;
        if (certType !== 'belt' && !hiddenInput.value) {
            e.preventDefault();
            alert('Please select a certificate template.');
            return false;
        }
    });
});
</script>

@include('admin.layout.footer')
