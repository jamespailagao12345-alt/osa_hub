@extends('layouts.app')

@section('title', 'Create Event')

@section('content')
<div class="container">
    <div class="admin-back-btn-wrap">
        <a href="{{ route('staff.events.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <h2 class="mb-4">Create New Event</h2>
    <form method="POST" action="{{ route('staff.events.store') }}">
        @csrf
        <div class="mb-3">
            <label for="title" class="form-label">Event Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="event_date" class="form-label">Event Date</label>
            <input type="date" name="event_date" id="event_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" name="location" id="location" class="form-control">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create Event</button>
    </form>
</div>
@endsection
