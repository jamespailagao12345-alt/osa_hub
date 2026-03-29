@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10">
        <div class="admin-back-btn-wrap">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
        </div>
        <style>
            .students-list-title {
                display: block;
                width: 100%;
                box-sizing: border-box;
                background-color: #ffffff;
                color: midnightblue;
                padding: .5rem 1rem; /* align with other header boxes */
                border: none;            /* remove all borders */
                border-bottom: 1px solid midnightblue; /* keep only bottom border in navy */
                border-radius: 0;        /* remove rounding for underline style */
            }
        </style>
        <h2 class="mb-3"><span class="students-list-title">Students List</span></h2>
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label for="department_id" class="form-label">Department</label>
                <select name="department_id" id="department_id" class="form-select">
                    <option value="">All</option>
                    @isset($departments)
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (isset($filters['department_id']) && (string)$filters['department_id']===(string)$dept->id) ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <div class="col-auto">
                <label for="course_id" class="form-label">Course</label>
                <select name="course_id" id="course_id" class="form-select">
                    <option value="">All</option>
                    @isset($courses)
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ (isset($filters['course_id']) && (string)$filters['course_id']===(string)$course->id) ? 'selected' : '' }}>{{ $course->name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <div class="col-auto">
                <label for="year_level" class="form-label">Year Level</label>
                <input type="text" name="year_level" id="year_level" class="form-control" value="{{ $filters['year_level'] ?? '' }}" placeholder="e.g. 1, 2, 3, 4">
            </div>
            <div class="col-auto">
                <label for="status" class="form-label">Status</label>
                <input type="text" name="status" id="status" class="form-control" value="{{ $filters['status'] ?? '' }}" placeholder="e.g. active">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.show-students-list') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr class="text-center" style="background-color:midnightblue; color:white">
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Points</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr class="text-center">
                        <td>{{ $student->user_id ?? '-' }}</td>
                        <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->department->name ?? '' }}</td>
                        <td>{{ $student->course->name ?? '-' }}</td>
                        <td>{{ $student->year_level ?? '-' }}</td>
                        <td>
                            @php
                                $totalPoints = \App\Models\StudentPoint::where('user_id', $student->id)->sum('points');
                            @endphp
                            <span class="badge bg-success">{{ $totalPoints }}</span>
                        </td>
                        <td>{{ $student->status ?? '-' }}</td>
                        <td>
                          @php
                            $userId = $student->id;
                            $isSuspended = $student->suspended ?? false;
                            
                            // Check if current user is admin or Prefect of Discipline
                            $currentUser = auth()->user();
                            $isAdmin = $currentUser && (int) $currentUser->role === 4;
                            $isStaff = $currentUser && (int) $currentUser->role === 2;
                            
                            // Check if current user is Prefect of Discipline
                            $isPrefectOfDiscipline = false;
                            if ($isStaff) {
                              $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($currentUser->email))])->first();
                              $userDesignation = $currentUser->designation
                                ?? optional($currentUser->staffProfile)->designation
                                ?? ($staffRecord ? $staffRecord->designation : null);
                              
                              if ($userDesignation && strcasecmp($userDesignation, 'Prefect of Discipline') === 0) {
                                $isPrefectOfDiscipline = true;
                              }
                            }
                            
                            // Only Prefect of Discipline can suspend/reactivate, admins can only view
                            $canSuspend = $isPrefectOfDiscipline;
                          @endphp
                          @if($isSuspended)
                            @if($isAdmin && !$canSuspend)
                              <button type="button" 
                                      class="badge badge-danger border-0 p-2" 
                                      data-toggle="modal" 
                                      data-target="#viewSuspendModal{{ $userId }}"
                                      title="View Suspension Details"
                                      style="cursor: pointer;">
                                Suspended
                              </button>
                            @else
                              <span class="badge badge-danger">Suspended</span>
                            @endif
                            @if($canSuspend)
                              <form action="{{ route('admin.students.reactivate', $userId) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reactivate this student account?');">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-success ml-2" title="Reactivate Student Account">
                                  <i class="bi bi-check-circle"></i> Reactivate
                                </button>
                              </form>
                            @endif
                          @else
                            @if($canSuspend)
                              <button type="button" 
                                      class="btn btn-sm btn-danger" 
                                      data-toggle="modal" 
                                      data-target="#suspendModal{{ $userId }}"
                                      title="Suspend Student Account">
                                <i class="bi bi-x-circle"></i> Suspend
                              </button>
                            @else
                              <span class="badge badge-success">Active Account</span>
                            @endif
                          @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $students->links() }}
        </div>
        </main>
    </div>
</div>

