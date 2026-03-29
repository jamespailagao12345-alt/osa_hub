@extends('layouts.app')

@php
    $user = auth()->user();
    $designation = $user->designation ?? optional($user->staffProfile)->designation ?? null;
    $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
    $computedTitle = $designation ? ($designation . ' — ' . $fullName) : $fullName;
@endphp
@section('title', $computedTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <!-- Main Content -->
        <main id="adminMain" class="col-md-10">
            <!-- Dashboard Header Component -->
            <x-dashboard-header 
                :name="$fullName"
                :designation="$designation"
                :roleLabel="'Admin Dashboard'"
            />

            <!-- Quick Actions -->
            <div class="mb-4 wow fadeInUp" data-wow-delay="100ms">
                <style>
                    .quick-actions-container {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 2rem;
                        justify-content: center;
                        padding: 4rem 7rem;
                        margin: 0 auto;
                        max-width: 100%;
                    }
                    .quick-actions-container > div {
                        flex: 0 1 calc((100% / 4) - (6rem / 4));
                        min-width: 200px;
                        max-width: calc((100% / 4) - (6rem / 4));
                        padding: 0;
                        display: flex;
                    }
                    .quick-actions-container .btn {
                        width: 100%;
                        padding: 1.5rem 1rem;
                        font-size: 1.1rem;
                        min-height: 140px;
                        max-height: 140px;
                        font-weight: 600;
                        line-height: 1.4;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        white-space: normal;
                        word-wrap: break-word;
                        text-align: center;
                        gap: 0.75rem;
                        background-color: #ffc107 !important; /* Yellow background */
                        border: 2px solid midnightblue !important;
                        border-color: midnightblue !important;
                        color: midnightblue !important;
                    }
                    .quick-actions-container .btn-group {
                        width: 100%;
                        display: flex;
                    }
                    .quick-actions-container .btn-group > .btn:first-child {
                        flex: 1;
                        min-width: 0;
                        padding-right: 0.5rem;
                    }
                    .quick-actions-container .btn-group .btn:first-child {
                        border-top-right-radius: 0;
                        border-bottom-right-radius: 0;
                    }
                    .quick-actions-container .btn-group .dropdown-toggle-split {
                        flex: 0 0 auto;
                        min-width: 30px;
                        max-width: 30px;
                        padding: 0.5rem;
                        font-size: 0.9rem;
                        background-color: midnightblue !important;
                        border-color: midnightblue !important;
                        color: white !important;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        min-height: 140px;
                        border-top-left-radius: 0;
                        border-bottom-left-radius: 0;
                        border-left: none !important;
                    }
                    .quick-actions-container .btn i {
                        font-size: 3rem;
                        margin-right: 0;
                        margin-bottom: 0;
                        flex-shrink: 0;
                        color: midnightblue !important;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 80px;
                        height: 80px;
                        background-color: white;
                        border: 3px solid midnightblue;
                        border-radius: 8px;
                    }
                    .quick-actions-container .btn span {
                        display: block;
                        text-align: center;
                        font-weight: 600;
                        color: midnightblue !important;
                        margin-top: 0;
                        line-height: 1.2;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    .quick-actions-container .btn,
                    .quick-actions-container .btn-primary,
                    .quick-actions-container .btn-secondary {
                        background-color: #ffc107 !important; /* Yellow */
                        border-color: midnightblue !important;
                        color: midnightblue !important;
                    }
                    .quick-actions-container .btn:hover,
                    .quick-actions-container .btn-primary:hover,
                    .quick-actions-container .btn-secondary:hover {
                        background-color: #ffca2c !important; /* Lighter yellow on hover */
                        border-color: midnightblue !important;
                        color: midnightblue !important;
                        transform: translateY(-2px);
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                    }
                    .quick-actions-container .btn:focus,
                    .quick-actions-container .btn-primary:focus,
                    .quick-actions-container .btn-secondary:focus {
                        background-color: #ffc107 !important;
                        border-color: midnightblue !important;
                        color: midnightblue !important;
                        box-shadow: 0 0 0 0.2rem rgba(25, 25, 112, 0.5);
                    }
                    .quick-actions-container .btn:active,
                    .quick-actions-container .btn-primary:active,
                    .quick-actions-container .btn-secondary:active {
                        background-color: #ffb300 !important; /* Darker yellow */
                        border-color: midnightblue !important;
                        color: midnightblue !important;
                    }
                    @media (max-width: 1400px) {
                        .quick-actions-container {
                            padding: 3.5rem 6rem;
                        }
                        .quick-actions-container > div {
                            flex: 0 1 calc((100% / 4) - (6rem / 4));
                            max-width: calc((100% / 4) - (6rem / 4));
                        }
                    }
                    @media (max-width: 992px) {
                        .quick-actions-container {
                            padding: 3rem 4rem;
                            gap: 2rem;
                        }
                        .quick-actions-container > div {
                            flex: 0 1 calc((100% / 3) - (4rem / 3)) !important;
                            max-width: calc((100% / 3) - (4rem / 3)) !important;
                            margin-bottom: 0;
                        }
                        .quick-actions-container .btn {
                            padding: 1.2rem 0.8rem;
                            font-size: 1rem;
                            min-height: 120px;
                            max-height: 120px;
                        }
                        .quick-actions-container .btn-group .dropdown-toggle-split {
                            min-width: 28px;
                            max-width: 28px;
                            padding: 0.4rem;
                        }
                        .quick-actions-container .btn i {
                            font-size: 2.5rem;
                            width: 70px;
                            height: 70px;
                        }
                    }
                    @media (max-width: 768px) {
                        .quick-actions-container {
                            padding: 2.5rem 3rem;
                            gap: 2rem;
                        }
                        .quick-actions-container > div {
                            flex: 0 1 calc((100% / 2) - 1rem) !important;
                            max-width: calc((100% / 2) - 1rem) !important;
                            margin-bottom: 0;
                        }
                        .quick-actions-container .btn {
                            padding: 1rem 0.7rem;
                            font-size: 0.95rem;
                            min-height: 110px;
                            max-height: 110px;
                        }
                        .quick-actions-container .btn-group .dropdown-toggle-split {
                            min-width: 26px;
                            max-width: 26px;
                            padding: 0.35rem;
                        }
                        .quick-actions-container .btn i {
                            font-size: 2rem;
                            width: 60px;
                            height: 60px;
                        }
                    }
                    @media (max-width: 576px) {
                        .quick-actions-container {
                            padding: 2rem 2.5rem;
                            gap: 2rem;
                        }
                        .quick-actions-container > div {
                            flex: 0 1 100% !important;
                            max-width: 100% !important;
                            margin-bottom: 0;
                        }
                        .quick-actions-container .btn {
                            padding: 1rem 0.6rem;
                            font-size: 0.9rem;
                            min-height: 100px;
                            max-height: 100px;
                            margin-bottom: 0;
                        }
                        .quick-actions-container .btn-group .dropdown-toggle-split {
                            min-width: 24px;
                            max-width: 24px;
                            padding: 0.3rem;
                        }
                        .quick-actions-container .btn i {
                            font-size: 1.8rem;
                            width: 50px;
                            height: 50px;
                        }
                    }
                </style>
                <div class="quick-actions-container">
                    @php 
                        $isAdmin = auth()->user()?->role === 4;
                        $userDesignation = auth()->user()->designation ?? optional(auth()->user()->staffProfile)->designation ?? null;
                        $isOSAStaff = strcasecmp($userDesignation, 'OSA Staff') === 0;
                        $showAdminUI = $isAdmin || $isOSAStaff;
                    @endphp
                    @if($showAdminUI)
                        <div>
                            <a href="{{ route('admin.appointments.index') }}" class="btn btn-primary w-100">
                                <i class="bi bi-calendar-check"></i>
                                <span>Appointments</span>
                            </a>
                        </div>
                        
                        <!-- Events Dropdown -->
                        <div>
                            <div class="btn-group w-100">
                                <a href="{{ route('admin.events.index') }}" class="btn btn-primary">
                                    <i class="bi bi-calendar-event"></i>
                                    <span>Events</span>
                                </a>
                                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="sr-only">Toggle dropdown</span>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.events.index') }}"><i class="bi bi-calendar-event"></i> Events</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('admin.events.create') }}"><i class="bi bi-plus-circle"></i> Create Event</a>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <a href="{{ route('admin.calendar') }}" class="btn btn-primary w-100">
                                <i class="bi bi-calendar3"></i>
                                <span>Calendar</span>
                            </a>
                        </div>
                        
                        <!-- Staff Dashboard Dropdown -->
                        <div>
                            <div class="btn-group w-100">
                                <a href="{{ route('admin.staff.dashboard') }}" class="btn btn-primary">
                                    <i class="bi bi-speedometer2"></i>
                                    <span>Staff Dashboard</span>
                                </a>
                                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="sr-only">Toggle dropdown</span>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.staff.dashboard') }}"><i class="bi bi-speedometer2"></i> Staff Dashboards</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('admin.show-staff') }}"><i class="bi bi-people"></i> Staff List</a>
                                    <a class="dropdown-item" href="{{ route('admin.add-staff') }}"><i class="bi bi-person-plus"></i> Add Staff</a>
                                    <a class="dropdown-item" href="{{ route('admin.student-leaders.index') }}"><i class="bi bi-person-badge"></i> Show Student Leaders</a>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <a href="{{ route('admin.show-students-list') }}" class="btn btn-secondary w-100">
                                <i class="bi bi-people-fill"></i>
                                <span>Show Students</span>
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('admin.organizations.index') }}" class="btn btn-secondary w-100">
                                <i class="bi bi-building"></i>
                                <span>Organizations</span>
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('admin.organizational-structure') }}" class="btn btn-primary w-100">
                                <i class="bi bi-diagram-3"></i>
                                <span>Organizational Structure</span>
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('admin.files.index') }}" class="btn btn-primary w-100">
                                <i class="bi bi-folder"></i>
                                <span>Files</span>
                            </a>
                        </div>
                        @if($isOSAStaff)
                        <div>
                            <a href="{{ route('staff.organizations.index') }}" class="btn btn-primary w-100">
                                <i class="bi bi-building"></i>
                                <span>My Organization</span>
                            </a>
                        </div>
                        @endif
                    @else
                        <div>
                            <a href="{{ route('admin.staff.dashboard') }}" class="btn btn-primary w-100">
                                <i class="bi bi-speedometer2"></i>
                                <span>Staff Dashboards</span>
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('admin.organizational-structure') }}" class="btn btn-primary w-100">
                                <i class="bi bi-diagram-3"></i>
                                <span>Organizational Structure</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Events -->
            <style>
                .pending-events-card {
                    background-color: light;
                    color: black;
                    margin: 0 7rem;
                    padding-top: 2rem;
                    padding-bottom: 2rem;
                }
                .pending-events-card .card-body {
                    padding-top: 1.5rem;
                    padding-bottom: 1.5rem;
                }
                @media (max-width: 1400px) {
                    .pending-events-card {
                        margin: 0 6rem;
                    }
                }
                @media (max-width: 992px) {
                    .pending-events-card {
                        margin: 0 4rem;
                    }
                }
                @media (max-width: 768px) {
                    .pending-events-card {
                        margin: 0 3rem;
                    }
                }
                @media (max-width: 576px) {
                    .pending-events-card {
                        margin: 0 2.5rem;
                    }
                }
            </style>
            <div class="card mb-4 wow fadeInUp pending-events-card" data-wow-delay="200ms">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">Pending Events for Approval</h5>
                </div>
                <div class="card-body" style="background-color: white; color: black;">
                    @if($pendingEvents->isEmpty())
                        <p class="text-white-50">No pending events.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle bg-white">
                                <thead>
                                    <tr align="center" style="background-color:midnightblue; color:white">
                                        <th>Event</th>
                                        <th>Coordinator</th>
                                        <th>Date</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center text-dark">
                                    @foreach($pendingEvents as $event)
                                    <tr>
                                        <td>{{ $event->name }}</td>
                                        <td>
                                            @if($event->organization_id && $event->organization)
                                                {{ $event->organization->name }}
                                            @elseif($event->coordinator_name)
                                                {{ $event->coordinator_name }}
                                            @elseif($event->creator)
                                                {{ $event->creator->first_name }} {{ $event->creator->last_name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($event->event_date)
                                                {{ \Carbon\Carbon::parse($event->event_date)->format('Y-m-d') }}
                                            @elseif($event->start_time)
                                                {{ \Carbon\Carbon::parse($event->start_time)->format('Y-m-d') }}
                                            @else
                                                <span class="text-muted">No date</span>
                                            @endif
                                        </td>
                                        <td>{{ $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : '' }}</td>
                                        <td>{{ $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i') : '' }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.events.approve', $event->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.events.decline', $event->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger">Decline</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>

@endsection
