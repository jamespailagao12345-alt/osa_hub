@extends('layouts.app')

@section('title', 'Clients List - ' . $designation->name)

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main class="col-md-10 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="h4 mb-0">
            <i class="bi bi-people text-primary"></i>
            Clients List
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
            <i class="bi bi-people"></i>
            Clients List
            <span class="badge bg-light text-primary ml-2">{{ $clients->total() }}</span>
          </h5>
        </div>
        <div class="card-body">
          @if($clients->isEmpty())
            <div class="alert alert-info mb-0">
              <i class="bi bi-info-circle mr-2"></i>
              <strong>No clients found</strong>
              <p class="mb-0 mt-2">There are no clients with appointments at this time.</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead>
                  <tr class="text-center" style="background-color: midnightblue; color: white;">
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>Total Appointments</th>
                    <th>Last Appointment</th>
                    <th>Categories</th>
                    <th>Reasons for Counseling</th>
                    <th>Session</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($clients as $client)
                    @php
                      // Format last appointment date
                      $formattedLastDate = '';
                      if ($client['last_appointment_date']) {
                        if ($client['last_appointment_date'] instanceof \Carbon\Carbon) {
                          $formattedLastDate = $client['last_appointment_date']->format('M d, Y');
                        } else {
                          $formattedLastDate = \Carbon\Carbon::parse($client['last_appointment_date'])->format('M d, Y');
                        }
                      }
                      
                      // Get unique categories with colors
                      $uniqueCategories = is_array($client['categories']) 
                        ? collect($client['categories'])->unique()->values() 
                        : collect($client['categories'] ?? [])->unique()->values();
                      $uniqueReasons = is_array($client['reasons']) 
                        ? collect($client['reasons'])->unique()->values() 
                        : collect($client['reasons'] ?? [])->unique()->values();
                      $categoryColors = [
                        'Red' => ['bg' => '#dc3545', 'text' => 'white'],
                        'Blue' => ['bg' => '#0d6efd', 'text' => 'white'],
                        'Yellow' => ['bg' => '#ffc107', 'text' => 'black'],
                      ];
                    @endphp
                    <tr>
                      <td>
                        <strong>{{ $client['full_name'] ?? '-' }}</strong>
                        @if($client['user_id'] && isset($client['user']))
                          <br><small class="text-muted">User ID: {{ $client['user_id'] }}</small>
                        @endif
                      </td>
                      <td>{{ $client['email'] ?? '-' }}</td>
                      <td>{{ $client['contact_number'] ?? '-' }}</td>
                      <td class="text-center">
                        <span class="badge bg-info">{{ $client['appointment_count'] }}</span>
                      </td>
                      <td class="text-center">
                        @if($formattedLastDate)
                          {{ $formattedLastDate }}
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td class="text-center">
                        @if($uniqueCategories->isNotEmpty())
                          <div class="d-flex flex-wrap justify-content-center gap-1">
                            @foreach($uniqueCategories as $category)
                              @php
                                $color = $categoryColors[$category] ?? ['bg' => '#6c757d', 'text' => 'white'];
                              @endphp
                              <span class="badge" style="background-color: {{ $color['bg'] }}; color: {{ $color['text'] }};">
                                {{ $category }}
                              </span>
                            @endforeach
                          </div>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td>
                        @if($uniqueReasons->isNotEmpty())
                          <div class="d-flex flex-column gap-1">
                            @foreach($uniqueReasons as $reason)
                              <span class="badge bg-secondary">{{ $reason }}</span>
                            @endforeach
                          </div>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td class="text-center">
                        <button type="button" 
                                class="btn btn-sm btn-info" 
                                data-toggle="modal" 
                                data-target="#remarksModal{{ md5($client['email'] . ($client['user_id'] ?? '')) }}"
                                title="View Remarks">
                          <i class="bi bi-chat-left-text"></i> Remarks
                        </button>
                      </td>
                      <td class="text-center">
                        @php
                          $clientsReturnPath = route('admin.staff.dashboard.guidance-counselor.clients', [
                            'designation' => $designation->name
                          ]);
                          // Build filter for appointments by this client's email or user_id
                          $clientEmail = $client['email'] ?? '';
                          $clientUserId = $client['user_id'] ?? null;
                        @endphp
                        <a href="{{ route('admin.appointments.index', [
                          'return_to' => urlencode($clientsReturnPath),
                          'filter_email' => $clientEmail,
                          'filter_user_id' => $clientUserId
                        ]) }}" 
                           class="btn btn-sm btn-outline-primary" 
                           title="View Client Appointments">
                          <i class="bi bi-eye"></i> View Appointments
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
              {{ $clients->links() }}
            </div>
          @endif
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Remarks Modals for each client -->
@foreach($clients as $client)
  @php
    $modalId = 'remarksModal' . md5($client['email'] . ($client['user_id'] ?? ''));
    $clientAppointments = $client['appointments'] ?? collect([]);
  @endphp
  <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="{{ $modalId }}Label">
            <i class="bi bi-person-circle"></i> Client Remarks - {{ $client['full_name'] ?? 'N/A' }}
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Client Details -->
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-info-circle"></i> Client Information</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Full Name:</strong> {{ $client['full_name'] ?? '-' }}</p>
                  <p><strong>Email:</strong> {{ $client['email'] ?? '-' }}</p>
                  <p><strong>Contact Number:</strong> {{ $client['contact_number'] ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                  <p><strong>User ID:</strong> {{ $client['user_id'] ?? '-' }}</p>
                  <p><strong>Total Appointments:</strong> {{ $client['appointment_count'] ?? 0 }}</p>
                  <p><strong>Last Appointment:</strong> 
                    @if($client['last_appointment_date'])
                      {{ $client['last_appointment_date'] instanceof \Carbon\Carbon 
                        ? $client['last_appointment_date']->format('M d, Y') 
                        : \Carbon\Carbon::parse($client['last_appointment_date'])->format('M d, Y') }}
                    @else
                      -
                    @endif
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Appointments with Remarks -->
          <div class="card">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Appointments & Remarks</h6>
            </div>
            <div class="card-body">
              @if($clientAppointments->isEmpty())
                <p class="text-muted text-center">No appointments found for this client.</p>
              @else
                <div class="accordion" id="appointmentsAccordion{{ md5($client['email'] . ($client['user_id'] ?? '')) }}">
                  @foreach($clientAppointments as $index => $appointment)
                    @php
                      $appointmentDate = $appointment->action_taken === 'reschedule' && $appointment->rescheduled_date 
                        ? \Carbon\Carbon::parse($appointment->rescheduled_date) 
                        : $appointment->appointment_date;
                      $appointmentTime = $appointment->action_taken === 'reschedule' && $appointment->rescheduled_time 
                        ? $appointment->rescheduled_time 
                        : $appointment->appointment_time;
                      $appointmentId = 'appointment' . $appointment->id;
                      $collapseId = 'collapse' . $appointment->id;
                    @endphp
                    <div class="card mb-2">
                      <div class="card-header" id="heading{{ $appointment->id }}">
                        <h6 class="mb-0">
                          <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                            <div class="d-flex justify-content-between align-items-center">
                              <span>
                                <i class="bi bi-calendar-event"></i> 
                                Appointment #{{ $appointment->id }} - 
                                {{ $appointmentDate instanceof \Carbon\Carbon 
                                  ? $appointmentDate->format('M d, Y') 
                                  : \Carbon\Carbon::parse($appointmentDate)->format('M d, Y') }}
                                @if($appointmentTime)
                                  {{ date('g:i A', strtotime($appointmentTime)) }}
                                @endif
                              </span>
                              <span>
                                @if($appointment->session)
                                  <span class="badge {{ $appointment->session === 'Finish' ? 'bg-success' : 'bg-primary' }}">
                                    {{ $appointment->session }}
                                  </span>
                                @endif
                                @if($appointment->status)
                                  <span class="badge bg-secondary">{{ ucfirst($appointment->status) }}</span>
                                @endif
                              </span>
                            </div>
                          </button>
                        </h6>
                      </div>
                      <div id="{{ $collapseId }}" class="collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $appointment->id }}" data-parent="#appointmentsAccordion{{ md5($client['email'] . ($client['user_id'] ?? '')) }}">
                        <div class="card-body">
                          <div class="mb-3">
                            <p><strong>Appointment Date:</strong> 
                              {{ $appointmentDate instanceof \Carbon\Carbon 
                                ? $appointmentDate->format('M d, Y') 
                                : \Carbon\Carbon::parse($appointmentDate)->format('M d, Y') }}
                              @if($appointmentTime)
                                at {{ date('g:i A', strtotime($appointmentTime)) }}
                              @endif
                            </p>
                            <p><strong>Reason for Counseling:</strong> {{ $appointment->reason_for_counseling ?? '-' }}</p>
                            <p><strong>Category:</strong> 
                              @if($appointment->category)
                                @php
                                  $categoryColors = [
                                    'Red' => '#dc3545',
                                    'Blue' => '#0d6efd',
                                    'Yellow' => '#ffc107'
                                  ];
                                  $bgColor = $categoryColors[$appointment->category] ?? '#6c757d';
                                  $textColor = $appointment->category === 'Yellow' ? 'black' : 'white';
                                @endphp
                                <span class="badge" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                                  {{ $appointment->category }}
                                </span>
                              @else
                                -
                              @endif
                            </p>
                            <p><strong>Concern:</strong> {{ $appointment->concern ?? '-' }}</p>
                            <p><strong>Status:</strong> 
                              <span class="badge bg-secondary">{{ ucfirst($appointment->status) }}</span>
                              @if($appointment->session)
                                <span class="badge {{ $appointment->session === 'Finish' ? 'bg-success' : 'bg-primary' }}">
                                  {{ $appointment->session }}
                                </span>
                              @endif
                            </p>
                            @if($appointment->action_taken === 'reschedule' && $appointment->rescheduled_date)
                              <p><strong>Rescheduled To:</strong> 
                                {{ \Carbon\Carbon::parse($appointment->rescheduled_date)->format('M d, Y') }}
                                @if($appointment->rescheduled_time)
                                  at {{ date('g:i A', strtotime($appointment->rescheduled_time)) }}
                                @endif
                              </p>
                            @endif
                          </div>
                          
                          <hr>
                          
                          <form action="{{ route('admin.appointments.update-remarks', $appointment->id) }}" method="POST" class="remarks-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="return_to" value="{{ route('admin.staff.dashboard.guidance-counselor.clients', ['designation' => $designation->name]) }}">
                            <div class="form-group">
                              <label for="remarks{{ $appointment->id }}"><strong>Remarks:</strong></label>
                              <textarea class="form-control" 
                                        id="remarks{{ $appointment->id }}" 
                                        name="remarks" 
                                        rows="5" 
                                        placeholder="Enter remarks for this appointment/session...">{{ $appointment->remarks ?? '' }}</textarea>
                              <small class="form-text text-muted">Add notes, observations, or progress notes for this session.</small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                              <i class="bi bi-save"></i> Save Remarks
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endforeach

@endsection

@push('scripts')
<script>
  // Handle form submission with success message
  document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.remarks-form');
    forms.forEach(function(form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        const originalClass = submitButton.className;
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
        
        fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          }
        })
        .then(response => {
          if (!response.ok) {
            return response.json().then(data => {
              throw new Error(data.message || 'Server error');
            });
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            submitButton.innerHTML = '<i class="bi bi-check-circle"></i> Saved!';
            submitButton.className = 'btn btn-success btn-sm';
            
            // Reset button after 2 seconds
            setTimeout(function() {
              submitButton.disabled = false;
              submitButton.innerHTML = originalText;
              submitButton.className = originalClass;
            }, 2000);
          } else {
            alert('Error saving remarks: ' + (data.message || 'Unknown error'));
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
            submitButton.className = originalClass;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error saving remarks: ' + error.message);
          submitButton.disabled = false;
          submitButton.innerHTML = originalText;
          submitButton.className = originalClass;
        });
      });
    });
  });
</script>
@endpush

