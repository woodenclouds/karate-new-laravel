@include('user.layout.header')
@include('user.layout.navbar')

<style>
    .refund-container {
        max-width: 900px;
        margin: 120px auto;
        background: #ffffff;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        font-family: 'Segoe UI', Tahoma, sans-serif;
        line-height: 1.7;
    }

    .refund-container h1 {
        font-size: 2rem;
        color: #e63946;
        margin-bottom: 15px;
        border-bottom: 2px solid #e63946;
        padding-bottom: 10px;
    }

    .refund-container h2 {
        font-size: 1.4rem;
        margin-top: 30px;
        color: #333;
        border-left: 4px solid #e63946;
        padding-left: 10px;
    }

    .refund-container p {
        color: #555;
        margin-top: 10px;
        text-align: justify;
    }

    .refund-container strong {
        color: #000;
    }

    .refund-container a {
        color: #e63946;
        text-decoration: none;
        font-weight: 600;
    }

    .refund-container a:hover {
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

<div class="refund-container">
    <h1>Privacy Policy</h1>
    <p>
        At <strong>Mentors Sports Karate</strong>, we respect your privacy and are committed to protecting your personal information. 
        This Privacy Policy explains how we collect, use, and safeguard your data when you interact with our website and services.
    </p>

    <h2>1. Information We Collect</h2>
    <p>
        We may collect the following types of information:
        <br>– Personal Information: Name, email address, phone number, billing details, or any data you provide.  
        <br>– Usage Data: IP address, browser type, operating system, pages visited, and time spent.  
        <br>– Cookies: Data to improve your experience and analyze site performance.
    </p>

    <h2>2. How We Use Your Information</h2>
    <p>
        We use your data to:
        <br>– Provide and manage our services.  
        <br>– Process transactions and payments.  
        <br>– Communicate with you regarding updates or support.  
        <br>– Improve website functionality and security.  
        <br>– Prevent fraud and comply with legal requirements.
    </p>

    <h2>3. Sharing of Information</h2>
    <p>
        We do not sell or rent your personal data. However, we may share information with:
        <br>– Trusted service providers (e.g., payment processors, hosting).  
        <br>– Legal authorities when required by law.  
        <br>– Business transfers such as mergers or acquisitions.
    </p>

    <h2>4. Data Security</h2>
    <p>
        We implement industry-standard security measures to protect your data. 
        However, no system can be guaranteed 100% secure.
    </p>

    <h2>5. Your Rights</h2>
    <p>
        Depending on your location, you may have rights to:
        <br>– Access or request a copy of your personal data.  
        <br>– Request corrections or updates.  
        <br>– Request deletion of your information (subject to legal obligations).  
        <br>– Opt-out of marketing communications.
    </p>

    <h2>6. Cookies</h2>
    <p>
        We use cookies to enhance your browsing experience. You can disable cookies in your browser settings, 
        but some features may not function properly.
    </p>

    <h2>7. Third-Party Links</h2>
    <p>
        Our website may contain links to third-party sites. We are not responsible for their privacy practices and encourage you 
        to review their policies.
    </p>

    <h2>8. Policy Updates</h2>
    <p>
        We may update this Privacy Policy periodically. Any changes will be reflected on this page with a revised "Last Updated" date.
    </p>

    <h2>9. Contact Us</h2>
    <p>
        If you have questions about this Privacy Policy, please contact us at:  
        <a href="mailto:mentorssportskaratedo@gmail.com">mentorssportskaratedo@gmail.com</a>
    </p>

    <p class="last-updated">
        Last updated: August 16, 2025
    </p>
</div>

@include('user.layout.footer')
