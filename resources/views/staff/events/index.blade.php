@extends('layouts.app')

@section('title', 'My Events')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('staff.partials.sidebar')
        <main id="staffMain" class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Events Created by Me</h2>
                <a href="{{ route('staff.events.create') }}" class="btn btn-primary">Create Event</a>
            </div>
            @if($events->isEmpty())
                <p class="text-muted">No events created yet.</p>
            @else
                <div class="card mb-4 wow fadeInUp" data-wow-delay="100ms">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">My Events</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Participants</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($events as $event)
                                    <tr>
                                        <td>{{ $event->name }}</td>
                                        <td>
                                            @if($event->event_date)
                                                {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}
                                            @else
                                                TBD
                                            @endif
                                        </td>
                                        <td>{{ $event->location ?? 'TBD' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $event->status === 'pending' ? 'warning text-dark' : ($event->status === 'approved' ? 'success' : 'secondary') }}">
                                                {{ ucfirst($event->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $event->participants_count ?? 0 }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Pending Events for Approval -->
            <div class="card mb-4 wow fadeInUp" data-wow-delay="150ms">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Pending Events for Approval</h5>
                </div>
                <div class="card-body">
                    @if($pendingEvents->isEmpty())
                        <p class="text-muted">No pending events.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Creator</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingEvents as $event)
                                    <tr>
                                        <td>{{ $event->title ?? $event->name }}</td>
                                        <td>{{ $event->creator->first_name }} {{ $event->creator->last_name }}</td>
                                        <td>{{ optional($event->event_date)->format('M d, Y') }}</td>
                                        <td>
                                            <form action="{{ route('staff.events.approve', $event->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form action="{{ route('staff.events.decline', $event->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <div class="input-group input-group-sm d-inline-flex" style="width: auto;">
                                                    <input type="text" name="reason" class="form-control form-control-sm" placeholder="Decline reason" required style="min-width: 150px;">
                                                    <button type="submit" class="btn btn-sm btn-danger">Decline</button>
                                                </div>
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

            <!-- Recent Participants -->
            <div class="card mb-4 wow fadeInUp" data-wow-delay="180ms">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Recent Participants</h5>
                </div>
                <div class="card-body">
                    @if($participants->isEmpty())
                        <p class="text-muted">No recent participation records.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Event</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($participants as $p)
                                        <tr>
                                            <td>{{ $p->user->first_name }} {{ $p->user->last_name }}</td>
                                            <td>{{ $p->event->title ?? $p->event->name }}</td>
                                            <td>{{ optional($p->created_at)->format('M d, Y') }}</td>
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
