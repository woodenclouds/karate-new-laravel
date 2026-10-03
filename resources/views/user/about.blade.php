@include('user.layout.header')
@include('user.layout.navbar')
<style>
    /* Section background and spacing */
    .highlights-section {
        padding: 0 20px;
        background: #000000;
        /* light background */
    }

    .highlights-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        /* Default for mobile */
        gap: 25px;
        margin-top: 30px;
    }

    @media (min-width: 600px) {
        .highlights-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 900px) {
        .highlights-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 1200px) {
        .highlights-grid {
            grid-template-columns: repeat(4, 1fr);
            /* Full 4 columns */
        }
    }

    /* Individual cards */
    .highlight-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        position: relative;
        /* needed for absolute label */
        overflow: hidden;
        /* hide label before hover */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .highlight-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    /* Image styling */
    .highlight-icon img {
        width: 150px;
        height: 150px;
        object-fit: contain;
        margin-bottom: 10px;
    }

    /* Label initially hidden */
    .highlight-label {
        position: absolute;
        bottom: -50px;
        /* hidden below card */
        left: 0;
        width: 100%;
        text-align: center;
        font-size: 1.2rem;
        font-weight: 600;
        color: #e63946;
        /* label color */
        transition: bottom 0.3s ease;
    }

    /* Slide label up on hover */
    .highlight-card:hover .highlight-label {
        bottom: 20px;
    }

    /* Numbers */
    .highlight-number {
        font-size: 2rem;
        font-weight: 700;
        color: #e63946;
        /* highlight color */
        margin-bottom: 8px;
    }

    /* Labels */
    .highlight-label {
        font-size: 1rem;
        font-weight: 500;
        color: #333;
    }

    .affiliation-layout {
        display: flex;
        flex-direction: column;
        gap: 40px;
    }

    .content-section {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .content-section.reverse {
        flex-direction: row-reverse;
    }

    .content-section img {
        max-width: 200px;
        border-radius: 10px;
    }

    .content-section p {
        flex: 1;
        font-size: 16px;
        line-height: 1.6;
        color: #d4d0d0;
    }

    /* Responsive for mobile */
    @media (max-width: 768px) {
        .content-section,
        .content-section.reverse {
            flex-direction: column;
            text-align: center;
        }

        .content-section img {
            max-width: 100%;
            margin-bottom: 15px;
        }
    }
</style>
<!-- Meta Tags -->
@section('meta_title', 'IKF | School Karate Coaching | Karate Martial Arts Training')
@section('meta_description',
    'IKF provides organized karate coaching and martial arts programs in schools throughout
    India, all taught by certified instructors and national-level trainers.')

    <!-- Breadcrumb Hero Section -->
    <section class="breadcrumb-hero">
        <div class="geometric-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        <div class="container">
            <div class="breadcrumb-content">
                <h1 class="page-title">About US</h1>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="story-section">
        <div class="container">
            <div class="story-header">
                <div class="story-subtitle">ABOUT MENTORS SPORTS KARATE - DO</div>
            </div>
            <div class="story-content">
                <p>The Mentors Sports Karate - Do is a well-respected organization committed to spreading the love of karate
                    among school children all over India. We focus on a structured approach to karate coaching, grading, and
                    events, with the goal of fostering discipline, confidence, and physical fitness in the youth of today.
                </p>
                <p>What sets us apart from traditional karate academies or dojos is our unique method of bringing certified
                    instructors right into schools. This makes it easier and more convenient for students to join top-notch
                    karate classes without ever having to leave their campus.</p>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="choose-section">
        <div class="container">
            <div class="choose-header">
                <div class="choose-subtitle">OUR VALUES</div>
                <h2 class="choose-title">Mission & <span>Vision</span></h2>
            </div>
            <div class="testimonials-grid" style="align-items:center;">
                <div class="testimonial-card">
                    <h3>Our Mission</h3>
                    <p>To promote karate among school students by offering well-structured martial arts training led by
                        certified instructors. We’re dedicated to helping children develop discipline, fitness, and
                        self-confidence through regular classes, official grading, and competitive events.</p>
                </div>
                <div class="testimonial-card">
                    <h3>Our Vision</h3>
                    <p>To become the top platform for karate coaching in schools across India, equipping them with
                        structured programs, certified instructors, and a recognized grading system—nurturing a new
                        generation that excels in karate and personal discipline.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Affiliation -->
    <section class="story-section">
        <div class="container">
            <div class="story-header">
                <div class="story-subtitle">AFFILIATION</div>
            </div>
            <div class="story-content">
                <div class="affiliation-layout">
                    <!-- Text -->

                    <!-- First Paragraph with image left -->
                    <div class="content-section">
                        <img src="{{ asset('assets/user/images/IKF.png') }}" alt="Karate Image 1">
                        <p>
                            The Indian Karate Federation (IKF) is a leading organization dedicated to the promotion and
                            development of Karate in India.
                            It works to nurture talent at the grassroots level and provides a structured pathway for
                            athletes to compete at state, national, and international events.
                            The Federation actively organizes training programs, seminars, and championships to raise the
                            standard of Karate and support athletes in achieving excellence.
                        </p>
                    </div>

                    <!-- Second Paragraph with image right -->
                    <div class="content-section reverse">
                        <img src="{{ asset('assets/user/images/KKA1.jpg') }}" alt="Karate Image 2">
                        <p>
                            The Kerala Karate Association (KKA) is the official body working towards the promotion and
                            growth of Karate in Kerala.
                            It functions under the guidelines of the Karate Association of India and aims to create
                            opportunities for athletes to excel at state, national, and international levels.
                            Through dedicated training camps, seminars, and tournaments, the Association nurtures young
                            talents, promotes discipline, and builds a strong Karate community across Kerala.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- What We Do -->
    <section class="perks-section">
        <div class="container">
            <div class="perks-content">
                <div class="perks-intro">
                    <div class="perks-subtitle">WHAT WE DO</div>
                    <h2 class="perks-title">Our Core <span>Programs</span></h2>
                </div>
                <div class="perks-grid">
                    <div class="perk-card">
                        <h3>Karate Classes in Schools</h3>
                        <p>Regular school-based classes conducted by certified instructors during or after school hours.</p>
                    </div>
                    <div class="perk-card">
                        <h3>KYU Grading & Exams</h3>
                        <p>Official belt grading for students (KYU & DAN levels) assessed by approved technical panels.</p>
                    </div>
                    <div class="perk-card">
                        <h3>Training Camps & Workshops</h3>
                        <p>Special holiday/weekend karate camps for intensive training and mentorship with national-level
                            trainers.</p>
                    </div>
                    <div class="perk-card">
                        <h3>Karate Competitions</h3>
                        <p>Organizing inter-school, zonal, and national level tournaments to showcase student talent.</p>
                    </div>
                    <div class="perk-card">
                        <h3>Instructor Monitoring</h3>
                        <p>Our instructors follow a strict curriculum and undergo evaluations to ensure program quality and
                            integrity.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- RECOGNISED BY -->
    <section class="section highlights-section">
        <div class="container">
            <div class="section-header">
                <h2 class="perks-title">RECOGNISED BY</h2>
            </div>

            <div class="highlights-grid">
                <div class="highlight-card">
                    <div class="highlight-icon">
                        <img src="{{ asset('assets/user/images/WFK.jpg') }}" alt="WFK" />
                    </div>
                    <div class="highlight-label">World Federation of Karate (WFK)</div>
                </div>

                <div class="highlight-card">
                    <div class="highlight-icon">
                        <img src="{{ asset('assets/user/images/akf.png') }}" alt="WFK" />
                    </div>
                    <div class="highlight-label">Asian Federation of Karate (AFK)</div>
                </div>

                <div class="highlight-card">
                    <div class="highlight-icon">
                        <img src="{{ asset('assets/user/images/olympic.jpeg') }}" alt="WFK" />
                    </div>
                    <div class="highlight-label">International Olympic Committee</div>
                </div>

                <div class="highlight-card">
                    <div class="highlight-icon">
                        <img src="{{ asset('assets/user/images/KAI.jpg') }}" alt="KAI" />
                    </div>
                    <div class="highlight-label">Karate Association of India</div>
                </div>

            </div>
        </div>
    </section>
    <!-- Why Choose IKF -->

    <section class="perks-section">
        <div class="container">
            <div class="perks-content">
                <div class="perks-intro">
                    <div class="perks-subtitle">WHY CHOOSE US</div>
                    <h2 class="perks-title">Why <span>Choose MENTORS SPORTS KARATE - DO</span></h2>
                    <p class="perks-description">Explore the key advantages that make Mentors Sports Karate the preferred
                        choice for school-based karate training across India.</p>
                </div>
                <div class="perks-grid">
                    <div class="perk-card">
                        <h3>School-Based Karate</h3>
                        <p>We implement our training programs directly in schools, saving travel time and increasing student
                            participation.</p>
                    </div>
                    <div class="perk-card">
                        <h3>Certified Instructors</h3>
                        <p>All our trainers are certified, background-verified, and follow a standardized curriculum .</p>
                    </div>
                    <div class="perk-card">
                        <h3>Transparent Grading</h3>
                        <p>We conduct grading exams with transparent evaluation and nationally recognized certifications.
                        </p>
                    </div>
                    <div class="perk-card">
                        <h3>Focus on Fitness & Discipline</h3>
                        <p>Karate with us is more than sport—it’s about discipline, physical health, and practical
                            self-defense skills.</p>
                    </div>
                    <div class="perk-card">
                        <h3>Child-Friendly Learning</h3>
                        <p>Our learning environment is supportive, encouraging, and designed to motivate young learners at
                            every level.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>





    <!-- Who We Serve -->
    <section class="story-section">
        <div class="container">
            <div class="story-header">
                <div class="story-subtitle">OUR REACH</div>
            </div>
            <div class="story-content">
                <p>At MENTORS SPORTS KARATE - DO, we are proud to serve both government and private schools across India.
                    Our programs are implemented in CBSE, ICSE, and State Board institutions, reaching students in both
                    urban centers and rural communities. By integrating karate into school environments, we aim to make
                    structured martial arts training more accessible, scalable, and impactful for every student regardless
                    of location.</p>
            </div>
        </div>
    </section>


    <!-- CTA Footer -->
    <section class="cta-section" style="background: #111; color: #fff; padding: 40px 0;">
        <div class="container">
            <div class="cta-content"
                style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">

                <!-- CTA Text (80%) -->
                <div class="cta-text" style="flex: 0 0 80%; max-width: 80%;">
                    <h2 style="font-size: 28px; line-height: 1.3; margin-bottom: 10px;">
                        Join <strong>Mentors Sports Karate-Do</strong> Today!
                    </h2>
                    <p style="font-size: 16px; margin: 0;">
                        Bring certified karate training to your school and empower students with confidence and discipline.
                    </p>
                </div>

                <!-- CTA Button (20%) -->
                <div class="cta-buttons" style="flex: 0 0 18%; display: flex; justify-content: flex-end;">
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
