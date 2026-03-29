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
              <a href="{{ route('admin.events.index') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back to Events</a>
            @endif
        </div>
        <div class="py-3">
            <h1 class="h4 mb-4">
                <span class="badge bg-warning text-dark me-2">Current</span>
                Current Events
                <span class="badge bg-secondary">{{ $events->total() }}</span>
            </h1>
            <p class="text-muted small mb-3">Events happening on the current date ({{ \Carbon\Carbon::today()->format('M d, Y') }})</p>

            <!-- Search and Filter Form -->
            <form method="GET" action="{{ route('admin.events.current') }}" class="mb-4">
                @if(request()->has('return_to'))
                  <input type="hidden" name="return_to" value="{{ request('return_to') }}">
                @endif
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Events</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name or description...">
                    </div>
                    <div class="col-md-3">
                        <label for="description" class="form-label">Filter by Description</label>
                        <select class="form-control" id="description" name="description">
                            <option value="">All Descriptions</option>
                            @foreach($descriptions ?? [] as $desc)
                                <option value="{{ $desc }}" {{ request('description') == $desc ? 'selected' : '' }}>{{ $desc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="organization_id" class="form-label">Filter by Organization/Coordinator</label>
                        <select class="form-control" id="organization_id" name="organization_id">
                            <option value="">All Organizations</option>
                            @foreach($organizations ?? [] as $org)
                                <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                @if(request()->hasAny(['search', 'description', 'organization_id']))
                    @if(request()->has('return_to'))
                      <a href="{{ route('admin.events.current', ['return_to' => request('return_to')]) }}" class="btn btn-outline-secondary btn-sm">Clear Filters</a>
                    @else
                      <a href="{{ route('admin.events.current') }}" class="btn btn-outline-secondary btn-sm">Clear Filters</a>
                    @endif
                @endif
            </form>

            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Description</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Location</th>
                            <th>Coordinator</th>
                            <th>View Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                        <tr>
                            <td><strong>{{ $event->name }}</strong></td>
                            <td>{{ $event->description ?? 'N/A' }}</td>
                            <td>
                                @if($event->start_time)
                                    {{ \Carbon\Carbon::parse($event->start_time)->format('M d, Y h:i A') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($event->end_time)
                                    {{ \Carbon\Carbon::parse($event->end_time)->format('M d, Y h:i A') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $event->location ?? 'N/A' }}</td>
                            <td>{{ $event->organization->name ?? ($event->coordinator_name ?? 'N/A') }}</td>
                            <td>
                                <a href="/admin/events/{{ $event->id }}" class="btn btn-sm btn-primary">View Details</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No current events at the moment.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $events->links() }}
            </div>
            
            <!-- Admin Created Events Section -->
            <div class="mt-5">
                <h3 class="h6 mb-3">
                    <span class="badge bg-primary me-2">Admin Created</span>
                    Admin Created Current Events
                    <span class="badge bg-secondary">{{ $adminEvents->total() ?? 0 }}</span>
                </h3>
                <p class="text-muted small mb-3">Events happening today created by administrators</p>
                
                <div class="bg-white shadow rounded-lg overflow-x-auto">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Description</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($adminEvents ?? [] as $event)
                            <tr>
                                <td><strong>{{ $event->name }}</strong></td>
                                <td>{{ $event->description ?? 'N/A' }}</td>
                                <td>
                                    @if($event->start_time)
                                        {{ \Carbon\Carbon::parse($event->start_time)->format('M d, Y h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($event->end_time)
                                        {{ \Carbon\Carbon::parse($event->end_time)->format('M d, Y h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $event->location ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-sm btn-warning me-1">Edit</a>
                                    <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-sm btn-primary">View Details</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No admin-created current events at the moment.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if(isset($adminEvents) && $adminEvents->hasPages())
                <div class="mt-3">
                    {{ $adminEvents->links() }}
                </div>
                @endif
            </div>
        </div>
        </main>
    </div>
</div>
@endsection