<!-- Suspension Modals -->
@foreach($students as $student)
  @php
    $userId = $student->id;
    $isSuspended = $student->suspended ?? false;
    
    // Check if current user is admin or Prefect of Discipline
    $currentUser = auth()->user();
    $isAdmin = $currentUser && (int) $currentUser->role === 4;
    $isStaff = $currentUser && (int) $currentUser->role === 2;
    
    // Check if current user is Prefect of Discipline
    $isPrefectOfDiscipline = false;
    if ($isStaff) {
      $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($currentUser->email))])->first();
      $userDesignation = $currentUser->designation
        ?? optional($currentUser->staffProfile)->designation
        ?? ($staffRecord ? $staffRecord->designation : null);
      
      if ($userDesignation && strcasecmp($userDesignation, 'Prefect of Discipline') === 0) {
        $isPrefectOfDiscipline = true;
      }
    }
    
    // Only Prefect of Discipline can suspend/reactivate
    $canSuspend = $isPrefectOfDiscipline;
    
    $firstName = $student->first_name ?? '';
    $middleName = $student->middle_name ?? '';
    $lastName = $student->last_name ?? '';
    $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
    $studentId = $student->user_id ?? '-';
    $email = $student->email ?? '-';
    $course = optional($student->course)->name ?? '-';
    $yearLevel = $student->year_level ?? '-';
    $department = optional($student->department)->name ?? '-';
    $contactNumber = $student->contact_number ?? '-';
  @endphp
  
  <!-- Suspend Modal (Only for Prefect of Discipline) -->
  @if(!$isSuspended && $canSuspend)
  <div class="modal fade" id="suspendModal{{ $userId }}" tabindex="-1" aria-labelledby="suspendModalLabel{{ $userId }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="suspendModalLabel{{ $userId }}">
            <i class="bi bi-x-circle"></i> Suspend Student Account
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{ route('admin.students.suspend', $userId) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <!-- Student Details -->
            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-person-circle"></i> Student Information</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <p><strong>Student ID:</strong> {{ $studentId }}</p>
                    <p><strong>Full Name:</strong> {{ $fullName ?: '-' }}</p>
                    <p><strong>Email:</strong> {{ $email }}</p>
                    <p><strong>Contact Number:</strong> {{ $contactNumber ?: '-' }}</p>
                  </div>
                  <div class="col-md-6">
                    <p><strong>Department:</strong> {{ $department ?: '-' }}</p>
                    <p><strong>Course:</strong> {{ $course ?: '-' }}</p>
                    <p><strong>Year Level:</strong> {{ $yearLevel ?: '-' }}</p>
                    <p><strong>Status:</strong> {{ $student->status ?? '-' }}</p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Suspension Reason -->
            <div class="form-group">
              <label for="suspension_reason{{ $userId }}"><strong>Reason for Suspension <span class="text-danger">*</span></strong></label>
              <textarea class="form-control @error('suspension_reason') is-invalid @enderror" 
                        id="suspension_reason{{ $userId }}" 
                        name="suspension_reason" 
                        rows="5" 
                        placeholder="Enter the reason for suspending this student account (minimum 10 characters)..."
                        required>{{ old('suspension_reason') }}</textarea>
              <small class="form-text text-muted">Please provide a detailed reason for suspending this account. The student will see this message when attempting to login.</small>
              @error('suspension_reason')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle"></i> <strong>Warning:</strong> This action will prevent the student from logging into their account. The student will receive a message that their account has been suspended.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">
              <i class="bi bi-x-circle"></i> Suspend Account
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif
  
  <!-- View Suspend Modal (Read-only for Admins, Only for suspended accounts) -->
  @if($isSuspended && $isAdmin && !$canSuspend)
  <div class="modal fade" id="viewSuspendModal{{ $userId }}" tabindex="-1" aria-labelledby="viewSuspendModalLabel{{ $userId }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="viewSuspendModalLabel{{ $userId }}">
            <i class="bi bi-x-circle"></i> Suspend Student Account
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Student Details -->
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-person-circle"></i> Student Information</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Student ID:</strong> {{ $studentId }}</p>
                  <p><strong>Full Name:</strong> {{ $fullName ?: '-' }}</p>
                  <p><strong>Email:</strong> {{ $email }}</p>
                  <p><strong>Contact Number:</strong> {{ $contactNumber ?: '-' }}</p>
                </div>
                <div class="col-md-6">
                  <p><strong>Department:</strong> {{ $department ?: '-' }}</p>
                  <p><strong>Course:</strong> {{ $course ?: '-' }}</p>
                  <p><strong>Year Level:</strong> {{ $yearLevel ?: '-' }}</p>
                  <p><strong>Status:</strong> <span class="badge badge-danger">Suspended</span></p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Suspension Reason (Read-only for Admins) -->
          <div class="card">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-file-text"></i> Reason for Suspension</h6>
            </div>
            <div class="card-body">
              <p class="mb-0">{{ $student->suspension_reason ?? 'No reason provided.' }}</p>
            </div>
          </div>
          
          <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i> <strong>Note:</strong> As an admin, you can view suspension details but cannot reactivate accounts. Only staff with "Prefect of Discipline" designation has the authority to reactivate student accounts.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  @endif
  
@endforeach

@endsection
