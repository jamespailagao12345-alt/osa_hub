@extends('layouts.app')

@section('title', $service . ' - ' . $designation->name)

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main class="col-md-10 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="h4 mb-0">
            <i class="bi bi-{{ $serviceConfig['icon'] }} text-{{ $serviceConfig['color'] }}"></i>
            {{ $service }}
          </h2>
          <p class="text-muted mb-0">{{ $designation->name }}</p>
        </div>
        <a href="{{ route('admin.staff.dashboard.designation', ['designation' => $designation->name]) }}" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Back
        </a>
      </div>

      <div class="card">
        <div class="card-header" style="background-color: midnightblue; color: white;">
          <h5 class="mb-0">
            <i class="bi bi-{{ $serviceConfig['icon'] }}"></i>
            Appointments - {{ $service }}
            <span class="badge bg-light text-primary ml-2">{{ $appointments->total() }}</span>
          </h5>
        </div>
        <div class="card-body">
          @if($appointments->isEmpty())
            <div class="alert alert-info mb-0">
              <i class="bi bi-info-circle mr-2"></i>
              <strong>No appointments found</strong>
              <p class="mb-0 mt-2">There are no appointments for {{ $service }} at this time.</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead>
                  <tr class="text-center" style="background-color: midnightblue; color: white;">
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>Appointment Date</th>
                    <th>Schedule Time</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Session</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($appointments as $appointment)
                    @php
                      // Get appointment date (rescheduled or original)
                      $appointmentDate = $appointment->action_taken === 'reschedule' && $appointment->rescheduled_date 
                        ? \Carbon\Carbon::parse($appointment->rescheduled_date) 
                        : $appointment->appointment_date;
                      
                      // Get appointment time (rescheduled or original)
                      $appointmentTime = $appointment->action_taken === 'reschedule' && $appointment->rescheduled_time 
                        ? $appointment->rescheduled_time 
                        : $appointment->appointment_time;
                      
                      // Status badge color
                      $statusBadgeClass = 'bg-secondary';
                      if ($appointment->status === 'approved') {
                        $statusBadgeClass = 'bg-success';
                      } elseif ($appointment->status === 'pending') {
                        $statusBadgeClass = 'bg-warning';
                      } elseif ($appointment->status === 'rescheduled') {
                        $statusBadgeClass = 'bg-info';
                      } elseif ($appointment->status === 'declined') {
                        $statusBadgeClass = 'bg-danger';
                      } elseif ($appointment->status === 'cancelled') {
                        $statusBadgeClass = 'bg-secondary';
                      }
                      
                      // Category color
                      $categoryBgColor = '#6c757d';
                      $categoryTextColor = 'white';
                      if ($appointment->category === 'Red') {
                        $categoryBgColor = '#dc3545';
                      } elseif ($appointment->category === 'Blue') {
                        $categoryBgColor = '#0d6efd';
                      } elseif ($appointment->category === 'Yellow') {
                        $categoryBgColor = '#ffc107';
                        $categoryTextColor = 'black';
                      }
                      
                      // Format appointment date
                      $formattedDate = '';
                      if ($appointmentDate instanceof \Carbon\Carbon) {
                        $formattedDate = $appointmentDate->format('M d, Y');
                      } elseif ($appointmentDate) {
                        $formattedDate = \Carbon\Carbon::parse($appointmentDate)->format('M d, Y');
                      }
                      
                      // Format appointment time
                      $formattedTime = '';
                      if ($appointmentTime) {
                        $formattedTime = date('g:i A', strtotime($appointmentTime));
                      }
                    @endphp
                    <tr>
                      <td>
                        <strong>{{ $appointment->full_name }}</strong>
                        @if($appointment->reason_for_counseling)
                          <br><small class="text-muted">{{ $appointment->reason_for_counseling }}</small>
                        @endif
                      </td>
                      <td>{{ $appointment->email ?? '-' }}</td>
                      <td>{{ $appointment->contact_number ?? '-' }}</td>
                      <td class="text-center">{{ $formattedDate }}</td>
                      <td class="text-center">{{ $formattedTime }}</td>
                      <td class="text-center">
                        @if($appointment->category)
                          <span class="badge" style="background-color: {{ $categoryBgColor }}; color: {{ $categoryTextColor }};">
                            {{ $appointment->category }}
                          </span>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td class="text-center">
                        <span class="badge {{ $statusBadgeClass }}">{{ ucfirst($appointment->status) }}</span>
                        @if($appointment->action_taken === 'reschedule')
                          <br><small class="text-info">
                            <i class="bi bi-calendar-event"></i> Rescheduled
                          </small>
                        @endif
                      </td>
                      <td class="text-center">
                        @if($appointment->session)
                          <span class="badge {{ $appointment->session === 'Finish' ? 'bg-success' : 'bg-primary' }}">
                            {{ $appointment->session }}
                          </span>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td class="text-center">
                        @php
                          $appointmentReturnPath = route('admin.staff.dashboard.guidance-counselor.service', [
                            'designation' => $designation->name,
                            'service' => $service
                          ]);
                        @endphp
                        <a href="{{ route('admin.appointments.index', ['return_to' => urlencode($appointmentReturnPath)]) }}" 
                           class="btn btn-sm btn-outline-primary" 
                           title="View Appointment Details">
                          <i class="bi bi-eye"></i> View
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
              {{ $appointments->links() }}
            </div>
          @endif
        </div>
      </div>
    </main>
  </div>
</div>
@endsection

