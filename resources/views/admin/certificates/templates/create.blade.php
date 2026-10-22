@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

<style>
    .upload-template-page {
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

    .upload-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
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

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
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

    .save-btn {
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
        margin-top: 10px;
    }

    .save-btn:hover {
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

    .requirements-section {
        margin-bottom: 25px;
    }

    .requirements-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
        font-size: 14px;
    }

    .requirements-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .requirements-list li {
        padding: 8px 0;
        padding-left: 20px;
        position: relative;
        color: #555;
        font-size: 14px;
    }

    .requirements-list li:before {
        content: "•";
        position: absolute;
        left: 0;
        color: #0d6efd;
        font-weight: bold;
    }

    .requirements-list ul {
        list-style: none;
        padding-left: 20px;
        margin-top: 5px;
    }

    .requirements-list ul li:before {
        content: "–";
        color: #666;
    }

    .tip-box, .note-box {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }

    .tip-box {
        background: #e7f3ff;
        border-left: 4px solid #0d6efd;
    }

    .note-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
    }

    .tip-box strong, .note-box strong {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-size: 14px;
    }

    .tip-box p, .note-box p {
        margin: 0;
        color: #555;
        font-size: 13px;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .upload-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="page-content upload-template-page">
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Upload Certificate Template</h1>
            <a href="{{ route('admin.certificates.templates.index') }}" class="back-btn">
                <i class="bi bi-arrow-left"></i> Back to Templates
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="upload-container">
            <!-- Left Column - Form -->
            <div class="form-card">
                <div class="section-header">Upload Background Image</div>
                <div class="form-body">
                    <form action="{{ route('admin.certificates.templates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group" id="belt_name_group" style="display: none;">
                            <label for="belt_name" class="form-label">
                                Belt <span class="required">*</span>
                            </label>
                            <select class="form-control @error('belt_name') is-invalid @enderror"
                                    id="belt_name"
                                    name="belt_name">
                                <option value="">Select belt</option>
                                @foreach ($beltNames as $beltName)
                                    <option value="{{ $beltName }}" {{ ($selectedBelt ?? old('belt_name')) === $beltName ? 'selected' : '' }}>
                                        {{ $beltName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('belt_name')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">This template is used when the Excel Next Belt matches this belt.</div>
                        </div>

                        <div class="form-group" id="placement_name_group" style="display: none;">
                            <label for="placement_name" class="form-label">
                                Result / Placement <span class="required">*</span>
                            </label>
                            <select class="form-control @error('placement_name') is-invalid @enderror"
                                    id="placement_name"
                                    name="placement_name">
                                <option value="">Select placement</option>
                                @foreach ($placementNames as $pName)
                                    <option value="{{ $pName }}" {{ ($selectedPlacement ?? old('placement_name')) === $pName ? 'selected' : '' }}>
                                        {{ $pName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('placement_name')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">This template is picked automatically when the Excel Result/Placement column matches this placement.</div>
                        </div>

                        <div class="form-group" id="name_group">
                            <label for="name" class="form-label">
                                Template Name <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="e.g., General Competition Certificate">
                            @error('name')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">Shows on: template list in admin. Used for general competition certificates.</div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Optional description of this template">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
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
                                <option value="belt" {{ old('certificate_type') === 'belt' ? 'selected' : '' }}>Belt Certificate</option>
                                <option value="intro_school" {{ old('certificate_type') === 'intro_school' ? 'selected' : '' }}>Intro School (1st–3rd + Participation)</option>
                                <option value="championship" {{ old('certificate_type') === 'championship' ? 'selected' : '' }}>Championship (1st–3rd + Participation)</option>
                                <option value="competition" {{ old('certificate_type') === 'competition' ? 'selected' : '' }}>Competition Certificate (Single template)</option>
                            </select>
                            @error('certificate_type')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="background_image" class="form-label">
                                Background Image <span class="required">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('background_image') is-invalid @enderror" 
                                   id="background_image" 
                                   name="background_image" 
                                   accept="image/jpeg,image/jpg,image/png"
                                   required>
                            @error('background_image')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">Upload the high-resolution background design. Dimensions: A4 portrait (21cm × 29.7cm) or Belt size (24cm × 33cm). Max 10MB.</div>
                        </div>

                        <button type="submit" class="save-btn">
                            <i class="bi bi-check-circle"></i> Save Template
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column - Instructions -->
            <div class="instructions-card">
                <div class="instructions-header">Instructions</div>
                <div class="instructions-body">
                    <div class="requirements-section">
                        <div class="requirements-title">Template Types:</div>
                        <ul class="requirements-list">
                            <li><strong>Belt Certificate:</strong> Upload 1 template per belt (Yellow, Orange, etc.). System auto-picks by participant's Next Belt.</li>
                            <li><strong>Intro School:</strong> Upload 4 templates (1st Place, 2nd Place, 3rd Place, Participation). System auto-picks by Excel Result/Placement.</li>
                            <li><strong>Championship:</strong> Upload 4 templates (1st Place, 2nd Place, 3rd Place, Participation). System auto-picks by Excel Result/Placement.</li>
                            <li><strong>Competition:</strong> Upload a custom template name and select it manually when creating the batch.</li>
                        </ul>
                    </div>

                    <div class="tip-box">
                        <strong>Auto Template Matching:</strong>
                        <p>For Belt, Intro School, and Championship, you only upload the background templates once here. When generating certificates, the system matches each participant's row automatically.</p>
                    </div>

                    <div class="note-box">
                        <strong>Resolution & Quality:</strong>
                        <p>Recommended 300 DPI (JPG/PNG). The system will overlay text (Name, Category, Date, Venue, etc.) precisely onto the designated lines.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function toggleTemplateNameFields() {
        var type = document.getElementById('certificate_type').value;
        var isBelt = type === 'belt';
        var isPlacement = (type === 'intro_school' || type === 'championship');
        var isCompetition = type === 'competition';

        document.getElementById('belt_name_group').style.display = isBelt ? '' : 'none';
        document.getElementById('placement_name_group').style.display = isPlacement ? '' : 'none';
        document.getElementById('name_group').style.display = isCompetition ? '' : 'none';

        var beltSelect = document.getElementById('belt_name');
        var placementSelect = document.getElementById('placement_name');
        var nameInput = document.getElementById('name');

        if (beltSelect) beltSelect.required = isBelt;
        if (placementSelect) placementSelect.required = isPlacement;
        if (nameInput) nameInput.required = isCompetition;
    }
    document.getElementById('certificate_type').addEventListener('change', toggleTemplateNameFields);
    toggleTemplateNameFields();
</script>

@include('admin.layout.footer')
