@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

<main class="page-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Certificate Event: {{ $certificate->event_title }}</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <a href="{{ route('admin.certificates.downloadAll', $certificate->id) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-download"></i> Download All Certificates
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @php
            $importSkipped = session('cert_import_skipped', []);
            $downloadSkipped = session('cert_download_skipped', []);
            $importStats = session('cert_import_stats');
        @endphp

        @if(!empty($importStats))
            <div class="alert alert-info">
                Imported <strong>{{ $importStats['imported'] ?? 0 }}</strong> participants
                @if(($importStats['skipped'] ?? 0) > 0)
                    — <strong>{{ $importStats['skipped'] }}</strong> Excel row(s) skipped
                @endif
            </div>
        @endif

        @if(!empty($importSkipped))
            <div class="card border-warning mb-3">
                <div class="card-header bg-warning-subtle">
                    <strong>Skipped during Excel import ({{ count($importSkipped) }})</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Row / ID</th>
                                    <th>Name</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($importSkipped as $skip)
                                    <tr>
                                        <td>{{ $skip['row'] ?? '—' }}</td>
                                        <td>{{ $skip['name'] ?? '—' }}</td>
                                        <td>{{ $skip['reason'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($downloadSkipped))
            <div class="card border-danger mb-3">
                <div class="card-header bg-danger text-white">
                    <strong>Skipped during certificate download ({{ count($downloadSkipped) }})</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Reg ID</th>
                                    <th>Name</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($downloadSkipped as $skip)
                                    <tr>
                                        <td>{{ $skip['row'] ?? '—' }}</td>
                                        <td>{{ $skip['name'] ?? '—' }}</td>
                                        <td>{{ $skip['reason'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Event Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Title:</strong> {{ $certificate->event_title }}</p>
                        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($certificate->event_date)->format('d M Y') }}</p>
                        <p><strong>Venue:</strong> {{ $certificate->certificateVenue() }}</p>
                        @php
                            $typeBadges = [
                                'belt' => ['class' => 'bg-primary', 'text' => 'Belt Certificate'],
                                'intro_school' => ['class' => 'bg-warning text-dark', 'text' => 'Intro School'],
                                'championship' => ['class' => 'bg-danger', 'text' => 'Championship'],
                                'competition' => ['class' => 'bg-success', 'text' => 'Competition'],
                            ];
                            $sBadge = $typeBadges[$certificate->certificate_type] ?? ['class' => 'bg-secondary', 'text' => ucfirst(str_replace('_', ' ', $certificate->certificate_type))];
                        @endphp
                        <p><strong>Type:</strong> 
                            <span class="badge {{ $sBadge['class'] }}">
                                {{ $sBadge['text'] }}
                            </span>
                        </p>
                        @php
                            $typeNames = [
                                'belt' => 'Belt Certificate (Auto-matched by Belt)',
                                'intro_school' => 'Intro School (Auto-matched by Placement)',
                                'championship' => 'Championship (Auto-matched by Placement)',
                                'competition' => 'Competition Certificate',
                            ];
                        @endphp
                        <p><strong>Certificate Type:</strong> {{ $typeNames[$certificate->certificate_type] ?? ucfirst($certificate->certificate_type) }}</p>
                        @if($certificate->certificate_type === 'competition')
                            <p><strong>Template:</strong> {{ $certificate->template->name ?? 'No Template' }}</p>
                        @else
                            <p><strong>Template Mode:</strong> Dynamic Auto-Matching</p>
                        @endif
                        <p><strong>Total Participants:</strong> {{ $certificate->certificate_count }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Participants ({{ count($participants) }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Reg ID</th>
                                        <th>Name</th>
                                        @if(in_array($certificate->certificate_type, ['intro_school', 'championship']))
                                            <th>Placement</th>
                                        @elseif($certificate->certificate_type === 'belt')
                                            <th>Belt</th>
                                        @endif
                                        <th>Place</th>
                                        <th>Date of Reg</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($participants as $index => $participant)
                                        <tr>
                                            <td>{{ $participant['registration_id'] ?? '-' }}</td>
                                            <td>{{ $participant['name'] ?? '-' }}</td>
                                            @if(in_array($certificate->certificate_type, ['intro_school', 'championship']))
                                                <td>
                                                    <span class="badge {{ ($participant['placement_key'] ?? '') === '1st' ? 'bg-warning text-dark' : (($participant['placement_key'] ?? '') === '2nd' ? 'bg-secondary' : (($participant['placement_key'] ?? '') === '3rd' ? 'bg-danger' : 'bg-info')) }}">
                                                        {{ $participant['placement'] ?? 'Participation' }}
                                                    </span>
                                                </td>
                                            @elseif($certificate->certificate_type === 'belt')
                                                <td>
                                                    <span class="badge bg-primary">
                                                        {{ $participant['next_belt'] ?? ($participant['current_belt'] ?? '-') }}
                                                    </span>
                                                </td>
                                            @endif
                                            <td>{{ $certificate->certificateVenue() !== '' ? $certificate->certificateVenue() : '-' }}</td>
                                            <td>
                                                @if(!empty($participant['date_of_reg']))
                                                    @php
                                                        try {
                                                            echo \Carbon\Carbon::parse($participant['date_of_reg'])->format('d M Y');
                                                        } catch (\Exception $e) {
                                                            echo $participant['date_of_reg'];
                                                        }
                                                    @endphp
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.certificates.view', ['certificateId' => $certificate->id, 'registrationId' => $participant['registration_id']]) }}" 
                                                   target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No participants found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@include('admin.layout.footer')

