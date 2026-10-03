@include('user.layout.header')
@include('user.layout.navbar')

<style>
 

    .page-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 90vh;
        padding: 20px;
    }

    .success-wrapper {
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        width: 100%;
        padding: 40px 30px;
        text-align: center;
    }

    h2 {
        color: #28a745;
        margin-bottom: 15px;
    }

    h4 {
        margin-top: 10px;
        color: #333;
    }
    .success-wrapper p{
         color: #333;
    }

    .btn {
        padding: 10px 20px;
        font-size: 16px;
        margin-top: 25px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .note {
        font-size: 14px;
        color: #555;
        margin-top: 20px;
        line-height: 1.6;
    }

    .text-danger {
        color: #dc3545;
    }
</style>


<div class="page-wrapper">
    <div class="success-wrapper">
        @if(session('registration_id'))
            <h2>🎉 Registration Successful</h2>
            <p>Thank you for registering!</p>
            <p>Your Registration ID:</p>
            <h4><strong>{{ $registration->registration_code }}</strong></h4>

            <a href="{{ route('user.form.download') }}" class="btn btn-danger">
                ⬇️ Download Registration Form (PDF)
            </a>

            <p class="note">
                Please save a copy of the registration form for your records.<br>
            </p>
            <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
        @else
            <h3 class="text-danger">No registration data found.</h3>
            <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
        @endif
    </div>
</div>

@include('user.layout.footer')
