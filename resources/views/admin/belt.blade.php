<!-- Header -->
@include('admin.layout.header')
<!-- End Header  -->

<!-- Navbar -->
@include('admin.layout.navbar')
<!-- End Navbar -->

<!-- Sidebar -->
@include('admin.layout.sidebar')
<!-- End Sidebar -->
<!-- Add Belt Modal -->
<div class="modal fade" id="addBeltModal" tabindex="-1" aria-labelledby="addBeltModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.belt.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBeltModalLabel">Add Belt Upgrade Fee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @php
                        $beltOptions = [
                            'White Belt',
                            'Yellow Belt',
                            'Orange Belt',
                            'Green Belt',
                            'Blue Belt',
                            'Purple Belt',
                            'Brown Belt 4th Kyu',
                            'Brown Belt 3rd Kyu',
                            'Brown Belt 2nd Kyu',
                            'Brown Belt 1st Kyu',
                            'Black Shodan',
                        ];
                    @endphp

                    <div class="mb-3">
                        <label for="from_belt" class="form-label">From Belt</label>
                        <select class="form-select" id="from_belt" name="from_belt" required>
                            <option value="">Select From Belt</option>
                            @foreach ($beltOptions as $beltOption)
                                <option value="{{ $beltOption }}">{{ $beltOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="to_belt" class="form-label">To Belt</label>
                        <select class="form-select" id="to_belt" name="to_belt" required>
                            <option value="">Select To Belt</option>
                            @foreach ($beltOptions as $beltOption)
                                <option value="{{ $beltOption }}">{{ $beltOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="fee" class="form-label">Fee</label>
                        <input type="number" class="form-control" id="fees" name="fees" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Belt</button>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- End Add belt modal --}}
{{-- Main conent Start --}}
<main class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">

        <div class="row w-100">
            <div class="col-12 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBeltModal">
                    +Add Belt
                </button>
            </div>
        </div>
    </div>
    <!--end breadcrumb-->
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <h5 class="mb-0">Belt Upgrade Fees</h5>
                <form class="ms-auto position-relative">
                    <div class="position-absolute top-50 translate-middle-y search-icon px-3">
                        <i class="bi bi-search"></i>
                    </div>
                    <input class="form-control ps-5" type="text" placeholder="Search by belt">
                </form>
            </div>

            <div class="table-responsive mt-3">
                <table class="table align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID</th>
                            <th>From Belt</th>
                            <th>To Belt</th>
                            <th>Fee</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="belts-table-body">
                        @foreach ($belts as $key => $belt)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $belt->from_belt }}</td>
                                <td>{{ $belt->to_belt }}</td>
                                <td>{{ $belt->fees }}</td>
                                <td>
                                    <div class="table-actions d-flex align-items-center gap-3 fs-6">
                                        <!-- Edit Button -->
                                        <a href="javascript:void(0);" class="text-warning" data-bs-toggle="modal"
                                            data-bs-target="#editModal-{{ $belt->id }}" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.belt.delete', $belt->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this belt entry?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn p-0 text-danger" title="Delete">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal (inside loop so $belt is defined) -->
                            <div class="modal fade" id="editModal-{{ $belt->id }}" tabindex="-1"
                                aria-labelledby="editModalLabel-{{ $belt->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="{{ route('admin.belt.update', $belt->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editModalLabel-{{ $belt->id }}">Edit
                                                    Belt Fee</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="from_belt_{{ $belt->id }}"
                                                        class="form-label">From
                                                        Belt</label>
                                                    <select class="form-select" id="from_belt_{{ $belt->id }}"
                                                        name="from_belt" required>
                                                        <option value="{{ $belt->from_belt }}" selected>
                                                            {{ $belt->from_belt }}</option>
                                                        @foreach ($beltOptions as $option)
                                                            @if ($option !== $belt->from_belt)
                                                                <option value="{{ $option }}">
                                                                    {{ $option }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="to_belt_{{ $belt->id }}" class="form-label">To
                                                        Belt</label>
                                                    <select class="form-select" id="to_belt_{{ $belt->id }}"
                                                        name="to_belt" required>
                                                        <option value="{{ $belt->to_belt }}" selected>
                                                            {{ $belt->to_belt }}</option>
                                                        @foreach ($beltOptions as $option)
                                                            @if ($option !== $belt->to_belt)
                                                                <option value="{{ $option }}">
                                                                    {{ $option }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="fees_{{ $belt->id }}"
                                                        class="form-label">Fee</label>
                                                    <input type="number" class="form-control" name="fees"
                                                        id="fees_{{ $belt->id }}" value="{{ $belt->fees }}"
                                                        step="0.01" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Update</button>
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<!--end page main-->

<!-- footer -->
@include('admin.layout.footer')
<!-- End footer -->
