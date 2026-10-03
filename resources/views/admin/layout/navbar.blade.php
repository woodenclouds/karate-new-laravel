<!--start top header-->
<header class="top-header">
    <nav class="navbar navbar-expand w-100 justify-content-between">

        <!-- Left side: Menu toggle -->
        <div class="mobile-toggle-icon d-xl-none">
            <i class="bi bi-list"></i>
        </div>

        <!-- Right side: Logout icon -->
        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link text-dark" style="font-size: 20px; padding: 0; border: none;">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </li>
        </ul>

    </nav>
</header>
<!--end top header-->
