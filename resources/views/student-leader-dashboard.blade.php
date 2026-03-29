@extends('layouts.app')

@section('title', 'Student Leader Dashboard')

@php
    $user = auth()->user();
    // Student leaders use position, not designation
    $position = $user->position ?? null;
    $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
@endphp

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('student-leader.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <div class="admin-back-btn-wrap mb-3">
        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">Back to Student Dashboard</a>
      </div>
    <!-- Dashboard Header Component -->
    <x-dashboard-header 
        :name="$fullName"
        :designation="$position"
        :roleLabel="'My Student Leader Dashboard'"
    />

    <!-- Student Profile Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card" style="border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            @php
                                $user = auth()->user();
                                $student = \App\Models\Student::where('user_id', $user->id)->first();
                                $profileImage = $user->image ?? ($student ? $student->personal_data_sheet_image : null);
                                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? ''));
                                
                                // Get initials for avatar
                                $initials = '';
                                $firstInitial = strtoupper(substr($user->first_name ?? '', 0, 1));
                                $lastInitial = strtoupper(substr($user->last_name ?? '', 0, 1));
                                $initials = $firstInitial . $lastInitial;
                            @endphp
                            <div class="mb-3">
                                @if($profileImage)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($profileImage) }}" 
                                         alt="{{ $fullName }}" 
                                         class="rounded-circle" 
                                         style="width: 120px; height: 120px; object-fit: cover; border: 3px solid midnightblue;">
                                @else
                                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 120px; height: 120px; border: 3px solid midnightblue;">
                                        <span class="text-white" style="font-size: 2.5rem; font-weight: bold;">{{ $initials ?: 'U' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-9">
                            <h4 class="mb-2" style="font-weight: 600; color: #333;">
                                {{ $fullName ?: 'Student' }}
                            </h4>
                            <p class="text-muted mb-2">
                                <strong>Student ID:</strong> {{ $user->user_id ?? 'N/A' }}
                            </p>
                            <p class="text-muted mb-2">
                                <strong>Email:</strong> {{ $user->email ?? 'N/A' }}
                            </p>
                            @if($user->department)
                                <p class="text-muted mb-2">
                                    <strong>Department:</strong> {{ $user->department->name }}
                                </p>
                            @endif
                            @if($user->course)
                                <p class="text-muted mb-2">
                                    <strong>Course:</strong> {{ $user->course->name }}
                                </p>
                            @endif
                            @if($user->year_level)
                                <p class="text-muted mb-0">
                                    <strong>Year Level:</strong> {{ $user->year_level }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>My Events</h5>
                    <a href="{{ route('student-leader.events.index') }}" class="btn btn-secondary mt-2">View Events</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Participants History</h5>
                    <a href="{{ route('student-leader.participants.history') }}" class="btn btn-info mt-2">Open History</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Messages</h5>
                    <a href="{{ route('student-leader.messages.index') }}" class="btn btn-success mt-2">Open Inbox</a>
                </div>
            </div>
        </div>
    </div>

    <!-- My Organizations Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card" style="border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">My Organizations</h5>
                </div>
                <div class="card-body">
                    @if(isset($allOrganizations) && $allOrganizations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Organization Name</th>
                                        <th>Type</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allOrganizations as $org)
                                        <tr>
                                            <td>{{ $org['name'] }}</td>
                                            <td>
                                                @if($org['type'] === 'Academic')
                                                    <span class="badge bg-warning text-dark">Academic</span>
                                                @else
                                                    <span class="badge bg-info">Non-Academic</span>
                                                @endif
                                            </td>
                                            <td>{{ $org['position'] ?? 'Member' }}</td>
                                            <td>
                                                @if($org['is_primary'])
                                                    <span class="badge bg-primary">Primary</span>
                                                @else
                                                    <span class="badge bg-success">Affiliated</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">You are not currently affiliated with any organizations.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Organization Registration Requests Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card" style="border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">Pending Organization Registration Requests</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(isset($pendingRequests) && $pendingRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Organization</th>
                                        <th>Organization Type</th>
                                        <th>Position</th>
                                        <th>Date Requested</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingRequests as $request)
                                        @php
                                            $isAcademic = $request->organization && $request->organization->department_id;
                                            // Note: student() relationship returns User model directly
                                            $studentName = $request->student 
                                                ? trim(($request->student->first_name ?? '') . ' ' . ($request->student->last_name ?? ''))
                                                : 'N/A';
                                            $orgName = $request->organization ? $request->organization->name : 'N/A';
                                        @endphp
                                        <tr>
                                            <td>{{ $studentName }}</td>
                                            <td>{{ $orgName }}</td>
                                            <td>
                                                @if($isAcademic)
                                                    <span class="badge bg-warning text-dark">Academic</span>
                                                @else
                                                    <span class="badge bg-info">Non-Academic</span>
                                                @endif
                                            </td>
                                            <td>{{ $request->position ?? 'N/A' }}</td>
                                            <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('student-leader.organization-requests.approve', $request->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" {{ $isAcademic ? 'disabled' : '' }} title="{{ $isAcademic ? 'Academic organizations cannot be approved/declined' : 'Approve request' }}">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('student-leader.organization-requests.decline', $request->id) }}" class="d-inline ms-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm" {{ $isAcademic ? 'disabled' : '' }} title="{{ $isAcademic ? 'Academic organizations cannot be approved/declined' : 'Decline request' }}">
                                                        Decline
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No pending organization registration requests.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- End of content -->
    </main>
  </div>
</div>

@endsection
