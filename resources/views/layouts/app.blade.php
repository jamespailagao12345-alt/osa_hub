<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>OSA Central Hub - @yield('title', 'USTP Balubal')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">

    <!-- MACode-inspired CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/animate/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/owl-carousel/css/owl.carousel.css') }}">
    
    <!-- Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @stack('styles')

    <!-- Custom color scheme -->
    <style>
        :root {
            --primary:rgb(3, 1, 45);
            --accent:rgb(242, 255, 0);
            --secondary:rgb(0, 255, 85);
            --warning:rgb(238, 24, 24);
            --info: #05B4E1;
            --danger:rgb(123, 11, 7);
            --success:rgb(0, 132, 255);
            --dark:rgb(48, 49, 45);
            --light: #F5F9F6;
            --grey: #6E807A;
        }

        .text-primary { color: var(--primary) !important; }
        .bg-primary { background-color: var(--primary) !important; }
        /* Global button styling - all buttons yellow background */
        .btn,
        .btn-primary,
        .btn-secondary,
        .btn-success,
        .btn-danger,
        .btn-warning,
        .btn-info,
        .btn-light,
        .btn-dark,
        .btn-outline-primary,
        .btn-outline-secondary,
        .btn-outline-success,
        .btn-outline-danger,
        .btn-outline-warning,
        .btn-outline-info,
        .btn-outline-light,
        .btn-outline-dark {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000 !important;
        }
        
        .btn:hover,
        .btn-primary:hover,
        .btn-secondary:hover,
        .btn-success:hover,
        .btn-danger:hover,
        .btn-warning:hover,
        .btn-info:hover,
        .btn-light:hover,
        .btn-dark:hover,
        .btn-outline-primary:hover,
        .btn-outline-secondary:hover,
        .btn-outline-success:hover,
        .btn-outline-danger:hover,
        .btn-outline-warning:hover,
        .btn-outline-info:hover,
        .btn-outline-light:hover,
        .btn-outline-dark:hover {
            background-color: #ffca2c !important;
            border-color: #ffca2c !important;
            color: #000 !important;
        }
        
        /* Override outline buttons to have yellow background on hover */
        .btn-outline-primary:hover,
        .btn-outline-secondary:hover,
        .btn-outline-success:hover,
        .btn-outline-danger:hover,
        .btn-outline-warning:hover,
        .btn-outline-info:hover,
        .btn-outline-light:hover,
        .btn-outline-dark:hover {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
        }
        
        /* Active and focus states */
        .btn:active,
        .btn:focus,
        .btn-primary:active,
        .btn-primary:focus,
        .btn-secondary:active,
        .btn-secondary:focus,
        .btn-success:active,
        .btn-success:focus,
        .btn-danger:active,
        .btn-danger:focus,
        .btn-warning:active,
        .btn-warning:focus,
        .btn-info:active,
        .btn-info:focus {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000 !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.5) !important;
        }
        
        /* Disabled buttons - slightly darker yellow */
        .btn:disabled,
        .btn.disabled,
        .btn-primary:disabled,
        .btn-primary.disabled,
        .btn-secondary:disabled,
        .btn-secondary.disabled,
        .btn-success:disabled,
        .btn-success.disabled,
        .btn-danger:disabled,
        .btn-danger.disabled,
        .btn-warning:disabled,
        .btn-warning.disabled,
        .btn-info:disabled,
        .btn-info.disabled,
        .btn-light:disabled,
        .btn-light.disabled,
        .btn-dark:disabled,
        .btn-dark.disabled {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000 !important;
            opacity: 0.6 !important;
        }
        
        /* Password toggle buttons - no background */
        /* Target buttons with toggle in ID and Password/password in ID */
        button[id*="toggle"][id*="Password"],
        button[id*="toggle"][id*="password"],
        button[id*="Toggle"][id*="Password"],
        button[id*="Toggle"][id*="password"],
        /* Target btn-link buttons positioned absolutely (common pattern for password toggles) */
        button.btn-link.position-absolute,
        .btn-link.position-absolute,
        /* Target buttons with specific password toggle IDs */
        button#togglePassword,
        button#togglePasswordConfirmation,
        button#toggleNewPassword,
        button#toggleNewPasswordConfirmation,
        button#toggle_current_password,
        button#toggle_new_password,
        button#toggle_new_password_confirmation,
        button#toggleAssistantPassword,
        button#togglePasswordConfirmation {
            background-color: transparent !important;
            background: none !important;
            border: none !important;
            box-shadow: none !important;
        }
        
        /* Hover states for password toggle buttons */
        button[id*="toggle"][id*="Password"]:hover,
        button[id*="toggle"][id*="password"]:hover,
        button[id*="Toggle"][id*="Password"]:hover,
        button[id*="Toggle"][id*="password"]:hover,
        button.btn-link.position-absolute:hover,
        .btn-link.position-absolute:hover,
        button#togglePassword:hover,
        button#togglePasswordConfirmation:hover,
        button#toggleNewPassword:hover,
        button#toggleNewPasswordConfirmation:hover,
        button#toggle_current_password:hover,
        button#toggle_new_password:hover,
        button#toggle_new_password_confirmation:hover,
        button#toggleAssistantPassword:hover {
            background-color: transparent !important;
            background: none !important;
            border: none !important;
            box-shadow: none !important;
        }
        
        /* Active and focus states for password toggle buttons */
        button[id*="toggle"][id*="Password"]:active,
        button[id*="toggle"][id*="Password"]:focus,
        button[id*="toggle"][id*="password"]:active,
        button[id*="toggle"][id*="password"]:focus,
        button[id*="Toggle"][id*="Password"]:active,
        button[id*="Toggle"][id*="Password"]:focus,
        button[id*="Toggle"][id*="password"]:active,
        button[id*="Toggle"][id*="password"]:focus,
        button.btn-link.position-absolute:active,
        button.btn-link.position-absolute:focus,
        .btn-link.position-absolute:active,
        .btn-link.position-absolute:focus,
        button#togglePassword:active,
        button#togglePassword:focus,
        button#togglePasswordConfirmation:active,
        button#togglePasswordConfirmation:focus,
        button#toggleNewPassword:active,
        button#toggleNewPassword:focus,
        button#toggleNewPasswordConfirmation:active,
        button#toggleNewPasswordConfirmation:focus,
        button#toggle_current_password:active,
        button#toggle_current_password:focus,
        button#toggle_new_password:active,
        button#toggle_new_password:focus,
        button#toggle_new_password_confirmation:active,
        button#toggle_new_password_confirmation:focus,
        button#toggleAssistantPassword:active,
        button#toggleAssistantPassword:focus {
            background-color: transparent !important;
            background: none !important;
            border: none !important;
            box-shadow: none !important;
        }
        
        body { font-family: 'Source Sans Pro', sans-serif; color: var(--dark); }
        /* Utility: smaller, bold text available to all views */
        .small-bold { font-size: .95rem; font-weight: 700; }
        /* Ensure navbar is fixed and always visible on all pages */
        header {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 1050 !important;
        }

        .navbar {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            width: 100% !important;
            background-color: midnightblue !important;
            box-shadow: none !important;
            border: none !important;
            border-bottom: none !important;
        }
        
        .navbar-brand {
            color: white !important;
        }
        
        .navbar-brand .text-primary {
            color: #ffc107 !important; /* Yellow accent for "OSA" */
        }
        
        .navbar .btn-primary {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: midnightblue !important;
        }
        
        .navbar .btn-primary:hover {
            background-color: #ffca2c !important;
            border-color: #ffca2c !important;
            color: midnightblue !important;
        }
        
        header {
            border-bottom: none !important;
            box-shadow: none !important;
        }

        /* Global dashboard header - visible on all pages (smaller size) */
        .dashboard-header-global {
            position: fixed !important;
            top: 5px !important; /* Below the navbar */
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 1040 !important;
            background-color: #ffffff !important;
            border-bottom: none !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
            min-height: 20px !important;
        }

        /* Ensure main content area accounts for both fixed headers (navbar + dashboard header) */
        body {
            padding-top: 70px !important; /* navbar (~56px) + dashboard header (~74px) */
        }

        /* Additional spacing for main content */
        #main-content {
            padding-top: 0.5rem !important;
            margin-top: 0 !important;
        }

        /* Ensure Quick Actions and sidebars are visible */
        aside, .sidebar, .col-md-3, .col-md-9 {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* Remove top margin from Quick Actions cards */
        aside .card {
            margin-top: 0 !important;
        }

        /* Global Back button alignment - right side for uniformity */
        .admin-back-btn-wrap { 
            margin: .5rem 0 1rem; 
            display: flex;
            justify-content: flex-end;
        }

        /* Global header color standardization */
        /* Main headers (h2, h3, h4, section-header): midnightblue background */
        .section-header,
        h2 .section-header,
        h3 .section-header {
            background-color: midnightblue !important;
            color: white !important;
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: .5rem 1rem;
            border: none;
            border-radius: 0;
        }
        
        /* Main card headers (h4, h5 in main sections): midnightblue background */
        .card-header.bg-primary,
        .card-header:not(.bg-secondary):not(.bg-light):not(.bg-info):not(.bg-success):not(.bg-warning):not(.bg-danger):not(.bg-yellow) {
            background-color: midnightblue !important;
            color: white !important;
        }
        
        /* Sub-headers (h5, h6, secondary card headers): yellow background */
        .card-header.bg-secondary,
        h5.card-header:not(.bg-primary),
        h6.card-header,
        h5.border-bottom,
        h6.border-bottom {
            background-color: #ffc107 !important; /* yellow */
            color: white !important;
        }
        
        /* Table headers: midnightblue background */
        thead tr,
        table thead th {
            background-color: midnightblue !important;
            color: white !important;
        }

        /* Add right margin to all elements with midnightblue background and adjust sizes */
        /* Card headers */
        .card-header[style*="background-color: midnightblue"],
        .card-header[style*="background-color:midnightblue"],
        .card-header[style*="background: midnightblue"],
        .card-header[style*="background:midnightblue"] {
            margin-right: 1rem !important;
            width: calc(100% - 1rem) !important;
            box-sizing: border-box;
        }

        /* Section headers */
        .section-header,
        h2 .section-header,
        h3 .section-header {
            margin-right: 1rem !important;
            width: calc(100% - 1rem) !important;
            box-sizing: border-box;
        }

        /* List group items */
        .list-group-item[style*="background-color: midnightblue"],
        .list-group-item[style*="background-color:midnightblue"],
        .list-group-item[style*="background: midnightblue"],
        .list-group-item[style*="background:midnightblue"] {
            margin-right: 1rem !important;
            width: calc(100% - 1rem) !important;
            box-sizing: border-box;
        }

        /* Buttons with midnightblue background */
        .btn[style*="background-color: midnightblue"],
        .btn[style*="background-color:midnightblue"],
        .btn[style*="background: midnightblue"],
        .btn[style*="background:midnightblue"] {
            margin-right: 1rem !important;
            box-sizing: border-box;
        }

        /* Spans and inline elements with midnightblue background */
        span[style*="background-color: midnightblue"],
        span[style*="background-color:midnightblue"],
        span[style*="background: midnightblue"],
        span[style*="background:midnightblue"],
        h3 span[style*="background-color: midnightblue"],
        h3 span[style*="background-color:midnightblue"],
        h3 span[style*="background: midnightblue"],
        h3 span[style*="background:midnightblue"],
        h2 span[style*="background-color: midnightblue"],
        h2 span[style*="background-color:midnightblue"],
        h2 span[style*="background: midnightblue"],
        h2 span[style*="background:midnightblue"] {
            margin-right: 1rem !important;
            display: inline-block;
            box-sizing: border-box;
        }

        /* Generic elements with midnightblue background (fallback for other elements not covered above) */
        [style*="background-color: midnightblue"]:not(.navbar):not(header):not(.navbar-brand):not(.navbar-nav):not(.navbar-collapse):not(table thead th):not(thead tr):not(.card-header):not(.section-header):not(.list-group-item):not(.btn):not(span):not(h2 span):not(h3 span),
        [style*="background-color:midnightblue"]:not(.navbar):not(header):not(.navbar-brand):not(.navbar-nav):not(.navbar-collapse):not(table thead th):not(thead tr):not(.card-header):not(.section-header):not(.list-group-item):not(.btn):not(span):not(h2 span):not(h3 span),
        [style*="background: midnightblue"]:not(.navbar):not(header):not(.navbar-brand):not(.navbar-nav):not(.navbar-collapse):not(table thead th):not(thead tr):not(.card-header):not(.section-header):not(.list-group-item):not(.btn):not(span):not(h2 span):not(h3 span),
        [style*="background:midnightblue"]:not(.navbar):not(header):not(.navbar-brand):not(.navbar-nav):not(.navbar-collapse):not(table thead th):not(thead tr):not(.card-header):not(.section-header):not(.list-group-item):not(.btn):not(span):not(h2 span):not(h3 span) {
            margin-right: 1rem !important;
            box-sizing: border-box;
        }

        /* Adjust full-width elements to compensate for margin */
        [style*="background-color: midnightblue"][style*="width: 100%"],
        [style*="background-color:midnightblue"][style*="width: 100%"],
        [style*="background: midnightblue"][style*="width: 100%"],
        [style*="background:midnightblue"][style*="width: 100%"] {
            width: calc(100% - 1rem) !important;
        }

        /* Adjust cards that contain midnightblue card headers to maintain position */
        /* Note: Using a class-based approach for better browser compatibility */
        .card .card-header[style*="background-color: midnightblue"] ~ *,
        .card .card-header[style*="background-color:midnightblue"] ~ *,
        .card .card-header[style*="background: midnightblue"] ~ *,
        .card .card-header[style*="background:midnightblue"] ~ * {
            /* Card body and other siblings remain at full width */
        }
        
        /* Ensure cards with midnightblue headers maintain proper spacing */
        .card-header[style*="background-color: midnightblue"] ~ .card-body,
        .card-header[style*="background-color:midnightblue"] ~ .card-body,
        .card-header[style*="background: midnightblue"] ~ .card-body,
        .card-header[style*="background:midnightblue"] ~ .card-body {
            width: 100%;
        }

        /* Exclude navbar, header, sidebar, and table headers from right margin */
        .navbar,
        .navbar[style*="background-color: midnightblue"],
        .navbar[style*="background-color:midnightblue"],
        .navbar[style*="background: midnightblue"],
        .navbar[style*="background:midnightblue"],
        header,
        header[style*="background-color: midnightblue"],
        header[style*="background-color:midnightblue"],
        .navbar-brand,
        .navbar-nav,
        .navbar-collapse,
        aside,
        aside[style*="background-color: midnightblue"],
        aside[style*="background-color:midnightblue"],
        aside[style*="background: midnightblue"],
        aside[style*="background:midnightblue"],
        .sidebar,
        .sidebar[style*="background-color: midnightblue"],
        .sidebar[style*="background-color:midnightblue"],
        .admin-sidebar-custom,
        .admin-sidebar-custom[style*="background-color: midnightblue"],
        .admin-sidebar-custom[style*="background-color:midnightblue"],
        .staff-sidebar-custom,
        .staff-sidebar-custom[style*="background-color: midnightblue"],
        .staff-sidebar-custom[style*="background-color:midnightblue"],
        #adminSidebar,
        #adminSidebar[style*="background-color: midnightblue"],
        #adminSidebar[style*="background-color:midnightblue"],
        table thead th,
        table thead th[style*="background-color: midnightblue"],
        table thead th[style*="background-color:midnightblue"],
        thead tr[style*="background-color: midnightblue"],
        thead tr[style*="background-color:midnightblue"],
        thead tr {
            margin-right: 0 !important;
        }

        @media (max-width: 991.98px) {
            /* Adjust dashboard header on mobile */
            .dashboard-header-global {
                top: 56px !important;
                min-height: 65px !important;
            }
            body {
                padding-top: 135px !important; /* More space on mobile */
            }
        }

        /* Dashboard header styling - smaller size to prevent overlap */
        .dashboard-header-global .dashboard-header {
            margin-bottom: 0 !important;
            padding: 0.5rem 0 !important;
        }
        .dashboard-header-global .dashboard-header h1 {
            font-size: 1.5rem !important;
            font-weight: 600;
            margin-bottom: 0.25rem !important;
            line-height: 1.2;
        }
        .dashboard-header-global .dashboard-header p {
            font-size: 0.875rem !important;
            color: var(--grey);
            margin-bottom: 0 !important;
            line-height: 1.3;
        }

        /* Keep original dashboard header styles for pages that still use it */
        .dashboard-header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: .5rem;
        }
        .dashboard-header p {
            font-size: 1rem;
            color: var(--grey);
        }

        /* Ensure sidebar and main content are below the fixed headers */
        .sidebar, .admin-sidebar-custom, .staff-sidebar-custom, .main, .container-fluid, .col-md-10, #adminMain, #staffMain {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* Ensure proper row layout for admin dashboard */
        .container-fluid > .row {
            display: flex !important;
            flex-wrap: nowrap !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        /* Ensure sidebar and main content stay side by side */
        .admin-sidebar-custom.col-md-2,
        .staff-sidebar-custom.col-md-2,
        aside.col-md-2.sidebar {
            flex: 0 0 16.666667% !important;
            max-width: 16.666667% !important;
            margin-right: 0 !important;
        }

        #adminMain.col-md-10,
        #staffMain.col-md-10,
        main.col-md-10 {
            flex: 0 0 83.333333% !important;
            max-width: 83.333333% !important;
            margin-left: 0 !important;
        }

        @media (max-width: 767.98px) {
            .container-fluid > .row {
                flex-wrap: wrap !important;
            }
            .admin-sidebar-custom.col-md-2,
            .staff-sidebar-custom.col-md-2,
            aside.col-md-2.sidebar {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
            #adminMain.col-md-10,
            #staffMain.col-md-10,
            main.col-md-10 {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
        }
        /* Dropdown styling (all screen sizes) */
        .dropdown-menu {
            margin-top: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.15);
            min-width: 200px;
            }
        .dropdown-item {
            padding: 0.75rem 1.5rem;
                display: flex;
                align-items: center;
            gap: 0.5rem;
        }
        .dropdown-item i {
            font-size: 1.1rem;
            }
        .dropdown-header {
            font-weight: 600;
            padding: 0.75rem 1.5rem;
                display: flex;
                align-items: center;
            gap: 0.5rem;
            }
        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: #f8f9fa;
        }
        #logout-btn-mobile:hover,
        #logout-btn-mobile:focus {
            background-color: #f8f9fa !important;
            color: #212529 !important;
        }
    </style>
