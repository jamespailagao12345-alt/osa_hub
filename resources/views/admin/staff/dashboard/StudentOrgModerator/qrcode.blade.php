@extends('layouts.app')

@section('title', 'Event QR Code')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main class="col-md-10 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Event QR Code</h2>
        <a href="{{ route('admin.staff.dashboard.StudentOrgModerator') }}" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Back
        </a>
      </div>

      <div class="card">
        <div class="card-header" style="background-color: midnightblue; color: white;">
          <h5 class="mb-0">{{ $event->name }}</h5>
        </div>
        <div class="card-body text-center">
          <div class="mb-3">
            <p class="text-muted">Scan this QR code for event attendance</p>
          </div>
          <div class="mb-3">
            {!! $qrCode !!}
          </div>
          <div class="mt-3">
            <a href="{{ route('admin.staff.dashboard.StudentOrgModerator') }}" class="btn btn-primary">Back to Events</a>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>
@endsection

