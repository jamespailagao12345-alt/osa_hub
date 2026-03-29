@extends('layouts.app')

@section('title', 'Student Details')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="admin-back-btn-wrap">
        <a href="{{ route('admin.staff.dashboard.AdmissionServicesOfficer.student-management') }}" class="btn btn-secondary">&larr; Back to Student Management</a>
      </div>
    </div>
  </div>
  
  <div class="row">
    <div class="col-md-3 col-lg-2">
      <div class="list-group mb-3">
        <div class="list-group-item active" style="background-color: midnightblue; border-color: midnightblue;">Quick Actions</div>
        <a href="{{ route('admin.appointments.index') }}" class="list-group-item list-group-item-action">Assigned Appointments</a>
        @php
          $isStaff = (auth()->user()->role ?? 0) == 2;
          $isAdmin = (auth()->user()->role ?? 0) == 4;
          $designationName = 'Admission Services Officer';
        @endphp
        @if($isStaff)
          <a href="{{ route('staff.organizations.index') }}" class="list-group-item list-group-item-action">My Organization</a>
        @endif
        @if($isAdmin)
          <a href="{{ route('admin.events.index') }}#create" class="list-group-item list-group-item-action">Create Event</a>
        @endif
        <a href="{{ route('admin.staff.dashboard.AdmissionServicesOfficer.student-management') }}" class="list-group-item list-group-item-action">Student Management</a>
        <a href="{{ route('admin.qrscan') }}" class="list-group-item list-group-item-action">
          <i class="bi bi-qr-code-scan"></i> Scan QR Code
        </a>
      </div>
    </div>
    
    <main class="col-md-9 col-lg-10">
      <h2 class="mb-3">Student Details</h2>
      
      <div class="card mb-4">
        <div class="card-header" style="background-color: midnightblue; color: white;">
          <h5 class="mb-0">Personal Information</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-3"><strong>Student ID:</strong></div>
            <div class="col-md-9">{{ $student->user->user_id ?? 'N/A' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Name:</strong></div>
            <div class="col-md-9">
              {{ $student->last_name }}, {{ $student->first_name }} 
              {{ $student->middle_name ? $student->middle_name : '' }}
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Email:</strong></div>
            <div class="col-md-9">{{ $student->email ?? $student->user->email ?? 'N/A' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Contact Number:</strong></div>
            <div class="col-md-9">{{ $student->contact_number ?? $student->user->contact_number ?? 'N/A' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Sex:</strong></div>
            <div class="col-md-9">{{ ucfirst($student->gender ?? $student->user->gender ?? 'N/A') }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Birth Date:</strong></div>
            <div class="col-md-9">
              @php
                $birthDate = $student->birth_date ?? $student->user->birth_date ?? null;
              @endphp
              {{ $birthDate ? \Carbon\Carbon::parse($birthDate)->format('F d, Y') : 'N/A' }}
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Age:</strong></div>
            <div class="col-md-9">{{ $student->age ?? $student->user->age ?? 'N/A' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Civil Status:</strong></div>
            <div class="col-md-9">{{ ucfirst($student->civil_status ?? $student->user->civil_status ?? 'N/A') }}</div>
          </div>
          @if($student->maiden_name || ($student->user && $student->user->maiden_name))
          <div class="row mb-3">
            <div class="col-md-3"><strong>Maiden Name:</strong></div>
            <div class="col-md-9">{{ $student->maiden_name ?? $student->user->maiden_name ?? 'N/A' }}</div>
          </div>
          @endif
          <div class="row mb-3">
            <div class="col-md-3"><strong>Place of Birth:</strong></div>
            <div class="col-md-9">{{ optional($student->user->personalInformation)->place_of_birth ?? $student->place_of_birth ?? $student->user->place_of_birth ?? 'N/A' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Nationality:</strong></div>
            <div class="col-md-9">{{ optional($student->user->personalInformation->nationality)->name ?? optional($student->user->personalInformation)->nationality ?? 'N/A' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Religion:</strong></div>
            <div class="col-md-9">{{ optional($student->user->personalInformation)->religion ?? 'N/A' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Complete Home Address:</strong></div>
            <div class="col-md-9">{{ optional($student->user->homeAddress)->complete_address ?? $student->complete_home_address ?? $student->user->complete_home_address ?? 'N/A' }}</div>
          </div>
          @if($student->personal_data_sheet_image || ($student->user && $student->user->image))
          <div class="row mb-3">
            <div class="col-md-3"><strong>Personal Data Sheet Image:</strong></div>
            <div class="col-md-9">
              @php
                $imagePath = $student->personal_data_sheet_image ?? ($student->user ? $student->user->image : null);
              @endphp
              @if($imagePath)
                <img src="{{ Storage::url($imagePath) }}" alt="Personal Data Sheet" style="max-width: 300px; max-height: 300px; border: 1px solid #ddd; border-radius: 5px;">
              @else
                N/A
              @endif
            </div>
          </div>
          @endif
        </div>
      </div>

      @php
        $pwdInfo = $student->user->pwdInformation ?? null;
        $indigenousInfo = $student->user->indigenousMember ?? null;
        $govAffiliation = $student->user->governmentAffiliation ?? null;
        $fraternityInfo = $student->user->fraternityMember ?? null;
      @endphp

      @if($pwdInfo && $pwdInfo->is_pwd)
      <div class="card mb-4">
        <div class="card-header" style="background-color: #17a2b8; color: white;">
          <h5 class="mb-0">PWD Information</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-3"><strong>PWD Status:</strong></div>
            <div class="col-md-9"><span class="badge bg-info">Yes</span></div>
          </div>
          @if($pwdInfo->pwd_id_number)
          <div class="row mb-3">
            <div class="col-md-3"><strong>PWD ID Number:</strong></div>
            <div class="col-md-9">{{ $pwdInfo->pwd_id_number }}</div>
          </div>
          @endif
          @if($pwdInfo->disability_type)
          <div class="row mb-3">
            <div class="col-md-3"><strong>Disability Type:</strong></div>
            <div class="col-md-9">{{ $pwdInfo->disability_type }}</div>
          </div>
          @endif
          @if($pwdInfo->pwd_id_image)
          <div class="row mb-3">
            <div class="col-md-3"><strong>PWD ID Image:</strong></div>
            <div class="col-md-9">
              <img src="{{ Storage::url($pwdInfo->pwd_id_image) }}" alt="PWD ID" style="max-width: 300px; max-height: 300px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
          </div>
          @endif
        </div>
      </div>
      @endif

      @if($indigenousInfo && $indigenousInfo->is_indigenous_group_member)
      <div class="card mb-4">
        <div class="card-header" style="background-color: #28a745; color: white;">
          <h5 class="mb-0">Indigenous Member Information</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-3"><strong>Indigenous Group Member:</strong></div>
            <div class="col-md-9"><span class="badge bg-success">Yes</span></div>
          </div>
          @if($indigenousInfo->indigenous_group_specify)
          <div class="row mb-3">
            <div class="col-md-3"><strong>Indigenous Group:</strong></div>
            <div class="col-md-9">{{ $indigenousInfo->indigenous_group_specify }}</div>
          </div>
          @endif
          @if($indigenousInfo->tribal_affiliation)
          <div class="row mb-3">
            <div class="col-md-3"><strong>Tribal Affiliation:</strong></div>
            <div class="col-md-9">{{ $indigenousInfo->tribal_affiliation }}</div>
          </div>
          @endif
        </div>
      </div>
      @endif

      @if($govAffiliation && $govAffiliation->is_government_member == 'yes')
      <div class="card mb-4">
        <div class="card-header" style="background-color: #6c757d; color: white;">
          <h5 class="mb-0">Government Affiliation</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-3"><strong>Government Member:</strong></div>
            <div class="col-md-9"><span class="badge bg-secondary">Yes</span></div>
          </div>
          @if($govAffiliation->government_level)
          <div class="row mb-3">
            <div class="col-md-3"><strong>Government Level:</strong></div>
            <div class="col-md-9">{{ ucfirst(str_replace('_', ' ', $govAffiliation->government_level)) }}</div>
          </div>
          @endif
          @if($govAffiliation->government_role_position)
          <div class="row mb-3">
            <div class="col-md-3"><strong>Role/Position:</strong></div>
            <div class="col-md-9">{{ $govAffiliation->government_role_position }}</div>
          </div>
          @endif
          @if($govAffiliation->government_unit_name)
          <div class="row mb-3">
            <div class="col-md-3"><strong>Government Unit:</strong></div>
            <div class="col-md-9">{{ $govAffiliation->government_unit_name }}</div>
          </div>
          @endif
        </div>
      </div>
      @endif

      @if($fraternityInfo && $fraternityInfo->fraternity_sorority_name)
      <div class="card mb-4">
        <div class="card-header" style="background-color: #dc3545; color: white;">
          <h5 class="mb-0">Fraternity/Sorority Membership</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-3"><strong>Name:</strong></div>
            <div class="col-md-9">{{ $fraternityInfo->fraternity_sorority_name }}</div>
          </div>
          @if($fraternityInfo->fraternity_sorority_position)
          <div class="row mb-3">
            <div class="col-md-3"><strong>Position:</strong></div>
            <div class="col-md-9">{{ $fraternityInfo->fraternity_sorority_position }}</div>
          </div>
          @endif
          @if($fraternityInfo->type)
          <div class="row mb-3">
            <div class="col-md-3"><strong>Type:</strong></div>
            <div class="col-md-9">{{ ucfirst($fraternityInfo->type) }}</div>
          </div>
          @endif
        </div>
      </div>
      @endif
      
      <div class="card mb-4">
        <div class="card-header" style="background-color: #ffc107; color: white;">
          <h5 class="mb-0">Academic Information</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-3"><strong>School Year:</strong></div>
            <div class="col-md-9">{{ $student->school_year ?? $student->user->school_year ?? 'N/A' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Semester:</strong></div>
            <div class="col-md-9">{{ $student->semester ?? $student->user->semester ?? 'N/A' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Student Type:</strong></div>
            <div class="col-md-9">{{ ucfirst($student->student_type ?? $student->user->student_type ?? 'N/A') }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Department:</strong></div>
            <div class="col-md-9">{{ $student->department->name ?? ($student->user && $student->user->department ? $student->user->department->name : 'N/A') }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Course:</strong></div>
            <div class="col-md-9">{{ $student->course->name ?? ($student->user && $student->user->course ? $student->user->course->name : 'N/A') }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Year Level:</strong></div>
            <div class="col-md-9">{{ $student->year_level ?? $student->user->year_level ?? 'N/A' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Organization:</strong></div>
            <div class="col-md-9">{{ $student->organization->name ?? ($student->user && $student->user->organization ? $student->user->organization->name : 'N/A') }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Scholarship:</strong></div>
            <div class="col-md-9">{{ $student->scholarship->name ?? ($student->user && $student->user->scholarship ? $student->user->scholarship->name : 'N/A') }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Student Type 1:</strong></div>
            <div class="col-md-9">{{ ucfirst($student->student_type1 ?? $student->user->student_type1 ?? 'N/A') }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3"><strong>Student Type 2:</strong></div>
            <div class="col-md-9">{{ ucfirst($student->student_type2 ?? $student->user->student_type2 ?? 'N/A') }}</div>
          </div>
        </div>
      </div>
      
      @php
        $parentSpouseGuardian = $student->parent_spouse_guardian ?? $student->user->parent_spouse_guardian ?? null;
      @endphp
      @if($parentSpouseGuardian)
      <div class="card mb-4">
        <div class="card-header" style="background-color: #ffc107; color: white;">
          <h5 class="mb-0">Parent/Spouse/Guardian Information</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-3"><strong>Parent/Spouse/Guardian:</strong></div>
            <div class="col-md-9">{{ $parentSpouseGuardian }}</div>
          </div>
          @php
            $parentAddress = $student->parent_spouse_guardian_address ?? $student->user->parent_spouse_guardian_address ?? null;
          @endphp
          @if($parentAddress)
          <div class="row mb-3">
            <div class="col-md-3"><strong>Address:</strong></div>
            <div class="col-md-9">{{ $parentAddress }}</div>
          </div>
          @endif
        </div>
      </div>
      @endif
      
      @php
        $elementarySchool = $student->elementary_school ?? $student->user->elementary_school ?? null;
        $highSchool = $student->high_school ?? $student->user->high_school ?? null;
        $collegeName = $student->college_name ?? $student->user->college_name ?? null;
      @endphp
      @if($elementarySchool || $highSchool || $collegeName)
      <div class="card mb-4">
        <div class="card-header" style="background-color: #ffc107; color: white;">
          <h5 class="mb-0">Schools Attended</h5>
        </div>
        <div class="card-body">
          @if($elementarySchool)
          <div class="mb-3">
            <strong>Elementary:</strong> {{ $elementarySchool }}
            @php
              $elementaryAddress = $student->elementary_address ?? $student->user->elementary_address ?? null;
              $elementaryYear = $student->elementary_year_graduated ?? $student->user->elementary_year_graduated ?? null;
            @endphp
            @if($elementaryAddress) - {{ $elementaryAddress }} @endif
            @if($elementaryYear) ({{ $elementaryYear }}) @endif
          </div>
          @endif
          @if($highSchool)
          <div class="mb-3">
            <strong>High School:</strong> {{ $highSchool }}
            @php
              $highSchoolAddress = $student->high_school_address ?? $student->user->high_school_address ?? null;
              $highSchoolYear = $student->high_school_year_graduated ?? $student->user->high_school_year_graduated ?? null;
            @endphp
            @if($highSchoolAddress) - {{ $highSchoolAddress }} @endif
            @if($highSchoolYear) ({{ $highSchoolYear }}) @endif
          </div>
          @endif
          @if($collegeName)
          <div class="mb-3">
            <strong>College:</strong> {{ $collegeName }}
            @php
              $collegeAddress = $student->college_address ?? $student->user->college_address ?? null;
              $collegeCourse = $student->college_course ?? $student->user->college_course ?? null;
              $collegeYear = $student->college_year ?? $student->user->college_year ?? null;
            @endphp
            @if($collegeAddress) - {{ $collegeAddress }} @endif
            @if($collegeCourse) - {{ $collegeCourse }} @endif
            @if($collegeYear) ({{ $collegeYear }}) @endif
          </div>
          @endif
        </div>
      </div>
      @endif
      
      @php
        $form137 = $student->form_137_presented ?? $student->user->form_137_presented ?? false;
        $tor = $student->tor_presented ?? $student->user->tor_presented ?? false;
        $goodMoral = $student->good_moral_cert_presented ?? $student->user->good_moral_cert_presented ?? false;
        $birthCert = $student->birth_cert_presented ?? $student->user->birth_cert_presented ?? false;
        $marriageCert = $student->marriage_cert_presented ?? $student->user->marriage_cert_presented ?? false;
      @endphp
      @if($form137 || $tor || $goodMoral || $birthCert || $marriageCert)
      <div class="card mb-4">
        <div class="card-header" style="background-color: #ffc107; color: white;">
          <h5 class="mb-0">Entrance Credentials Presented</h5>
        </div>
        <div class="card-body">
          <div class="row">
            @if($form137)
            <div class="col-md-3 mb-2">
              <span class="badge bg-success">Form 137</span>
            </div>
            @endif
            @if($tor)
            <div class="col-md-3 mb-2">
              <span class="badge bg-success">TOR</span>
            </div>
            @endif
            @if($goodMoral)
            <div class="col-md-3 mb-2">
              <span class="badge bg-success">Good Moral Cert.</span>
            </div>
            @endif
            @if($birthCert)
            <div class="col-md-3 mb-2">
              <span class="badge bg-success">Birth Cert.</span>
            </div>
            @endif
            @if($marriageCert)
            <div class="col-md-3 mb-2">
              <span class="badge bg-success">Marriage Cert.</span>
            </div>
            @endif
          </div>
        </div>
      </div>
      @endif
      
    </main>
  </div>
</div>
@endsection

