<!-- Header -->
@include('admin.layout.header')
<!-- End Header  -->

<!-- Navbar -->
@include('admin.layout.navbar')
<!-- End Navbar -->

<!-- Sidebar -->
@include('admin.layout.sidebar')
<!-- End Sidebar -->

<!--start content-->
<main class="page-content">

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xl-4">
        <div class="col">
            <div class="card radius-10">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total Events</p>
                        <h4 class="my-1">{{ $totalEvents }}</h4>
                    </div>
                    <div class="widget-icon-large bg-gradient-purple text-white ms-auto">
                        <i class="bi bi-calendar-event-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total Registrations</p>
                        <h4 class="my-1">{{ $totalRegistrations }}</h4>
                    </div>
                    <div class="widget-icon-large bg-gradient-success text-white ms-auto">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total Messages</p>
                        <h4 class="my-1">{{ $totalMessages }}</h4>
                    </div>
                    <div class="widget-icon-large bg-gradient-danger text-white ms-auto">
                        <i class="bi bi-chat-left-text-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col">
            <div class="card radius-10">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Bounce Rate</p>
                        <h4 class="my-1">38.15%</h4>
                        <p class="mb-0 font-13 text-success"><i class="bi bi-caret-up-fill"></i> +12.2%</p>
                    </div>
                    <div class="widget-icon-large bg-gradient-info text-white ms-auto">
                        <i class="bi bi-bar-chart-line-fill"></i>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    
    <div class="card radius-10 mt-4">
        {{-- <div class="card-header bg-transparent d-flex justify-content-between">
            <h5 class="mb-0">Recent Registrations</h5>
            <a href="{{ route('admin.message') }}" class="btn btn-sm btn-primary">View All</a>
        </div> --}}
       <div class="card radius-10 mt-4">
    <div class="card-header bg-transparent d-flex justify-content-between">
        <h5 class="mb-0">Recent Messages</h5>
        <a href="{{ route('admin.message') }}" class="btn btn-sm btn-primary">View All</a> {{-- Adjust route --}}
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentMessages as $index => $msg)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $msg->name }}</td>
                            <td>{{ $msg->email }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($msg->message, 40) }}</td>
                            <td>{{ \Carbon\Carbon::parse($msg->created_at)->format('d M Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No recent messages</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

    </div>
    

</main>
<!--end page main-->
<!-- Sidebar -->
@include('admin.layout.footer')
<!-- End Sidebar -->