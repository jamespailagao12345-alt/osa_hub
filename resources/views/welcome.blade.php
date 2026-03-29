@extends('layouts.app')

@section('title', 'OSA Central Hub - USTP Balubal')

@section('content')

@if(session('success'))
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

<!-- Top contact bar for welcome page only -->
<div class="topbar" role="banner">
    <div class="container">
        <div class="d-flex justify-content-center py-2">
            <div class="site-info">
                <a href="tel:+6312344556666" class="me-3">
                    <span class="mai-call text-primary" aria-hidden="true"></span>
                    <span class="visually-hidden">Call:</span>
                    +63 123 4455 6666
                </a>
                <a href="mailto:osa.balubal@ustp.edu.ph">
                    <span class="mai-mail text-primary" aria-hidden="true"></span>
                    <span class="visually-hidden">Email:</span>
                    osa.balubal@ustp.edu.ph
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-hero bg-image overlay-dark" id="hero-carousel-container">
    @if(!empty($bgImages))
        @foreach($bgImages as $index => $bgImage)
            <div class="hero-bg-slide {{ $index === 0 ? 'active' : '' }}" style="background-image: url('{{ $bgImage }}');"></div>
        @endforeach
    @else
        <div class="hero-bg-slide active" style="background-image: url('{{ asset('assets/img/bg_image_1.jpg') }}');"></div>
    @endif
    <div class="hero-section">
        <div class="container wow zoomIn" style="width: 100%;">
            <div class="hero-content-wrapper" style="width: 100%;">
                <span class="subhead d-block" style="width: 100%; text-align: center;">Breaking Limits</span>
                <h1 class="display-4" style="width: 100%; text-align: center;">
                    <span class="osa-text">OSA</span>
                    <span class="central-hub-text">Central Hub</span>
                </h1>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Remove all top spacing - navbar is fixed, content starts immediately below */
    body {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    
    /* Get exact navbar height and position topbar directly below with zero gap */
    header .navbar {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
        line-height: 1 !important;
    }
    
    header .navbar .container-fluid {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
        line-height: 1 !important;
    }
    
    header {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
        line-height: 0 !important;
    }
    
    .topbar {
        position: relative;
        margin-top: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
        border-top: none !important;
        border-bottom: none !important;
        line-height: 1 !important;
    }
    
    /* Position topbar directly below navbar with zero gap */
    .topbar .container {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    
    .topbar .d-flex {
        padding-top: 0.15rem !important;
        padding-bottom: 0.15rem !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    
    /* Use JavaScript to get exact navbar height and position topbar */
    @media (max-width: 768px) {
        .topbar {
            margin-top: 5px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
        
        .topbar .d-flex {
            padding-top: 0.15rem !important;
            padding-bottom: 0.15rem !important;
        }
    }
    
    #hero-carousel-container {
        position: relative;
        overflow: hidden;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    .hero-bg-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        opacity: 0;
        transition: opacity 2s ease-in-out;
        z-index: 0;
    }
    
    .hero-bg-slide.active {
        opacity: 1;
    }
    
    .hero-section {
        position: relative;
        z-index: 1;
        width: 100%;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 8vh;
        padding-bottom: 8vh;
    }
    
    .hero-section .container {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 1rem;
        padding-right: 1rem;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        height: 100%;
    }
    
    .hero-section .hero-content-wrapper {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    
    .hero-section .subhead {
        width: 100%;
        display: block;
        text-align: center;
        font-size: min(4.5vw, 70px);
        font-weight: 700;
        margin-bottom: 0;
        margin-top: 0;
        color: #ffc107 !important; /* Yellow */
        text-shadow: 
            0 0 10px rgba(255, 193, 7, 0.8),
            0 0 20px rgba(255, 193, 7, 0.6),
            3px 3px 0px midnightblue,
            -3px -3px 0px midnightblue,
            3px -3px 0px midnightblue,
            -3px 3px 0px midnightblue; /* Yellow glow with midnightblue outline */
        letter-spacing: 2px;
        line-height: 1.1;
        position: relative;
        top: 0;
    }
    
    .hero-section .display-4 {
        width: 100%;
        display: block;
        text-align: center;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 0;
        margin-top: 0.1rem;
    }
    
    .hero-section .display-4 .osa-text {
        color: #ffc107 !important; 
        -webkit-text-stroke: 12px midnightblue; 
        text-stroke: 12px midnightblue;
        -webkit-text-fill-color: #ffc107; 
        text-fill-color: #ffc107;
        font-weight: 900;
        display: block;
        font-size: min(32vw, 560px); 
        text-shadow: 
            0 0 30px rgba(255, 193, 7, 0.5),
            0 0 60px rgba(255, 193, 7, 0.3),
            8px 8px 0px midnightblue,
            -8px -8px 0px midnightblue,
            8px -8px 0px midnightblue,
            -8px 8px 0px midnightblue,
            0 8px 0px midnightblue,
            0 -8px 0px midnightblue,
            8px 0 0px midnightblue,
            -8px 0 0px midnightblue; 
        letter-spacing: 8px; 
        paint-order: stroke fill; 
        margin-bottom: -0.3em; 
        line-height: 0.9; 
        position: relative;
        z-index: 1;
    }
    
    .hero-section .display-4 .central-hub-text {
        color: #000000 !important; 
        font-weight: 700;
        display: block;
        font-size: min(5vw, 90px);
        margin-left: 0;
        margin-top: -0.4em; 
        letter-spacing: 2px;
        line-height: 1.2;
        position: relative;
        z-index: 20; 
        padding-top: 0;
        text-shadow: 
            3px 3px 6px rgba(255, 255, 255, 1),
            -3px -3px 6px rgba(255, 255, 255, 1),
            3px -3px 6px rgba(255, 255, 255, 1),
            -3px 3px 6px rgba(255, 255, 255, 1),
            0 3px 6px rgba(255, 255, 255, 1),
            0 -3px 6px rgba(255, 255, 255, 1),
            3px 0 6px rgba(255, 255, 255, 1),
            -3px 0 6px rgba(255, 255, 255, 1); /* Stronger white shadow for better visibility on background */
    }
    
    @media (max-width: 768px) {
        .hero-section {
            padding-top: 5vh;
            padding-bottom: 5vh;
        }
        
        .hero-section .subhead {
            font-size: min(6vw, 60px);
            margin-bottom: 0;
            text-shadow: 
                0 0 8px rgba(255, 193, 7, 0.8),
                0 0 16px rgba(255, 193, 7, 0.6),
                2px 2px 0px midnightblue,
                -2px -2px 0px midnightblue,
                2px -2px 0px midnightblue,
                -2px 2px 0px midnightblue;
        }
        
        .hero-section .display-4 {
            margin-top: 0.3rem;
        }
        
        .hero-section .display-4 .osa-text {
            font-size: min(36vw, 500px); 
            -webkit-text-stroke: 10px midnightblue; 
            text-stroke: 10px midnightblue;
            text-shadow: 
                0 0 25px rgba(255, 193, 7, 0.5),
                0 0 50px rgba(255, 193, 7, 0.3),
                6px 6px 0px midnightblue,
                -6px -6px 0px midnightblue,
                6px -6px 0px midnightblue,
                -6px 6px 0px midnightblue,
                0 6px 0px midnightblue,
                0 -6px 0px midnightblue,
                6px 0 0px midnightblue,
                -6px 0 0px midnightblue;
            letter-spacing: 6px; 
            margin-bottom: -0.2em; 
            line-height: 0.9;
            z-index: 1; 
        }
        
        .hero-section .display-4 .central-hub-text {
            font-size: min(6vw, 80px);
            margin-top: -0.3em; 
            padding-top: 0;
            z-index: 20; 
            text-shadow: 
                3px 3px 6px rgba(255, 255, 255, 1),
                -3px -3px 6px rgba(255, 255, 255, 1),
                3px -3px 6px rgba(255, 255, 255, 1),
                -3px 3px 6px rgba(255, 255, 255, 1),
                0 3px 6px rgba(255, 255, 255, 1),
                0 -3px 6px rgba(255, 255, 255, 1),
                3px 0 6px rgba(255, 255, 255, 1),
                -3px 0 6px rgba(255, 255, 255, 1); 
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const topbar = document.querySelector('.topbar');
        if (topbar) {
            topbar.style.marginTop = '10px';
            topbar.style.paddingTop = '0';
            topbar.style.marginBottom = '0';
        }
        
        const slides = document.querySelectorAll('.hero-bg-slide');
        if (slides.length <= 1) return;
        
        let currentIndex = 0;
        
        function showNextSlide() {
            slides[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % slides.length;
            slides[currentIndex].classList.add('active');
        }
        
        
        setInterval(showNextSlide, 3000);
    });
</script>
@endpush


@include('welcome-osa')
@include('show-staff')
@include('upcoming-events')
@include('make-appointment')

{{-- Registration is disabled. Remove any registration links or forms. --}}

@endsection