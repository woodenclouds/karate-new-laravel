@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

<main class="page-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Certificate Events</h4>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.certificates.templates.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-file-earmark-image"></i> Manage Templates
                </a>
                <a href="{{ route('admin.certificates.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Create Certificate Event
                </a>
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

        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Event Title</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Participants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificates as $certificate)
                            <tr>
                                <td>{{ $certificate->event_title }}</td>
                                <td>
                                    <span class="badge bg-{{ $certificate->certificate_type === 'belt' ? 'info' : 'success' }}">
                                        {{ ucfirst($certificate->certificate_type) }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($certificate->event_date)->format('d M Y') }}</td>
                                <td>{{ $certificate->certificateVenue() }}</td>
                                <td>{{ $certificate->certificate_count }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.certificates.show', $certificate->id) }}" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.certificates.edit', $certificate->id) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ route('admin.certificates.downloadAll', $certificate->id) }}" 
                                           class="btn btn-sm btn-success" title="Download All">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No certificate events found. 
                                    <a href="{{ route('admin.certificates.create') }}">Create one</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

@include('admin.layout.footer')

