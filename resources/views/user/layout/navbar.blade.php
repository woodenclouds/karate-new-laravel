<header class="header">
    <nav class="nav-container">
        <!-- Logo -->
        <a href="{{ route('user.index') }}" class="logo">
            <img src="{{ asset('assets/admin/images/karate_logo.png') }}" alt="Logo" class="logo-img">
            <span class="logo-text" style="color: white;">MENTORS SPORTS KARATE - DO</span>
        </a>

        <!-- Hamburger (mobile) -->
        <div class="mobile-toggle" id="menuToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <!-- Desktop Nav -->
        <ul class="nav-menu desktop-nav">
            <li><a href="{{ route('user.index') }}#home" class="nav-link">Home</a></li>
            <li><a href="{{ route('user.about') }}" class="nav-link">About</a></li>
            <li><a href="{{ route('user.events') }}" class="nav-link">Events</a></li>
            <li><a href="{{ route('user.contact') }}" class="nav-link">Contact</a></li>
        </ul>
    </nav>

    <!-- Sidebar Nav (Mobile) -->
    <div class="sidebar-menu" id="sidebarMenu">
        <ul class="nav-menu mobile-nav">
            <li><a href="{{ route('user.index') }}#home" class="nav-link">Home</a></li>
            <li><a href="{{ route('user.about') }}" class="nav-link">About</a></li>
            <li><a href="{{ route('user.events') }}" class="nav-link">Events</a></li>
            <li><a href="{{ route('user.contact') }}" class="nav-link">Contact</a></li>
        </ul>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>
</header>
