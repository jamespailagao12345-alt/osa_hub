@extends('layouts.app')

@section('title', 'Student Management - Student Information Sheet')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('duplicate_message'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle"></i> Duplicate Student Detected!</strong>
        <p class="mb-2">{{ session('duplicate_message') }}</p>
        @if(session('duplicate_student_id'))
            <a href="{{ route('admin.staff.dashboard.AdmissionServicesOfficer.student.show', session('duplicate_student_id')) }}" class="btn btn-sm btn-primary mt-2">
                <i class="bi bi-eye"></i> View Student Details
            </a>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> Please fix the following errors:
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="admin-back-btn-wrap d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.staff.dashboard.designation', ['designation' => 'Admission Services Officer']) }}" class="btn btn-secondary">&larr; Back</a>
        @php
          $user = $student->user;
          $verificationEmailCount = $user->verification_email_count ?? 0;
        @endphp
        <div class="d-flex align-items-center gap-3">
          <div class="text-muted">
            <small>Verification emails sent: <strong>{{ $verificationEmailCount }}</strong></small>
          </div>
          <form action="{{ route('admin.staff.dashboard.AdmissionServicesOfficer.student.resend-verification', $student->id) }}" method="POST" style="display: inline-block;">
            @csrf
            <button type="submit" class="btn btn-info btn-sm" onclick="return confirm('Are you sure you want to resend the verification email to {{ $user->email }}?')">
              <i class="bi bi-envelope"></i> Resend Verification Email
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-3 col-lg-2">
      @php
        $currentUser = auth()->user();
        $isStaff = (auth()->user()->role ?? 0) == 2;
        $isAdmin = (auth()->user()->role ?? 0) == 4;
        $designationName = 'Admission Services Officer';
        
        // Get staff profile information
        $staff = null;
        $staffImage = null;
        $staffName = '';
        $staffDesignation = '';
        
        if ($currentUser) {
          // Try to get staff from Staff table by email
          $staff = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($currentUser->email))])->first();
          
          if ($staff) {
            $profileImage = $staff->image ?? $currentUser->image ?? null;
            $staffName = trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''));
            $staffDesignation = $staff->designation ?? '';
          } else {
            // Fallback to user data
            $profileImage = $currentUser->image ?? null;
            $staffName = trim(($currentUser->first_name ?? '') . ' ' . ($currentUser->last_name ?? ''));
            $staffDesignation = $currentUser->designation ?? optional($currentUser->staffProfile)->designation ?? '';
          }
          
          // Normalize image path and generate URL (similar to staff dashboard)
          if ($profileImage) {
            $imagePath = $profileImage;
            $imagePath = ltrim($imagePath, '/');
            // Normalize image path - try multiple possible locations
            $possiblePaths = [
              $imagePath, // Original path
              'staff-image/' . basename($imagePath),
              'profile_images/' . basename($imagePath),
            ];
            
            $foundPath = null;
            foreach ($possiblePaths as $path) {
              if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                $foundPath = $path;
                break;
              }
            }
            
            // Generate URL - use found path or original as fallback
            if ($foundPath) {
              $staffImage = \Illuminate\Support\Facades\Storage::disk('public')->url($foundPath);
            } else {
              // Fallback: generate URL from original path (might work if symlinked)
              $staffImage = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
            }
          } else {
            $staffImage = null;
          }
        }
      @endphp
      
      <!-- Staff Profile Card -->
      <div class="card mb-3" style="border: 1px solid #ddd;">
        <div class="card-body text-center p-3">
          @if($staffImage)
            <img src="{{ $staffImage }}" alt="{{ $staffName }}" class="img-fluid rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid midnightblue;">
          @else
            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-2" style="width: 100px; height: 100px; border: 3px solid midnightblue;">
              <span class="text-white" style="font-size: 2rem; font-weight: bold;">
                {{ strtoupper(substr($staffName, 0, 1)) }}{{ strtoupper(substr($staffName, strrpos($staffName, ' ') + 1, 1)) }}
              </span>
            </div>
          @endif
          <h6 class="mb-1" style="font-weight: bold; color: midnightblue;">{{ $staffName }}</h6>
          <p class="mb-0 text-muted" style="font-size: 0.85rem;">{{ $staffDesignation ?: 'Staff' }}</p>
        </div>
      </div>
      
      <div class="list-group mb-3">
        <div class="list-group-item active" style="background-color: midnightblue; border-color: midnightblue;">Quick Actions</div>
        <a href="{{ route('admin.appointments.index') }}" class="list-group-item list-group-item-action">Assigned Appointments</a>
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
      <!-- Department Selection Section -->
      <div class="card mb-3">
        <div class="card-header" style="background-color: midnightblue; color: white;">
          <h5 class="mb-0">Add Student by Department</h5>
        </div>
        <div class="card-body">
          <div class="row align-items-end">
            <div class="col-md-6">
              <label for="quick-department-select">Select Department:</label>
              <select id="quick-department-select" class="form-control">
                <option value="">Choose a department...</option>
                @foreach($departments as $dept)
                  <option value="{{ $dept->id }}" data-dept-name="{{ $dept->name }}">{{ $dept->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <button type="button" id="fill-department-btn" class="btn btn-primary" disabled>Auto-Fill Form</button>
              <button type="button" id="clear-department-btn" class="btn btn-secondary" style="display: none;">Clear Selection</button>
            </div>
          </div>
          <div id="department-info" class="mt-2" style="display: none;">
            <small class="text-muted">
              <strong>Selected:</strong> <span id="selected-dept-name"></span> | 
              <strong>Organization:</strong> <span id="selected-org-name"></span>
            </small>
          </div>
        </div>
      </div>

      <style>
        .personal-data-sheet {
          background: white;
          padding: 20px;
          border: 1px solid #ddd;
        }
        .form-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          margin-bottom: 20px;
          border-bottom: 2px solid #333;
          padding-bottom: 15px;
        }
        .university-info {
          flex: 1;
        }
        .university-name {
          font-weight: bold;
          font-size: 0.9rem;
          margin-top: 5px;
        }
        .campus-locations {
          font-size: 0.75rem;
          color: #666;
          margin-top: 3px;
        }
        .document-info {
          text-align: right;
          font-size: 0.85rem;
        }
        .doc-code-box {
          background: #f0f0f0;
          padding: 8px 12px;
          border: 1px solid #ccc;
          margin-bottom: 8px;
          display: inline-block;
        }
        .doc-meta-box {
          background: #f0f0f0;
          padding: 5px 10px;
          border: 1px solid #ccc;
          font-size: 0.75rem;
        }
        .form-title-section {
          text-align: center;
          margin: 20px 0;
        }
        .form-title {
          font-weight: bold;
          font-size: 1.2rem;
          margin-bottom: 10px;
        }
        .form-section {
          margin-bottom: 20px;
          padding: 10px;
          border-left: 3px solid midnightblue;
          background: #f9f9f9;
        }
        .section-label {
          font-weight: bold;
          font-size: 1rem;
          color: midnightblue;
          margin-bottom: 10px;
        }
        .section-row {
          display: flex;
          gap: 15px;
          margin-bottom: 10px;
          flex-wrap: wrap;
        }
        .form-field {
          display: flex;
          flex-direction: column;
          min-width: 150px;
          flex: 1;
        }
        .form-field label {
          font-size: 0.85rem;
          font-weight: 600;
          margin-bottom: 3px;
        }
        .form-field input,
        .form-field select,
        .form-field textarea {
          border: 1px solid #ccc;
          border-radius: 3px;
          padding: 6px 10px;
        }
        .form-field-full {
          width: 100%;
        }
        .checkbox-group {
          display: flex;
          gap: 20px;
          flex-wrap: wrap;
          margin-top: 10px;
        }
        .checkbox-item {
          display: flex;
          align-items: center;
          gap: 5px;
        }
        .inline-fields {
          display: flex;
          gap: 10px;
          align-items: flex-end;
        }
      </style>
      
      <div class="personal-data-sheet">
        <!-- Form Header -->
        <div class="form-header">
          <div class="university-info">
            <div class="university-name">UNIVERSITY OF SCIENCE AND TECHNOLOGY OF SOUTHERN PHILIPPINES</div>
            <div class="campus-locations">Alubijid | Balubal | Cagayan de Oro | Claveria | Jasaan | Oroquieta | Panaon | Villanueva</div>
          </div>
          <div class="document-info">
            <div class="doc-code-box">Document Code No. FM-USTP-RGTR-03</div>
            <div class="doc-meta-box">
              <div>Rev. No. 00</div>
              <div>Effective Date: 10.01.21</div>
              <div>Page No. 1 of 1</div>
            </div>
          </div>
        </div>

        @php
          $user = $student->user;
          $homeAddress = $user->addresses()->where('type', 'home')->first();
          $studentInfo = $user->studentInformation;
          $personalInfo = $user->personalInformation;
          $documentChecklist = $user->documentChecklist;
          // Load all educational backgrounds and access by level
          $educationalBackgrounds = $user->educationalBackgrounds;
          $elementaryBg = $educationalBackgrounds->where('level', 'elementary')->first();
          $juniorHighBg = $educationalBackgrounds->where('level', 'junior_high')->first();
          $seniorHighBg = $educationalBackgrounds->where('level', 'senior_high')->first();
          $collegeBg = $educationalBackgrounds->where('level', 'college')->first();
          // For backward compatibility, use the first one or create a helper object
          $educationalBg = $juniorHighBg ?? $seniorHighBg ?? $elementaryBg ?? $collegeBg ?? null;
          $pwdInfo = $user->pwdInformation;
          $indigenousInfo = $user->indigenousMember;
          $govAffiliation = $user->governmentAffiliation;
          $fraternityInfo = $user->fraternityMember;
          $emergencyContact = $user->emergencyContacts->first();
          
          // Calculate age from birth_date if not set
          $age = $user->age;
          if (!$age && $user->birth_date) {
            $birthDate = \Carbon\Carbon::parse($user->birth_date);
            $age = $birthDate->age;
          }
        @endphp

        <!-- Form Title Section -->
        <div class="form-title-section">
          <div class="form-title">Student Information Sheet</div>
          <!-- School Year and Semester/Summer centered below title -->
          <div class="inline-fields" style="justify-content: center; margin-top: 15px;">
            <div class="form-field" style="max-width: 150px;">
              <label>School Year</label>
              <input type="text" name="school_year" class="form-control" value="{{ old('school_year', $studentInfo->school_year ?? $user->school_year ?? '') }}" placeholder="e.g., 2024-2025">
            </div>
            <div class="form-field" style="max-width: 150px;">
              <label>Semester/Summer</label>
              <select name="semester" class="form-control">
                <option value="">Select</option>
                <option value="1st Semester" {{ old('semester', $studentInfo->semester ?? $user->semester) == '1st Semester' ? 'selected' : '' }}>1st Semester</option>
                <option value="2nd Semester" {{ old('semester', $studentInfo->semester ?? $user->semester) == '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                <option value="Summer" {{ old('semester', $studentInfo->semester ?? $user->semester) == 'Summer' ? 'selected' : '' }}>Summer</option>
              </select>
            </div>
          </div>
        </div>
        
        <form action="{{ route('admin.staff.dashboard.AdmissionServicesOfficer.student.update', $student->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          
          <!-- Personal Data Sheet Image Upload -->
          <div class="form-section">
            <div class="section-label">Personal Data Sheet Image</div>
            <div class="section-row">
              <div class="form-field form-field-full">
                <label>Upload Photo/Image</label>
                <input type="file" name="personal_data_sheet_image" class="form-control" accept="image/*" id="personal-data-sheet-image">
                <small class="text-muted">Upload student's photo or Personal Data Sheet image (JPG, PNG, etc.)</small>
                <div id="image-preview" class="mt-2" style="display: none;">
                  <img id="preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
              </div>
            </div>
          </div>

          <!-- Student Type and Student ID row - above Section A -->
          <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-left: 3px solid midnightblue;">
            <div class="form-field" style="max-width: 200px;">
              <label>Student Type</label>
              <div style="display: flex; gap: 15px; margin-top: 5px;">
                <label style="font-weight: normal;">
                  <input type="radio" name="student_type" value="new" {{ old('student_type', $studentInfo->student_type ?? $user->student_type) == 'new' ? 'checked' : '' }}> New Student
                </label>
                <label style="font-weight: normal;">
                  <input type="radio" name="student_type" value="old" {{ old('student_type', $studentInfo->student_type ?? $user->student_type) == 'old' ? 'checked' : '' }}> Old Student
                </label>
              </div>
            </div>
            <div class="form-field" style="max-width: 200px;">
              <label>Student ID No.</label>
              <input type="text" name="user_id" class="form-control" value="{{ old('user_id', $user->user_id) }}" placeholder="Student ID" required>
            </div>
          </div>
          
          <!-- Section A: Name -->
          <div class="form-section">
            <div class="section-label">A. NAME</div>
            <div class="section-row">
              <div class="form-field">
                <label>Last Name:</label>
                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" required>
              </div>
              <div class="form-field">
                <label>First Name:</label>
                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
              </div>
              <div class="form-field">
                <label>Middle Initial:</label>
                <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $user->middle_name) }}" maxlength="1" pattern="[A-Za-z]" title="Enter only one alphabet character">
              </div>
              <div class="form-field">
                <label>Ext. Name:</label>
                <input type="text" name="ext_name" class="form-control" value="{{ old('ext_name', $user->ext_name) }}" placeholder="Jr., Sr., III, etc.">
              </div>
            </div>
          </div>

          <!-- Section B: HOME ADDRESS -->
          <div class="form-section">
            <div class="section-label">B. HOME ADDRESS</div>
            <div class="section-row">
              <div class="form-field">
                <label>Province:</label>
                <select name="province" id="province-select" class="form-control" required>
                  <option value="">Select Province</option>
                </select>
              </div>
              <div class="form-field">
                <label>City/Municipality:</label>
                <select name="city_municipality" id="city-select" class="form-control" required disabled>
                  <option value="">Select City/Municipality</option>
                </select>
              </div>
              <div class="form-field">
                <label>Barangay:</label>
                <select name="barangay" id="barangay-select" class="form-control" required disabled>
                  <option value="">Select Barangay</option>
                </select>
              </div>
              <div class="form-field form-field-full">
                <label>Street/House No.:</label>
                <input type="text" name="street" id="street-input" class="form-control" value="{{ old('street', $homeAddress->street ?? '') }}" placeholder="Enter street name and house number">
              </div>
              <div class="form-field">
                <label>Zip Code:</label>
                <input type="text" name="zip_code" id="zip-code-input" class="form-control" value="{{ old('zip_code', $homeAddress->zip_code ?? '') }}" readonly>
              </div>
            </div>
          </div>

          <!-- Section C: PERSONAL DETAILS -->
          <div class="form-section">
            <div class="section-label">C. PERSONAL DETAILS</div>
            <div class="section-row">
              <div class="form-field">
                <label>Age:</label>
                <input type="number" name="age" id="age-input" class="form-control" value="{{ old('age', $age) }}" min="1" max="100" required>
              </div>
              <div class="form-field">
                <label>Date of Birth (mm/dd/yyyy):</label>
                <input type="date" name="birth_date" id="birth-date-input" class="form-control" value="{{ old('birth_date', $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d') : '') }}" required>
              </div>
              <div class="form-field">
                <label>Place of Birth:</label>
                <input type="text" name="place_of_birth" class="form-control" value="{{ old('place_of_birth', $user->place_of_birth ?? $personalInfo->place_of_birth ?? '') }}" required>
              </div>
              <div class="form-field">
                <label>Sex:</label>
                <select name="gender" class="form-control" required>
                  <option value="">Select</option>
                  <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                  <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                </select>
              </div>
              <div class="form-field">
                <label>Civil Status:</label>
                <select name="civil_status" class="form-control" required>
                  <option value="">Select</option>
                  <option value="single" {{ old('civil_status', $user->civil_status) == 'single' ? 'selected' : '' }}>Single</option>
                  <option value="married" {{ old('civil_status', $user->civil_status) == 'married' ? 'selected' : '' }}>Married</option>
                  <option value="divorced" {{ old('civil_status', $user->civil_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                  <option value="widowed" {{ old('civil_status', $user->civil_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                </select>
              </div>
              <div class="form-field">
                <label>Nationality:</label>
                <select name="nationality_id" class="form-control" id="nationality-select">
                  <option value="">Select Nationality</option>
                  @foreach($nationalities ?? [] as $nationality)
                    <option value="{{ $nationality->id }}" {{ old('nationality_id', $personalInfo->nationality_id ?? null) == $nationality->id ? 'selected' : '' }}>
                      {{ $nationality->name }}
                    </option>
                  @endforeach
                </select>
                <small class="text-muted">Or enter a new nationality:</small>
                <input type="text" name="nationality" class="form-control mt-1" value="{{ old('nationality', $user->nationality ?? $personalInfo->nationality ?? '') }}" placeholder="Enter new nationality (optional)" id="nationality-input">
              </div>
            </div>
          </div>

          <!-- Section D: Other -->
          <div class="form-section">
            <div class="section-label">D. Other:</div>
            <div class="section-row">
              <div class="form-field">
                <label>Religion:</label>
                <input type="text" name="religion" class="form-control" value="{{ old('religion', $user->religion ?? $personalInfo->religion ?? '') }}">
              </div>
              <div class="form-field">
                <label>Mobile No.:</label>
                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $user->contact_number) }}" required>
              </div>
              <div class="form-field">
                <label>Tel No.:</label>
                <input type="text" name="tel_no" class="form-control" value="{{ old('tel_no', $user->tel_no) }}">
              </div>
              <div class="form-field">
                <label>Email Address:</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
              </div>
            </div>
            <div class="section-row" id="spouse-section" style="display: none;">
              <div class="form-field">
                <label>If Married, Name of Spouse:</label>
                <input type="text" name="spouse_name" class="form-control" value="{{ old('spouse_name', $user->spouse_name) }}">
              </div>
              <div class="form-field">
                <label>Spouse Contact No.:</label>
                <input type="text" name="spouse_contact_no" class="form-control" value="{{ old('spouse_contact_no', $user->spouse_contact_no) }}">
              </div>
            </div>
          </div>

          <!-- Section E: SPECIAL SKILLS AND TALENTS -->
          <div class="form-section">
            <div class="section-label">E. SPECIAL SKILLS AND TALENTS</div>
            <div class="section-row">
              <div class="form-field">
                <label>Sport:</label>
                <input type="text" name="sport" class="form-control" value="{{ old('sport', $user->sport) }}">
              </div>
              <div class="form-field">
                <label>Arts:</label>
                <input type="text" name="arts" class="form-control" value="{{ old('arts', $user->arts) }}">
              </div>
              <div class="form-field">
                <label>Technical:</label>
                <input type="text" name="technical" class="form-control" value="{{ old('technical', $user->technical) }}">
              </div>
            </div>
          </div>

          <!-- Section F: EDUCATION BACKGROUND -->
          <div class="form-section">
            <div class="section-label">F. EDUCATION BACKGROUND</div>
            <div class="mb-3">
              <strong>Junior High School / High School:</strong>
              <div class="section-row">
                <div class="form-field form-field-full">
                  <label>Junior High School / High School:</label>
                  <input type="text" name="junior_high_school_name" class="form-control" value="{{ old('junior_high_school_name', $user->junior_high_school_name ?? optional($juniorHighBg)->name ?? '') }}" placeholder="Junior High School / High School">
                </div>
                <div class="form-field">
                  <label>Year Completed/Graduated:</label>
                  <input type="text" name="junior_high_school_year_completed" class="form-control" value="{{ old('junior_high_school_year_completed', $user->junior_high_school_year_completed ?? optional($juniorHighBg)->year_completed ?? '') }}" placeholder="Year Completed/Graduated">
                </div>
                <div class="form-field form-field-full">
                  <label>Complete School Address:</label>
                  <textarea name="junior_high_school_address" class="form-control" rows="2" placeholder="Complete School Address">{{ old('junior_high_school_address', $user->junior_high_school_address ?? optional($juniorHighBg)->address ?? '') }}</textarea>
                </div>
                <div class="form-field">
                  <label>Honors/Awards:</label>
                  <input type="text" name="junior_high_school_honors_awards" class="form-control" value="{{ old('junior_high_school_honors_awards', $user->junior_high_school_honors_awards ?? optional($juniorHighBg)->honors_awards ?? '') }}" placeholder="Honors/Awards">
                </div>
              </div>
            </div>
            <div class="mb-3">
              <strong>Senior High School:</strong>
              <div class="section-row">
                <div class="form-field">
                  <label>Senior High School:</label>
                  <input type="text" name="senior_high_school_name" class="form-control" value="{{ old('senior_high_school_name', $user->senior_high_school_name ?? optional($seniorHighBg)->name ?? '') }}" placeholder="Senior High School">
                </div>
                <div class="form-field">
                  <label>Year Graduated:</label>
                  <input type="text" name="senior_high_school_year_graduated" class="form-control" value="{{ old('senior_high_school_year_graduated', $user->senior_high_school_year_graduated ?? optional($seniorHighBg)->year_completed ?? '') }}" placeholder="Year Graduated">
                </div>
                <div class="form-field">
                  <label>Track and Strand:</label>
                  <input type="text" name="senior_high_school_track_strand" class="form-control" value="{{ old('senior_high_school_track_strand', $user->senior_high_school_track_strand ?? optional($seniorHighBg)->track_strand ?? '') }}" placeholder="Track and Strand">
                </div>
                <div class="form-field">
                  <label>LRN:</label>
                  <input type="text" name="senior_high_school_lrn" class="form-control" value="{{ old('senior_high_school_lrn', $user->senior_high_school_lrn ?? optional($seniorHighBg)->lrn ?? '') }}" placeholder="LRN">
                </div>
                <div class="form-field form-field-full">
                  <label>Complete School Address:</label>
                  <textarea name="senior_high_school_address" class="form-control" rows="2" placeholder="Complete School Address">{{ old('senior_high_school_address', $user->senior_high_school_address ?? optional($seniorHighBg)->address ?? '') }}</textarea>
                </div>
                <div class="form-field">
                  <label>Honor/Awards:</label>
                  <input type="text" name="senior_high_school_honors_awards" class="form-control" value="{{ old('senior_high_school_honors_awards', $user->senior_high_school_honors_awards ?? optional($seniorHighBg)->honors_awards ?? '') }}" placeholder="Honor/Awards">
                </div>
              </div>
            </div>
            <div class="mb-3">
              <strong>If TRANSFEREE/ SECOND COURSER, please indicate necessary details.</strong>
              <div class="section-row">
                <div class="form-field">
                  <label>Last School Attended:</label>
                  <input type="text" name="last_school_attended" class="form-control" value="{{ old('last_school_attended', $user->last_school_attended ?? optional($collegeBg)->name ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Course:</label>
                  <input type="text" name="last_school_course" class="form-control" value="{{ old('last_school_course', $user->last_school_course ?? optional($collegeBg)->course ?? '') }}">
                </div>
                <div class="form-field form-field-full">
                  <label>Complete Address:</label>
                  <textarea name="last_school_address" class="form-control" rows="2">{{ old('last_school_address', $user->last_school_address ?? optional($collegeBg)->address ?? '') }}</textarea>
                </div>
                <div class="form-field">
                  <label>Last School Year Attended:</label>
                  <input type="text" name="last_school_year_attended" class="form-control" value="{{ old('last_school_year_attended', $user->last_school_year_attended ?? optional($collegeBg)->year_completed ?? '') }}">
                </div>
              </div>
            </div>
          </div>

          <!-- Section G: FAMILY BACKGROUND -->
          <div class="form-section">
            <div class="section-label">G. FAMILY BACKGROUND</div>
            <div class="mb-3">
              <strong>Father's Information:</strong>
              <div class="section-row">
                <div class="form-field">
                  <label>Father's Name:</label>
                  <input type="text" name="father_name" class="form-control" value="{{ old('father_name', optional($user->familyMember('father'))->name ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Contact Number:</label>
                  <input type="text" name="father_contact_number" class="form-control" value="{{ old('father_contact_number', optional($user->familyMember('father'))->contact_number ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Occupation:</label>
                  <input type="text" name="father_occupation" class="form-control" value="{{ old('father_occupation', optional($user->familyMember('father'))->occupation ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Name of workplace:</label>
                  <input type="text" name="father_workplace" class="form-control" value="{{ old('father_workplace', optional($user->familyMember('father'))->workplace ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Father's Monthly Income:</label>
                  <input type="text" name="father_monthly_income" class="form-control" value="{{ old('father_monthly_income', optional($user->familyMember('father'))->monthly_income ?? '') }}">
                </div>
              </div>
            </div>
            <div class="mb-3">
              <strong>Mother's Information:</strong>
              <div class="section-row">
                <div class="form-field">
                  <label>Mother's Name:</label>
                  <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', optional($user->familyMember('mother'))->name ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Contact No.:</label>
                  <input type="text" name="mother_contact_number" class="form-control" value="{{ old('mother_contact_number', optional($user->familyMember('mother'))->contact_number ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Occupation:</label>
                  <input type="text" name="mother_occupation" class="form-control" value="{{ old('mother_occupation', optional($user->familyMember('mother'))->occupation ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Name of Workplace:</label>
                  <input type="text" name="mother_workplace" class="form-control" value="{{ old('mother_workplace', optional($user->familyMember('mother'))->workplace ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Mother's Monthly Income:</label>
                  <input type="text" name="mother_monthly_income" class="form-control" value="{{ old('mother_monthly_income', optional($user->familyMember('mother'))->monthly_income ?? '') }}">
                </div>
              </div>
            </div>
            <div class="mb-3">
              <strong>Guardian's Information (Skip this section if you are currently living with your parents):</strong>
              <div class="section-row">
                <div class="form-field">
                  <label>Guardian's Name:</label>
                  <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', optional($user->familyMember('guardian'))->name ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Relationship:</label>
                  <input type="text" name="guardian_relationship" class="form-control" value="{{ old('guardian_relationship', optional($user->familyMember('guardian'))->relation ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Contact Number:</label>
                  <input type="text" name="guardian_contact_number" class="form-control" value="{{ old('guardian_contact_number', optional($user->familyMember('guardian'))->contact_number ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Occupation:</label>
                  <input type="text" name="guardian_occupation" class="form-control" value="{{ old('guardian_occupation', optional($user->familyMember('guardian'))->occupation ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Name of workplace:</label>
                  <input type="text" name="guardian_workplace" class="form-control" value="{{ old('guardian_workplace', optional($user->familyMember('guardian'))->workplace ?? '') }}">
                </div>
                <div class="form-field">
                  <label>Guardian's Monthly Income:</label>
                  <input type="text" name="guardian_monthly_income" class="form-control" value="{{ old('guardian_monthly_income', optional($user->familyMember('guardian'))->monthly_income ?? '') }}">
                </div>
              </div>
            </div>
          </div>

          <!-- Section H: OTHER INFORMATION -->
          <div class="form-section">
            <div class="section-label">H. OTHER INFORMATION</div>
            <div class="section-row">
              <div class="form-field">
                <label>Are you a an active scholar for this semester?</label>
                <div style="display: flex; gap: 15px; margin-top: 5px;">
                  <label style="font-weight: normal;">
                    <input type="radio" name="is_active_scholar" value="1" {{ old('is_active_scholar', $studentInfo->is_active_scholar ?? $user->is_active_scholar ?? 0) == '1' || old('is_active_scholar', $studentInfo->is_active_scholar ?? $user->is_active_scholar ?? 0) == 1 ? 'checked' : '' }}> Yes
                  </label>
                  <label style="font-weight: normal;">
                    <input type="radio" name="is_active_scholar" value="0" {{ old('is_active_scholar', $studentInfo->is_active_scholar ?? $user->is_active_scholar ?? 0) == '0' || old('is_active_scholar', $studentInfo->is_active_scholar ?? $user->is_active_scholar ?? 0) == 0 || old('is_active_scholar', $studentInfo->is_active_scholar ?? $user->is_active_scholar ?? 0) == null ? 'checked' : '' }}> No
                  </label>
                </div>
              </div>
              <div class="form-field">
                <label>If you have a scholarship, kindly indicate the name of the Scholarship Grant:</label>
                <input type="text" name="scholarship_grant_name" class="form-control" value="{{ old('scholarship_grant_name', $studentInfo->scholarship_grant_name ?? $user->scholarship_grant_name ?? '') }}">
              </div>
            </div>
            <div class="section-row">
              <div class="form-field">
                <label>Are you a part of an Indigenous Group:</label>
                <div style="display: flex; gap: 15px; margin-top: 5px;">
                  <label style="font-weight: normal;">
                    <input type="radio" name="is_indigenous_group_member" value="1" {{ old('is_indigenous_group_member', optional($indigenousInfo)->is_member ?? 0) == '1' || old('is_indigenous_group_member', optional($indigenousInfo)->is_member ?? 0) == 1 ? 'checked' : '' }}> Yes
                  </label>
                  <label style="font-weight: normal;">
                    <input type="radio" name="is_indigenous_group_member" value="0" {{ old('is_indigenous_group_member', optional($indigenousInfo)->is_member ?? 0) == '0' || old('is_indigenous_group_member', optional($indigenousInfo)->is_member ?? 0) == 0 || old('is_indigenous_group_member', optional($indigenousInfo)->is_member ?? 0) == null ? 'checked' : '' }}> No
                  </label>
                </div>
              </div>
              <div class="form-field">
                <label>If you are a part of an Indigenous group, please specify:</label>
                <input type="text" name="indigenous_group_specify" class="form-control" value="{{ old('indigenous_group_specify', optional($indigenousInfo)->group_name ?? '') }}">
              </div>
            </div>
            <div class="section-row">
              <div class="form-field">
                <label>Are you a Person with Disability (PWD)?</label>
                <small class="text-muted d-block">(Only those with an official PWD ID are considered for this category.)</small>
                <div style="display: flex; gap: 15px; margin-top: 5px;">
                  <label style="font-weight: normal;">
                    <input type="radio" name="is_pwd" id="is_pwd_yes_edit" value="1" {{ old('is_pwd', optional($pwdInfo)->is_pwd ?? 0) == '1' || old('is_pwd', optional($pwdInfo)->is_pwd ?? 0) == 1 ? 'checked' : '' }}> Yes
                  </label>
                  <label style="font-weight: normal;">
                    <input type="radio" name="is_pwd" id="is_pwd_no_edit" value="0" {{ old('is_pwd', optional($pwdInfo)->is_pwd ?? 0) == '0' || old('is_pwd', optional($pwdInfo)->is_pwd ?? 0) == 0 || old('is_pwd', optional($pwdInfo)->is_pwd ?? 0) == null ? 'checked' : '' }}> No
                  </label>
                </div>
              </div>
              <div class="form-field" id="disability_specify_field_edit" style="display: {{ old('is_pwd', optional($pwdInfo)->is_pwd ?? 0) == '1' || old('is_pwd', optional($pwdInfo)->is_pwd ?? 0) == 1 ? 'block' : 'none' }};">
                <label>If answer is YES, please specify your disability:</label>
                <input type="text" name="disability_type" id="disability_type_edit" class="form-control" value="{{ old('disability_type', optional($pwdInfo)->disability_type ?? '') }}" placeholder="Specify your disability">
              </div>
              <div class="form-field">
                <label>If you are a PWD, kindly upload your valid ID here:</label>
                <input type="file" name="pwd_id_image" class="form-control" accept="image/*">
                <small class="text-muted">Upload 1 supported file: image. Max 100 MB.</small>
              </div>
            </div>
            <div class="section-row">
              <div class="form-field">
                <label>Are you a member of any government or political organization?</label>
                <small class="text-muted d-block">(Please check the option that applies to you. Examples include Sangguniang Kabataan (SK), Barangay, Municipal, City, Provincial, or National government positions or organizations.)</small>
                <select name="is_government_member" class="form-control" id="is-government-member">
                  <option value="no" {{ old('is_government_member', optional($govAffiliation)->is_member ?? 'no') == 'no' || old('is_government_member', optional($govAffiliation)->is_member ?? 'no') == null ? 'selected' : '' }}>No – I am not a member of any government or political organization.</option>
                  <option value="yes" {{ old('is_government_member', optional($govAffiliation)->is_member ?? 'no') == 'yes' ? 'selected' : '' }}>Yes – I am currently involved</option>
                </select>
              </div>
            </div>
            <div class="section-row">
              <div class="form-field" id="government-level-field" style="display: none;">
                <label>If you are a government official, kindly specify the level:</label>
                <select name="government_level" class="form-control">
                  <option value="">Select option:</option>
                  <option value="barangay" {{ old('government_level', optional($govAffiliation)->level) == 'barangay' ? 'selected' : '' }}>Barangay Government</option>
                  <option value="municipal_city" {{ old('government_level', optional($govAffiliation)->level) == 'municipal_city' ? 'selected' : '' }}>Municipal/City Government</option>
                  <option value="provincial" {{ old('government_level', optional($govAffiliation)->level) == 'provincial' ? 'selected' : '' }}>Provincial Government</option>
                </select>
              </div>
              <div class="form-field" id="government-role-field" style="display: none;">
                <label>If you are a government official, indicate your role or position:</label>
                <small class="text-muted d-block">(For example: SK Chairperson, SK Councilor, Barangay Secretary, Barangay Treasurer, Municipal Youth Representative, City Council Staff, or any official role in a government office or organization.)</small>
                <input type="text" name="government_role_position" class="form-control" value="{{ old('government_role_position', optional($govAffiliation)->role_position ?? '') }}" placeholder="Your role or position">
              </div>
            </div>
            <div class="section-row">
              <div class="form-field">
                <label>Current living arrangement:</label>
                <select name="living_arrangement" class="form-control" id="living-arrangement">
                  <option value="">Select</option>
                  <option value="home" {{ old('living_arrangement', $user->living_arrangement) == 'home' ? 'selected' : '' }}>I live at home – with my parents or immediate family</option>
                  <option value="boarding_house" {{ old('living_arrangement', $user->living_arrangement) == 'boarding_house' ? 'selected' : '' }}>I live in a boarding house – renting a room or space near the campus</option>
                  <option value="relatives" {{ old('living_arrangement', $user->living_arrangement) == 'relatives' ? 'selected' : '' }}>I live with relatives – staying with extended family members</option>
                  <option value="working_student" {{ old('living_arrangement', $user->living_arrangement) == 'working_student' ? 'selected' : '' }}>I live as a working student – employed while studying and living independently</option>
                  <option value="others" {{ old('living_arrangement', $user->living_arrangement) == 'others' ? 'selected' : '' }}>Others (please specify)</option>
                </select>
              </div>
              <div class="form-field" id="living-arrangement-others-field" style="display: none;">
                <label>Others (please specify):</label>
                <input type="text" name="living_arrangement_others_specify" class="form-control" value="{{ old('living_arrangement_others_specify', $user->living_arrangement_others_specify) }}">
              </div>
            </div>
            <div class="section-row">
              <div class="form-field">
                <label>Are you a single parent?</label>
                <div style="display: flex; gap: 15px; margin-top: 5px;">
                  <label style="font-weight: normal;">
                    <input type="radio" name="is_single_parent" value="1" {{ old('is_single_parent', $user->is_single_parent ?? 0) == '1' || old('is_single_parent', $user->is_single_parent ?? 0) == 1 ? 'checked' : '' }}> YES
                  </label>
                  <label style="font-weight: normal;">
                    <input type="radio" name="is_single_parent" value="0" {{ old('is_single_parent', $user->is_single_parent ?? 0) == '0' || old('is_single_parent', $user->is_single_parent ?? 0) == 0 || old('is_single_parent', $user->is_single_parent ?? 0) == null ? 'checked' : '' }}> NO
                  </label>
                </div>
              </div>
              <div class="form-field">
                <label>Are you a member of a fraternity /or Sorority?</label>
                <small class="text-muted d-block">(Please indicate the name and your position if applicable)</small>
                <input type="text" name="fraternity_sorority_name" class="form-control" value="{{ old('fraternity_sorority_name', optional($fraternityInfo)->name ?? '') }}" placeholder="Name of Fraternity/Sorority">
              </div>
              <div class="form-field">
                <label>Position in Fraternity/Sorority:</label>
                <input type="text" name="fraternity_sorority_position" class="form-control" value="{{ old('fraternity_sorority_position', optional($fraternityInfo)->position ?? '') }}" placeholder="Your position (if applicable)">
              </div>
            </div>
            <div class="section-row">
              <div class="form-field">
                <label>Did you have any previous criminal offense/record?</label>
                <div style="display: flex; gap: 15px; margin-top: 5px;">
                  <label style="font-weight: normal;">
                    <input type="radio" name="has_criminal_record" value="1" {{ old('has_criminal_record', $user->has_criminal_record ?? 0) == '1' || old('has_criminal_record', $user->has_criminal_record ?? 0) == 1 ? 'checked' : '' }}> YES
                  </label>
                  <label style="font-weight: normal;">
                    <input type="radio" name="has_criminal_record" value="0" {{ old('has_criminal_record', $user->has_criminal_record ?? 0) == '0' || old('has_criminal_record', $user->has_criminal_record ?? 0) == 0 || old('has_criminal_record', $user->has_criminal_record ?? 0) == null ? 'checked' : '' }}> NO
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Section I: Degree Applied/Enrolled -->
          <div class="form-section">
            <div class="section-label">I. Degree Applied/Enrolled (State in Full)</div>
            <div class="section-row">
              <div class="form-field">
                <label>Department</label>
                <select name="department_id" class="form-control" id="department-select" required>
                  <option value="">Select Department</option>
                  @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('department_id', $student->department_id ?? $user->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-field">
                <label>Course</label>
                <select name="course_id" class="form-control" id="course-select" required>
                  <option value="">Select Course</option>
                  @foreach($courses as $course)
                    <option value="{{ $course->id }}" data-department="{{ $course->department_id }}" {{ old('course_id', $student->course_id ?? $user->course_id) == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-field">
                <label>Year Level</label>
                <input type="number" name="year_level" class="form-control" value="{{ old('year_level', $studentInfo->year_level ?? $user->year_level) }}" min="1" max="10" placeholder="Year Level" required>
              </div>
            </div>
            <div class="section-row">
              <div class="form-field">
                <label>Organization</label>
                <select name="organization_id" class="form-control" id="organization-select">
                  <option value="">Select Organization</option>
                  @foreach($organizations as $org)
                    <option value="{{ $org->id }}" data-department="{{ $org->department_id }}" {{ old('organization_id', $student->organization_id ?? $user->organization_id) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-field">
                <label>Student Type 1</label>
                @php
                  $currentStudentType1 = old('student_type1', $studentInfo->student_type1 ?? $student->student_type1 ?? $user->student_type1 ?? '');
                @endphp
                <select name="student_type1" class="form-control" required>
                  <option value="">Select Type</option>
                  <option value="regular" {{ $currentStudentType1 == 'regular' ? 'selected' : '' }}>Regular</option>
                  <option value="irregular" {{ $currentStudentType1 == 'irregular' ? 'selected' : '' }}>Irregular</option>
                  <option value="transferee" {{ $currentStudentType1 == 'transferee' ? 'selected' : '' }}>Transferee</option>
                </select>
              </div>
              <div class="form-field">
                <label>Student Type 2</label>
                @php
                  $currentStudentType2 = old('student_type2', $studentInfo->student_type2 ?? $student->student_type2 ?? $user->student_type2 ?? '');
                @endphp
                <select name="student_type2" class="form-control" id="student-type2-select" required>
                  <option value="">Select Type</option>
                  <option value="paying" {{ $currentStudentType2 == 'paying' ? 'selected' : '' }}>Paying</option>
                  <option value="scholar" {{ $currentStudentType2 == 'scholar' ? 'selected' : '' }}>Scholar</option>
                </select>
              </div>
            </div>
            <div class="section-row">
              <div class="form-field">
                <label>Scholarship</label>
                @php
                  $currentScholarshipId = old('scholarship_id', $student->scholarship_id ?? $studentInfo->scholarship_id ?? $user->scholarship_id ?? '');
                  $isScholar = ($currentStudentType2 ?? '') == 'scholar';
                @endphp
                <select name="scholarship_id" class="form-control" id="scholarship-select" {{ !$isScholar ? 'disabled' : '' }}>
                  <option value="">Select Scholarship</option>
                  @foreach($scholarships as $scholarship)
                    <option value="{{ $scholarship->id }}" {{ $currentScholarshipId == $scholarship->id ? 'selected' : '' }}>{{ $scholarship->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <!-- Section J: Entrance Credential -->
          <div class="form-section">
            <div class="section-label">J. Entrance Credential</div>
            <div class="mb-2"><strong>Presented:</strong></div>
            <div class="checkbox-group">
              <div class="checkbox-item">
                <input type="checkbox" name="form_137_presented" id="form_137" value="1" {{ old('form_137_presented', $documentChecklist->form_137_presented ?? false) ? 'checked' : '' }}>
                <label for="form_137" style="font-weight: normal; margin: 0;">Form 137</label>
              </div>
              <div class="checkbox-item">
                <input type="checkbox" name="tor_presented" id="tor" value="1" {{ old('tor_presented', $documentChecklist->tor_presented ?? false) ? 'checked' : '' }}>
                <label for="tor" style="font-weight: normal; margin: 0;">TOR</label>
              </div>
              <div class="checkbox-item">
                <input type="checkbox" name="good_moral_cert_presented" id="good_moral" value="1" {{ old('good_moral_cert_presented', $documentChecklist->good_moral_cert_presented ?? false) ? 'checked' : '' }}>
                <label for="good_moral" style="font-weight: normal; margin: 0;">Good Moral Cert.</label>
              </div>
              <div class="checkbox-item">
                <input type="checkbox" name="birth_cert_presented" id="birth_cert" value="1" {{ old('birth_cert_presented', $documentChecklist->birth_cert_presented ?? false) ? 'checked' : '' }}>
                <label for="birth_cert" style="font-weight: normal; margin: 0;">Birth Cert.</label>
              </div>
              <div class="checkbox-item">
                <input type="checkbox" name="marriage_cert_presented" id="marriage_cert" value="1" {{ old('marriage_cert_presented', $documentChecklist->marriage_cert_presented ?? false) ? 'checked' : '' }}>
                <label for="marriage_cert" style="font-weight: normal; margin: 0;">Marriage Cert.</label>
              </div>
            </div>
          </div>

          <div class="text-center mt-4 mb-3">
            <button type="submit" class="btn btn-primary btn-lg">Submit Student Information Sheet</button>
          </div>
        </form>
      </div>
    </main>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Department auto-fill functionality
    const quickDeptSelect = document.getElementById('quick-department-select');
    const fillDeptBtn = document.getElementById('fill-department-btn');
    const clearDeptBtn = document.getElementById('clear-department-btn');
    const deptInfo = document.getElementById('department-info');
    const selectedDeptName = document.getElementById('selected-dept-name');
    const selectedOrgName = document.getElementById('selected-org-name');
    const departmentSelect = document.getElementById('department-select');
    const courseSelect = document.getElementById('course-select');
    const organizationSelect = document.getElementById('organization-select');
    
    // Organizations data for auto-fill
    const organizations = @json($organizations);
    
    // Enable/disable fill button based on department selection
    if (quickDeptSelect) {
      quickDeptSelect.addEventListener('change', function() {
        if (this.value) {
          fillDeptBtn.disabled = false;
          const selectedOption = this.options[this.selectedIndex];
          const deptName = selectedOption.getAttribute('data-dept-name');
          selectedDeptName.textContent = deptName;
          
          // Find department-related organization
          const deptId = parseInt(this.value);
          const deptOrg = organizations.find(org => org.department_id == deptId);
          if (deptOrg) {
            selectedOrgName.textContent = deptOrg.name;
          } else {
            selectedOrgName.textContent = 'No department-related organization';
          }
          deptInfo.style.display = 'block';
        } else {
          fillDeptBtn.disabled = true;
          deptInfo.style.display = 'none';
        }
      });
      
      // Auto-fill form when button is clicked
      fillDeptBtn.addEventListener('click', async function() {
        const deptId = quickDeptSelect.value;
        if (deptId && departmentSelect) {
          // Set department
          departmentSelect.value = deptId;
          
          // Load courses and organizations dynamically
          await Promise.all([
            loadCourses(deptId),
            loadOrganizations(deptId)
          ]);
          
          // Find and set department-related organization after loading
          const deptOrg = organizations.find(org => org.department_id == deptId);
          if (deptOrg && organizationSelect) {
            organizationSelect.value = deptOrg.id;
          }
          
          // Show clear button
          fillDeptBtn.style.display = 'none';
          clearDeptBtn.style.display = 'inline-block';
          
          // Scroll to form
          document.querySelector('form').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
      
      // Clear selection
      clearDeptBtn.addEventListener('click', async function() {
        quickDeptSelect.value = '';
        fillDeptBtn.disabled = true;
        fillDeptBtn.style.display = 'inline-block';
        clearDeptBtn.style.display = 'none';
        deptInfo.style.display = 'none';
        if (departmentSelect) {
          departmentSelect.value = '';
          // Reset courses and organizations
          await Promise.all([
            loadCourses(''),
            loadOrganizations(null)
          ]);
        }
        if (organizationSelect) {
          organizationSelect.value = '';
        }
        if (courseSelect) {
          courseSelect.value = '';
        }
      });
    }
    
    // Image preview functionality
    const imageInput = document.getElementById('personal-data-sheet-image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    if (imageInput && imagePreview && previewImg) {
      imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            previewImg.src = e.target.result;
            imagePreview.style.display = 'block';
          };
          reader.readAsDataURL(file);
        } else {
          imagePreview.style.display = 'none';
        }
      });
    }

    // Dynamic loading of courses and organizations via AJAX
    const baseUrl = "{{ url('/') }}";
    const currentCourseId = "{{ old('course_id', $student->course_id ?? $user->course_id ?? '') }}";
    const currentOrganizationId = "{{ old('organization_id', $student->organization_id ?? $user->organization_id ?? '') }}";
    const currentDepartmentId = "{{ old('department_id', $student->department_id ?? $user->department_id ?? '') }}";
    
    // Load courses dynamically based on department
    async function loadCourses(departmentId, preserveSelection = true) {
      if (!courseSelect) return;
      
      // Store current selection before clearing
      const currentSelection = preserveSelection ? courseSelect.value : null;
      
      // Show loading state
      courseSelect.disabled = true;
      courseSelect.innerHTML = '<option value="">Loading courses...</option>';
      
      if (!departmentId) {
        courseSelect.innerHTML = '<option value="">Select Course</option>';
        // If we have a current course ID, try to preserve it by checking all courses
        if (currentCourseId && preserveSelection) {
          // Load all courses to find the current one
          try {
            const allCourses = @json($courses);
            allCourses.forEach(course => {
              const option = document.createElement('option');
              option.value = course.id;
              option.textContent = course.name;
              option.setAttribute('data-department', course.department_id);
              if (currentCourseId == course.id) {
                option.selected = true;
              }
              courseSelect.appendChild(option);
            });
          } catch (e) {
            console.error('Error preserving course:', e);
          }
        }
        courseSelect.disabled = false;
        return;
      }
      
      try {
        const response = await fetch(`${baseUrl}/api/courses/${departmentId}`);
        if (!response.ok) throw new Error('Failed to load courses');
        
        const courses = await response.json();
        
        // Clear and populate course dropdown
        courseSelect.innerHTML = '<option value="">Select Course</option>';
        courses.forEach(course => {
          const option = document.createElement('option');
          option.value = course.id;
          option.textContent = course.name;
          option.setAttribute('data-department', course.department_id);
          
          // Preserve current value if it matches
          if (preserveSelection && currentCourseId && currentCourseId == course.id) {
            option.selected = true;
          } else if (preserveSelection && currentSelection && currentSelection == course.id) {
            option.selected = true;
          }
          
          courseSelect.appendChild(option);
        });
        
        courseSelect.disabled = false;
      } catch (error) {
        console.error('Error loading courses:', error);
        courseSelect.innerHTML = '<option value="">Error loading courses</option>';
        courseSelect.disabled = false;
      }
    }
    
    // Load organizations dynamically based on department
    async function loadOrganizations(departmentId, preserveSelection = true) {
      if (!organizationSelect) return;
      
      // Store current selection before clearing
      const currentSelection = preserveSelection ? organizationSelect.value : null;
      
      // Show loading state
      organizationSelect.disabled = true;
      organizationSelect.innerHTML = '<option value="">Loading organizations...</option>';
      
      try {
        let url = `${baseUrl}/api/organizations`;
        if (departmentId) {
          url += `?department_id=${encodeURIComponent(departmentId)}`;
        }
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to load organizations');
        
        const orgs = await response.json();
        
        // Filter organizations by department if department is selected
        let filteredOrgs = orgs;
        if (departmentId) {
          filteredOrgs = orgs.filter(org => 
            !org.department_id || org.department_id == departmentId
          );
        }
        
        // Clear and populate organization dropdown
        organizationSelect.innerHTML = '<option value="">Select Organization</option>';
        filteredOrgs.forEach(org => {
          const option = document.createElement('option');
          option.value = org.id;
          option.textContent = org.name;
          if (org.department_id) {
            option.setAttribute('data-department', org.department_id);
          }
          
          // Preserve current value if it matches
          if (preserveSelection && currentOrganizationId && currentOrganizationId == org.id) {
            option.selected = true;
          } else if (preserveSelection && currentSelection && currentSelection == org.id) {
            option.selected = true;
          }
          
          organizationSelect.appendChild(option);
        });
        
        organizationSelect.disabled = false;
      } catch (error) {
        console.error('Error loading organizations:', error);
        organizationSelect.innerHTML = '<option value="">Error loading organizations</option>';
        organizationSelect.disabled = false;
      }
    }
    
    // Handle department change - dynamically load courses and organizations
    function handleDepartmentChange() {
      const deptId = departmentSelect ? departmentSelect.value : '';
      
      // Load courses and organizations dynamically
      loadCourses(deptId);
      loadOrganizations(deptId);
    }
    
    // Initialize on page load - preserve initial PHP-rendered selections
    if (departmentSelect) {
      // Ensure current values are preserved from PHP-rendered options
      if (currentCourseId && courseSelect && courseSelect.querySelector(`option[value="${currentCourseId}"]`)) {
        courseSelect.value = currentCourseId;
      }
      if (currentOrganizationId && organizationSelect && organizationSelect.querySelector(`option[value="${currentOrganizationId}"]`)) {
        organizationSelect.value = currentOrganizationId;
      }
      
      // Listen for department changes - only reload when user changes department
      departmentSelect.addEventListener('change', function() {
        const deptId = this.value;
        // When department changes, reload courses and organizations
        // The load functions will preserve current selections if they match
        loadCourses(deptId, true).then(() => {
          // Try to preserve current course selection
          if (currentCourseId && courseSelect) {
            const option = courseSelect.querySelector(`option[value="${currentCourseId}"]`);
            if (option) {
              courseSelect.value = currentCourseId;
            }
          }
        });
        loadOrganizations(deptId, true).then(() => {
          // Try to preserve current organization selection
          if (currentOrganizationId && organizationSelect) {
            const option = organizationSelect.querySelector(`option[value="${currentOrganizationId}"]`);
            if (option) {
              organizationSelect.value = currentOrganizationId;
            }
          }
        });
      });
    }

    // Calculate age from birth date
    const birthDateInput = document.getElementById('birth-date-input');
    const ageInput = document.getElementById('age-input');
    
    function calculateAge() {
      if (birthDateInput && ageInput && birthDateInput.value) {
        const birthDate = new Date(birthDateInput.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
          age--;
        }
        ageInput.value = age > 0 ? age : '';
      }
    }
    
    // Calculate age on page load
    if (birthDateInput && ageInput) {
      calculateAge();
      
      // Also calculate on change
      birthDateInput.addEventListener('change', calculateAge);
    }

    // Convert all text inputs and textareas to uppercase automatically
    const textInputs = document.querySelectorAll('input[type="text"], textarea');
    textInputs.forEach(input => {
      // Skip email, number, and date fields
      if (input.type === 'email' || input.type === 'number' || input.type === 'date') {
        return;
      }
      
      // Add style for visual uppercase display
      input.style.textTransform = 'uppercase';
      
      // Convert to uppercase on input
      input.addEventListener('input', function() {
        const cursorPosition = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(cursorPosition, cursorPosition);
      });
      
      // Convert to uppercase on paste
      input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text').toUpperCase();
        const start = this.selectionStart;
        const end = this.selectionEnd;
        this.value = this.value.substring(0, start) + pastedText + this.value.substring(end);
        const newCursorPosition = start + pastedText.length;
        this.setSelectionRange(newCursorPosition, newCursorPosition);
      });
      
      // Convert existing value to uppercase
      if (input.value) {
        input.value = input.value.toUpperCase();
      }
    });

    // Enter key navigation - move to next visible field, don't skip any
    const form = document.querySelector('form');
    if (form) {
      // Function to get all visible focusable fields in order
      function getVisibleFocusableFields() {
        return Array.from(form.querySelectorAll(
          'input:not([type="radio"]):not([type="checkbox"]):not([type="submit"]):not([type="button"]):not([type="hidden"]):not([disabled]), ' +
          'select:not([disabled]), textarea:not([disabled])'
        )).filter(field => {
          // Check if field is visible (not hidden by display:none)
          const style = window.getComputedStyle(field);
          if (style.display === 'none') return false;
          
          // Check if parent section is visible
          let parent = field;
          while (parent && parent !== form) {
            const parentStyle = window.getComputedStyle(parent);
            if (parentStyle.display === 'none') return false;
            parent = parent.parentElement;
          }
          
          return true;
        });
      }
      // Use event delegation to handle dynamically shown/hidden fields
      form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          const target = e.target;
          
          // Only handle Enter on input, select, and textarea fields
          if (!['INPUT', 'SELECT', 'TEXTAREA'].includes(target.tagName)) {
            return;
          }
          
          // Skip if it's a radio, checkbox, submit, button, or hidden field
          if (target.type === 'radio' || target.type === 'checkbox' || 
              target.type === 'submit' || target.type === 'button' || 
              target.type === 'hidden' || target.disabled) {
            return;
          }
          
          e.preventDefault();
          
          // Get all currently visible focusable fields
          const focusableFields = getVisibleFocusableFields();
          const currentIndex = focusableFields.indexOf(target);
          
          // Find next visible focusable field
          const nextIndex = currentIndex + 1;
          if (nextIndex < focusableFields.length) {
            // Focus on next field
            const nextField = focusableFields[nextIndex];
            nextField.focus();
            
            // For select fields, open dropdown
            if (nextField.tagName === 'SELECT') {
              setTimeout(() => {
                nextField.focus();
              }, 10);
            }
          } else {
            // Last field - submit form
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
              submitButton.click();
            } else {
              form.submit();
            }
          }
        }
      });
    }
    
    // Declare civil status select once at the top level
    const civilStatusSelect = document.querySelector('select[name="civil_status"]');
    const genderSelect = document.querySelector('select[name="gender"]');
    
    // Show/hide Section B based on Civil Status and Sex
    function toggleSectionB() {
      const sectionB = document.getElementById('section-b');
      const maidenNameInput = document.querySelector('input[name="maiden_name"]');
      
      if (genderSelect && civilStatusSelect && sectionB) {
        const gender = genderSelect.value;
        const civilStatus = civilStatusSelect.value;
        
        // Hide Section B if sex is "male" OR civil status is "single"
        if (gender === 'male' || civilStatus === 'single') {
          sectionB.style.display = 'none';
          if (maidenNameInput) {
            maidenNameInput.value = '';
          }
        } else {
          sectionB.style.display = 'block';
        }
      }
    }
    
    // Listen for changes in Sex and Civil Status
    if (genderSelect) {
      genderSelect.addEventListener('change', toggleSectionB);
    }
    if (civilStatusSelect) {
      civilStatusSelect.addEventListener('change', toggleSectionB);
    }
    
    // Initial check on page load
    toggleSectionB();
    
    // Handle nationality dropdown/input interaction
    const nationalitySelect = document.getElementById('nationality-select');
    const nationalityInput = document.getElementById('nationality-input');
    
    if (nationalitySelect && nationalityInput) {
      // When dropdown is selected, clear input
      nationalitySelect.addEventListener('change', function() {
        if (this.value) {
          nationalityInput.value = '';
        }
      });
      
      // When input is typed, clear dropdown selection
      nationalityInput.addEventListener('input', function() {
        if (this.value) {
          nationalitySelect.value = '';
        }
      });
    }
    
    // Toggle Scholarship dropdown based on Student Type 2
    const studentType2Select = document.getElementById('student-type2-select');
    const scholarshipSelect = document.getElementById('scholarship-select');
    
    function toggleScholarship() {
      if (studentType2Select && scholarshipSelect) {
        if (studentType2Select.value === 'scholar') {
          scholarshipSelect.disabled = false;
          scholarshipSelect.style.opacity = '1';
        } else {
          scholarshipSelect.disabled = true;
          scholarshipSelect.style.opacity = '0.5';
          scholarshipSelect.value = ''; // Clear selection when disabled
        }
      }
    }
    
    if (studentType2Select) {
      studentType2Select.addEventListener('change', toggleScholarship);
      // Initial check
      toggleScholarship();
    }
    
    // Toggle spouse section based on civil status
    const spouseSection = document.getElementById('spouse-section');
    
    function toggleSpouseSection() {
      if (civilStatusSelect && spouseSection) {
        if (civilStatusSelect.value === 'married') {
          spouseSection.style.display = 'flex';
        } else {
          spouseSection.style.display = 'none';
        }
      }
    }
    
    if (civilStatusSelect) {
      civilStatusSelect.addEventListener('change', toggleSpouseSection);
      toggleSpouseSection();
    }
    
    // Toggle government member fields
    const isGovernmentMember = document.getElementById('is-government-member');
    const governmentLevelField = document.getElementById('government-level-field');
    const governmentRoleField = document.getElementById('government-role-field');
    
    function toggleGovernmentFields() {
      if (isGovernmentMember && governmentLevelField && governmentRoleField) {
        if (isGovernmentMember.value === 'yes') {
          governmentLevelField.style.display = 'block';
          governmentRoleField.style.display = 'block';
        } else {
          governmentLevelField.style.display = 'none';
          governmentRoleField.style.display = 'none';
        }
      }
    }
    
    if (isGovernmentMember) {
      isGovernmentMember.addEventListener('change', toggleGovernmentFields);
      toggleGovernmentFields();
    }
    
    // Toggle living arrangement others field
    const livingArrangement = document.getElementById('living-arrangement');
    const livingArrangementOthersField = document.getElementById('living-arrangement-others-field');
    
    function toggleLivingArrangementOthers() {
      if (livingArrangement && livingArrangementOthersField) {
        if (livingArrangement.value === 'others') {
          livingArrangementOthersField.style.display = 'block';
        } else {
          livingArrangementOthersField.style.display = 'none';
        }
      }
    }
    
    if (livingArrangement) {
      livingArrangement.addEventListener('change', toggleLivingArrangementOthers);
      toggleLivingArrangementOthers();
    }

    // Address cascading dropdowns functionality
    // Get elements - ensure they exist before using
    const provinceSelect = document.getElementById('province-select');
    const citySelect = document.getElementById('city-select');
    const barangaySelect = document.getElementById('barangay-select');
    const zipCodeInput = document.getElementById('zip-code-input');
    // baseUrl is already declared above (line 1031), so we reuse it

    // Load provinces on page load - fetches from database via API
    async function loadProvinces() {
      // Re-check element exists (in case DOM wasn't ready)
      const provinceDropdown = document.getElementById('province-select');
      if (!provinceDropdown) {
        console.error('Province dropdown element not found');
        return;
      }
      
      try {
        const apiUrl = `${baseUrl}/api/provinces`;
        console.log('Loading provinces from:', apiUrl);
        
        const response = await fetch(apiUrl, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          cache: 'no-cache'
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const provinces = await response.json();
        console.log('Provinces received from API:', provinces);
        
        // Clear and populate province dropdown
        provinceDropdown.innerHTML = '<option value="">Select Province</option>';
        
        if (provinces && Array.isArray(provinces) && provinces.length > 0) {
          provinces.forEach(province => {
            const option = document.createElement('option');
            option.value = province.code || province.id;
            option.textContent = province.name || province.code;
            
            // Preserve old value if it matches
            const oldProvince = "{{ old('province', '') }}";
            if (oldProvince && (oldProvince === province.code || oldProvince === province.id)) {
              option.selected = true;
            }
            
            provinceDropdown.appendChild(option);
          });
          console.log(`Successfully loaded ${provinces.length} provinces into dropdown`);
        } else {
          provinceDropdown.innerHTML = '<option value="">No provinces available. Please add provinces in Address Management.</option>';
          console.warn('No provinces found in database or invalid response format');
        }
      } catch (error) {
        console.error('Error loading provinces from database:', error);
        const provinceDropdown = document.getElementById('province-select');
        if (provinceDropdown) {
          provinceDropdown.innerHTML = '<option value="">Error loading provinces. Please check your connection and try refreshing the page.</option>';
        }
      }
    }

    // Load cities based on selected province - fetches from database via API
    async function loadCities(provinceCode) {
      if (!citySelect) return;
      
      citySelect.disabled = true;
      citySelect.innerHTML = '<option value="">Loading cities...</option>';
      barangaySelect.disabled = true;
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      zipCodeInput.value = '';
      
      if (!provinceCode) {
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        citySelect.disabled = true;
        return;
      }
      
      try {
        const response = await fetch(`${baseUrl}/api/cities?province=${encodeURIComponent(provinceCode)}`);
        if (!response.ok) throw new Error('Failed to load cities');
        
        const cities = await response.json();
        
        // Clear and populate city dropdown
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        
        if (cities && cities.length > 0) {
          cities.forEach(city => {
            const option = document.createElement('option');
            option.value = city.name;
            option.textContent = city.name;
            option.setAttribute('data-zip-code', city.zip_code || '');
            
            // Preserve old value if it matches
            const oldCity = "{{ old('city_municipality', '') }}";
            if (oldCity && oldCity === city.name) {
              option.selected = true;
              zipCodeInput.value = city.zip_code || '';
            }
            
            citySelect.appendChild(option);
          });
        } else {
          citySelect.innerHTML = '<option value="">No cities available for this province. Please add cities in Address Management.</option>';
        }
        
        citySelect.disabled = false;
        
        // If old city was selected, load barangays
        if (citySelect.value) {
          loadBarangays(citySelect.value, provinceCode);
        }
      } catch (error) {
        console.error('Error loading cities from database:', error);
        citySelect.innerHTML = '<option value="">Error loading cities. Please check your connection.</option>';
        citySelect.disabled = false;
      }
    }

    // Load barangays based on selected city - fetches from database via API
    async function loadBarangays(cityName, provinceCode) {
      if (!barangaySelect) return;
      
      barangaySelect.disabled = true;
      barangaySelect.innerHTML = '<option value="">Loading barangays...</option>';
      
      if (!cityName) {
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        barangaySelect.disabled = true;
        return;
      }
      
      try {
        const response = await fetch(`${baseUrl}/api/barangays?city=${encodeURIComponent(cityName)}&province=${encodeURIComponent(provinceCode)}`);
        if (!response.ok) throw new Error('Failed to load barangays');
        
        const barangays = await response.json();
        
        // Clear and populate barangay dropdown
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        
        if (barangays && barangays.length > 0) {
          barangays.forEach(barangay => {
            const option = document.createElement('option');
            option.value = barangay.name;
            option.textContent = barangay.name;
            
            // Preserve old value if it matches
            const oldBarangay = "{{ old('barangay', '') }}";
            if (oldBarangay && oldBarangay === barangay.name) {
              option.selected = true;
            }
            
            barangaySelect.appendChild(option);
          });
        } else {
          barangaySelect.innerHTML = '<option value="">No barangays available for this city. Please add barangays in Address Management.</option>';
        }
        
        barangaySelect.disabled = false;
      } catch (error) {
        console.error('Error loading barangays from database:', error);
        barangaySelect.innerHTML = '<option value="">Error loading barangays. Please check your connection.</option>';
        barangaySelect.disabled = false;
      }
    }

    // Handle province change
    if (provinceSelect) {
      provinceSelect.addEventListener('change', function() {
        const provinceCode = this.value;
        loadCities(provinceCode);
      });
    }

    // Handle city change - auto-fills zip code from database
    if (citySelect) {
      citySelect.addEventListener('change', function() {
        const cityName = this.value;
        const provinceCode = provinceSelect ? provinceSelect.value : '';
        
        // Auto-fill zip code from selected city option (fetched from database)
        const selectedOption = this.options[this.selectedIndex];
        const zipCode = selectedOption ? selectedOption.getAttribute('data-zip-code') : '';
        
        if (zipCode && zipCode.trim() !== '') {
          zipCodeInput.value = zipCode;
        } else {
          // Fallback: fetch zip code from API if not in data attribute
          if (cityName && provinceCode) {
            fetch(`${baseUrl}/api/zip-code?city=${encodeURIComponent(cityName)}&province=${encodeURIComponent(provinceCode)}`)
              .then(response => response.json())
              .then(data => {
                if (data.zip_code) {
                  zipCodeInput.value = data.zip_code;
                }
              })
              .catch(error => {
                console.error('Error fetching zip code:', error);
              });
          } else {
            zipCodeInput.value = '';
          }
        }
        
        // Load barangays from database
        loadBarangays(cityName, provinceCode);
      });
    }

    // Initialize provinces dropdown on page load - fetches from provinces table
    // This ensures provinces from the database are loaded into the dropdown
    // Use setTimeout to ensure DOM is fully ready
    setTimeout(function() {
      const provinceDropdown = document.getElementById('province-select');
      if (provinceDropdown) {
        console.log('Initializing province dropdown...');
        loadProvinces();
      } else {
        console.error('Province dropdown element not found on page load');
        // Retry after a short delay
        setTimeout(function() {
          const retryDropdown = document.getElementById('province-select');
          if (retryDropdown) {
            console.log('Retrying province dropdown initialization...');
            loadProvinces();
          }
        }, 500);
      }
    }, 100);
    
    // Function to restore address from student data
    async function restoreAddress() {
      @php
        $homeAddress = $user->addresses()->where('type', 'home')->first();
      @endphp
      const studentProvince = @json($homeAddress->province ?? '');
      const studentCity = @json($homeAddress->city_municipality ?? '');
      const studentBarangay = @json($homeAddress->barangay ?? '');
      const studentZipCode = @json($homeAddress->zip_code ?? '');
      const studentStreet = @json($homeAddress->street ?? '');
      
      // Use old() values if validation failed, otherwise use student data
      const oldProvince = "{{ old('province', '') }}" || studentProvince;
      const oldCity = "{{ old('city_municipality', '') }}" || studentCity;
      const oldBarangay = "{{ old('barangay', '') }}" || studentBarangay;
      const oldZipCode = "{{ old('zip_code', '') }}" || studentZipCode;
      const oldStreet = "{{ old('street', '') }}" || studentStreet;
      
      // Restore street if exists
      if (oldStreet && document.getElementById('street-input')) {
        document.getElementById('street-input').value = oldStreet;
      }
      
      if (!oldProvince || !provinceSelect) {
        if (oldZipCode && zipCodeInput) {
          zipCodeInput.value = oldZipCode;
        }
        return;
      }
      
      // Wait for provinces to load
      await new Promise(resolve => setTimeout(resolve, 500));
      
      // Try to find matching province by code or name (for addresses table compatibility)
      const provinceOptions = Array.from(provinceSelect.options);
      let matchedProvince = null;
      let matchedProvinceCode = null;
      
      for (const option of provinceOptions) {
        // Match by code (if oldProvince is a code)
        if (option.value === oldProvince) {
          matchedProvince = option.value;
          matchedProvinceCode = option.value;
          break;
        }
        // Match by name (if oldProvince is a name from addresses table)
        if (option.textContent.toLowerCase().trim() === oldProvince.toLowerCase().trim()) {
          matchedProvince = option.value;
          matchedProvinceCode = option.value;
          break;
        }
      }
      
      if (matchedProvince && matchedProvinceCode) {
        provinceSelect.value = matchedProvince;
        await loadCities(matchedProvinceCode);
        
        // After cities load, try to match city
        if (oldCity && citySelect) {
          await new Promise(resolve => setTimeout(resolve, 500));
          
          const cityOptions = Array.from(citySelect.options);
          let matchedCity = null;
          
          for (const option of cityOptions) {
            // Match by exact name or case-insensitive
            if (option.value === oldCity || option.textContent.toLowerCase().trim() === oldCity.toLowerCase().trim()) {
              matchedCity = option.value;
              citySelect.value = option.value;
              
              // Set zip code
              const zipCode = option.getAttribute('data-zip-code');
              if (zipCode) {
                zipCodeInput.value = zipCode;
              } else if (oldZipCode) {
                zipCodeInput.value = oldZipCode;
              }
              
              // Load barangays
              await loadBarangays(option.value, matchedProvinceCode);
              
              // After barangays load, try to match barangay
              if (oldBarangay && barangaySelect) {
                await new Promise(resolve => setTimeout(resolve, 500));
                
                const barangayOptions = Array.from(barangaySelect.options);
                for (const brgyOption of barangayOptions) {
                  // Match by exact name or case-insensitive
                  if (brgyOption.value === oldBarangay || brgyOption.textContent.toLowerCase().trim() === oldBarangay.toLowerCase().trim()) {
                    barangaySelect.value = brgyOption.value;
                    break;
                  }
                }
              }
              break;
            }
          }
        } else if (oldZipCode && zipCodeInput) {
          zipCodeInput.value = oldZipCode;
        }
      } else if (oldZipCode && zipCodeInput) {
        zipCodeInput.value = oldZipCode;
      }
    }
    
    // Restore address on page load
    restoreAddress();
    
    // Handle PWD disability field visibility
    const isPwdYesEdit = document.getElementById('is_pwd_yes_edit');
    const isPwdNoEdit = document.getElementById('is_pwd_no_edit');
    const disabilityFieldEdit = document.getElementById('disability_specify_field_edit');
    
    function toggleDisabilityFieldEdit() {
      if (isPwdYesEdit && isPwdYesEdit.checked) {
        if (disabilityFieldEdit) {
          disabilityFieldEdit.style.display = 'block';
        }
      } else {
        if (disabilityFieldEdit) {
          disabilityFieldEdit.style.display = 'none';
          const disabilityInput = document.getElementById('disability_type_edit');
          if (disabilityInput && !isPwdYesEdit.checked) {
            // Only clear if switching to No
            if (isPwdNoEdit && isPwdNoEdit.checked) {
              disabilityInput.value = '';
            }
          }
        }
      }
    }
    
    // Set initial state
    toggleDisabilityFieldEdit();
    
    // Add event listeners
    if (isPwdYesEdit) {
      isPwdYesEdit.addEventListener('change', toggleDisabilityFieldEdit);
    }
    if (isPwdNoEdit) {
      isPwdNoEdit.addEventListener('change', toggleDisabilityFieldEdit);
    }
  });
</script>
@endsection
