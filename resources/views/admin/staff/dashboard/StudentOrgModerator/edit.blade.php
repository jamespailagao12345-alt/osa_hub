@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')

    <main class="col-md-10 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Edit Event</h2>
        <a href="{{ route('admin.staff.dashboard.StudentOrgModerator.view-events') }}" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Back
        </a>
      </div>

      <div class="card">
        <div class="card-header" style="background-color: midnightblue; color: white;">
          <h5 class="mb-0">Event Information</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.staff.dashboard.StudentOrgModerator.event.update', $event) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="title" class="form-label">Event Name <span class="text-danger">*</span></label>
                <input
                  type="text"
                  id="title"
                  name="title"
                  class="form-control"
                  value="{{ old('title', $event->name) }}"
                  required
                >
              </div>
              <div class="col-md-6">
                <label for="organization_id" class="form-label">Organization <span class="text-danger">*</span></label>
                <select name="organization_id" id="organization_id" class="form-control" required>
                  <option value="">Select Organization</option>
                  @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ (int) old('organization_id', $event->organization_id) === (int) $org->id ? 'selected' : '' }}>
                      {{ $org->name }}
                      @if($org->department)
                        - {{ $org->department->name }}
                      @endif
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <label for="event_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                @php
                  $eventStartDate = $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('Y-m-d') : null;
                  $eventEndDate = $event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('Y-m-d') : null;
                @endphp
                <input
                  type="date"
                  id="event_date"
                  name="event_date"
                  class="form-control"
                  value="{{ old('event_date', $eventStartDate) }}"
                  required
                >
              </div>
              <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input
                  type="date"
                  id="end_date"
                  name="end_date"
                  class="form-control"
                  value="{{ old('end_date', $eventEndDate) }}"
                >
              </div>
              <div class="col-md-3">
                <label for="start_time" class="form-label">Start Time</label>
                <input
                  type="time"
                  id="start_time"
                  name="start_time"
                  class="form-control"
                  value="{{ old('start_time', optional($event->start_time ? \Carbon\Carbon::parse($event->start_time) : null)->format('H:i')) }}"
                >
              </div>
              <div class="col-md-3">
                <label for="end_time" class="form-label">End Time</label>
                <input
                  type="time"
                  id="end_time"
                  name="end_time"
                  class="form-control"
                  value="{{ old('end_time', optional($event->end_time ? \Carbon\Carbon::parse($event->end_time) : null)->format('H:i')) }}"
                >
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="location" class="form-label">Location</label>
                <input
                  type="text"
                  id="location"
                  name="location"
                  class="form-control"
                  value="{{ old('location', $event->location) }}"
                  placeholder="Event Location"
                >
              </div>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea
                id="description"
                name="description"
                class="form-control"
                rows="3"
                placeholder="Event Description"
              >{{ old('description', $event->description) }}</textarea>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">Update Event</button>
              <a href="{{ route('admin.staff.dashboard.StudentOrgModerator.view-events') }}" class="btn btn-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</div>
@endsection

