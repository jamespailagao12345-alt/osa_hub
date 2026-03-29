@extends('layouts.app')

@section('title', 'All My Appointments')

@section('content')
<div class="container">
    <h2 class="mb-4">All My Appointments</h2>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Concern</th>
                    <th>Status</th>
                    <th>Assigned Staff</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appt)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($appt->appointment_date)->format('M d, Y') }}</td>
                    <td>{{ date('g:i A', strtotime($appt->appointment_time)) }}</td>
                    <td>{{ $appt->concern }}</td>
                    <td>
                        <span class="badge bg-{{ $appt->status === 'pending' ? 'warning text-dark' : ($appt->status === 'approved' ? 'success' : ($appt->status === 'cancelled' ? 'danger' : 'secondary')) }}">
                            {{ ucfirst($appt->status) }}
                        </span>
                    </td>
                    <td>
                        {{ $appt->assignedStaff ? $appt->assignedStaff->first_name . ' ' . $appt->assignedStaff->last_name : 'TBD' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No appointments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-back-btn-wrap" style="margin-top: 1rem;">
        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection
