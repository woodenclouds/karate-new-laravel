<!-- Header -->
@include('admin.layout.header')
<!-- End Header -->

<!-- Navbar -->
@include('admin.layout.navbar')
<!-- End Navbar -->

<!-- Sidebar -->
@include('admin.layout.sidebar')
<!-- End Sidebar -->

<main class="page-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">User Messages</h4>
        </div>

        <div class="card">
            <div class="card-body">
                @if($messages->count())
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Received At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($messages as $index => $msg)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $msg->name }}</td>
                                        <td>{{ $msg->email }}</td>
                                        <td>{{ $msg->subject }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($msg->message, 60) }}</td>
                                        <td>{{ $msg->created_at->format('d M Y, h:i A') }}</td>
                                        <td>
                                            <form action="{{ route('admin.message.delete', $msg->id) }}" method="POST"
                                                onsubmit="return confirm('Delete this message?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No messages received yet.</p>
                @endif
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
@include('admin.layout.footer')
