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
    <h1>Refund Policy</h1>
    <p>
      We value your commitment to our programs and services. This Refund Policy outlines the circumstances under which payments are non-refundable.
      Please read this policy carefully before completing your registration or purchase.
    </p>

    <h2>1. No Refunds After Registration</h2>
    <p>
      Once your registration is successfully completed, all fees paid are <strong>final and non-refundable</strong>. This applies to all events, courses, memberships, or services offered on this website.
    </p>

    <h2>2. Exceptions for Errors</h2>
    <p>
      In rare situations where a payment was made in error due to a technical or administrative issue on our part, a refund may be considered.
      Such requests must be submitted in writing within <strong>48 hours</strong> of payment to
      <a href="mailto:mentorssportskaratedo@gmail.com">mentorssportskaratedo@gmail.com</a>.
    </p>

    <h2>3. Cancellations and No-Shows</h2>
    <p>
      If you cancel after completing your registration or fail to attend a scheduled event, you will not be eligible for a refund.
    </p>

    <h2>4. Policy Visibility and Agreement</h2>
    <p>
      This policy is displayed on our website and in our Terms &amp; Conditions. By proceeding with registration or payment, you acknowledge that you have read and agree to this Refund Policy.
    </p>

    <h2>5. Legal Compliance</h2>
    <p>
      Local consumer protection laws may provide additional rights that override this policy. Please review the laws in your region and seek legal advice if needed.
    </p>

    <p class="last-updated">
      Last updated: August 12, 2025
    </p>
</div>

@include('user.layout.footer')