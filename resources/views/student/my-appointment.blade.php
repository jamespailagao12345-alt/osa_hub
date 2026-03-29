@extends('layouts.app')

@section('title', 'My Appointments')

@section('content')
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar: Quick Actions -->
    <div class="col-md-3 d-flex align-items-start">
      <div class="card mb-4 w-100" style="margin-top: 3.5rem;">
        <div class="card-header bg-primary text-white" style="text-align: center; font-size: 1.5rem; padding-top: 0.7rem; padding-bottom: 0.7rem;">Quick Actions</div>
        <div class="card-body">
          <div class="mb-3">
            <h5>Book Appointment</h5>
            <a href="{{ route('student.make-appointment') }}" class="btn btn-primary w-100">Book an Appointment</a>
          </div>
          <div class="mb-3">
            <h5>View Events</h5>
            <a href="{{ route('student.events.index') }}" class="btn btn-secondary w-100">See Upcoming</a>
          </div>
          <div class="mb-3">
            <h5>Organization Registration Request</h5>
            <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#orgRegModal">Request Organization Registration</button>
          </div>
          @if(auth()->user()->designation === 'student-leader')
          <div class="mb-3">
            <h5>Organizational Dashboard</h5>
            <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#assistantSwitchModal">Open</button>
          </div>
          @endif
          <div class="mb-3">
            <h5>My QR Code</h5>
            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#qrModal">View QR</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Main Content -->
    <div class="col-md-9">
      <div class="dashboard-header text-center">
        <h1>My Appointments</h1>
        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary mt-3">&larr; Return to Dashboard</a>
      </div>
      <div class="page-section">
        <!-- Place all appointment main content here -->
        @yield('appointment-content')
      </div>
    </div>
  </div>
</div>
</head>
<body>

  <!-- Back to top button -->
  <div class="back-to-top"></div>

  <header>
    <div class="topbar">
      <div class="container">
        <div class="row">
          <div class="col-sm-8 text-sm">
            <div class="site-info">
              <a href="#"><span class="mai-call text-primary"></span> +63 919 1234567</a>
              <span class="divider">|</span>
              <a href="#"><span class="mai-mail text-primary"></span> osa.balubal@ustp.edu.ph</a>
            </div>
          </div>
          <div class="col-sm-4 text-right text-sm">
            <div class="social-mini-button">
              <a href="#"><span class="mai-logo-facebook-f"></span></a>
              <a href="#"><span class="mai-logo-twitter"></span></a>
              <a href="#"><span class="mai-logo-dribbble"></span></a>
              <a href="#"><span class="mai-logo-instagram"></span></a>
            </div>
          </div>
        </div> <!-- .row -->
      </div> <!-- .container -->
    </div> <!-- .topbar -->

    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
      <div class="container">
        <a class="navbar-brand" href="#"><span class="text-primary">USTP</span>-Balubal</a>

        <form action="#">
          <div class="input-group input-navbar">
            <div class="input-group-prepend">
              <span class="input-group-text" id="icon-addon1"><span class="mai-search"></span></span>
            </div>
            <input type="text" class="form-control" placeholder="Enter keyword.." aria-label="Username" aria-describedby="icon-addon1">
          </div>
        </form>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupport" aria-controls="navbarSupport" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupport">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
              <a class="nav-link" href="index.html">MyDashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="about.html">About Us</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="doctors.html">OSA Staff</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="blog.html">HIMULAK</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="contact.html">Contact US</a>
            </li>

            @if(Route::has('login'))

                @auth

                <li class="nav-item">
                  <a class="nav-link" style="background-color:midnightblue; color:white;" href="{{'myappointment'}}">My Appointment</a>
                </li>

                    <x-app-layout>
                    </x-app-layout>

                @else

                    <li class="nav-item">
                        <a class="btn btn-primary ml-lg-3" href="{{route('login')}}">Login</a>
                    </li>

                    <li class="nav-item">
                        <!-- Registration disabled: <a class="btn btn-primary ml-lg-3" href="{{route('register')}}">Register</a> -->
                    </li>
                @endauth
            @endif

          </ul>
        </div> <!-- .navbar-collapse -->
      </div> <!-- .container -->
    </nav>
  </header>

    <div align="center" style="padding:70px">
        <table>
            <tr style="background-color:midnightblue;">
                <th style="padding:10px; font-size:20px; color:white">Staff</th>
                <th style="padding:10px; font-size:20px; color:white">Date</th>
                <th style="padding:10px; font-size:20px; color:white">Message</th>
                <th style="padding:10px; font-size:20px; color:white">Status</th>
                <th style="padding:10px; font-size:20px; color:white">Cancel</th>
            </tr>
            @foreach($appoint as $appoints)

            <tr style="background-color:#5DFFBF;" align="center">
                <td style="padding:10px; font-size:20px; color:black">{{$appoints->staff}}</td>
                <td style="padding:10px; font-size:20px; color:black">{{$appoints->date}}</td>
                <td style="padding:10px; font-size:20px; color:black">{{$appoints->message}}</td>
                <td style="padding:10px; font-size:20px; color:black">{{$appoints->status}}</td>
                <td>
                  <a class="btn btn-danger" onclick="return 
                  confirm('Are you sure want to delete this message?')" href="{{url('cancel_appoint',$appoints->id)}}">Cancel</a>
                </td>
            </tr>

            @endforeach

        </table>

    </div>

    @foreach ($appoint as $appointment)
        @if($appointment->status === 'approved')
            <form method="POST" action="{{ route('student.appointments.reschedule', $appointment->id) }}" class="flex gap-2 items-center">
                @csrf
                @method('PUT')
                <input type="date" name="appointment_date" value="{{ $appointment->appointment_date->format('Y-m-d') }}" required class="border rounded px-2 py-1" />
                <input type="time" name="appointment_time" value="{{ $appointment->appointment_time }}" required class="border rounded px-2 py-1" />
                <button type="submit" class="px-3 py-1 bg-yellow-600 text-white rounded">Reschedule</button>
            </form>
        @endif
    @endforeach

<script src="../assets/js/jquery-3.5.1.min.js"></script>

<script src="../assets/js/bootstrap.bundle.min.js"></script>

<script src="../assets/vendor/owl-carousel/js/owl.carousel.min.js"></script>

<script src="../assets/vendor/wow/wow.min.js"></script>

<script src="../assets/js/theme.js"></script>
  
</body>
</html>