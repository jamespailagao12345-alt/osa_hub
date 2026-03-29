@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main class="col-md-10 py-4">
        <div class="admin-back-btn-wrap mb-3">
            @if(request()->has('return_to'))
              <a href="{{ urldecode(request('return_to')) }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            @else
              <a href="{{ route('admin.events.index') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back to Events</a>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="py-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4 mb-0">Event Details</h1>
                <div>
                    @if($event->status === 'pending')
                        <form method="POST" action="{{ route('admin.events.approve', $event->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this event?');">
                            @csrf
                            <button type="submit" class="btn btn-success">Approve Event</button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#declineModal">
                            Decline Event
                        </button>
                    @endif
                </div>
            </div>

            @if($isDeclined)
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>This event has been declined.</strong> It is considered closed and cannot be edited or updated.
                    @if($event->decline_reason)
                        <div class="mt-2">
                            <strong>Reason for Decline:</strong>
                            <p class="mb-0 mt-1">{{ $event->decline_reason }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Event Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Event Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Event Title:</th>
                                    <td><strong>{{ $event->name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $event->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Organization:</th>
                                    <td>{{ $event->organization->name ?? ($event->coordinator_name ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td>{{ $event->location ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $event->status === 'pending' ? 'warning text-dark' : ($event->status === 'approved' ? 'success' : ($event->status === 'declined' ? 'danger' : 'secondary')) }}">
                                            {{ ucfirst($event->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Start Date/Time:</th>
                                    <td>
                                        @if($event->start_time)
                                            {{ \Carbon\Carbon::parse($event->start_time)->format('M d, Y h:i A') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>End Date/Time:</th>
                                    <td>
                                        @if($event->end_time)
                                            {{ \Carbon\Carbon::parse($event->end_time)->format('M d, Y h:i A') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>{{ $event->creator->first_name ?? '' }} {{ $event->creator->last_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $event->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if($event->required_student_participation && !$isDeclined)
            <!-- Participation Monitoring Section -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #198754; color: white;">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Participation Monitoring</h5>
                </div>
                <div class="card-body">
                    @php
                        $user = auth()->user();
                        $isAdmin = $user && (int) $user->role === 4;
                        $isStaff = $user && (int) $user->role === 2;
                        $canManage = $isAdmin || ($event->created_by === $user->id) || ($isStaff && $event->created_by === $user->id);
                    @endphp

                    @if($event->monitoring_started)
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Monitoring Started</strong> at {{ $event->monitoring_started_at ? \Carbon\Carbon::parse($event->monitoring_started_at)->format('M d, Y h:i A') : 'N/A' }}
                        </div>
                        
                        @php
                            $participants = $event->participants;
                            $attended = $participants->where('attendance_status', 'Attended')->count();
                            $late = $participants->where('attendance_status', 'Late')->count();
                            $absent = $participants->where('attendance_status', 'Absent')->count();
                            $scanned = $participants->where('qr_scanned', true)->count();
                            $total = $participants->count();
                        @endphp
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h3 class="mb-0 text-success">{{ $attended }}</h3>
                                    <small class="text-muted">Attended</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h3 class="mb-0 text-warning">{{ $late }}</h3>
                                    <small class="text-muted">Late</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h3 class="mb-0 text-danger">{{ $absent }}</h3>
                                    <small class="text-muted">Absent</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h3 class="mb-0 text-info">{{ $scanned }}/{{ $total }}</h3>
                                    <small class="text-muted">Scanned/Total</small>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Monitoring Not Started</strong> - Click the button below to begin tracking student participation.
                        </div>
                    @endif

                    <div class="mb-3">
                        <h6>Timer Thresholds</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted"><strong>Attended:</strong> {{ $event->attended_threshold_minutes ?? 60 }} minutes</small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted"><strong>Late:</strong> {{ $event->late_threshold_minutes ?? 60 }} minutes</small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted"><strong>Absent:</strong> {{ $event->absent_threshold_minutes ?? 120 }} minutes</small>
                            </div>
                        </div>
                    </div>

                    @if($canManage)
                        @if(!$event->monitoring_started)
                            <form method="POST" action="{{ route('admin.events.start-monitoring', $event->id) }}" class="d-inline" onsubmit="return confirm('Start participation monitoring for this event? This will begin tracking attendance based on QR code scans.');">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-play-circle me-2"></i>Start Participation Monitoring
                                </button>
                            </form>
                        @endif

                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#thresholdModal">
                            <i class="bi bi-gear me-2"></i>Edit Timer Thresholds
                        </button>

                        <a href="{{ route('admin.qrscan') }}" class="btn btn-info">
                            <i class="bi bi-qr-code-scan me-2"></i>Scan QR Codes
                        </a>
                    @endif
                </div>
            </div>

            <!-- Threshold Settings Modal -->
            @if($canManage)
            <div class="modal fade" id="thresholdModal" tabindex="-1" aria-labelledby="thresholdModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="thresholdModalLabel">Edit Timer Thresholds</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('admin.events.update-thresholds', $event->id) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="modal_attended_threshold" class="form-label">Attended Threshold (minutes)</label>
                                    <input type="number" class="form-control" id="modal_attended_threshold" name="attended_threshold_minutes" value="{{ $event->attended_threshold_minutes ?? 60 }}" min="0" step="1" required>
                                    <small class="form-text text-muted">Students scanned within this time after monitoring starts will be marked as "Attended"</small>
                                </div>
                                <div class="mb-3">
                                    <label for="modal_late_threshold" class="form-label">Late Threshold (minutes)</label>
                                    <input type="number" class="form-control" id="modal_late_threshold" name="late_threshold_minutes" value="{{ $event->late_threshold_minutes ?? 60 }}" min="0" step="1" required>
                                    <small class="form-text text-muted">Students scanned after attended threshold will be marked as "Late"</small>
                                </div>
                                <div class="mb-3">
                                    <label for="modal_absent_threshold" class="form-label">Absent Threshold (minutes)</label>
                                    <input type="number" class="form-control" id="modal_absent_threshold" name="absent_threshold_minutes" value="{{ $event->absent_threshold_minutes ?? 120 }}" min="0" step="1" required>
                                    <small class="form-text text-muted">Unscanned students after this time will be automatically marked as "Absent"</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
            @endif

            <!-- Requirements Section -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Event Requirements</h5>
                </div>
                <div class="card-body">
                    <!-- Default Requirements -->
                    <div class="mb-4">
                        <h6 class="mb-3">Default Requirements:</h6>
                        <ul class="list-group">
                            @foreach($defaultRequirements as $req)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $req }}
                                    @if(in_array($req, $forwardedRequirements->pluck('requirement_name')->toArray()))
                                        <span class="badge bg-success">Forwarded</span>
                                    @else
                                        <span class="badge bg-secondary">Not Yet</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Forwarded Requirements -->
                    <div class="mb-4">
                        <h6 class="mb-3">Forwarded Requirements:</h6>
                        @if($forwardedRequirements->isEmpty())
                            <p class="text-muted">No requirements have been forwarded yet.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Requirement Name</th>
                                            <th>Status</th>
                                            <th>Uploaded By</th>
                                            <th>Date Forwarded</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($forwardedRequirements as $requirement)
                                            <tr>
                                                <td>{{ $requirement->requirement_name }}</td>
                                                <td>
                                                    @if($requirement->is_uploaded)
                                                        <span class="badge bg-success">Uploaded</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($requirement->uploader)
                                                        {{ $requirement->uploader->first_name }} {{ $requirement->uploader->last_name }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>{{ $requirement->created_at->format('M d, Y h:i A') }}</td>
                                                <td>
                                                    @if($requirement->is_uploaded && $requirement->file_path)
                                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($requirement->file_path) }}" class="btn btn-sm btn-primary" target="_blank">Download</a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <!-- Add Requirement Form -->
                    @if(!$isDeclined)
                        <div class="border-top pt-3">
                            <h6 class="mb-3">Add Additional Requirement:</h6>
                            <form method="POST" action="{{ route('admin.events.add-requirement', $event->id) }}" class="d-flex gap-2 mb-3">
                                @csrf
                                <input type="text" name="requirement_name" class="form-control" placeholder="Enter requirement name..." required>
                                <button type="submit" class="btn btn-primary">Add Requirement</button>
                            </form>
                            <small class="text-muted">Admin can require additional files if needed.</small>
                            
                            <!-- Notify Organization Button -->
                            @if($event->organization && $event->organization->official_email)
                                <div class="mt-3">
                                    <form method="POST" action="{{ route('admin.events.notify-requirements', $event->id) }}" class="d-inline" onsubmit="return confirm('Send notification to organization about missing requirements?');">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bi bi-envelope me-2"></i>Notify Organization About Missing Requirements
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
  </div>
</div>

<!-- Decline Event Modal -->
@if($event->status === 'pending')
<div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="declineModalLabel">Decline Event</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.events.decline', $event->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Once declined, this event cannot be edited or updated. Please provide a reason for declining.
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Decline <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="Enter the reason why this event is being declined..." required minlength="5" maxlength="1000">{{ old('reason') }}</textarea>
                        <small class="form-text text-muted">Minimum 5 characters, maximum 1000 characters.</small>
                        @error('reason')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Decline Event</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
