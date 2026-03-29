@extends('layouts.app')

@section('title', 'Student Org. Moderator Dashboard')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main class="col-md-10 py-4">
      @php
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        $backRoute = $isAdmin ? route('admin.staff.dashboard') : route('admin.staff.dashboard');
      @endphp
      <div class="admin-back-btn-wrap mb-3" style="margin-top: 40px;">
        <a href="{{ $backRoute }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
      </div>
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">My Events - Student Org. Moderator</h2>
      </div>

      <!-- My Organizations -->
      <div class="card mb-4">
        <div class="card-header" style="background-color: midnightblue; color: white;">
          <h5 class="mb-0">My Organizations</h5>
        </div>
        <div class="card-body">
          @if($userOrganizations->isEmpty())
            <div class="alert alert-info">
              <p class="mb-0">You are not assigned to any organizations yet.</p>
            </div>
          @else
            <div class="row">
              @foreach($userOrganizations as $organization)
                <div class="col-12 mb-3">
                  <div class="card border-primary">
                    <div class="card-header" style="background-color: #ffc107; color: white;">
                      <h6 class="mb-0">
                        <a href="{{ route('admin.organizations.profile', $organization->id) }}" style="color: white; text-decoration: none; cursor: pointer;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                          {{ $organization->name }}
                        </a>
                      </h6>
                      @if($organization->department)
                        <small>{{ $organization->department->name }} - Academic Organization</small>
                      @else
                        <small>Non-Academic Organization</small>
                      @endif
                    </div>
                    <div class="card-body">
                      <div class="mb-3">
                        <span class="badge bg-primary fs-6" style="color: white;">{{ $organization->studentCount ?? 0 }} Student Member{{ ($organization->studentCount ?? 0) !== 1 ? 's' : '' }}</span>
                      </div>
                      <div class="d-flex flex-column gap-2">
                        <a href="{{ route('staff.organizations.assistants', $organization->id) }}" class="btn btn-primary btn-sm">
                          <i class="bi bi-people"></i> My Student Leaders
                        </a>
                        <a href="{{ route('staff.student-leaders.create', ['organization_id' => $organization->id]) }}" class="btn btn-success btn-sm">
                          <i class="bi bi-person-plus"></i> Add Assistant
                        </a>
                        <a href="{{ route('admin.staff.dashboard.StudentOrgModerator.create-event', ['organization_id' => $organization->id]) }}" class="btn btn-warning btn-sm">
                          <i class="bi bi-calendar-event"></i> Create Event
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>

      @php
        // Group events by organization
        $eventsByOrg = $events->groupBy('organization_id');
      @endphp

      @if($events->isEmpty())
        <div class="alert alert-info">
          <i class="bi bi-info-circle"></i> No events created yet. Please create an event from within an organization above.
        </div>
      @else
        {{-- Show events grouped by organization --}}
        @foreach($eventsByOrg as $orgId => $orgEvents)
          @php
            $org = $orgEvents->first()->organization ?? $userOrganizations->firstWhere('id', $orgId);
          @endphp
          @if($org)
            <div class="card mb-3">
              <div class="card-header d-flex justify-content-between align-items-center" style="background-color: midnightblue; color: white;">
                <h5 class="mb-0">{{ $org->name }}</h5>
                <span class="badge bg-light text-dark">{{ $orgEvents->count() }} event(s)</span>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Event Name</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($orgEvents as $event)
                        <tr>
                          <td><strong>{{ $event->name }}</strong></td>
                          <td>{{ $event->description ?? 'N/A' }}</td>
                          <td>{{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('M d, Y') : 'N/A' }}</td>
                          <td>
                            @if($event->start_time)
                              {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                            @else
                              N/A
                            @endif
                          </td>
                          <td>
                            @if($event->end_time)
                              {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                            @else
                              N/A
                            @endif
                          </td>
                          <td>{{ $event->location ?? 'N/A' }}</td>
                          <td>
                            <span class="badge bg-{{ $event->status === 'approved' ? 'success' : ($event->status === 'pending' ? 'warning text-dark' : 'secondary') }}">
                              {{ ucfirst($event->status) }}
                            </span>
                          </td>
                          <td>
                            <div class="d-flex gap-2">
                              <a href="{{ route('admin.staff.dashboard.StudentOrgModerator.event.edit', $event) }}" class="btn btn-sm btn-warning">Edit</a>
                              <form action="{{ route('admin.staff.dashboard.StudentOrgModerator.event.delete', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                              </form>
                              @if($event->status === 'approved')
                                <a href="{{ route('admin.staff.dashboard.StudentOrgModerator.event.qrcode', $event) }}" class="btn btn-sm btn-info" target="_blank">QR Code</a>
                              @endif
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          @endif
        @endforeach
      @endif
    </main>
  </div>
</div>
@endsection