</head>
<body>
    
    @php use Illuminate\Support\Facades\Auth; @endphp

    <!-- Back to top button (hidden by default, shown via JS) -->
    <a href="#top" class="back-to-top" aria-label="Back to top"></a>

    <div id="top"></div>



    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <header>

        <!-- Main navigation -->
    <nav class="navbar navbar-expand-lg navbar-light" aria-label="Main navigation" style="z-index:1050; box-shadow: none !important; border: none !important;">
            <div class="container-fluid px-4">
                    <a class="navbar-brand" href="{{ url('/') }}" aria-label="OSA Central Hub Home">
                        <span class="text-primary">OSA</span>Central Hub
                    </a>
                    <!-- Dropdown Menu (Always Visible) -->
                    @if(!request()->routeIs('login') && !request()->routeIs('password.request') && !request()->routeIs('password.reset'))
                    <div class="dropdown ml-auto">
                    <button 
                            class="btn btn-primary dropdown-toggle" 
                        type="button" 
                            id="mobileNavDropdown"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-label="Menu">
                            <i class="bi bi-list"></i> Menu
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="mobileNavDropdown" id="mobileNavDropdownMenu">
                            @guest
                                <a class="dropdown-item" href="{{ route('login') }}">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </a>
                            @else
                                    @php
                                        $profileRoute = route('student.profile');
                                        if (\Illuminate\Support\Facades\Auth::user()->role == 4) {
                                            $profileRoute = route('admin.profile');
                                        } elseif (\Illuminate\Support\Facades\Auth::user()->role == 2) {
                                            $profileRoute = route('staff.profile');
                                        } elseif (\Illuminate\Support\Facades\Auth::user()->role == 3) {
                                            $profileRoute = route('student-leader.profile');
                                        }
                                    @endphp
                                <div class="dropdown-header">
                                    <i class="bi bi-person-circle"></i> Hi, {{ \Illuminate\Support\Facades\Auth::user()->first_name }}
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ $profileRoute }}">
                                    <i class="bi bi-person"></i> Profile
                                </a>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#changeImageModal" onclick="event.preventDefault();">
                                    <i class="bi bi-image"></i> Change Image
                                </a>
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-mobile" class="d-inline m-0">
                                            @csrf
                                    <button type="submit" class="dropdown-item" id="logout-btn-mobile" style="border: none; background: none; width: 100%; text-align: left; padding: 0.5rem 1.5rem; color: #212529; cursor: pointer;">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                        </form>
                            @endguest
                        </div>
                </div>
                    @endif
            </div>
        </nav>
    </header>


    <main id="main-content" class="py-4">
        @yield('content')
    </main>

    <!-- Change Image Modal -->
    @auth
    <div class="modal fade" id="changeImageModal" tabindex="-1" role="dialog" aria-labelledby="changeImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeImageModalLabel">Change Profile Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="changeImageForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="profileImage">Select Image</label>
                            <input type="file" class="form-control-file" id="profileImage" name="image" accept="image/*" required>
                            <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG, GIF, WEBP (Max: 10MB)</small>
                        </div>
                        <div id="imagePreview" class="mt-3 text-center" style="display: none;">
                            <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        </div>
                        <div id="imageUploadError" class="alert alert-danger mt-3" style="display: none;"></div>
                        <div id="imageUploadSuccess" class="alert alert-success mt-3" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="uploadImageBtn">
                            <span class="spinner-border spinner-border-sm" id="uploadSpinner" style="display: none;" role="status" aria-hidden="true"></span>
                            Upload Image
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('profileImage');
            const previewImg = document.getElementById('previewImg');
            const imagePreview = document.getElementById('imagePreview');
            const changeImageForm = document.getElementById('changeImageForm');
            const uploadBtn = document.getElementById('uploadImageBtn');
            const uploadSpinner = document.getElementById('uploadSpinner');
            const errorDiv = document.getElementById('imageUploadError');
            const successDiv = document.getElementById('imageUploadSuccess');

            // Preview image when selected
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.style.display = 'block';
                        errorDiv.style.display = 'none';
                        successDiv.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.style.display = 'none';
                }
            });

            // Handle form submission
            changeImageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                uploadBtn.disabled = true;
                uploadSpinner.style.display = 'inline-block';
                errorDiv.style.display = 'none';
                successDiv.style.display = 'none';

                fetch('{{ route("profile.update-image") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    uploadBtn.disabled = false;
                    uploadSpinner.style.display = 'none';
                    
                    if (data.success) {
                        successDiv.textContent = data.message;
                        successDiv.style.display = 'block';
                        
                        // Reload page after 1.5 seconds to show new image
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        errorDiv.textContent = data.message || 'Failed to upload image';
                        errorDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    uploadBtn.disabled = false;
                    uploadSpinner.style.display = 'none';
                    errorDiv.textContent = 'An error occurred: ' + error.message;
                    errorDiv.style.display = 'block';
                });
            });

            // Reset form when modal is closed
            $('#changeImageModal').on('hidden.bs.modal', function() {
                changeImageForm.reset();
                imagePreview.style.display = 'none';
                errorDiv.style.display = 'none';
                successDiv.style.display = 'none';
                uploadBtn.disabled = false;
                uploadSpinner.style.display = 'none';
            });
        });
    </script>
    @endauth

    <!-- Scripts -->
    <script src="{{ asset('assets/js/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/wow/wow.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/owl-carousel/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize WOW animations
            new WOW().init();   

            // Ensure main content is focusable for skip links (if added later)
            document.getElementById('main-content').setAttribute('tabindex', '-1');

            // Mobile dropdown menu handler
            var mobileDropdownBtn = document.getElementById('mobileNavDropdown');
            var mobileDropdownMenu = document.getElementById('mobileNavDropdownMenu');
            
            if (mobileDropdownBtn && mobileDropdownMenu) {
                // Use jQuery for Bootstrap dropdown if available, otherwise manual toggle
                if (typeof jQuery !== 'undefined' && jQuery.fn.dropdown) {
                    jQuery(mobileDropdownBtn).dropdown();
                } else {
                    // Manual toggle handler
                    mobileDropdownBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        var isExpanded = this.getAttribute('aria-expanded') === 'true';
                        
                        // Toggle dropdown - close if open, open if closed
                        if (isExpanded) {
                            // Close dropdown
                            mobileDropdownMenu.classList.remove('show');
                            this.setAttribute('aria-expanded', 'false');
                            this.classList.remove('show');
                        } else {
                            // Open dropdown
                            mobileDropdownMenu.classList.add('show');
                            this.setAttribute('aria-expanded', 'true');
                            this.classList.add('show');
                        }
                    });
                }
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (mobileDropdownBtn && mobileDropdownMenu) {
                        var isClickInside = mobileDropdownBtn.contains(e.target) || mobileDropdownMenu.contains(e.target);
                        
                        if (!isClickInside) {
                            mobileDropdownMenu.classList.remove('show');
                            mobileDropdownBtn.setAttribute('aria-expanded', 'false');
                            mobileDropdownBtn.classList.remove('show');
                        }
                    }
                });
                
                // Close dropdown when clicking on dropdown items (except logout form)
                if (mobileDropdownMenu) {
                    mobileDropdownMenu.addEventListener('click', function(e) {
                        // Don't close if clicking on logout button (form submission)
                        if (e.target.id !== 'logout-btn-mobile' && e.target.closest('#logout-form-mobile') === null) {
                            // Close after a short delay to allow navigation
                            setTimeout(function() {
                                mobileDropdownMenu.classList.remove('show');
                                mobileDropdownBtn.setAttribute('aria-expanded', 'false');
                                mobileDropdownBtn.classList.remove('show');
                            }, 100);
                        }
                    });
                }
            }

            // Bootstrap dropdowns will work automatically with data attributes
            // No need for explicit initialization

            // Auto-dismiss flash alerts after 3 seconds (Bootstrap 4 compatible)
            setTimeout(function() {
                document.querySelectorAll('.alert.alert-dismissible').forEach(function(alert) {
                    var closeBtn = alert.querySelector('[data-dismiss="alert"]');
                    if (closeBtn) {
                        closeBtn.click();
                    } else {
                        // Fallback: fade out and remove
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.remove();
                        }, 500);
                    }
                });
            }, 3000);

            // Handle logout form submission to prevent CSRF errors (desktop)
            const logoutForm = document.getElementById('logout-form');
            const logoutBtn = document.getElementById('logout-btn');
            
            // Handle logout form submission for mobile
            const logoutFormMobile = document.getElementById('logout-form-mobile');
            const logoutBtnMobile = document.getElementById('logout-btn-mobile');
            
            function handleLogout(form, btn) {
                if (!form || !btn) return;
                
                let isSubmitting = false;
                
                form.addEventListener('submit', function(e) {
                    // Prevent double submission
                    if (isSubmitting) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Always refresh CSRF token from meta tag before submission
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfInput = form.querySelector('input[name="_token"]');
                    
                    if (csrfMeta && csrfInput) {
                        const freshToken = csrfMeta.getAttribute('content');
                        if (freshToken) {
                            csrfInput.value = freshToken;
                        }
                    }
                    
                    isSubmitting = true;
                    btn.disabled = true;
                    if (btn.textContent !== undefined) {
                        btn.textContent = 'Logging out...';
                    }
                });
            }
            
            // Handle desktop logout
            handleLogout(logoutForm, logoutBtn);
            
            // Handle mobile logout
            handleLogout(logoutFormMobile, logoutBtnMobile);
                
                // Also update CSRF token on page visibility change (in case session refreshed)
                document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const forms = [logoutForm, logoutFormMobile].filter(Boolean);
                    forms.forEach(function(form) {
                        const csrfInput = form.querySelector('input[name="_token"]');
                        if (csrfMeta && csrfInput) {
                            const freshToken = csrfMeta.getAttribute('content');
                            if (freshToken) {
                                csrfInput.value = freshToken;
                            }
                        }
                    });
                    }
                });
            
            // Global CSRF token refresh for all forms before submission
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.tagName === 'FORM') {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfInput = form.querySelector('input[name="_token"]');
                    
                    if (csrfMeta && csrfInput) {
                        const freshToken = csrfMeta.getAttribute('content');
                        if (freshToken) {
                            csrfInput.value = freshToken;
                        }
                    }
                }
            }, true); // Use capture phase to refresh before form submission
            
            // Refresh CSRF token periodically (every 30 minutes) to prevent expiration
            setInterval(function() {
                // Refresh the page's CSRF token by making a lightweight request
                fetch(window.location.href, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin'
                }).then(function(response) {
                    // CSRF token is automatically updated in the meta tag via Laravel
                    // The meta tag should already be up to date
                }).catch(function(error) {
                    console.warn('CSRF token refresh check failed:', error);
                });
            }, 30 * 60 * 1000); // Every 30 minutes

            // Global uppercase input conversion for all text inputs (system-wide)
            // Excludes: email, password, file inputs, number inputs, and hidden inputs
            function initializeUppercaseInputs() {
                const textInputs = document.querySelectorAll('input[type="text"], input[type="tel"], textarea');
                textInputs.forEach(input => {
                    // Skip if already processed
                    if (input.dataset.uppercaseInitialized === 'true') {
                        return;
                    }
                    
                    // Skip email, password, file, hidden, search, and number inputs
                    const skipTypes = ['email', 'password', 'file', 'hidden', 'search', 'number'];
                    const skipNames = ['email', 'password', 'password_confirmation', 'password_confirmation_new', 'current_password', 'new_password', 'new_password_confirmation'];
                    
                    if (skipTypes.includes(input.type) || skipNames.includes(input.name) || skipNames.includes(input.id)) {
                        return;
                    }
                    
                    // Mark as initialized
                    input.dataset.uppercaseInitialized = 'true';
                    
                    // Special handling for middle_name (middle initial) - only allow one letter
                    if (input.name === 'middle_name' || input.id === 'middle_name') {
                        input.addEventListener('input', function(e) {
                            // Remove any non-alphabetic characters and keep only first letter
                            this.value = this.value.replace(/[^A-Za-z]/g, '').substring(0, 1).toUpperCase();
                        });
                        
                        input.addEventListener('keypress', function(e) {
                            // Only allow alphabetic characters
                            const char = String.fromCharCode(e.which);
                            if (!/[A-Za-z]/.test(char)) {
                                e.preventDefault();
                            }
                            // If already has a character, prevent adding more
                            if (this.value.length >= 1) {
                                e.preventDefault();
                            }
                        });
                        
                        input.addEventListener('paste', function(e) {
                            e.preventDefault();
                            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                            const firstLetter = pastedText.replace(/[^A-Za-z]/g, '').substring(0, 1).toUpperCase();
                            this.value = firstLetter;
                        });
                        return;
                    }
                    
                    // Convert to uppercase on input
                    input.addEventListener('input', function(e) {
                        const cursorPosition = this.selectionStart;
                        const originalValue = this.value;
                        this.value = this.value.toUpperCase();
                        
                        // Restore cursor position (adjust if length changed)
                        const newLength = this.value.length;
                        const lengthDiff = this.value.length - originalValue.length;
                        const newPosition = Math.min(cursorPosition + lengthDiff, newLength);
                        this.setSelectionRange(newPosition, newPosition);
                    });
                    
                    // Also convert on paste
                    input.addEventListener('paste', function(e) {
                        setTimeout(() => {
                            const cursorPosition = this.selectionStart;
                            this.value = this.value.toUpperCase();
                            // Restore cursor position
                            this.setSelectionRange(cursorPosition, cursorPosition);
                        }, 0);
                    });
                });
            }
            
            // Initialize on page load
            initializeUppercaseInputs();
            
            // Re-initialize for dynamically added inputs (e.g., AJAX-loaded forms)
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        initializeUppercaseInputs();
                    }
                });
            });
            
            // Observe the entire document for new nodes
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>

    @stack('scripts')
</body>
</html>