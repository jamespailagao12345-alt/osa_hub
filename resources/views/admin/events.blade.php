@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main class="col-md-10 py-4">
        <div class="admin-back-btn-wrap">
            @if(request()->has('return_to'))
              <a href="{{ urldecode(request('return_to')) }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            @else
              <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            @endif
        </div>
        <div class="py-3">
            <h1 class="h4 mb-4">Staff Events Management</h1>

            <!-- Category Books Grid -->
            <div class="row g-4 mb-5">
                <!-- Pending Events Book -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('admin.events.pending', request()->only(['return_to'])) }}" class="text-decoration-none">
                        <div class="book-card position-relative" style="cursor: pointer; transition: transform 0.2s;">
                            <div class="book-icon-container text-center">
                                <svg width="300" height="400" viewBox="0 0 150 200" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
                                    <!-- Book cover -->
                                    <rect x="10" y="20" width="120" height="160" fill="darkgreen" rx="4"/>
                                    <!-- Book pages -->
                                    <rect x="15" y="25" width="110" height="150" fill="darkgreen"/>
                                    <!-- Book binding -->
                                    <rect x="8" y="20" width="4" height="160" fill="#0d4f0d"/>
                                </svg>
                                <div class="book-title-overlay position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; font-size: 1.8rem; text-align: center; width: 160px; line-height: 1.2;">
                                    Pending Events
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-warning text-dark">{{ $pendingEvents->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Current Events Book -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('admin.events.current', request()->only(['return_to'])) }}" class="text-decoration-none">
                        <div class="book-card position-relative" style="cursor: pointer; transition: transform 0.2s;">
                            <div class="book-icon-container text-center">
                                <svg width="300" height="400" viewBox="0 0 150 200" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
                                    <!-- Book cover -->
                                    <rect x="10" y="20" width="120" height="160" fill="orange" rx="4"/>
                                    <!-- Book pages -->
                                    <rect x="15" y="25" width="110" height="150" fill="orange"/>
                                    <!-- Book binding -->
                                    <rect x="8" y="20" width="4" height="160" fill="#cc6600"/>
                                </svg>
                                <div class="book-title-overlay position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; font-size: 1.8rem; text-align: center; width: 160px; line-height: 1.2;">
                                    Current Events
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-warning text-dark">{{ $currentEvents->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Upcoming Events Book -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('admin.events.upcoming', request()->only(['return_to'])) }}" class="text-decoration-none">
                        <div class="book-card position-relative" style="cursor: pointer; transition: transform 0.2s;">
                            <div class="book-icon-container text-center">
                                <svg width="300" height="400" viewBox="0 0 150 200" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
                                    <!-- Book cover -->
                                    <rect x="10" y="20" width="120" height="160" fill="midnightblue" rx="4"/>
                                    <!-- Book pages -->
                                    <rect x="15" y="25" width="110" height="150" fill="midnightblue"/>
                                    <!-- Book binding -->
                                    <rect x="8" y="20" width="4" height="160" fill="#0a0f3f"/>
                                </svg>
                                <div class="book-title-overlay position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; font-size: 1.8rem; text-align: center; width: 160px; line-height: 1.2;">
                                    Upcoming Events
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-success">{{ $upcomingEvents->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Most Recent Events Book -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('admin.events.recent', request()->only(['return_to'])) }}" class="text-decoration-none">
                        <div class="book-card position-relative" style="cursor: pointer; transition: transform 0.2s;">
                            <div class="book-icon-container text-center">
                                <svg width="300" height="400" viewBox="0 0 150 200" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
                                    <!-- Book cover -->
                                    <rect x="10" y="20" width="120" height="160" fill="darkgray" rx="4"/>
                                    <!-- Book pages -->
                                    <rect x="15" y="25" width="110" height="150" fill="darkgray"/>
                                    <!-- Book binding -->
                                    <rect x="8" y="20" width="4" height="160" fill="#404040"/>
                                </svg>
                                <div class="book-title-overlay position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; font-size: 1.8rem; text-align: center; width: 160px; line-height: 1.2;">
                                    Recent Events
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-info text-dark">{{ $mostRecentEvents->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Created Events Book -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('admin.events.created', request()->only(['return_to'])) }}" class="text-decoration-none">
                        <div class="book-card position-relative" style="cursor: pointer; transition: transform 0.2s;">
                            <div class="book-icon-container text-center">
                                <svg width="300" height="400" viewBox="0 0 150 200" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
                                    <!-- Book cover -->
                                    <rect x="10" y="20" width="120" height="160" fill="brown" rx="4"/>
                                    <!-- Book pages -->
                                    <rect x="15" y="25" width="110" height="150" fill="brown"/>
                                    <!-- Book binding -->
                                    <rect x="8" y="20" width="4" height="160" fill="#5c2e0a"/>
                                </svg>
                                <div class="book-title-overlay position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; font-size: 1.8rem; text-align: center; width: 160px; line-height: 1.2;">
                                    Created Events
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-secondary">{{ ($approvedCreatedEvents->count() + $declinedCreatedEvents->count()) }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <style>
                .book-card:hover {
                    transform: translateY(-5px);
                }
                .book-icon-container {
                    position: relative;
                    display: inline-block;
                }
                .book-title-overlay {
                    pointer-events: none;
                    line-height: 1.2;
                }
            </style>

        </div>
        </main>
    </div>
</div>
@endsection