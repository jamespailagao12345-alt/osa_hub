@extends('layouts.app')

@section('title', 'Counseling Events History')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main class="col-md-10 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Counseling Events History</h2>
        <div>
          <a href="{{ route('admin.staff.dashboard.GuidanceCounselor.create-event') }}" class="btn btn-primary">
            <i class="bi bi-calendar-plus"></i> Create Event
          </a>
          <a href="{{ route('admin.staff.dashboard.designation', ['designation' => 'Guidance Counsellor']) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
          </a>
        </div>
      </div>

      <div class="card">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0">All Counseling Events</h5>
        </div>
        <div class="card-body">
          @if($events->isEmpty())
            <p class="text-muted">No events created yet.</p>
          @else
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Event Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($events as $event)
                  <tr>
                    <td><strong>{{ $event->name }}</strong></td>
                    <td>
                      @if($event->event_date)
                        {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}
                      @else
                        TBD
                      @endif
                    </td>
                    <td>
                      @if($event->end_date)
                        {{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y') }}
                      @else
                        TBD
                      @endif
                    </td>
                    <td>
                      @if($event->start_time && $event->end_time)
                        {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                      @else
                        TBD
                      @endif
                    </td>
                    <td>{{ $event->location ?? 'TBD' }}</td>
                    <td>
                      <span class="badge bg-{{ $event->status === 'pending' ? 'warning text-dark' : ($event->status === 'approved' ? 'success' : ($event->status === 'declined' ? 'danger' : 'secondary')) }}">
                        {{ ucfirst($event->status) }}
                      </span>
                    </td>
                    <td>
                      <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-sm btn-info">View</a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
              {{ $events->links() }}
            </div>
          @endif
        </div>
      </div>
    </main>
  </div>
</div>
@endsection

