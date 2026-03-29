@extends('layouts.app')

@section('title', $designation->name . ' Dashboard')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.staff.partials.designation-sidebar', ['designation' => $designation])

    <main id="staffMain" class="col-md-10">
      @php
        // Normalize designation name to handle both spellings (standardize on American spelling)
        $normalizedDesignationName = trim($designation->name);
        if (strcasecmp($normalizedDesignationName, 'Guidance Counsellor') === 0) {
          $normalizedDesignationName = 'Guidance Counselor';
        }
        $isGuidanceCounselor = strcasecmp($normalizedDesignationName, 'Guidance Counselor') === 0;
        $isNurse = strcasecmp($normalizedDesignationName, 'Nurse') === 0;
      @endphp
      
      @if($isNurse && isset($dashboardOverview))
      <!-- Dashboard Overview Section -->
      <div class="mb-4">
        <h4 class="mb-3" style="color: midnightblue; font-weight: 600;">Dashboard Overview</h4>
        <div class="row">
          <!-- Appointments On Queue Card -->
          <div class="col-md-4 mb-3">
            <div class="card h-100" style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
              <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                  <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 500;">Appointments On Queue</h6>
                  <h3 class="mb-0" style="color: midnightblue; font-weight: 700;">{{ $dashboardOverview['appointmentsOnQueue'] }}</h3>
                  @if($dashboardOverview['appointmentsOnQueue'] == 0)
                    <p class="text-muted mb-0 mt-2" style="font-size: 0.75rem;">Just keep going.</p>
                  @else
                    <p class="text-muted mb-0 mt-2" style="font-size: 0.75rem;">Pending appointments</p>
                  @endif
                </div>
                <div class="ms-3">
                  <div style="width: 60px; height: 60px; background-color: #20B2AA; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="mai-calendar" style="color: white; font-size: 1.5rem;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Recent Cases Card -->
          <div class="col-md-4 mb-3">
            <div class="card h-100" style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
              <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                  <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 500;">Recent Cases</h6>
                  <h3 class="mb-0" style="color: midnightblue; font-weight: 700;">{{ $dashboardOverview['recentCases']->count() }}</h3>
                  <p class="text-muted mb-0 mt-2" style="font-size: 0.75rem;">Last 10 cases</p>
                </div>
                <div class="ms-3">
                  <div style="width: 60px; height: 60px; background-color: #FF6347; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="mai-document" style="color: white; font-size: 1.5rem;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Accounts Card -->
          <div class="col-md-4 mb-3">
            <div class="card h-100" style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
              <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                  <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 500;">Accounts</h6>
                  <div class="mb-1">
                    <span style="color: midnightblue; font-weight: 600;">Student: {{ $dashboardOverview['studentCount'] }}</span>
                  </div>
                  <div>
                    <span style="color: midnightblue; font-weight: 600;">Faculty: {{ $dashboardOverview['facultyCount'] }}</span>
                  </div>
                  <p class="text-muted mb-0 mt-2" style="font-size: 0.75rem;">Total served</p>
                </div>
                <div class="ms-3">
                  <div style="width: 60px; height: 60px; background-color: #9370DB; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="mai-people" style="color: white; font-size: 1.5rem;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0"><span class="px-2 py-1" style="background-color: midnightblue; color: white; border-radius: 4px;">{{ $designation->name }} — Staff</span></h2>
        @if (isset($isAdmin) && $isAdmin)
          @if (strcasecmp($designation->name, 'Admission Services Officer') === 0)
            <div class="d-flex gap-2">
              <a href="{{ route('admin.staff.dashboard') }}" class="btn btn-secondary">All Staff Dashboards</a>
              <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary">Staff Dashboard</a>
            </div>
          @else
            <a href="{{ route('admin.staff.dashboard') }}" class="btn btn-secondary">Back</a>
          @endif
        @endif
      </div>
      
      @php
        $canCreateStaffEventMain = (isset($isStaff) && $isStaff) || (isset($isAdmin) && $isAdmin);
      @endphp

      @if($canCreateStaffEventMain && !$isGuidanceCounselor && !$isNurse)
      <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="{{ route('admin.staff.dashboard.StudentOrgModerator.create-event') }}" class="btn btn-primary btn-lg">
          <i class="bi bi-calendar-plus"></i> Create Event
        </a>
      </div>
      @endif
      
      @if($isGuidanceCounselor)
      <div class="mb-4">
        <a href="{{ route('admin.staff.dashboard.GuidanceCounselor.create-event') }}" class="btn btn-outline-primary btn-lg">
          <i class="bi bi-calendar-plus"></i> Counseling Events
        </a>
      </div>
      @endif

      @if (strcasecmp($designation->name, 'Admission Services Officer') === 0)
      <div class="card mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>All Students</h5>
            <a href="{{ route('admin.staff.dashboard.AdmissionServicesOfficer.student-management') }}" class="btn btn-primary">Add Student</a>
          </div>
          
          <!-- Search and Filter Section -->
          <form method="GET" action="{{ request()->url() }}" class="mb-3">
            <div class="row">
              <div class="col-md-4 mb-2">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search by name, email, ID, or contact..." 
                       value="{{ request('search') }}">
              </div>
              <div class="col-md-3 mb-2">
                <select name="department_id" class="form-control">
                  <option value="">All Departments</option>
                  @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                      {{ $dept->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2 mb-2">
                <select name="year_level" class="form-control">
                  <option value="">All Year Levels</option>
                  <option value="1" {{ request('year_level') == '1' ? 'selected' : '' }}>1st Year</option>
                  <option value="2" {{ request('year_level') == '2' ? 'selected' : '' }}>2nd Year</option>
                  <option value="3" {{ request('year_level') == '3' ? 'selected' : '' }}>3rd Year</option>
                  <option value="4" {{ request('year_level') == '4' ? 'selected' : '' }}>4th Year</option>
                  <option value="5" {{ request('year_level') == '5' ? 'selected' : '' }}>5th Year</option>
                </select>
              </div>
              <div class="col-md-3 mb-2">
                <button type="submit" class="btn btn-primary w-100">Search & Filter</button>
              </div>
            </div>
            @if(request()->has('search') || request()->has('department_id') || request()->has('year_level'))
              <div class="mt-2">
                <a href="{{ request()->url() }}" class="btn btn-sm btn-secondary">Clear Filters</a>
                <small class="text-muted ml-2">
                  Showing {{ $students->count() }} result(s)
                </small>
              </div>
            @endif
          </form>
          
          <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
              <thead style="background-color:midnightblue; color:white">
                <tr>
                  <th>Student ID</th>
                  <th>First Name</th>
                  <th>Middle Name</th>
                  <th>Last Name</th>
                  <th>Contact Number</th>
                  <th>Email</th>
                  <th>Department</th>
                  <th>Course</th>
                  <th>Organization</th>
                  <th>Year Level</th>
                  <th>Sex</th>
                  <th>Birth Date</th>
                  <th>Type 1</th>
                  <th>Type 2</th>
                  <th>Scholarship</th>
                  <th>Points</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @if(!request()->has('search') && !request()->has('department_id') && !request()->has('year_level'))
                  <tr>
                    <td colspan="18" class="text-center py-4">
                      <p class="text-muted mb-0">
                        Please enter a search term or select a department/year level filter to find students.
                      </p>
                    </td>
                  </tr>
                @elseif($students->isEmpty())
                  <tr>
                    <td colspan="18" class="text-center py-4">
                      <p class="text-muted mb-0">
                        No students found matching your search criteria.
                      </p>
                    </td>
                  </tr>
                @else
                  @foreach($students as $student)
                  <tr>
                    <td>{{ $student->user_id_display ?? $student->user->user_id ?? $student->user_id ?? $student->id }}</td>
                    <td>{{ $student->first_name ?? $student->user->first_name ?? '-' }}</td>
                    <td>{{ $student->middle_name ?? $student->user->middle_name ?? '' }}</td>
                    <td>{{ $student->last_name ?? $student->user->last_name ?? '-' }}</td>
                    <td>{{ $student->contact_number ?? $student->user->contact_number ?? '' }}</td>
                    <td>{{ $student->email ?? $student->user->email ?? '-' }}</td>
                    <td>{{ optional($student->department)->name ?? (optional($student->user->department)->name ?? '-') }}</td>
                    <td>{{ optional($student->course)->name ?? (optional($student->user->course)->name ?? '-') }}</td>
                    <td>{{ optional($student->organization)->name ?? (optional($student->user->organization)->name ?? '-') }}</td>
                    <td>{{ $student->year_level ?? $student->user->year_level ?? '-' }}</td>
                    <td>{{ ucfirst($student->gender ?? $student->user->gender ?? '-') }}</td>
                    <td>{{ $student->birth_date ?? $student->user->birth_date ?? '-' }}</td>
                    <td>{{ ucfirst($student->student_type1 ?? $student->user->student_type1 ?? '-') }}</td>
                    <td>{{ ucfirst($student->student_type2 ?? $student->user->student_type2 ?? '-') }}</td>
                    <td>
                        @if($student->scholarship)
                            {{ $student->scholarship->name }}
                        @elseif($student->user && $student->user->scholarship)
                            {{ $student->user->scholarship->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @php
                            $userId = $student->user_id ?? $student->id ?? ($student->user->id ?? null);
                            $totalPoints = $userId ? \App\Models\StudentPoint::where('user_id', $userId)->sum('points') : 0;
                        @endphp
                        <span class="badge bg-success">{{ $totalPoints }}</span>
                    </td>
                    <td>{{ $student->status ?? $student->user->status ?? '-' }}</td>
                    <td>
                      @php
                        // Determine if this is a Student model or User model
                        $isStudentModel = isset($student->isStudentModel) && $student->isStudentModel;
                        $isUserModel = isset($student->isUserModel) && $student->isUserModel;
                        // For Student model, use its id; for User model, check if it has a student record
                        $studentId = $isStudentModel ? $student->id : ($isUserModel && $student->student ? $student->student->id : null);
                      @endphp
                      @if($studentId)
                        <div class="d-flex gap-2 align-items-center">
                          <a href="{{ route('admin.staff.dashboard.AdmissionServicesOfficer.student.edit', $studentId) }}" class="btn btn-sm btn-warning">Update</a>
                          <form action="{{ route('admin.staff.dashboard.AdmissionServicesOfficer.student.destroy', $studentId) }}" method="POST" style="display:inline-block; margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</button>
                          </form>
                        </div>
                      @elseif($isUserModel)
                        <span class="badge badge-info">User Only</span>
                        <small class="text-muted d-block">No Student Record</small>
                      @else
                        <span class="badge badge-secondary">No ID</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Add Student Modal -->
      <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="{{ route('admin.staff.dashboard.AdmissionServicesOfficer.student-management.store') }}">
              @csrf
              <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Add Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-2">
                  <label for="student_id" class="form-label">Student ID</label>
                  <input type="text" class="form-control" name="student_id" required>
                </div>
                <div class="mb-2">
                  <label for="first_name" class="form-label">First Name</label>
                  <input type="text" class="form-control" name="first_name" required>
                </div>
                <div class="mb-2">
                  <label for="middle_name" class="form-label">Middle Initial</label>
                  <input type="text" class="form-control" name="middle_name" maxlength="1" pattern="[A-Za-z]" title="Enter only one alphabet character">
                </div>
                <div class="mb-2">
                  <label for="last_name" class="form-label">Last Name</label>
                  <input type="text" class="form-control" name="last_name" required>
                </div>
                <div class="mb-2">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-2">
                  <label for="contact_number" class="form-label">Contact Number</label>
                  <input type="text" class="form-control" name="contact_number">
                </div>
                <div class="mb-2">
                  <label for="department" class="form-label">Department</label>
                  <input type="text" class="form-control" name="department" required>
                </div>
                <div class="mb-2">
                  <label for="course" class="form-label">Course</label>
                  <input type="text" class="form-control" name="course" required>
                </div>
                <div class="mb-2">
                  <label for="year_level" class="form-label">Year Level</label>
                  <input type="text" class="form-control" name="year_level">
                </div>
                <div class="mb-2">
                  <label for="gender" class="form-label">Sex</label>
                  <input type="text" class="form-control" name="gender">
                </div>
                <div class="mb-2">
                  <label for="birth_date" class="form-label">Birth Date</label>
                  <input type="date" class="form-control" name="birth_date">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Student</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      @endif

      @if (strcasecmp($designation->name, 'Prefect of Discipline') === 0)
      <div class="card mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Students List</h5>
          </div>
          
          <!-- Search and Filter Section -->
          <form method="GET" action="{{ request()->url() }}" class="mb-3">
            <div class="row">
              <div class="col-md-4 mb-2">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search by name, email, ID, or contact..." 
                       value="{{ request('search') }}">
              </div>
              <div class="col-md-3 mb-2">
                <select name="department_id" class="form-control">
                  <option value="">All Departments</option>
                  @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                      {{ $dept->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2 mb-2">
                <select name="year_level" class="form-control">
                  <option value="">All Year Levels</option>
                  <option value="1" {{ request('year_level') == '1' ? 'selected' : '' }}>1st Year</option>
                  <option value="2" {{ request('year_level') == '2' ? 'selected' : '' }}>2nd Year</option>
                  <option value="3" {{ request('year_level') == '3' ? 'selected' : '' }}>3rd Year</option>
                  <option value="4" {{ request('year_level') == '4' ? 'selected' : '' }}>4th Year</option>
                  <option value="5" {{ request('year_level') == '5' ? 'selected' : '' }}>5th Year</option>
                </select>
              </div>
              <div class="col-md-3 mb-2">
                <button type="submit" class="btn btn-primary w-100">Search & Filter</button>
              </div>
            </div>
            @if(request()->has('search') || request()->has('department_id') || request()->has('year_level'))
              <div class="mt-2">
                <a href="{{ request()->url() }}" class="btn btn-sm btn-secondary">Clear Filters</a>
                <small class="text-muted ml-2">
                  Showing {{ $students->total() }} result(s)
                </small>
              </div>
            @else
              <div class="mt-2">
                <small class="text-muted">
                  Total: {{ $students->total() }} student(s)
                </small>
              </div>
            @endif
          </form>
          
          <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
              <thead style="background-color:midnightblue; color:white">
                <tr>
                  <th>Student ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Course</th>
                  <th>Year Level</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @if($students->isEmpty())
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <p class="text-muted mb-0">
                        No students found.
                      </p>
                    </td>
                  </tr>
                @else
                  @foreach($students as $student)
                  <tr>
                    <td>{{ $student->user_id_display ?? $student->user->user_id ?? $student->user_id ?? $student->id }}</td>
                    <td>
                      @php
                        $firstName = $student->first_name ?? $student->user->first_name ?? '';
                        $middleName = $student->middle_name ?? $student->user->middle_name ?? '';
                        $lastName = $student->last_name ?? $student->user->last_name ?? '';
                        $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
                      @endphp
                      {{ $fullName ?: '-' }}
                    </td>
                    <td>{{ $student->email ?? $student->user->email ?? '-' }}</td>
                    <td>{{ optional($student->course)->name ?? (optional($student->user->course)->name ?? '-') }}</td>
                    <td>{{ $student->year_level ?? $student->user->year_level ?? '-' }}</td>
                    <td>{{ $student->status ?? $student->user->status ?? '-' }}</td>
                    <td>
                      @php
                        // Get the user record to check suspension status
                        $userRecord = $student->user ?? null;
                        $userId = $userRecord ? $userRecord->id : ($student->user_id ?? null);
                        $isSuspended = $userRecord ? ($userRecord->suspended ?? false) : false;
                      @endphp
                      @if($userId)
                        @if($isSuspended)
                          <button type="button" 
                                  class="btn btn-sm btn-info" 
                                  data-toggle="modal" 
                                  data-target="#suspensionDetailsModal{{ $userId }}"
                                  title="View Suspension Details">
                            <i class="bi bi-info-circle"></i> View Details
                          </button>
                          <form action="{{ route('admin.students.reactivate', $userId) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reactivate this student account?');">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-sm btn-success" title="Reactivate Student Account">
                              <i class="bi bi-check-circle"></i> Reactivate
                            </button>
                          </form>
                        @else
                          <button type="button" 
                                  class="btn btn-sm btn-danger" 
                                  data-toggle="modal" 
                                  data-target="#suspendModal{{ $userId }}"
                                  title="Suspend Student Account">
                            <i class="bi bi-x-circle"></i> Suspend
                          </button>
                        @endif
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                @endif
              </tbody>
            </table>
          </div>
          
          <!-- Pagination with Arrow Buttons -->
          @if($students->hasPages())
            <div class="d-flex justify-content-center align-items-center mt-4 gap-3">
              @if($students->onFirstPage())
                <button class="btn btn-outline-secondary" disabled>
                  <i class="bi bi-chevron-left"></i>
                </button>
              @else
                <a href="{{ $students->appends(request()->except('page'))->previousPageUrl() }}" class="btn btn-outline-primary">
                  <i class="bi bi-chevron-left"></i>
                </a>
              @endif
              
              <span class="text-muted">
                Page {{ $students->currentPage() }} of {{ $students->lastPage() }} 
                (Showing {{ $students->firstItem() }}-{{ $students->lastItem() }} of {{ $students->total() }} results)
              </span>
              
              @if($students->hasMorePages())
                <a href="{{ $students->appends(request()->except('page'))->nextPageUrl() }}" class="btn btn-outline-primary">
                  <i class="bi bi-chevron-right"></i>
                </a>
              @else
                <button class="btn btn-outline-secondary" disabled>
                  <i class="bi bi-chevron-right"></i>
                </button>
              @endif
            </div>
          @endif
        </div>
      </div>
      @endif

      <!-- Suspension Modals -->
      @if (strcasecmp($designation->name, 'Prefect of Discipline') === 0 && !$students->isEmpty())
        @foreach($students as $student)
          @php
            $userRecord = $student->user ?? null;
            $userId = $userRecord ? $userRecord->id : ($student->user_id ?? null);
            $isSuspended = $userRecord ? ($userRecord->suspended ?? false) : false;
            if (!$userId) continue;
            
            $firstName = $student->first_name ?? $student->user->first_name ?? '';
            $middleName = $student->middle_name ?? $student->user->middle_name ?? '';
            $lastName = $student->last_name ?? $student->user->last_name ?? '';
            $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
            $studentId = $student->user_id_display ?? $student->user->user_id ?? $student->user_id ?? $student->id;
            $email = $student->email ?? $student->user->email ?? '-';
            $course = optional($student->course)->name ?? (optional($student->user->course)->name ?? '-');
            $yearLevel = $student->year_level ?? $student->user->year_level ?? '-';
            $department = optional($student->department)->name ?? (optional($student->user->department)->name ?? '-');
            $contactNumber = $student->contact_number ?? $student->user->contact_number ?? '-';
          @endphp
          
          <!-- Suspend Modal -->
          @if(!$isSuspended)
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
                            <p><strong>Status:</strong> {{ $student->status ?? $student->user->status ?? '-' }}</p>
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
          
          <!-- Suspension Details Modal (for suspended accounts) -->
          @if($isSuspended)
          <div class="modal fade" id="suspensionDetailsModal{{ $userId }}" tabindex="-1" aria-labelledby="suspensionDetailsModalLabel{{ $userId }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                  <h5 class="modal-title" id="suspensionDetailsModalLabel{{ $userId }}">
                    <i class="bi bi-info-circle"></i> Suspension Details
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
                  
                  <!-- Suspension Reason -->
                  <div class="card">
                    <div class="card-header bg-light">
                      <h6 class="mb-0"><i class="bi bi-file-text"></i> Reason for Suspension</h6>
                    </div>
                    <div class="card-body">
                      <p class="mb-0">{{ $userRecord->suspension_reason ?? 'No reason provided.' }}</p>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <form action="{{ route('admin.students.reactivate', $userId) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reactivate this student account?');">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success">
                      <i class="bi bi-check-circle"></i> Reactivate Account
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
          @endif
        @endforeach
      @endif

      @php
        // Ensure $isGuidanceCounselor is defined for use in Guidance Services section
        // Normalize designation name to handle both spellings (standardize on American spelling)
        // This handles backward compatibility with existing data that may use British spelling
        if (!isset($isGuidanceCounselor)) {
          $normalizedDesignationName = trim($designation->name);
          if (strcasecmp($normalizedDesignationName, 'Guidance Counsellor') === 0) {
            $normalizedDesignationName = 'Guidance Counselor';
          }
          $isGuidanceCounselor = strcasecmp($normalizedDesignationName, 'Guidance Counselor') === 0;
        }
      @endphp
      @if($isGuidanceCounselor)
      <div class="card mb-4">
        <div class="card-header" style="background-color: midnightblue; color: white;">
          <h5 class="mb-0"><i class="bi bi-list-ul"></i> Guidance Services</h5>
        </div>
        <div class="card-body">
          @php
            // Define service configurations
            $services = [
              'Initial Interview' => ['color' => 'primary', 'icon' => 'person-check'],
              'Information Services' => ['color' => 'info', 'icon' => 'info-circle'],
              'Counseling Services' => ['color' => 'success', 'icon' => 'chat-dots'],
              'External Referral' => ['color' => 'warning', 'icon' => 'arrow-up-right-circle'],
              'Internal Referral' => ['color' => 'secondary', 'icon' => 'arrow-right-circle'],
              'Exit Interview' => ['color' => 'danger', 'icon' => 'person-x'],
            ];
            
            // Get appointments by reason (already grouped in controller)
            $appointmentsByReason = $appointmentsByReason ?? collect([]);
            $returnPath = '/admin/staff/dashboard/' . $designation->name;
          @endphp
          <div class="row">
            @foreach($services as $serviceName => $serviceConfig)
              @php
                $serviceAppointments = $appointmentsByReason->get($serviceName, collect([]));
                $appointmentCount = $serviceAppointments->count();
              @endphp
              <div class="col-md-6 mb-3">
                <div class="card border-{{ $serviceConfig['color'] }}">
                  <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <a href="{{ route('admin.staff.dashboard.guidance-counselor.service', ['designation' => $designation->name, 'service' => $serviceName]) }}" 
                         class="btn btn-outline-{{ $serviceConfig['color'] }} btn-lg w-100 d-flex align-items-center justify-content-center" 
                         style="min-height: 80px; text-decoration: none;">
                        <div class="text-center">
                          <i class="bi bi-{{ $serviceConfig['icon'] }} fs-3 d-block mb-2"></i>
                          <strong>{{ $serviceName }}</strong>
                          @if($appointmentCount > 0)
                            <span class="badge bg-{{ $serviceConfig['color'] }} ml-2">{{ $appointmentCount }}</span>
                          @endif
                        </div>
                      </a>
                    </div>
                    @if($appointmentCount > 0)
                      <div class="mt-3">
                        <p class="text-muted text-center mb-2">
                          <small>{{ $appointmentCount }} appointment{{ $appointmentCount !== 1 ? 's' : '' }} available</small>
                        </p>
                        <div class="text-center">
                          <a href="{{ route('admin.staff.dashboard.guidance-counselor.service', ['designation' => $designation->name, 'service' => $serviceName]) }}" 
                             class="btn btn-sm btn-{{ $serviceConfig['color'] }}">
                            <i class="bi bi-arrow-right-circle"></i> View Appointments
                          </a>
                        </div>
                      </div>
                    @else
                      <div class="mt-3">
                        <p class="text-muted text-center mb-0">
                          <small>No appointments for this service.</small>
                        </p>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
      
      @endif

      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr align="center" style="background-color: midnightblue; color: white;">
              </thead>
              <tbody>
                <!-- Table content goes here -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<script>
  const worksheetData = @json(session('importedWorksheetData') ?? []);
  function filterWorksheet() {
    const form = document.getElementById('searchForm');
    const value = form.search_value.value.trim().toLowerCase();
    const filter = form.search_filter.value;
    let filtered = worksheetData.filter(row => {
      if (!value) return true;
      if (filter === 'Level') {
        // Accept both '1st yr', '2nd yr', etc. and numeric values
        const level = (row['Level']||'').toLowerCase();
        return level.includes(value) || level.replace(/[^0-9]/g, '') === value.replace(/[^0-9]/g, '');
      }
      return ((row[filter]||'').toLowerCase().includes(value));
    });
    let html = '';
    if (filtered.length === 0) {
      html = '<div class="alert alert-warning">No matching data found.</div>';
    } else {
      html = `<table class=\"table table-bordered\"><thead><tr>
        <th>Student No</th>
        <th>Full Name</th>
        <th>Program</th>
        <th>Sex</th>
        <th>Level</th>
        <th>Validation Date</th>
        <th>Email</th>
        <th>Contact</th>
      </tr></thead><tbody>`;
      filtered.forEach((row, idx) => {
        html += `<tr>
          <td contenteditable='true' oninput=\"updateCell(${idx}, 'Student No', this.innerText)\">${row['Student No']||''}</td>
          <td contenteditable='true' oninput=\"updateCell(${idx}, 'Full Name', this.innerText)\">${row['Full Name']||''}</td>
          <td contenteditable='true' oninput=\"updateCell(${idx}, 'Program', this.innerText)\">${row['Program']||''}</td>
          <td contenteditable='true' oninput=\"updateCell(${idx}, 'Sex', this.innerText)\">${row['Sex']||''}</td>
          <td contenteditable='true' oninput=\"updateCell(${idx}, 'Level', this.innerText)\">${row['Level']||''}</td>
          <td contenteditable='true' oninput=\"updateCell(${idx}, 'Validation Date', this.innerText)\">${row['Validation Date']||''}</td>
          <td contenteditable='true' oninput=\"updateCell(${idx}, 'Email', this.innerText)\">${row['Email']||''}</td>
          <td contenteditable='true' oninput=\"updateCell(${idx}, 'Contact', this.innerText)\">${row['Contact']||''}</td>
        </tr>`;
      });
      html += `</tbody></table><div class='mt-2 text-end'><strong>Total matches found: ${filtered.length}</strong></div>`;
      if (filtered.length > 0) {
        html += `<div class='mt-3 text-end'><button type='button' class='btn btn-primary' onclick='updateWorksheet()'>Update</button></div>`;
        html += `<div id='updateSuccessMsg' class='mt-2'></div>`;
      }
  // Update worksheet: catch all changes and save to backend
  function updateWorksheet() {
    // Collect current table data
    const table = document.querySelector('#searchResults table');
    if (!table) return;
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
    const updatedRows = Array.from(table.querySelectorAll('tbody tr')).map(tr => {
      const cells = Array.from(tr.querySelectorAll('td'));
      const rowObj = {};
      cells.forEach((td, idx) => {
        rowObj[headers[idx]] = td.innerText.trim();
      });
      return rowObj;
    });
    fetch('/admin/staff/dashboard/save-updated-worksheet', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ rows: updatedRows })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById('updateSuccessMsg').innerHTML = `<div class='alert alert-success'>All changes saved! File: ${data.filename}</div>`;
        worksheetData.length = 0;
        updatedRows.forEach(r => worksheetData.push(r));
        filterWorksheet();
      } else {
        document.getElementById('updateSuccessMsg').innerHTML = `<div class='alert alert-danger'>Failed to save changes.</div>`;
      }
    })
    .catch(() => alert('Failed to save changes.'));
  }
  // Update cell value in worksheetData
  function updateCell(idx, key, value) {
    worksheetData[idx][key] = value;
  }
    }
    document.getElementById('searchResults').innerHTML = html;
  }

  function applyChanges(idx) {
    const form = document.getElementById('editForm');
    worksheetData[idx]['Student No'] = form[`student_no_${idx}`].value;
    worksheetData[idx]['Full Name'] = form[`full_name_${idx}`].value;
    worksheetData[idx]['Program'] = form[`program_${idx}`].value;
    worksheetData[idx]['Courses'] = form[`courses_${idx}`].value;
    alert('Changes applied to this entry. (Note: This only updates the view, not the file. Backend update required for persistence.)');
  }

@endsection