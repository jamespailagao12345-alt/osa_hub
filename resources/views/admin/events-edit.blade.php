@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10 py-4">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back to Events</a>
            </div>
            <h2 class="mb-4">Edit Event</h2>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                // Extract dates and times from DATETIME fields
                $descValue = old('description', $event->description ?? '');
                $startDateTime = $event->start_time ? \Carbon\Carbon::parse($event->start_time) : null;
                $endDateTime = $event->end_time ? \Carbon\Carbon::parse($event->end_time) : null;
                $startDate = old('start_date', $startDateTime ? $startDateTime->format('Y-m-d') : '');
                $startTime = old('start_time', $startDateTime ? $startDateTime->format('H:i') : '00:00');
                $endDate = old('end_date', $endDateTime ? $endDateTime->format('Y-m-d') : '');
                $endTime = old('end_time', $endDateTime ? $endDateTime->format('H:i') : '23:59');
            @endphp

            <form method="POST" action="{{ route('admin.events.update', $event->id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Event Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $event->name) }}" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <select class="form-control" id="description" name="description" required>
                        <option value="">-- Select Description --</option>
                        <option value="National Holidays" {{ $descValue == 'National Holidays' ? 'selected' : '' }}>National Holidays</option>
                        <option value="City Holidays" {{ $descValue == 'City Holidays' ? 'selected' : '' }}>City Holidays</option>
                        <option value="Barangay Holiday" {{ $descValue == 'Barangay Holiday' ? 'selected' : '' }}>Barangay Holiday</option>
                        <option value="USTP System Imposed Activity" {{ $descValue == 'USTP System Imposed Activity' ? 'selected' : '' }}>USTP System Imposed Activity</option>
                        <option value="Balubal Campus Activity" {{ $descValue == 'Balubal Campus Activity' ? 'selected' : '' }}>Balubal Campus Activity</option>
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Date Started</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="start_time" class="form-label">Time Started</label>
                        <input type="time" class="form-control" id="start_time" name="start_time" value="{{ $startTime }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Date Ended</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="end_time" class="form-label">Time Ended</label>
                        <input type="time" class="form-control" id="end_time" name="end_time" value="{{ $endTime }}">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $event->location) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Required Student Participation</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="required_student_participation" name="required_student_participation" value="1" {{ old('required_student_participation', $event->required_student_participation ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="required_student_participation">
                            Enable student participation tracking via QR code scanning
                        </label>
                        <small class="form-text text-muted d-block">When enabled, a QR code will be generated for this event and all staff/student leaders can scan student QR codes for participation.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="points" class="form-label">Event Points</label>
                    <input type="number" class="form-control" id="points" name="points" value="{{ old('points', $event->points ?? '') }}" min="0" step="1">
                    <small class="form-text text-muted">Points awarded to students who submit feedback for this event. Leave empty if no points are awarded.</small>
                </div>

                @if($event->required_student_participation)
                <div class="mb-3 border-top pt-3">
                    <h5 class="mb-3">Participation Monitoring Timer Settings</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="attended_threshold_minutes" class="form-label">Attended Threshold (minutes)</label>
                            <input type="number" class="form-control" id="attended_threshold_minutes" name="attended_threshold_minutes" value="{{ old('attended_threshold_minutes', $event->attended_threshold_minutes ?? 60) }}" min="0" step="1">
                            <small class="form-text text-muted">Students scanned within this time after monitoring starts will be marked as "Attended"</small>
                        </div>
                        <div class="col-md-4">
                            <label for="late_threshold_minutes" class="form-label">Late Threshold (minutes)</label>
                            <input type="number" class="form-control" id="late_threshold_minutes" name="late_threshold_minutes" value="{{ old('late_threshold_minutes', $event->late_threshold_minutes ?? 60) }}" min="0" step="1">
                            <small class="form-text text-muted">Students scanned after attended threshold will be marked as "Late"</small>
                        </div>
                        <div class="col-md-4">
                            <label for="absent_threshold_minutes" class="form-label">Absent Threshold (minutes)</label>
                            <input type="number" class="form-control" id="absent_threshold_minutes" name="absent_threshold_minutes" value="{{ old('absent_threshold_minutes', $event->absent_threshold_minutes ?? 120) }}" min="0" step="1">
                            <small class="form-text text-muted">Unscanned students after this time will be automatically marked as "Absent"</small>
                        </div>
                    </div>
                </div>
                @endif

                <button type="submit" class="btn btn-primary">Update Event</button>
                <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </main>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Define the order of all form fields
    const fieldOrder = ['name', 'description', 'start_date', 'start_time', 'end_date', 'end_time', 'location'];
    
    // Add Enter key navigation to all fields
    fieldOrder.forEach(function(fieldId, index) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('keydown', function(e) {
                // Skip Enter key for select dropdowns
                if (field.tagName === 'SELECT' && e.key === 'Enter') {
                    e.preventDefault();
                    if (index < fieldOrder.length - 1) {
                        const nextFieldId = fieldOrder[index + 1];
                        const nextField = document.getElementById(nextFieldId);
                        if (nextField && nextField.style.display !== 'none') {
                            nextField.focus();
                        } else {
                            for (let i = index + 2; i < fieldOrder.length; i++) {
                                const skipField = document.getElementById(fieldOrder[i]);
                                if (skipField && skipField.style.display !== 'none') {
                                    skipField.focus();
                                    if (skipField.type === 'date' || skipField.type === 'time') {
                                        skipField.select();
                                    }
                                    break;
                                }
                            }
                        }
                    }
                    return;
                }
                
                if (e.key === 'Enter') {
                    e.preventDefault();
                    
                    if (index === fieldOrder.length - 1) {
                        document.querySelector('form[action*="events"]').submit();
                    } else {
                        for (let i = index + 1; i < fieldOrder.length; i++) {
                            const nextField = document.getElementById(fieldOrder[i]);
                            if (nextField && nextField.style.display !== 'none') {
                                nextField.focus();
                                if (nextField.type === 'date' || nextField.type === 'time') {
                                    nextField.select();
                                }
                                break;
                            }
                        }
                    }
                }
            });
        }
    });
});
</script>
@endpush
@endsection
