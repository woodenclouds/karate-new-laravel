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

    .update-btn {
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

    .update-btn:hover {
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

    .info-box {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        background: #e7f3ff;
        border-left: 4px solid #0d6efd;
    }

    .info-box strong {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-size: 14px;
    }

    .info-box p {
        margin: 0;
        color: #555;
        font-size: 13px;
        line-height: 1.6;
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
            <h1 class="page-title">Edit Certificate Event</h1>
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

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="create-container">
            <!-- Left Column - Form -->
            <div class="form-card">
                <div class="section-header">Edit Certificate Event Details</div>
                <div class="form-body">
                    <form action="{{ route('admin.certificates.update', $certificate->id) }}" method="POST" id="certificateForm">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="event_title" class="form-label">
                                Event Title <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('event_title') is-invalid @enderror" 
                                   id="event_title" 
                                   name="event_title" 
                                   value="{{ old('event_title', $certificate->event_title) }}" 
                                   placeholder="e.g., THRISSUR MEET"
                                   required>
                            @error('event_title')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">Shows on: Certificates admin list and batch details page. Not printed on the PDF itself.</div>
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
                                       value="{{ old('event_date', $certificate->event_date ? \Carbon\Carbon::parse($certificate->event_date)->format('Y-m-d') : '') }}" 
                                       required>
                                <i class="bi bi-calendar" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #999;"></i>
                            </div>
                            @error('event_date')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">Shows on: Certificates admin list, and as DATE OF REG on the PDF only if Excel has no Submitted At date.</div>
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
                                            {{ (string) old('event_id', $certificate->event_id) === (string) $event->id ? 'selected' : '' }}>
                                        {{ $event->title }}@if($event->event_date) — {{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('event_id')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">Links this batch to an event so the venue can be copied for the certificate PLACE line.</div>
                        </div>

                        <div class="form-group">
                            <label for="venue_display" class="form-label">Venue</label>
                            <input type="text"
                                   class="form-control"
                                   id="venue_display"
                                   value="{{ $certificate->certificateVenue() }}"
                                   readonly>
                            <div class="helper-text">Shows on: certificate PDF <strong>PLACE</strong> line (exact spelling). To change it, edit the event in Events.</div>
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
                                <option value="belt" {{ old('certificate_type', $certificate->certificate_type) === 'belt' ? 'selected' : '' }}>Belt Certificate</option>
                                <option value="competition" {{ old('certificate_type', $certificate->certificate_type) === 'competition' ? 'selected' : '' }}>Competition Certificate</option>
                            </select>
                            @error('certificate_type')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">Shows on: PDF layout — Belt prints REG. NO; Competition prints CATEGORY instead.</div>
                        </div>

                        <p class="helper-text text-info" id="belt_template_helper" style="display: {{ $certificate->certificate_type === 'belt' ? 'block' : 'none' }};">For Belt events, the certificate template is chosen per participant from the Excel <strong>Next Belt</strong> column.</p>

                        <div class="form-group" id="template_selection_group" style="display: {{ $certificate->certificate_type === 'competition' ? 'block' : 'none' }};">
                            <label class="form-label">
                                Select Certificate Template <span class="required">*</span>
                            </label>
                            <input type="hidden" name="template_id" id="selected_template_id" value="{{ old('template_id', $certificate->template_id) }}" {{ $certificate->certificate_type === 'competition' ? 'required' : '' }}>
                            <div class="template-selection">
                                @forelse($templates->where('certificate_type', 'competition') as $template)
                                    @php
                                        $imageUrl = $template->background_image && Storage::disk('public')->exists($template->background_image) 
                                            ? asset('storage/' . $template->background_image) 
                                            : null;
                                        $isSelected = old('template_id', $certificate->template_id) == $template->id;
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
                            <div class="template-instruction">Shows on: background image of every competition certificate PDF in this batch. Click a template to select it.</div>
                            @error('template_id')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="update-btn">
                            <i class="bi bi-check-circle"></i> Update Certificate Event
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column - Instructions -->
            <div class="instructions-card">
                <div class="instructions-header">Information</div>
                <div class="instructions-body">
                    <div class="info-box">
                        <strong>Current Event Details:</strong>
                        <p>
                            <strong>Event:</strong> {{ $certificate->event_title }}<br>
                            <strong>Date:</strong> {{ \Carbon\Carbon::parse($certificate->event_date)->format('d M Y') }}<br>
                            <strong>Venue:</strong> {{ $certificate->certificateVenue() }}<br>
                            <strong>Type:</strong> {{ ucfirst($certificate->certificate_type) }}<br>
                            <strong>Participants:</strong> {{ $certificate->certificate_count }}
                        </p>
                    </div>

                    <div class="info-box">
                        <strong>Where updates reflect:</strong>
                        <p>
                            <strong>Event Title / Date</strong> → Certificates admin list &amp; details<br>
                            <strong>Venue</strong> → PDF PLACE line (from Events)<br>
                            <strong>Certificate Type / Template</strong> → PDF layout &amp; background
                        </p>
                    </div>

                    <div class="info-box">
                        <strong>Note:</strong>
                        <p>You can update the event details, certificate type, and template. The participant data (from Excel) cannot be changed here. To update participants, create a new certificate event.</p>
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

