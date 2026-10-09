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

    .form-control, .form-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    .form-control::placeholder {
        color: #999;
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

    .current-image {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }

    .current-image img {
        max-width: 100%;
        max-height: 200px;
        border: 1px solid #ddd;
        border-radius: 3px;
        padding: 5px;
        background: white;
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
            <h1 class="page-title">Edit Certificate Template</h1>
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
                    <form action="{{ route('admin.certificates.templates.update', $template->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group" id="belt_name_group" style="display: none;">
                            <label for="belt_name" class="form-label">
                                Belt <span class="required">*</span>
                            </label>
                            <select class="form-control @error('belt_name') is-invalid @enderror"
                                    id="belt_name"
                                    name="belt_name">
                                <option value="">Select belt</option>
                                @foreach ($beltNames as $beltName)
                                    <option value="{{ $beltName }}" {{ $selectedBelt === $beltName ? 'selected' : '' }}>
                                        {{ $beltName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('belt_name')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">This template is used when the Excel Next Belt is this belt.</div>
                        </div>

                        <div class="form-group" id="name_group">
                            <label for="name" class="form-label">
                                Template Name <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $template->name) }}" 
                                   placeholder="e.g., Sports Certificate, Academic Award">
                            @error('name')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">Shows on: template list in admin. Used for competition certificates.</div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Optional description of this template">{{ old('description', $template->description) }}</textarea>
                            @error('description')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="certificate_type" class="form-label">
                                Certificate Type <span class="required">*</span>
                            </label>
                            <select class="form-select @error('certificate_type') is-invalid @enderror" 
                                    id="certificate_type" 
                                    name="certificate_type" 
                                    required>
                                <option value="">Select Type</option>
                                <option value="belt" {{ old('certificate_type', $template->certificate_type) === 'belt' ? 'selected' : '' }}>Belt Certificate</option>
                                <option value="competition" {{ old('certificate_type', $template->certificate_type) === 'competition' ? 'selected' : '' }}>Competition Certificate</option>
                            </select>
                            @error('certificate_type')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="background_image" class="form-label">
                                Background Image
                            </label>
                            @if($template->background_image)
                                @php
                                    $imageExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($template->background_image);
                                    $imageUrl = $imageExists ? asset('storage/' . $template->background_image) : null;
                                @endphp
                                @if($imageUrl)
                                    <div class="current-image">
                                        <strong style="display: block; margin-bottom: 8px; font-size: 12px;">Current Image:</strong>
                                        <img src="{{ $imageUrl }}" 
                                             alt="Current template background"
                                             onerror="this.style.display='none';">
                                    </div>
                                @endif
                            @endif
                            <input type="file" 
                                   class="form-control @error('background_image') is-invalid @enderror" 
                                   id="background_image" 
                                   name="background_image" 
                                   accept="image/jpeg,image/jpg">
                            @error('background_image')
                                <div class="text-danger" style="font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                            <div class="helper-text">Shows on: certificate PDF as the full background design. Upload a new JPG (max 5MB) to replace; leave empty to keep the current image.</div>
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
                        <div class="requirements-title">Template Requirements:</div>
                        <ul class="requirements-list">
                            <li><strong>Format:</strong> JPG/JPEG only</li>
                            <li><strong>Size:</strong> Maximum 5MB</li>
                            <li><strong>Recommended:</strong>
                                <ul>
                                    <li>Standard certificate size (8.5" x 11" or A4)</li>
                                    <li>High resolution (300 DPI recommended)</li>
                                    <li>Background image with space for text overlay</li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                    <div class="tip-box">
                        <strong>Tip:</strong>
                        <p>The template will be used as the background for all certificates. Text will be overlaid on top of this image, so ensure the background has appropriate contrast for text readability.</p>
                    </div>

                    <div class="note-box">
                        <strong>Note:</strong>
                        <p>Once uploaded, templates can be selected when creating certificate batches. You can upload multiple templates for different certificate types.</p>
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
        document.getElementById('belt_name_group').style.display = isBelt ? '' : 'none';
        document.getElementById('name_group').style.display = isBelt ? 'none' : '';
        document.getElementById('belt_name').required = isBelt;
        document.getElementById('name').required = !isBelt;
    }
    document.getElementById('certificate_type').addEventListener('change', toggleTemplateNameFields);
    toggleTemplateNameFields();
</script>

@include('admin.layout.footer')
