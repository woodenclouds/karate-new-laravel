@include('user.layout.header')
@include('user.layout.navbar')

<style>
    .terms-container {
        max-width: 900px;
        margin: 120px auto;
        background: #ffffff;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        font-family: 'Segoe UI', Tahoma, sans-serif;
        line-height: 1.7;
    }

    .terms-container h1 {
        font-size: 2rem;
        color: #e63946;
        margin-bottom: 15px;
        border-bottom: 2px solid #e63946;
        padding-bottom: 10px;
    }

    .terms-container h2 {
        font-size: 1.4rem;
        margin-top: 30px;
        color: #333;
        border-left: 4px solid #e63946;
        padding-left: 10px;
    }

    .terms-container p {
        color: #555;
        margin-top: 10px;
        text-align: justify;
    }

    .terms-container strong {
        color: #000;
    }

    .terms-container a {
        color: #e63946;
        text-decoration: none;
        font-weight: 600;
    }

    .terms-container a:hover {
        text-decoration: underline;
    }

    .last-updated {
        font-size: 0.9rem;
        margin-top: 30px;
        color: #777;
        text-align: right;
        font-style: italic;
    }
</style>

<div class="terms-container">
    <h1>Terms &amp; Conditions</h1>
    <p>
        Welcome to <strong>Mentors Sports Karate</strong>. By accessing or using our website and services, you agree to comply with the following Terms &amp; Conditions.
        Please read them carefully before proceeding.
    </p>

    <h2>1. Acceptance of Terms</h2>
    <p>
        By registering, accessing, or using our services, you acknowledge that you have read, understood, and agreed to these Terms &amp; Conditions.
        If you do not agree, please do not use our website or services.
    </p>

    <h2>2. Services</h2>
    <p>
        We provide digital solutions, consultancy, and related services as described on our website. The scope of services may change at our discretion.
    </p>

    <h2>3. User Responsibilities</h2>
    <p>
        Users must provide accurate information during registration and are responsible for maintaining the confidentiality of their accounts.
        Any misuse of our services may result in suspension or termination of access.
    </p>

    <h2>4. Payments</h2>
    <p>
        All fees for services are to be paid as per the agreed terms. Payments made are <strong>non-refundable</strong> except in cases of administrative errors on our part.
    </p>

    <h2>5. Intellectual Property</h2>
    <p>
        All content, logos, graphics, and materials provided on this website are the property of <strong>Mentors Sports Karate</strong> and may not be copied,
        reproduced, or distributed without written consent.
    </p>

    <h2>6. Limitation of Liability</h2>
    <p>
        We are not responsible for any direct, indirect, or incidental damages resulting from the use or inability to use our services.
    </p>

    <h2>7. Modifications to Terms</h2>
    <p>
        We reserve the right to update or modify these Terms &amp; Conditions at any time. Updates will be posted on this page with the effective date.
    </p>

    <h2>8. Governing Law</h2>
    <p>
        These Terms &amp; Conditions are governed by the laws applicable in your jurisdiction. Any disputes shall be resolved in the competent courts of that jurisdiction.
    </p>

    <p class="last-updated">
        Last updated: August 16, 2025
    </p>
</div>

@include('user.layout.footer')
