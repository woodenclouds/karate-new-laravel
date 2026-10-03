@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

@php
use Illuminate\Support\Facades\Storage;
@endphp

<style>
    .templates-container {
        background: #f5f5f5;
        min-height: 100vh;
        padding: 20px 0;
    }

    .templates-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .templates-title {
        font-size: 24px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .upload-btn {
        background: #0d6efd;
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

    .upload-btn:hover {
        background: #0b5ed7;
        color: white;
    }

    .templates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }

    .template-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .template-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .template-card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        background: #fff;
    }

    .template-name {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .template-preview {
        width: 100%;
        height: 400px;
        background: #f9f9f9;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .template-preview img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: white;
    }

    .template-preview-placeholder {
        color: #999;
        font-size: 14px;
        text-align: center;
        padding: 20px;
    }

    .template-actions {
        padding: 15px 20px;
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .delete-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 5px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .delete-btn:hover {
        background: #bb2d3b;
        color: white;
    }

    .edit-btn {
        background: #0dcaf0;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 5px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .edit-btn:hover {
        background: #0aa2c0;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-state h3 {
        margin-bottom: 10px;
        color: #333;
    }
</style>

<main class="page-content templates-container">
    <div class="container">
        <div class="templates-header">
            <h2 class="templates-title">Certificate Templates</h2>
            <a href="{{ route('admin.certificates.templates.create') }}" class="upload-btn">
                <i class="bi bi-upload"></i> Upload New Template
            </a>
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

        @if($templates->count() > 0)
            <div class="templates-grid">
                @foreach($templates as $template)
                    <div class="template-card">
                        <div class="template-card-header">
                            <h3 class="template-name">{{ $template->name }}</h3>
                            @if($template->description)
                                <p style="font-size: 12px; color: #666; margin: 5px 0 0 0;">{{ $template->description }}</p>
                            @endif
                            <span class="badge bg-{{ $template->certificate_type === 'belt' ? 'info' : 'success' }}" style="margin-top: 5px;">
                                {{ ucfirst($template->certificate_type) }}
                            </span>
                        </div>
                        <div class="template-preview">
                            @if($template->background_image)
                                @php
                                    // Check if file exists in storage
                                    $imageExists = Storage::disk('public')->exists($template->background_image);
                                    // Generate URL - use asset() with storage path
                                    $imageUrl = $imageExists ? asset('storage/' . $template->background_image) : null;
                                @endphp
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" 
                                         alt="{{ $template->name }} Template Preview"
                                         style="width: 100%; height: 100%; object-fit: contain; background: white;"
                                         loading="lazy"
                                         onerror="this.parentElement.innerHTML='<div class=\'template-preview-placeholder\'><i class=\'bi bi-image\' style=\'font-size: 48px; display: block; margin-bottom: 10px; opacity: 0.3;\'></i>Image failed to load<br><small style=\'font-size: 11px; color: #999;\'>Click Edit to re-upload</small></div>';">
                                @else
                                    <div class="template-preview-placeholder">
                                        <i class="bi bi-image" style="font-size: 48px; display: block; margin-bottom: 10px; opacity: 0.3;"></i>
                                        Image file missing<br>
                                        <small style="font-size: 11px; color: #999;">Click Edit below to re-upload</small>
                                    </div>
                                @endif
                            @else
                                <div class="template-preview-placeholder">
                                    <i class="bi bi-image" style="font-size: 48px; display: block; margin-bottom: 10px; opacity: 0.3;"></i>
                                    No preview available
                                </div>
                            @endif
                        </div>
                        <div class="template-actions">
                            <a href="{{ route('admin.certificates.templates.edit', $template->id) }}" 
                               class="edit-btn">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('admin.certificates.templates.destroy', $template->id) }}" 
                                  method="POST" 
                                  style="display: inline;"
                                  onsubmit="return confirm('Are you sure you want to delete this template?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <h3>No Templates Found</h3>
                <p>Get started by uploading your first certificate template.</p>
                <a href="{{ route('admin.certificates.templates.create') }}" class="upload-btn" style="margin-top: 20px;">
                    <i class="bi bi-upload"></i> Upload New Template
                </a>
            </div>
        @endif
    </div>
</main>

@include('admin.layout.footer')
