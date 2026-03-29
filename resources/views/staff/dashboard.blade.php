
@extends('layouts.app')

@php
    $designation = auth()->user()->designation ?? optional(auth()->user()->staffProfile)->designation ?? null;
    $fullName = trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? ''));
    $computedTitle = $designation ? ($designation . ' — ' . $fullName) : $fullName;
    $isAdmin = auth()->user()?->role === 4;
    // Check if user is OSA Staff
    $isOSAStaff = false;
    if (auth()->user() && (int) auth()->user()->role === 2) {
        $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim(auth()->user()->email))])->first();
        $userDesignation = auth()->user()->designation
            ?? optional(auth()->user()->staffProfile)->designation
            ?? ($staffRecord ? $staffRecord->designation : null);
        $normalizedDesignation = trim($userDesignation ?? '');
        $isOSAStaff = strcasecmp($normalizedDesignation, 'OSA Staff') === 0;
    }
    $showAdminUI = $isAdmin || $isOSAStaff;
@endphp

@section('title', 'Staff Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('staff.partials.sidebar')
        <main id="staffMain" class="col-md-10">
            @if($showAdminUI)
                <div class="admin-back-btn-wrap">
                    <button onclick="window.history.back()" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </button>
                </div>
            @endif
            
            @php
                $user = \Illuminate\Support\Facades\Auth::user();
                $role = $user->role ?? null;
                // Use case-insensitive email lookup to match other parts of the codebase
                $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
                $designation = $user->designation 
                    ?? optional($user->staffProfile)->designation 
                    ?? ($staffRecord ? $staffRecord->designation : null);
                $isStudentOrgModerator = strcasecmp($designation ?? '', 'Student Org. Moderator') === 0;
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                
                // Check if designation is "Nurse"
                $isNurse = strcasecmp($designation ?? '', 'Nurse') === 0;
            @endphp

            <!-- Dashboard Header Component -->
            <x-dashboard-header 
                :name="$fullName"
                :designation="$designation"
                :roleLabel="$designation ? ($designation . ' Dashboard') : 'My Staff Dashboard'"
            />

            <!-- Quick Actions -->
            <div class="mb-4 wow fadeInUp" data-wow-delay="100ms">
                <style>
                    .staff-quick-actions-container {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 2rem;
                        justify-content: center;
                        align-items: stretch;
                        padding: 2rem 4rem;
                        margin: 0 auto;
                        max-width: 100%;
                    }
                    .staff-quick-actions-container > div {
                        flex: 1 1 calc((100% / 4) - (6rem / 4));
                        min-width: 220px;
                        max-width: 280px;
                        padding: 0;
                    }
                    .staff-quick-actions-container .btn {
                        padding: 1.5rem 2rem;
                        font-size: 1.2rem;
                        min-height: 80px;
                        font-weight: 600;
                        line-height: 1.5;
                        width: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background-color: midnightblue;
                        border-color: midnightblue;
                        color: white;
                        text-align: center;
                    }
                    .staff-quick-actions-container .btn i {
                        font-size: 1.4rem;
                        margin-right: 0.5rem;
                    }
                    .staff-quick-actions-container .btn:hover,
                    .staff-quick-actions-container .btn:focus {
                        background-color: #1a237e;
                        border-color: #1a237e;
                        color: white;
                    }
                    .staff-quick-actions-container .btn-group {
                        width: 100%;
                    }
                    .staff-quick-actions-container .btn-group .btn {
                        min-height: 80px;
                    }
                    @media (max-width: 1400px) {
                        .staff-quick-actions-container > div {
                            flex: 1 1 calc((100% / 3) - 2rem);
                            max-width: 300px;
                        }
                    }
                    @media (max-width: 992px) {
                        .staff-quick-actions-container {
                            padding: 2rem 3rem;
                        }
                        .staff-quick-actions-container > div {
                            flex: 1 1 calc((100% / 2) - 1rem);
                            max-width: 350px;
                        }
                        .staff-quick-actions-container .btn {
                            padding: 1.3rem 1.8rem;
                            font-size: 1.1rem;
                            min-height: 75px;
                        }
                    }
                    @media (max-width: 768px) {
                        .staff-quick-actions-container {
                            padding: 2rem 2rem;
                            gap: 1.5rem;
                        }
                        .staff-quick-actions-container > div {
                            flex: 1 1 calc((100% / 2) - 0.75rem);
                            max-width: none;
                        }
                        .staff-quick-actions-container .btn {
                            padding: 1.2rem 1.5rem;
                            font-size: 1.05rem;
                            min-height: 70px;
                        }
                    }
                    @media (max-width: 576px) {
                        .staff-quick-actions-container {
                            padding: 2rem 1.5rem;
                            gap: 1.5rem;
                        }
                        .staff-quick-actions-container > div {
                            flex: 1 1 100%;
                            max-width: none;
                        }
                        .staff-quick-actions-container .btn {
                            padding: 1rem 1.2rem;
                            font-size: 1rem;
                            min-height: 65px;
                        }
                    }
                </style>
                <div class="staff-quick-actions-container">
                    <div>
                        <a href="{{ route('staff.appointments.index') }}" class="btn w-100">
                            <i class="bi bi-calendar"></i> My Appointments
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('staff.events.index') }}" class="btn w-100">
                            <i class="bi bi-calendar-event"></i> My Events
                        </a>
                    </div>
                    @if($role === 2)
                        <!-- My Organizations Dropdown -->
                        <div>
                            <div class="btn-group w-100">
                                <a href="{{ route('staff.organizations.index') }}" class="btn">
                                    <i class="bi bi-building"></i> My Organizations
                                </a>
                                <button type="button" class="btn dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="sr-only">Toggle dropdown</span>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('staff.organizations.index') }}"><i class="bi bi-building"></i> My Organizations</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('staff.files.index') }}"><i class="bi bi-folder"></i> My Files</a>
                                    <a class="dropdown-item" href="{{ route('staff.student-leaders.index') }}"><i class="bi bi-people"></i> My Student Leaders</a>
                                    <a class="dropdown-item" href="{{ route('staff.student-leaders.create') }}"><i class="bi bi-person-plus"></i> Add Student Leader</a>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($role === 4)
                        <div>
                            <a href="{{ route('admin.participants.export') }}" class="btn w-100">
                                <i class="bi bi-download"></i> Export Participants
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions Section from Admin Staff Dashboard -->
            <div class="mb-4 wow fadeInUp" data-wow-delay="200ms">
                <style>
                    .quick-actions-card {
                        margin: 0 4rem;
                    }
                    .quick-actions-card .card-header {
                        background-color: midnightblue;
                        color: white;
                        font-weight: 600;
                        padding: 1rem 1.5rem;
                    }
                    .quick-actions-card .list-group-item {
                        border-left: none;
                        border-right: none;
                        padding: 0.75rem 1.5rem;
                    }
                    .quick-actions-card .list-group-item:first-child {
                        border-top: none;
                    }
                    .quick-actions-card .list-group-item:last-child {
                        border-bottom: none;
                    }
                    .quick-actions-card .list-group-item:hover {
                        background-color: #f8f9fa;
                    }
                    .quick-actions-card .list-group-item i {
                        margin-right: 0.5rem;
                        color: midnightblue;
                    }
                    @media (max-width: 992px) {
                        .quick-actions-card {
                            margin: 0 3rem;
                        }
                    }
                    @media (max-width: 768px) {
                        .quick-actions-card {
                            margin: 0 2rem;
                        }
                    }
                    @media (max-width: 576px) {
                        .quick-actions-card {
                            margin: 0 1.5rem;
                        }
                    }
                </style>
                <div class="card quick-actions-card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        @if($showAdminUI)
                            <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}" href="{{ route('admin.appointments.index') }}">
                                <i class="bi bi-calendar"></i> Appointments
                            </a>
                            <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.events.*') ? 'active' : '' }}" href="{{ route('admin.events.index') }}">
                                <i class="bi bi-calendar-event"></i> Events
                            </a>
                            <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.calendar') ? 'active' : '' }}" href="{{ route('admin.calendar') }}">
                                <i class="bi bi-calendar3"></i> Calendar
                            </a>
                            <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.events.create') ? 'active' : '' }}" href="{{ route('admin.events.create') }}">
                                <i class="bi bi-plus-circle"></i> Create Event
                            </a>
                            <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.participants.export') ? 'active' : '' }}" href="{{ route('admin.participants.export') }}">
                                <i class="bi bi-download"></i> Export Participation
                            </a>
                            <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.show-staff') ? 'active' : '' }}" href="{{ route('admin.show-staff') }}">
                                <i class="bi bi-people"></i> Show Staff
                            </a>
                            <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.add-staff') ? 'active' : '' }}" href="{{ route('admin.add-staff') }}">
                                <i class="bi bi-person-plus"></i> Add Staff
                            </a>
                            <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.student-leaders.*') ? 'active' : '' }}" href="{{ route('admin.student-leaders.index') }}">
                                <i class="bi bi-people"></i> Show Student Leaders
                            </a>
                            <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.show-students-list') ? 'active' : '' }}" href="{{ route('admin.show-students-list') }}">
                                <i class="bi bi-book"></i> Show Students
                            </a>
                            <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.organizations.*') ? 'active' : '' }}" href="{{ route('admin.organizations.index') }}">
                                <i class="bi bi-building"></i> Organizations
                            </a>
                        @endif
                        <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.staff.dashboard') ? 'active' : '' }}" href="{{ route('admin.staff.dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Staff Dashboards
                        </a>
                    </div>
                </div>
            </div>


        </main>
    </div>
</div>

@endsection