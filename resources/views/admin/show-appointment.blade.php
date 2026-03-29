@extends('layouts.app')

@section('title', 'Appointments')

@section('content')
  <div class="admin-back-btn-wrap">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
  </div>
  <div class="container-fluid">
    <div class="row">
      @include('admin.partials.sidebar')
      <main class="col-md-10">
        <h2 class="mb-3">Appointments</h2>

        @php
          // Support either $appointments (new) or $data (legacy)
          $rows = isset($appointments) ? $appointments : ($data ?? collect());
        @endphp

        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr class="text-center" style="background-color:midnightblue; color:white">
                <th>ID</th>
                <th>Appointment Date</th>
                <th>Student/ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Concern/Message</th>
                <th>Assigned Staff</th>
                <th>Status</th>
                <th>Approve</th>
                <th>Cancel</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $appoint)
                <tr class="text-center">
                  <td>{{ $appoint->id }}</td>
                  <td>
                    @if(isset($appoint->appointment_date))
                      {{ optional($appoint->appointment_date)->format('M d, Y') }} {{ $appoint->appointment_time ? date('g:i A', strtotime($appoint->appointment_time)) : '' }}
                    @else
                      {{ $appoint->date ?? '-' }}
                    @endif
                  </td>
                  <td>{{ $appoint->user_id ?? '-' }}</td>
                  <td>{{ $appoint->full_name ?? $appoint->name ?? '-' }}</td>
                  <td>{{ $appoint->phone ?? '-' }}</td>
                  <td>{{ $appoint->email ?? '-' }}</td>
                  <td>{{ $appoint->concern ?? $appoint->message ?? '-' }}</td>
                  <td>
                    @if(isset($appoint->assignedStaff) && $appoint->assignedStaff)
                      {{ $appoint->assignedStaff->first_name }} {{ $appoint->assignedStaff->last_name }}
                    @else
                      {{ $appoint->staff ?? 'Unassigned' }}
                    @endif
                  </td>
                  <td>{{ ucfirst($appoint->status ?? '-') }}</td>
                  <td>
                    <form method="POST" action="{{ route('admin.appointments.approve', $appoint->id) }}">
                      @csrf
                      <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>
                  </td>
                  <td>
                    <form method="POST" action="{{ route('admin.appointments.cancel', $appoint->id) }}" onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
                      @csrf
                      <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="11" class="text-center text-muted">No appointments found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </main>
    </div>
  </div>
@endsection