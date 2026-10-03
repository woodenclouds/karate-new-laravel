<style>
    .logout-btn {
        display: block;
        text-align: left;
        padding: 10px 15px;
        color: inherit;
        font-size: 14px;
        width: 100%;
        transition: all 0.2s ease-in-out;
    }

    .logout-btn:hover {
        background-color: rgba(0, 0, 0, 0.08);
        color: #0d6efd;
    }

    .logout-btn .parent-icon-wrapper {
        display: flex;
        align-items: center;
    }

    .logout-btn .parent-icon {
        margin-right: 10px;
    }
</style>
<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src="{{ asset('assets/admin/images/karate_logo.png') }}" class="logo-icon" alt="logo icon">
        </div>
        <div>
           <h4 class="logo-text" style="color:#5e0000;font-size:16px;">Admin Dashboard</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class="bi bi-chevron-double-left"></i></div>
    </div>

    <!--navigation-->
    <ul class="metismenu" id="menu">

        <li>
            <a href="{{ route('admin.dashboard') }}">
                <div class="parent-icon"><i class="bi bi-house-door"></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.message') }}">
                <div class="parent-icon"><i class="bi bi-chat-dots"></i></div>
                <div class="menu-title">Messages</div>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.registrations') }}">
                <div class="parent-icon"><i class="bi bi-person-lines-fill"></i></div>
                <div class="menu-title">Registrations</div>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.pending') }}">
                <div class="parent-icon"><i class="bi bi-clock-history"></i></div>
                <div class="menu-title">Pending</div>
            </a>
        </li>

        <!--<li>-->
        <!--    <a href="{{ route('admin.payment.amounts') }}">-->
        <!--        <div class="parent-icon"><i class="bi bi-currency-rupee"></i></div>-->
        <!--        <div class="menu-title">Payment Amounts</div>-->
        <!--    </a>-->
        <!--</li>-->

        <li>
            <a href="{{ route('admin.event') }}">
                <div class="parent-icon"><i class="bi bi-calendar-event"></i></div>
                <div class="menu-title">Events</div>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.belt') }}">
                <div class="parent-icon"><i class="bi bi-shield-lock"></i></div>
                <div class="menu-title">Belts</div>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.category') }}">
                <div class="parent-icon"><i class="bi bi-tags"></i></div>
                <div class="menu-title">Categories</div>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.certificates.index') }}">
                <div class="parent-icon"><i class="bi bi-award"></i></div>
                <div class="menu-title">Certificates</div>
            </a>
        </li>

        <!--<li>-->
        <!--    <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">-->
        <!--        @csrf-->
        <!--        <button type="submit"-->
        <!--            style="border: none; background: none; padding: 10px 15px; width: 100%; text-align: left;">-->
        <!--            <div style="display: flex; align-items: center;">-->
        <!--                <div class="parent-icon"><i class="bi bi-box-arrow-right"></i></div>-->
        <!--                <div class="menu-title">Logout</div>-->
        <!--            </div>-->
        <!--        </button>-->
        <!--    </form>-->
        <!--</li>-->




    </ul>
    <!--end navigation-->
</aside>
<!--end sidebar -->
