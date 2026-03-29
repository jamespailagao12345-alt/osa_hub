
@extends('layouts.app')
@php use Illuminate\Support\Facades\Route; @endphp

@section('content')
<div class="login-page-wrapper" style="min-height: 100vh; position: relative; background-image: url('{{ asset('assets/img/bg_image_1.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.4); z-index: 1;"></div>
    <div class="container" style="position: relative; z-index: 2; padding-top: 2rem; padding-bottom: 2rem;">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-center mb-4">
                    <a href="{{ route('welcome') }}" style="text-decoration: none; display: inline-block;">
                        <img src="{{ asset('assets/img/ustp-logo1.1.png') }}" alt="USTP Logo" style="max-width: 200px; height: auto;">
                    </a>
                </div>
                <div class="card" style="box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <div class="position-relative">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" style="padding-right: 2.5rem;">
                                    <button type="button" class="btn btn-link position-absolute p-0" id="togglePassword" style="border: none; background: none; cursor: pointer; z-index: 10; right: 0.75rem; top: 50%; transform: translateY(-50%); height: auto; line-height: 1;">
                                        <i class="bi bi-eye" id="passwordIcon" style="font-size: 0.875rem; vertical-align: middle; color: black;"></i>
                                    </button>
                                </div>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4 d-flex justify-content-center">
                                <button type="submit" class="btn btn-sm" style="background-color: transparent !important; border-color: midnightblue; color: midnightblue;">
                                    {{ __('Login') }}
                                </button>
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <div class="mt-2 text-center">
                                    <a href="{{ route('password.request') }}" class="text-decoration-none" style="font-size: 0.75rem;">
                                        {{ __('Forget Password') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Hide navbar/header on login page */
    header {
        display: none !important;
    }
    
    /* Remove body padding since navbar is hidden */
    body {
        padding-top: 0 !important;
    }
    
    .login-page-wrapper {
        min-height: 100vh;
        margin-top: 0;
        padding-top: 0;
    }
    
    @media (max-width: 768px) {
        .login-page-wrapper {
            background-attachment: scroll;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');
    
    if (togglePasswordBtn && passwordField) {
        togglePasswordBtn.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            passwordIcon.classList.toggle('bi-eye');
            passwordIcon.classList.toggle('bi-eye-slash');
        });
    }
});
</script>
@endpush
@endsection
