@include('user.layout.header')
@include('user.layout.navbar')

<!-- Meta Tags -->
@section('meta_title', 'Contact')
@section('meta_description', 'IKF provides organized karate coaching and martial arts programs in schools throughout India, all taught by certified instructors and national-level trainers.')
<style>
    .error-message {
        color: red;
        font-size: 13px;
        margin-top: 4px;
        display: block;
    }

    input.error,
    textarea.error {
        border-color: red;
    }
</style>

<!-- Breadcrumb Hero Section -->
<section class="breadcrumb-hero">
    <div class="geometric-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    <div class="container">
        <div class="breadcrumb-content">
            <h1 class="page-title">Contact Us</h1>
        </div>
    </div>
</section>


<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <h1>Get in touch</h1>
        <div class="contact-content" style="display: flex; flex-wrap: wrap; gap: 40px; align-items: flex-start; justify-content: space-between;">
            
            <!-- Contact Info on Left -->
            <div class="contact-info" style="flex: 1; min-width: 280px;">
                <h2>Contact Details</h2>
                <div class="info-item">
                    <h3>Phone</h3>
                    <p>+91 97782 09009</p>
                </div>
                <div class="info-item">
                    <h3>Email</h3>
                    <p>mentorssportskaratedo@gmail.com</p>
                </div>
                <div class="info-item">
                    <h3>Address</h3>
                    <p>2nd Floor,ASR plaza Priyadarshini Road <br>Palakkad Kerala India -678001</p>
                </div>
            </div>

            <!-- Contact Form on Right -->
            <div class="contact-form" style="flex: 2; min-width: 300px;">
                <h2>Send a Message</h2>
                <p>We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="contactForm" action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name <span style="color:red">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}">
                            <small class="error-message" id="error-name"></small>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address <span style="color:red">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}">
                            <small class="error-message" id="error-email"></small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number <span style="color:red">*</span></label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}">
                            <small class="error-message" id="error-phone"></small>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}">
                            <small class="error-message" id="error-subject"></small>
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label for="message">Message <span style="color:red">*</span></label>
                        <textarea id="message" name="message" rows="5">{{ old('message') }}</textarea>
                        <small class="error-message" id="error-message"></small>
                    </div>
                    <button type="submit" class="submit-btn">Submit</button>
                </form>
            </div>

        </div>
    </div>
</section>


<!-- Map Section -->
<section class="map-section">
    <div class="container">
        <div class="map-container">
           <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4939209845315!2d76.64489457465532!3d10.77343168937516!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ba86dfbf8bb01a1%3A0xccee08ca69caa6aa!2sASR%20Complex!5e0!3m2!1sen!2sin!4v1754306530981!5m2!1sen!2sin" width="1400" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</section>


  <!-- FAQ Section -->
   <section class="faq-section" id="FAQ">
    <div class="container">
        <div class="faq-content">
            <div class="faq-header">
                <h2>Frequently Asked Questions</h2>
            </div>
            <div class="faq-list">

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>What is the Mentors Sports Karate Do?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>The Mentors Sports Karate Do  provides structured karate coaching to school students across India. We focus on KYU grading, tournaments, and training camps to promote authentic karate martial arts education.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>Do you offer karate classes in schools?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Absolutely! We do offer karate classes in schools, led by our certified instructors. This school-based training approach allows students to enjoy the benefits of martial arts right where they are, without the hassle of traveling to a separate karate center.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>How can a school start a karate program with Mentors Sports Karate Do?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Schools can partner with Mentors Sports Karate Do by contacting us through the number provided on our website. Once enrolled, our team will assign a certified instructor to conduct structured karate coaching at the school.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>Are your instructors certified to teach martial arts?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Absolutely! Every instructor at Mentors Sports Karate Do is not only qualified but also brings a wealth of experience in karate martial arts. They are trained to provide safe and disciplined instruction that aligns with national grading standards.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>What age groups can join the karate training program?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Our karate training program welcomes kids from all age groups, starting as young as 5 years old! We tailor the curriculum to fit each child's age and experience level, ensuring they learn effectively and have a great time doing it.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>Do you organize karate grading and belt tests?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Yes, we conduct KYU grading and belt tests at our schools. These events are managed by a certified panel and adhere to a national syllabus.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>Do students receive certificates after grading?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Yes, students who successfully pass their grading exams are awarded certificates from Mentors Sports Karate Do, which are recognized throughout India for their proficiency in karate martial arts.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>


<script>
   document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById('contactForm');

    form.addEventListener('submit', function (e) {
        let valid = true;

        // Reset previous errors
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        document.querySelectorAll('input, textarea').forEach(el => el.classList.remove('error'));

        const name = document.getElementById('name');
        const email = document.getElementById('email');
        const phone = document.getElementById('phone');
        const subject = document.getElementById('subject');
        const message = document.getElementById('message');

        const namePattern = /^[A-Za-z\s]+$/;
        const phonePattern = /^\d{10}$/;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!name.value.trim() || !namePattern.test(name.value.trim())) {
            document.getElementById('error-name').textContent = 'Enter a valid name.';
            name.classList.add('error');
            valid = false;
        }

        if (!email.value.trim() || !emailPattern.test(email.value.trim())) {
            document.getElementById('error-email').textContent = 'Enter a valid email.';
            email.classList.add('error');
            valid = false;
        }

        if (!phone.value.trim() || !phonePattern.test(phone.value.trim())) {
            document.getElementById('error-phone').textContent = 'Enter a valid 10-digit phone number.';
            phone.classList.add('error');
            valid = false;
        }

        if (subject.value.trim() && !namePattern.test(subject.value.trim())) {
            document.getElementById('error-subject').textContent = 'Subject must contain only letters.';
            subject.classList.add('error');
            valid = false;
        }

        if (!message.value.trim()) {
            document.getElementById('error-message').textContent = 'Message is required.';
            message.classList.add('error');
            valid = false;
        }

        if (!valid) {
            e.preventDefault(); // stop submission only if invalid
        }
    });
});

</script>

@include('user.layout.footer')
