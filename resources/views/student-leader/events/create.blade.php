@extends('layouts.app')
@section('title', 'Create Event')
@section('content')
<div class="container-fluid">
  <div class="row">
    @include('assistant.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <h2>Create New Event</h2>
  <form method="POST" action="{{ route('student-leader.events.store') }}">
    @csrf
    <div class="mb-3">
      <label for="name" class="form-label">Event Name</label>
      <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
    </div>
    <div class="mb-3">
      <label for="event_date" class="form-label">Event Date</label>
      <input type="date" class="form-control" id="event_date" name="event_date" required>
    </div>
    <button type="submit" class="btn btn-primary">Create Event</button>
  </form>
    </main>
  </div>
</div>
@endsection
