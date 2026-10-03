@include('user.layout.header')
@include('user.layout.navbar')
@section('meta_title', 'Mentors Sports Karate Do')
@section('meta_description', 'Discover upcoming events, register easily, and stay updated with our latest happenings.')
<!-- Hero Section -->
<section id="home" class="section hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-subtitle">Empowering Karate Across India</div>
                <h1 class="hero-title">Mentors Sports Karate</h1>
                <p class="hero-description">Official platform for KYU Grading, National Karate Competitions, and Training
                    Camps.</p>
                <div class="hero-features">
                    <div class="feature-item">
                        <div class="feature-text">
                            <div class="feature-title">Training</div>
                            <div class="feature-desc">Expert martial arts coaching</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-text">
                            <div class="feature-title">Competition</div>
                            <div class="feature-desc">Premier tournament events</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-text">
                            <div class="feature-title">Excellence</div>
                            <div class="feature-desc">Character development focus</div>
                        </div>
                    </div>
                </div>
                <a href="#about" class="hero-btn">Discover</a>
            </div>
            {{-- <div class="hero-image">
                <div class="hero-visual">
                    <img src="{{ asset('assets/user/images/spotlight.jpg') }}?height=400&width=500"
                        alt="Karate Visual" />
                </div>
            </div> --}}
        </div>
    </div>
</section>


<!-- About Section -->
<section id="about" class="section about-section">
    <div class="container">
        <div class="about-content">
            <div class="section-subtitle">About Us</div>
            <h2 class="section-title">Mentors Sports Karate - Do for Karate Grading & Events</h2>
            <p class="section-description" style="text-align:justify">
                Mentors Sports Karate - Do serves as the national organization committed to promoting karate across
                India. We achieve this by implementing structured grading systems, hosting official competitions, and
                organizing intensive training camps. Our mission is to uphold the true essence of martial arts,
                emphasizing discipline, integrity, and skill development.
            </p>
            <p class="section-description" style="text-align:justify">
                Whether you are just starting out and eager to join a karate class, an advanced student preparing for
                your KYU grading, or a dedicated martial artist aiming to compete at the national level, our platform is
                designed to support you every step of the way.
            </p>

            <div class="container">
                <a href="{{ route('user.about') }}" class="cta-btn">Know More</a>
            </div>


            {{-- <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Schools</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">15K+</div>
                    <div class="stat-label">Athletes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">200+</div>
                    <div class="stat-label">Events</div>
                </div>

            </div> --}}
        </div>
    </div>
</section>

<section class="services-section">
    <div class="container">
        <div class="services-header">
            <div class="services-subtitle">SERVICES</div>
            <h2 class="services-title">Our <span>Registrations</span></h2>
        </div>

        <div class="services-grid"> 
            @foreach ($categories as $category)
            @php $slug = Str::slug($category->name, '-'); @endphp
                <div class="perk-card {{ $slug }}">
                    <h3 class="service-title">{{ $category->name }} Registration</h3>
                    <p class="service-description">
                        {{ $category->description ?? 'Explore opportunities under this category.' }}
                    </p>
                    <a href="{{ url('Events?category=' . $slug) }}" class="service-btn">View Events</a>
                </div>
            @endforeach
        </div>

    </div>
</section>


<!-- CTA Footer -->
<section class="cta-section" style="background: #111; color: #fff; padding: 60px 0;">
    <div class="container">
        <div class="cta-content"
            style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;">

            <div class="cta-text" style="flex: 1 1 60%; max-width: 800px;">
                <h2 style="font-size: 32px; line-height: 1.4; margin-bottom: 15px;">
                    Join MENTORS SPORTS KARATE - DO Today.<br>Build Confidence & Discipline.
                </h2>
                <p style="font-size: 16px;">Bring certified karate training to your school and empower students.</p>
            </div>

            <div class="cta-buttons"
                style="flex: 1 1 35%; display: flex; gap: 15px; justify-content: flex-end; flex-wrap: wrap;">
                <a href="{{ route('user.contact') }}" class="btn-primary"
                    style="background: #e63946; color: #fff; padding: 12px 20px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; text-decoration: none;">
                    <i class="fas fa-phone" style="margin-right: 6px;"></i>
                    Contact Us
                </a>
            </div>

        </div>
    </div>
</section>


@include('user.layout.footer')
