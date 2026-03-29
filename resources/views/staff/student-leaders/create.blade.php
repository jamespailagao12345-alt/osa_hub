@extends('layouts.app')

@section('title', 'Add Student Leader - Personal Data Sheet')

@section('content')
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
        @include('staff.partials.sidebar')
        <main id="staffMain" class="col-md-10">
            <div class="admin-back-btn-wrap mb-3">
                <a href="{{ route('staff.student-leaders.index') }}" class="btn btn-secondary">Back</a>
            </div>
            <style>
                .personal-data-sheet {
                    background: white;
                    padding: 20px;
                    border: 1px solid #ddd;
                    margin-bottom: 20px;
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
                .section-header {
                    background-color: #003366;
                    color: white;
                    padding: 8px 12px;
                    font-weight: bold;
                    font-size: 0.9rem;
                    margin-top: 15px;
                    margin-bottom: 10px;
                    border-radius: 4px;
                }
                .form-section {
                    margin-bottom: 15px;
                }
                .form-row {
                    display: flex;
                    gap: 15px;
                    margin-bottom: 10px;
                    align-items: center;
                }
                .form-group {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                }
                .form-label {
                    font-weight: bold;
                    font-size: 0.85rem;
                    margin-bottom: 3px;
                    color: #333;
                }
                .form-control, .form-select {
                    padding: 6px 10px;
                    border: 1px solid #333;
                    border-radius: 3px;
                    font-size: 0.9rem;
                }
                .radio-group {
                    display: flex;
                    gap: 20px;
                    align-items: center;
                    flex-wrap: wrap;
                }
                .radio-option {
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                .radio-option input[type="radio"] {
                    margin: 0;
                }
                .radio-option label {
                    margin: 0;
                    font-weight: normal;
                    font-size: 0.9rem;
                }
                .photo-placeholder {
                    width: 150px;
                    height: 180px;
                    border: 1px solid #333;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #f9f9f9;
                    float: right;
                    margin-left: 20px;
                    margin-bottom: 15px;
                    flex-direction: column;
                    padding: 10px;
                }
                .photo-placeholder img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
                .photo-file-input {
                    display: none;
                }
                .photo-file-label {
                    display: inline-block;
                    padding: 6px 12px;
                    background-color: #007bff;
                    color: white;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 0.75rem;
                    margin-top: 10px;
                    text-align: center;
                }
                .photo-file-label:hover {
                    background-color: #0056b3;
                }
                .leadership-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                .leadership-table th,
                .leadership-table td {
                    border: 1px solid #333;
                    padding: 8px;
                    text-align: left;
                }
                .leadership-table th {
                    background-color: #f0f0f0;
                    font-weight: bold;
                    font-size: 0.85rem;
                }
                .leadership-table input {
                    width: 100%;
                    border: none;
                    padding: 4px;
                }
                .form-footer {
                    margin-top: 30px;
                    font-size: 0.7rem;
                    color: #666;
                    text-align: center;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
            </style>

            <div class="personal-data-sheet">
                <!-- Header -->
                <div class="form-header">
                    <div class="university-info">
                        <div class="university-name">University of Science and Technology of Southern Philippines</div>
                        <div class="campus-locations">Cagayan de Oro | Claveria | Jasaan | Oroquieta | Panaon | Villanueva | Balubal</div>
                    </div>
                    <div class="document-info">
                        Form No. 1<br>
                        Rev. 0<br>
                        Date: {{ \Carbon\Carbon::now()->format('F d, Y') }}
                    </div>
                </div>

                <!-- Title -->
                <div style="text-align: center; background-color: #003366; color: white; padding: 12px; margin: 20px 0; font-weight: bold; font-size: 1.1rem; border-radius: 4px;">
                    PERSONAL DATA SHEET
                </div>

                <form method="POST" action="{{ route('staff.student-leaders.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if($selectedOrganizationId)
                        <input type="hidden" name="organization_id" value="{{ $selectedOrganizationId }}">
                    @endif

                    <!-- Photo Placeholder (floated right) -->
                    <div class="photo-placeholder">
                        <div style="text-align: center; color: #999; font-size: 0.8rem; display: flex; flex-direction: column; align-items: center; width: 100%;">
                            <div style="margin-bottom: 10px;">📷</div>
                            <div style="margin-bottom: 10px;">2x2 Photo</div>
                            <label for="image" class="photo-file-label">Choose File</label>
                            <input type="file" name="image" id="image" accept="image/*" class="photo-file-input" onchange="previewImage(this)">
                        </div>
                    </div>
                    <div id="imagePreview" style="display: none;">
                        <img id="previewImg" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    <!-- Section 1: Complete Name -->
                    <div class="section-header">Complete Name:</div>
                    <div class="form-section">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}" required style="text-transform: uppercase;" onkeyup="this.value = this.value.toUpperCase()">
                            </div>
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name') }}" required style="text-transform: uppercase;" onkeyup="this.value = this.value.toUpperCase()">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Middle Initial</label>
                                <input type="text" name="middle_name" id="middle_name" class="form-control" value="{{ old('middle_name') }}" maxlength="1" pattern="[A-Za-z]" title="Enter only one alphabet character" style="text-transform: uppercase;" onkeyup="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Affiliation -->
                    <div class="section-header">Affiliation</div>
                    <div class="form-section">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Affiliation</label>
                                @if($selectedOrganizationId && $selectedOrganization)
                                    <input type="text" class="form-control" value="{{ $selectedOrganization->name }}" readonly style="background-color: #e9ecef;">
                                    <input type="hidden" name="organization_id" value="{{ $selectedOrganizationId }}">
                                @else
                                    <select name="organization_id" id="organization_id" class="form-select" required>
                                        <option value="">Select Organization</option>
                                        @foreach($organizations as $org)
                                            <option value="{{ $org->id }}" data-department-id="{{ $org->department_id ?? '' }}" {{ old('organization_id', $selectedOrganizationId ?? null) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="form-label">Position</label>
                                @php
                                    $positions = [
                                        'Org. Coordinator',
                                        'Org. President',
                                        'Org. Vice President for External Affairs',
                                        'Org. Vice President for Internal Affairs',
                                        'Org. Associate Secretary',
                                        'Org. General Secretary',
                                        'Org. Treasurer',
                                        'Org. Auditor',
                                        'Org. Public Relations Officers (1)',
                                        'Org. Public Relations Officers (2)',
                                        'Org. Sgt, at Arms(1)',
                                        'Org. Sgt, at Arms(2)',
                                        'Org. Year Level Representative (1)',
                                        'Org. Year Level Representative (2)',
                                        'Org. Year Level Representative (3)',
                                        'Org. Year Level Representative (4)',
                                        'Org. Ms. Representative',
                                        'Org. Mr. Representative',
                                        'Others(1)',
                                        'Others(2)',
                                    ];
                                @endphp
                                <select name="position" id="position" class="form-select">
                                    <option value="">Select Position</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos }}" {{ old('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Academic Year</label>
                                <input type="text" name="academic_year" id="academic_year" class="form-control" value="{{ old('academic_year', date('Y') . '-' . (date('Y') + 1)) }}" placeholder="YYYY-YYYY">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: PERSONAL INFORMATION -->
                    <div class="section-header">PERSONAL INFORMATION</div>
                    <div class="form-section">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="birth_date" id="birth_date" class="form-control" value="{{ old('birth_date') }}" onchange="calculateAge()">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Age</label>
                                <input type="number" name="age" id="age" class="form-control" value="{{ old('age') }}" min="1" max="100" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Civil Status</label>
                                <div class="radio-group">
                                    <div class="radio-option">
                                        <input type="radio" name="civil_status" id="civil_status_single" value="single" {{ old('civil_status') == 'single' ? 'checked' : '' }}>
                                        <label for="civil_status_single">Single</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" name="civil_status" id="civil_status_married" value="married" {{ old('civil_status') == 'married' ? 'checked' : '' }}>
                                        <label for="civil_status_married">Married</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" name="civil_status" id="civil_status_widowed" value="widowed" {{ old('civil_status') == 'widowed' ? 'checked' : '' }}>
                                        <label for="civil_status_widowed">Widowed</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" name="civil_status" id="civil_status_separated" value="separated" {{ old('civil_status') == 'separated' ? 'checked' : '' }}>
                                        <label for="civil_status_separated">Separated</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Sex</label>
                                <div class="radio-group">
                                    <div class="radio-option">
                                        <input type="radio" name="gender" id="gender_male" value="male" {{ old('gender') == 'male' ? 'checked' : '' }}>
                                        <label for="gender_male">Male</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" name="gender" id="gender_female" value="female" {{ old('gender') == 'female' ? 'checked' : '' }}>
                                        <label for="gender_female">Female</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" id="department_group" style="display: {{ ($selectedOrganization && $selectedOrganization->department_id) ? 'block' : 'none' }};">
                                <label class="form-label">Department</label>
                                <select name="department_id" id="department_id" class="form-select" {{ ($selectedOrganization && $selectedOrganization->department_id) ? 'disabled' : '' }}>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', ($selectedOrganization && $selectedOrganization->department_id == $dept->id) ? $dept->id : null) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                @if($selectedOrganization && $selectedOrganization->department_id)
                                    <input type="hidden" name="department_id" value="{{ $selectedOrganization->department_id }}">
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="form-label">Residential Address</label>
                                <input type="text" name="complete_home_address" id="complete_home_address" class="form-control" value="{{ old('complete_home_address') }}" placeholder="Complete Address">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Mobile No.</label>
                                <input type="text" name="contact_number" id="contact_number" class="form-control" value="{{ old('contact_number') }}" placeholder="09XXXXXXXXX">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: STUDENT DETAILS -->
                    <div class="section-header">STUDENT DETAILS</div>
                    <div class="form-section">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Course</label>
                                <select name="course_id" id="course_id" class="form-select">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" data-department-id="{{ $course->department_id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Student ID No.</label>
                                <input type="text" name="user_id" id="user_id" class="form-control" value="{{ old('user_id') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Year Level</label>
                                <div class="radio-group">
                                    <div class="radio-option">
                                        <input type="radio" name="year_level" id="year_level_1" value="1" {{ old('year_level') == '1' ? 'checked' : '' }}>
                                        <label for="year_level_1">First Year</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" name="year_level" id="year_level_2" value="2" {{ old('year_level') == '2' ? 'checked' : '' }}>
                                        <label for="year_level_2">Second Year</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" name="year_level" id="year_level_3" value="3" {{ old('year_level') == '3' ? 'checked' : '' }}>
                                        <label for="year_level_3">Third Year</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" name="year_level" id="year_level_4" value="4" {{ old('year_level') == '4' ? 'checked' : '' }}>
                                        <label for="year_level_4">Fourth Year</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: LEADERSHIP BACKGROUND -->
                    <div class="section-header">LEADERSHIP BACKGROUND</div>
                    <div class="form-section">
                        <table class="leadership-table">
                            <thead>
                                <tr>
                                    <th>Organization</th>
                                    <th>Position</th>
                                    <th>Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 10; $i++)
                                <tr>
                                    <td><input type="text" name="leadership_org[]" value="{{ old('leadership_org.' . $i) }}"></td>
                                    <td><input type="text" name="leadership_position[]" value="{{ old('leadership_position.' . $i) }}"></td>
                                    <td><input type="text" name="leadership_year[]" value="{{ old('leadership_year.' . $i) }}" placeholder="YYYY-YYYY"></td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden fields for password -->
                    <input type="hidden" name="password" value="temp123456">
                    <input type="hidden" name="password_confirmation" value="temp123456">

                    <!-- Form Footer -->
                    <div class="form-footer">
                        <div>C.M. Recto Avenue, Cagayan de Oro City 9000 Philippines</div>
                        <div>Tel. Nos. +63 (88) 856 1738 | +63 (88) 856 4080 | www.ustp.edu.ph</div>
                    </div>

                    <div style="clear: both; margin-top: 20px; text-align: center;">
                        <button type="submit" class="btn btn-primary btn-lg">Submit Personal Data Sheet</button>
                        <a href="{{ route('staff.student-leaders.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
function calculateAge() {
    const birthDate = document.getElementById('birth_date').value;
    if (birthDate) {
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        document.getElementById('age').value = age;
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const placeholder = document.querySelector('.photo-placeholder');
            placeholder.innerHTML = '<img src="' + e.target.result + '" id="previewImg" alt="Preview">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Check if organization is academic (has department_id) and autofill department, filter courses
document.addEventListener('DOMContentLoaded', function() {
    const organizationSelect = document.getElementById('organization_id');
    const organizationInput = document.querySelector('input[name="organization_id"][type="hidden"]');
    const departmentGroup = document.getElementById('department_group');
    const departmentSelect = document.getElementById('department_id');
    const courseSelect = document.getElementById('course_id');
    
    // If organization is read-only (provided via URL), get the organization ID from hidden input
    const currentOrgId = organizationInput ? organizationInput.value : (organizationSelect ? organizationSelect.value : null);
    
    // Store all courses for dynamic filtering
    const allCourses = Array.from(courseSelect.querySelectorAll('option')).map(opt => ({
        value: opt.value,
        text: opt.text,
        departmentId: opt.getAttribute('data-department-id'),
        element: opt
    }));
    
    function checkOrganization() {
        let orgDepartmentId = null;
        let isAcademic = false;
        let hasOrganization = false;
        
        // Handle case where organization is read-only (from URL parameter)
        if (organizationInput && !organizationSelect) {
            // Organization is already set via URL
            hasOrganization = true;
            @if($selectedOrganization && $selectedOrganization->department_id)
                orgDepartmentId = @json($selectedOrganization->department_id);
                isAcademic = true;
            @endif
        } else if (organizationSelect) {
            // Handle case where organization is a select dropdown
            const selectedOption = organizationSelect.options[organizationSelect.selectedIndex];
            hasOrganization = selectedOption && selectedOption.value;
            orgDepartmentId = selectedOption ? selectedOption.getAttribute('data-department-id') : null;
            isAcademic = orgDepartmentId && orgDepartmentId !== '';
        } else {
            return; // No organization field found
        }
        
        if (hasOrganization) {
            if (isAcademic) {
                // Organization is academic - autofill department and disable it
                departmentGroup.style.display = 'block';
                departmentSelect.value = orgDepartmentId;
                departmentSelect.disabled = true;
                departmentSelect.style.backgroundColor = '#e9ecef';
                
                // Add hidden input to ensure value is submitted
                let hiddenInput = departmentGroup.querySelector('input[type="hidden"][name="department_id"]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'department_id';
                    hiddenInput.value = orgDepartmentId;
                    departmentGroup.appendChild(hiddenInput);
                } else {
                    hiddenInput.value = orgDepartmentId;
                }
                
                // Filter courses to only show those from this department
                filterCoursesByDepartment(orgDepartmentId);
            } else {
                // Organization is non-academic - show department field but allow selection
                departmentGroup.style.display = 'block';
                departmentSelect.disabled = false;
                departmentSelect.style.backgroundColor = '';
                
                // Remove hidden input if it exists
                const hiddenInput = departmentGroup.querySelector('input[type="hidden"][name="department_id"]');
                if (hiddenInput) {
                    hiddenInput.remove();
                }
                
                // If department is already selected, filter courses by that department
                if (departmentSelect.value) {
                    filterCoursesByDepartment(departmentSelect.value);
                } else {
                    // Show all courses if no department selected
                    showAllCourses();
                }
            }
        } else {
            // No organization selected - hide department field
            departmentGroup.style.display = 'none';
            departmentSelect.value = '';
            showAllCourses();
        }
    }
    
    function filterCoursesByDepartment(departmentId) {
        // Clear current options except the first "Select Course" option
        courseSelect.innerHTML = '<option value="">Select Course</option>';
        
        // Convert departmentId to string for consistent comparison
        // (getAttribute returns strings, but JSON may return numbers)
        const departmentIdStr = String(departmentId);
        
        // Add only courses that match the department
        allCourses.forEach(course => {
            if (course.value === '') return; // Skip the empty option we just added
            
            // Convert course.departmentId to string for comparison
            if (String(course.departmentId) === departmentIdStr) {
                const option = document.createElement('option');
                option.value = course.value;
                option.textContent = course.text;
                option.setAttribute('data-department-id', course.departmentId);
                courseSelect.appendChild(option);
            }
        });
    }
    
    function showAllCourses() {
        // Restore all courses
        courseSelect.innerHTML = '<option value="">Select Course</option>';
        allCourses.forEach(course => {
            if (course.value === '') return;
            const option = document.createElement('option');
            option.value = course.value;
            option.textContent = course.text;
            option.setAttribute('data-department-id', course.departmentId);
            courseSelect.appendChild(option);
        });
    }
    
    // Handle organization change (only if it's a select, not read-only)
    if (organizationSelect) {
        organizationSelect.addEventListener('change', checkOrganization);
    }
    
    // Handle department change (for non-academic organizations)
    departmentSelect.addEventListener('change', function() {
        let orgDepartmentId = null;
        let isAcademic = false;
        
        // Check if organization is read-only (pre-selected via URL)
        if (organizationInput && !organizationSelect) {
            // Organization is read-only - get department ID from selected organization data
            @if($selectedOrganization && $selectedOrganization->department_id)
                orgDepartmentId = @json($selectedOrganization->department_id);
                isAcademic = true;
            @endif
        } else if (organizationSelect) {
            // Organization is a select dropdown - get department ID from selected option
            const selectedOption = organizationSelect.options?.[organizationSelect.selectedIndex];
            orgDepartmentId = selectedOption ? selectedOption.getAttribute('data-department-id') : null;
            isAcademic = orgDepartmentId && orgDepartmentId !== '';
        }
        
        // Only filter courses if organization is NOT academic (academic orgs already filtered)
        if (!isAcademic && departmentSelect.value) {
            filterCoursesByDepartment(departmentSelect.value);
        } else if (!isAcademic && !departmentSelect.value) {
            showAllCourses();
        }
    });
    
    // Check on page load
    checkOrganization();
    
    // Fetch past organizations when user_id or email is entered
    const userIdInput = document.getElementById('user_id');
    const emailInput = document.getElementById('email');
    const currentOrganizationId = @json($selectedOrganizationId ?? null);
    
    function fetchPastOrganizations() {
        const userId = userIdInput.value.trim();
        const email = emailInput.value.trim();
        
        if (!userId && !email) {
            clearLeadershipBackground();
            return;
        }
        
        // Debounce the API call
        clearTimeout(window.fetchOrganizationsTimeout);
        window.fetchOrganizationsTimeout = setTimeout(function() {
            fetch('{{ route("staff.student-leaders.fetch-past-organizations") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId,
                    email: email,
                    current_organization_id: currentOrganizationId
                })
            })
            .then(response => response.json())
            .then(data => {
                populateLeadershipBackground(data.past_organizations || []);
            })
            .catch(error => {
                console.error('Error fetching past organizations:', error);
            });
        }, 500); // Wait 500ms after user stops typing
    }
    
    function populateLeadershipBackground(pastOrgs) {
        const tbody = document.querySelector('.leadership-table tbody');
        const rows = tbody.querySelectorAll('tr');
        
        // Clear all rows first
        rows.forEach(row => {
            const inputs = row.querySelectorAll('input');
            inputs[0].value = ''; // organization
            inputs[1].value = ''; // position
            inputs[2].value = ''; // year
        });
        
        // Populate with past organizations (reverse chronological order - newest first)
        pastOrgs.forEach((org, index) => {
            if (index < rows.length) {
                const inputs = rows[index].querySelectorAll('input');
                inputs[0].value = org.organization_name || '';
                inputs[1].value = org.position || '';
                inputs[2].value = org.year || '';
            }
        });
    }
    
    function clearLeadershipBackground() {
        const tbody = document.querySelector('.leadership-table tbody');
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            const inputs = row.querySelectorAll('input');
            inputs[0].value = '';
            inputs[1].value = '';
            inputs[2].value = '';
        });
    }
    
    // Add event listeners for user_id and email
    if (userIdInput) {
        userIdInput.addEventListener('input', fetchPastOrganizations);
        userIdInput.addEventListener('blur', fetchPastOrganizations);
    }
    if (emailInput) {
        emailInput.addEventListener('input', fetchPastOrganizations);
        emailInput.addEventListener('blur', fetchPastOrganizations);
    }
    
    // Fetch on page load if values are already present (from old input)
    if ((userIdInput && userIdInput.value) || (emailInput && emailInput.value)) {
        fetchPastOrganizations();
    }
});
</script>
@endpush
@endsection