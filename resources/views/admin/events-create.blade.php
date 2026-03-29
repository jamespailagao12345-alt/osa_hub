@extends('layouts.app')

@section('title', 'Create Event')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10 py-4">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary rounded-pill px-3">Back</a>
            </div>
            <h2 class="mb-4">Create Approved Event</h2>

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

            <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Event Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <select class="form-control" id="description" name="description" required>
                        <option value="">-- Select Description --</option>
                        <option value="National Holidays" {{ old('description') == 'National Holidays' ? 'selected' : '' }}>National Holidays</option>
                        <option value="City Holidays" {{ old('description') == 'City Holidays' ? 'selected' : '' }}>City Holidays</option>
                        <option value="Barangay Holiday" {{ old('description') == 'Barangay Holiday' ? 'selected' : '' }}>Barangay Holiday</option>
                        <option value="USTP System Imposed Activity" {{ old('description') == 'USTP System Imposed Activity' ? 'selected' : '' }}>USTP System Imposed Activity</option>
                        <option value="Balubal Campus Activity" {{ old('description') == 'Balubal Campus Activity' ? 'selected' : '' }}>Balubal Campus Activity</option>
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', request('date')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="start_time" name="start_time" value="{{ old('start_time', '00:00') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', request('date')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" class="form-control" id="end_time" name="end_time" value="{{ old('end_time', '23:59') }}">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location" name="location" value="{{ old('location') }}">
                </div>
                <div class="mb-3">
                    <label for="points" class="form-label">Event Points</label>
                    <input type="number" class="form-control" id="points" name="points" value="{{ old('points') }}" min="0" step="1">
                    <small class="form-text text-muted">Points awarded to students who submit feedback for this event. Leave empty if no points are awarded.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Required Student Participation</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="required_student_participation" name="required_student_participation" value="1" {{ old('required_student_participation') ? 'checked' : '' }}>
                        <label class="form-check-label" for="required_student_participation">
                            Enable student participation tracking via QR code scanning
                        </label>
                        <small class="form-text text-muted d-block">When enabled, a QR code will be generated for this event and all staff/student leaders can scan student QR codes for participation.</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="event_files" class="form-label">Event Files (Optional)</label>
                    <input type="file" name="event_files[]" id="event_files" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls,.csv,.txt">
                    <small class="form-text text-muted">You can upload multiple files. Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX, XLS, CSV, TXT (Max 10MB per file). These files will be visible to Admin and OSA Staff.</small>
                    <div id="file-list" class="mt-2"></div>
                </div>
                <button type="submit" class="btn btn-primary">Create Event</button>
                <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </main>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Define the order of all form fields (description is now select, not textarea)
    const fieldOrder = ['name', 'description', 'start_date', 'start_time', 'end_date', 'end_time', 'location'];
    
    // Add Enter key navigation to all fields
    fieldOrder.forEach(function(fieldId, index) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('keydown', function(e) {
                // Skip Enter key for select dropdowns - use arrow keys instead
                if (field.tagName === 'SELECT' && e.key === 'Enter') {
                    e.preventDefault();
                    // Move to next field
                    if (index < fieldOrder.length - 1) {
                        const nextFieldId = fieldOrder[index + 1];
                        const nextField = document.getElementById(nextFieldId);
                        if (nextField && nextField.style.display !== 'none') {
                            nextField.focus();
                        } else {
                            // If next field is hidden, skip to the one after
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
                    
                    // If it's the last field (location), submit the form
                    if (index === fieldOrder.length - 1) {
                        document.querySelector('form[action="{{ route('admin.events.store') }}"]').submit();
                    } else {
                        // Move to next field (skip if hidden)
                        for (let i = index + 1; i < fieldOrder.length; i++) {
                            const nextField = document.getElementById(fieldOrder[i]);
                            if (nextField && nextField.style.display !== 'none') {
                                nextField.focus();
                                // For date/time inputs, also select the content
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


