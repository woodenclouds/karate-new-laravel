<style>
    .page {
        page-break-after: always;
        page-break-inside: avoid;
    }
    .page:last-child {
        page-break-after: auto;
    }
    .image-block {
    float: right;
    margin-top: -80px;
}
</style>

@foreach($registrations as $registration)
    <div class="page">
        @php $formData = $registration->submitted_data; @endphp

        @include('admin.registration_detail', [
            'event' => $event,
            'registration' => $registration,
            'formData' => $formData
        ])
    </div>
@endforeach
