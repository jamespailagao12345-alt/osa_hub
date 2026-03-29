@extends('layouts.app')

@section('title', 'Event Feedback')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="admin-back-btn-wrap mb-3">
                <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">&larr; Back to Dashboard</a>
            </div>

            <h2 class="mb-4">Event Feedback</h2>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Events Pending Feedback -->
            @if($participatedEvents->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Events Pending Feedback</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Submit feedback for these events to earn points!</p>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>Points Available</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($participatedEvents as $event)
                                        <tr>
                                            <td><strong>{{ $event->name }}</strong></td>
                                            <td>
                                                @if($event->start_time)
                                                    {{ \Carbon\Carbon::parse($event->start_time)->format('M d, Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $event->location ?? 'N/A' }}</td>
                                            <td>
                                                @if($event->points)
                                                    <span class="badge bg-success">{{ $event->points }} points</span>
                                                @else
                                                    <span class="text-muted">No points</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $participation = \App\Models\EventParticipant::where('event_id', $event->id)
                                                        ->where('user_id', auth()->id())
                                                        ->first();
                                                @endphp
                                                @if($participation && $participation->attendance_status)
                                                    <span class="badge bg-{{ $participation->attendance_status === 'Attended' ? 'success' : ($participation->attendance_status === 'Late' ? 'warning' : 'danger') }}">
                                                        {{ $participation->attendance_status }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Participated</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('student.events.feedback.create', $event->id) }}" class="btn btn-sm btn-primary">
                                                    Submit Feedback
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Feedback Submitted -->
            @if($feedbackSubmitted->isNotEmpty())
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Feedback Submitted</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Date</th>
                                        <th>Points Earned</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($feedbackSubmitted as $event)
                                        @php
                                            $feedback = \App\Models\EventFeedback::where('event_id', $event->id)
                                                ->where('user_id', auth()->id())
                                                ->first();
                                            $pointsEarned = \App\Models\StudentPoint::where('event_id', $event->id)
                                                ->where('user_id', auth()->id())
                                                ->sum('points');
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $event->name }}</strong></td>
                                            <td>
                                                @if($event->start_time)
                                                    {{ \Carbon\Carbon::parse($event->start_time)->format('M d, Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if($pointsEarned > 0)
                                                    <span class="badge bg-success">{{ $pointsEarned }} points</span>
                                                @else
                                                    <span class="text-muted">No points</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Feedback Submitted</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if($participatedEvents->isEmpty() && $feedbackSubmitted->isEmpty())
                <div class="alert alert-info">
                    <p class="mb-0">No events available for feedback submission at this time.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

