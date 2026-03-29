@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            @include('admin.partials.sidebar')
            <main class="col-md-10">
                <div class="admin-back-btn-wrap">
                    <a href="{{ route('admin.show-staff') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
                </div>
                <style>
                    .section-header { display:block; width:100%; box-sizing:border-box; background-color: midnightblue; color: white; padding:.5rem 1rem; border:none; border-radius:0; }
                </style>
                <h3 class="mt-4"><span class="section-header">Add Staff</span></h3>
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <form method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data" class="card p-4 mb-4">
                    @csrf
                    <table class="table table-borderless">
                        <tbody>
                <tr>
                    <td colspan="2">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                                <small class="text-muted">Email</small>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="contact_number" id="contact_number" class="form-control" placeholder="Contact">
                                <small class="text-muted">Contact Number</small>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="bi bi-info-circle"></i> Staff ID will be auto-generated (7 digits)</small>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First" required>
                                <small class="text-muted">First Name</small>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="middle_name" id="middle_name" class="form-control" placeholder="M" maxlength="1" pattern="[A-Za-z]" title="Enter only one alphabet character">
                                <small class="text-muted">Middle Initial</small>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last" required>
                                <small class="text-muted">Last Name</small>
                            </div>
                        </div>
                    </td>
                </tr>
                
                    

                    <tr>
                        <td colspan="2">
                            <div class="row g-2">
                                <div class="col-md-6" data-birth-age-pair>
                                    @include('components.birthdate_with_age', ['name' => 'birth_date', 'ageName' => 'age', 'value' => old('birth_date')])
                                    <small class="text-muted">Birth Date</small>
                                </div>
                                <div class="col-md-6">
                                    <select name="gender" id="gender" class="form-select">
                                        <option value="">Select Sex</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                    <small class="text-muted">Sex</small>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div>
                                        <label class="form-label">Department-Related Organization</label>
                                        <div id="department-org-container">
                                            <p class="text-muted small mb-2">Select a department first to see department-related organization</p>
                                        </div>
                                        <small class="text-muted d-block">Can select only one (if department is selected)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div>
                                        <label class="form-label">Non-Academic Organizations</label>
                                        <div id="non-academic-org-container" class="border p-3 rounded" style="max-height: 300px; overflow-y: auto; pointer-events: auto; position: relative; z-index: 1;">
                                            <p class="text-muted small mb-2">Loading organizations...</p>
                                        </div>
                                        <small class="text-muted d-block">Can select multiple non-academic organizations (available to all departments)</small>
                                    </div>
                                </div>
                                <div class="col-md-4 ms-1">
                                    <select name="department_id" id="department_id" class="form-select">
                                        <option value="">Select Dept</option>
                                        @isset($departments)
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        @endisset
                                    </select>
                                    <small class="text-muted">Department</small>
                                </div>
                                <div class="col-md-4">
                                    <select name="designation" id="designation" class="form-select" required>
                                        <option value="">Select Desig</option>
                                        @php
                                            $designations = \App\Models\Designation::all();
                                        @endphp
                                        @foreach($designations as $designation)
                                            <option value="{{ $designation->name }}">{{ $designation->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Designation</small>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="row g-2 align-items-start">
                                <div class="col-md-4">
                                    <input type="file" name="special_order" id="special_order" class="form-control" accept=".pdf,.doc,.docx">
                                    <small class="text-muted">S.O. (Special Order)</small>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex gap-2">
                                        <div class="flex-fill">
                                            <input type="date" name="contract_start_at" id="contract_start_at" class="form-control" placeholder="Start Date">
                                        </div>
                                        <div class="flex-fill">
                                            <input type="date" name="contract_end_at" id="contract_end_at" class="form-control" placeholder="End Date">
                                        </div>
                                    </div>
                                    <small class="text-muted">Effectivity Date (Contract Start -  Contract End)</small>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="length_of_service" id="length_of_service" class="form-control" min="0" placeholder="Yrs" readonly>
                                    <small class="text-muted">Length of Service (Years)</small>
                                </div>
                                <div class="col-md-4">
                                    <div id="countdown-timer" class="form-control" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-height: 38px; display: flex; align-items: center; padding: 0.375rem 0.75rem; font-weight: 500; color: #495057;">
                                        <span id="countdown-display" class="text-muted">Enter Contract Start Date and Contract End Date to see countdown</span>
                                    </div>
                                    <small class="text-muted">Length of Service (Live Countdown)</small>
                                </div>
                                
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required style="padding-right: 2.5rem;">
                                        <button type="button" class="btn btn-link position-absolute p-0" id="togglePassword" style="border: none; background: none; cursor: pointer; z-index: 10; right: 0.75rem; top: 50%; transform: translateY(-50%); height: auto; line-height: 1;">
                                            <i class="bi bi-eye" id="passwordIcon" style="font-size: 0.875rem; vertical-align: middle; color: black;"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm Password" required style="padding-right: 2.5rem;">
                                        <button type="button" class="btn btn-link position-absolute p-0" id="togglePasswordConfirmation" style="border: none; background: none; cursor: pointer; z-index: 10; right: 0.75rem; top: 50%; transform: translateY(-50%); height: auto; line-height: 1;">
                                            <i class="bi bi-eye" id="passwordConfirmationIcon" style="font-size: 0.875rem; vertical-align: middle; color: black;"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                                    <small class="text-muted">Profile Image</small>
                                </div>
                            </div>
                        </td>
                    </tr>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-success">Add Staff</button>
                </form>
            </main>
        </div>
    </div>
@endsection
                @push('scripts')
                <script>
                document.addEventListener('DOMContentLoaded', function(){
                    // Convert all text inputs to uppercase (except email, password, and number fields)
                    const textInputs = document.querySelectorAll('input[type="text"], input[type="tel"], textarea');
                    textInputs.forEach(input => {
                        // Skip email, password, and number fields
                        if (input.type === 'email' || input.type === 'password' || input.type === 'number' || input.name === 'email' || input.name === 'password' || input.name === 'password_confirmation') {
                            return;
                        }
                        
                        // Special handling for middle_name (middle initial) - only allow one letter
                        if (input.name === 'middle_name' || input.id === 'middle_name') {
                            input.addEventListener('input', function(e) {
                                // Remove any non-alphabetic characters and keep only first letter
                                this.value = this.value.replace(/[^A-Za-z]/g, '').substring(0, 1).toUpperCase();
                            });
                            
                            input.addEventListener('keypress', function(e) {
                                // Only allow alphabetic characters
                                const char = String.fromCharCode(e.which);
                                if (!/[A-Za-z]/.test(char)) {
                                    e.preventDefault();
                                }
                                // If already has a character, prevent adding more
                                if (this.value.length >= 1) {
                                    e.preventDefault();
                                }
                            });
                            
                            input.addEventListener('paste', function(e) {
                                e.preventDefault();
                                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                                const firstLetter = pastedText.replace(/[^A-Za-z]/g, '').substring(0, 1).toUpperCase();
                                this.value = firstLetter;
                            });
                            return;
                        }
                        
                        // Convert to uppercase on input
                        input.addEventListener('input', function(e) {
                            const cursorPosition = this.selectionStart;
                            this.value = this.value.toUpperCase();
                            // Restore cursor position
                            this.setSelectionRange(cursorPosition, cursorPosition);
                        });
                        
                        // Also convert on paste
                        input.addEventListener('paste', function(e) {
                            setTimeout(() => {
                                const cursorPosition = this.selectionStart;
                                this.value = this.value.toUpperCase();
                                this.setSelectionRange(cursorPosition, cursorPosition);
                            }, 0);
                        });
                    });
                    const deptSel = document.getElementById('department_id');
                    const deptOrgContainer = document.getElementById('department-org-container');
                    const nonAcademicContainer = document.getElementById('non-academic-org-container');
                    let selectedDeptOrgId = null;
                    let selectedNonAcademicIds = new Set();

                    // Save currently selected organizations before reloading
                    function saveSelectedOrganizations() {
                        // Save department org (radio button)
                        const deptRadio = deptOrgContainer.querySelector('input[type="radio"]:checked');
                        selectedDeptOrgId = deptRadio ? deptRadio.value : null;
                        
                        // Save non-academic orgs (checkboxes)
                        selectedNonAcademicIds.clear();
                        const checkboxes = nonAcademicContainer.querySelectorAll('input[type="checkbox"]:checked');
                        checkboxes.forEach(cb => selectedNonAcademicIds.add(cb.value));
                    }

                    // Restore selected organizations after reloading
                    function restoreSelectedOrganizations() {
                        // Restore department org
                        if (selectedDeptOrgId) {
                            const radio = deptOrgContainer.querySelector(`input[type="radio"][value="${selectedDeptOrgId}"]`);
                            if (radio) {
                                radio.checked = true;
                            }
                        }
                        
                        // Restore non-academic orgs
                        selectedNonAcademicIds.forEach(orgId => {
                            const checkbox = nonAcademicContainer.querySelector(`input[type="checkbox"][value="${orgId}"]`);
                            if (checkbox) {
                                checkbox.checked = true;
                            }
                        });
                    }

                    async function loadNonAcademicOrganizations(){
                        try {
                            // Always fetch non-academic organizations (those without department_id)
                            // This ensures they remain available regardless of department selection
                            const res = await fetch(`/api/organizations?unassigned=1`);
                            const data = await res.json();
                            
                            // Save current selections before clearing
                            saveSelectedOrganizations();
                            
                            // Clear container
                            nonAcademicContainer.innerHTML = '';
                            
                            // Ensure container is clickable
                            nonAcademicContainer.style.pointerEvents = 'auto';
                            nonAcademicContainer.style.cursor = 'default';
                            
                            if (!data || data.length === 0) {
                                nonAcademicContainer.innerHTML = '<p class="text-muted small mb-2">No non-academic organizations available</p>';
                                return;
                            }
                            
                            // Sort organizations by name (case-insensitive, same as edit-staff page)
                            data.sort((a, b) => a.name.toLowerCase().localeCompare(b.name.toLowerCase()));
                            
                            // Create checkboxes for each non-academic organization
                            data.forEach(org => {
                                const div = document.createElement('div');
                                div.className = 'form-check mb-2';
                                div.style.pointerEvents = 'auto';
                                div.style.cursor = 'pointer';
                                
                                const checkbox = document.createElement('input');
                                checkbox.className = 'form-check-input non-academic-org';
                                checkbox.type = 'checkbox';
                                checkbox.name = 'organization_ids[]';
                                checkbox.id = `org${org.id}`;
                                checkbox.value = org.id;
                                
                                // Ensure checkbox is always enabled (never disabled)
                                checkbox.disabled = false;
                                checkbox.style.pointerEvents = 'auto';
                                checkbox.style.cursor = 'pointer';
                                
                                // Add click event listener to ensure it works
                                checkbox.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    // Checkbox state will be handled by default behavior
                                });
                                
                                const label = document.createElement('label');
                                label.className = 'form-check-label';
                                label.htmlFor = `org${org.id}`;
                                label.textContent = org.name;
                                label.style.pointerEvents = 'auto';
                                label.style.cursor = 'pointer';
                                
                                // Add click event to label as well
                                label.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    // Toggle checkbox when label is clicked
                                    checkbox.checked = !checkbox.checked;
                                    checkbox.dispatchEvent(new Event('change'));
                                });
                                
                                div.appendChild(checkbox);
                                div.appendChild(label);
                                nonAcademicContainer.appendChild(div);
                            });
                            
                            // Restore selected non-academic organizations
                            restoreSelectedOrganizations();
                        } catch (e) {
                            console.error('Error loading non-academic organizations:', e);
                            nonAcademicContainer.innerHTML = '<p class="text-muted small mb-2">Error loading non-academic organizations</p>';
                        }
                    }

                    async function loadDepartmentOrganization(deptId){
                        // Save current selections
                        saveSelectedOrganizations();
                        
                        try {
                            if (deptId) {
                                // Fetch department-related organizations for this department
                                const res = await fetch(`/api/organizations?department_id=${encodeURIComponent(deptId)}`);
                                const data = await res.json();
                                
                                // Filter to only department-related orgs (those with department_id matching the selected dept)
                                const deptOrgs = data.filter(org => org.department_id != null && parseInt(org.department_id) === parseInt(deptId));
                                
                                // Clear container
                                deptOrgContainer.innerHTML = '';
                                
                                if (deptOrgs.length > 0) {
                                    // Create radio buttons for department-related organizations
                                    // Only one can be selected
                                    deptOrgs.forEach(org => {
                                        const div = document.createElement('div');
                                        div.className = 'form-check mb-2';
                                        
                                        const radio = document.createElement('input');
                                        radio.className = 'form-check-input department-org';
                                        radio.type = 'radio';
                                        radio.name = 'department_organization_id';
                                        radio.id = `dept-org${org.id}`;
                                        radio.value = org.id;
                                        
                                        // Handle radio button change - add to organization_ids
                                        radio.addEventListener('change', function() {
                                            if (this.checked) {
                                                // Uncheck other department org radios
                                                deptOrgContainer.querySelectorAll('input[type="radio"]').forEach(r => {
                                                    if (r !== this) r.checked = false;
                                                });
                                                
                                                // Remove any existing department org hidden inputs
                                                deptOrgContainer.querySelectorAll('.dept-org-hidden').forEach(el => el.remove());
                                                
                                                // Add this org to organization_ids
                                                const hidden = document.createElement('input');
                                                hidden.type = 'hidden';
                                                hidden.name = 'organization_ids[]';
                                                hidden.value = org.id;
                                                hidden.className = 'dept-org-hidden';
                                                deptOrgContainer.appendChild(hidden);
                                            } else {
                                                // Remove from organization_ids
                                                const hiddenInput = deptOrgContainer.querySelector(`input[name="organization_ids[]"][value="${org.id}"].dept-org-hidden`);
                                                if (hiddenInput) {
                                                    hiddenInput.remove();
                                                }
                                            }
                                        });
                                        
                                        const label = document.createElement('label');
                                        label.className = 'form-check-label';
                                        label.htmlFor = `dept-org${org.id}`;
                                        label.textContent = org.name;
                                        
                                        div.appendChild(radio);
                                        div.appendChild(label);
                                        deptOrgContainer.appendChild(div);
                                    });
                                } else {
                                    deptOrgContainer.innerHTML = '<p class="text-muted small mb-2">No department-related organization found for this department</p>';
                                    // Remove any hidden department org inputs
                                    deptOrgContainer.querySelectorAll('.dept-org-hidden').forEach(el => el.remove());
                                }
                            } else {
                                // No department selected - clear department org container
                                deptOrgContainer.innerHTML = '<p class="text-muted small mb-2">Select a department first to see department-related organization</p>';
                                // Remove any hidden department org inputs
                                deptOrgContainer.querySelectorAll('.dept-org-hidden').forEach(el => el.remove());
                            }
                            
                            // Restore selected organizations
                            restoreSelectedOrganizations();
                            
                            // If department org was restored, trigger change to add to organization_ids
                            if (selectedDeptOrgId) {
                                const restoredRadio = deptOrgContainer.querySelector(`input[type="radio"][value="${selectedDeptOrgId}"]`);
                                if (restoredRadio) {
                                    restoredRadio.dispatchEvent(new Event('change'));
                                }
                            }
                        } catch (e) {
                            console.error('Error loading department organization:', e);
                            deptOrgContainer.innerHTML = '<p class="text-muted small mb-2">Error loading department organization</p>';
                        }
                    }

                    // Ensure non-academic container is ready and clickable
                    if (nonAcademicContainer) {
                        nonAcademicContainer.style.pointerEvents = 'auto';
                        nonAcademicContainer.style.position = 'relative';
                        nonAcademicContainer.style.zIndex = '1';
                    }
                    
                    if (deptSel) {
                        deptSel.addEventListener('change', ()=>{
                            const id = deptSel.value || '';
                            loadDepartmentOrganization(id);
                            // Ensure non-academic organizations remain visible and selectable
                            // They should always be available regardless of department selection
                            // Reload to ensure they're fresh and clickable
                            setTimeout(() => {
                                loadNonAcademicOrganizations();
                            }, 100);
                        });

                        // Load non-academic organizations on page load
                        // Use setTimeout to ensure DOM is fully ready
                        setTimeout(() => {
                            loadNonAcademicOrganizations();
                        }, 100);
                        
                        // Load department organization if department is already selected
                        if (deptSel.value) {
                            loadDepartmentOrganization(deptSel.value);
                        }
                    } else {
                        // If department select doesn't exist, still load non-academic organizations
                        setTimeout(() => {
                            loadNonAcademicOrganizations();
                        }, 100);
                    }
                    
                    // Add a global click handler to ensure checkboxes work
                    document.addEventListener('click', function(e) {
                        // If clicking on a non-academic org checkbox or label, ensure it's handled
                        if (e.target && (e.target.classList.contains('non-academic-org') || e.target.closest('.non-academic-org'))) {
                            const checkbox = e.target.type === 'checkbox' ? e.target : e.target.closest('.form-check')?.querySelector('.non-academic-org');
                            if (checkbox && checkbox.disabled) {
                                checkbox.disabled = false;
                            }
                        }
                    });
                    
                    // Password confirmation validation
                    const passwordField = document.getElementById('password');
                    const passwordConfirmationField = document.getElementById('password_confirmation');
                    
                    function validatePasswordMatch() {
                        if (passwordConfirmationField.value && passwordField.value !== passwordConfirmationField.value) {
                            passwordConfirmationField.setCustomValidity('Passwords do not match');
                            passwordConfirmationField.classList.add('is-invalid');
                        } else {
                            passwordConfirmationField.setCustomValidity('');
                            passwordConfirmationField.classList.remove('is-invalid');
                        }
                    }
                    
                    if (passwordField && passwordConfirmationField) {
                        passwordField.addEventListener('input', validatePasswordMatch);
                        passwordConfirmationField.addEventListener('input', validatePasswordMatch);
                    }
                    
                    // Password visibility toggle
                    const togglePasswordBtn = document.getElementById('togglePassword');
                    const togglePasswordConfirmationBtn = document.getElementById('togglePasswordConfirmation');
                    const passwordIcon = document.getElementById('passwordIcon');
                    const passwordConfirmationIcon = document.getElementById('passwordConfirmationIcon');
                    
                    if (togglePasswordBtn && passwordField) {
                        togglePasswordBtn.addEventListener('click', function() {
                            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                            passwordField.setAttribute('type', type);
                            passwordIcon.classList.toggle('bi-eye');
                            passwordIcon.classList.toggle('bi-eye-slash');
                        });
                    }
                    
                    if (togglePasswordConfirmationBtn && passwordConfirmationField) {
                        togglePasswordConfirmationBtn.addEventListener('click', function() {
                            const type = passwordConfirmationField.getAttribute('type') === 'password' ? 'text' : 'password';
                            passwordConfirmationField.setAttribute('type', type);
                            passwordConfirmationIcon.classList.toggle('bi-eye');
                            passwordConfirmationIcon.classList.toggle('bi-eye-slash');
                        });
                    }
                    
                    // Length of Service Countdown Timer
                    const contractStartInput = document.getElementById('contract_start_at');
                    const contractEndInput = document.getElementById('contract_end_at');
                    const lengthOfServiceInput = document.getElementById('length_of_service');
                    const countdownDisplay = document.getElementById('countdown-display');
                    const countdownTimer = document.getElementById('countdown-timer');
                    let countdownInterval = null;
                    
                    function calculateLengthOfService(startDate, endDate) {
                        if (!startDate || !endDate) return null;
                        
                        const start = new Date(startDate);
                        start.setHours(0, 0, 0, 0); // Set to start of day
                        const end = new Date(endDate);
                        end.setHours(23, 59, 59, 999); // Set to end of day
                        
                        // Calculate difference in milliseconds (end date minus start date)
                        const diffTime = end - start;
                        
                        if (diffTime < 0) {
                            // End date is before start date (invalid)
                            return {
                                years: 0,
                                months: 0,
                                weeks: 0,
                                days: 0,
                                hours: 0,
                                minutes: 0,
                                seconds: 0,
                                totalYears: 0,
                                expired: true,
                                invalid: true
                            };
                        }
                        
                        // Calculate years (approximate)
                        const years = Math.floor(diffTime / (365.25 * 24 * 60 * 60 * 1000));
                        const remainingAfterYears = diffTime % (365.25 * 24 * 60 * 60 * 1000);
                        
                        // Calculate months (approximate)
                        const months = Math.floor(remainingAfterYears / (30.44 * 24 * 60 * 60 * 1000));
                        const remainingAfterMonths = remainingAfterYears % (30.44 * 24 * 60 * 60 * 1000);
                        
                        // Calculate weeks
                        const weeks = Math.floor(remainingAfterMonths / (7 * 24 * 60 * 60 * 1000));
                        const remainingAfterWeeks = remainingAfterMonths % (7 * 24 * 60 * 60 * 1000);
                        
                        // Calculate days
                        const days = Math.floor(remainingAfterWeeks / (24 * 60 * 60 * 1000));
                        const remainingAfterDays = remainingAfterWeeks % (24 * 60 * 60 * 1000);
                        
                        // Calculate hours
                        const hours = Math.floor(remainingAfterDays / (60 * 60 * 1000));
                        const remainingAfterHours = remainingAfterDays % (60 * 60 * 1000);
                        
                        // Calculate minutes
                        const minutes = Math.floor(remainingAfterHours / (60 * 1000));
                        const remainingAfterMinutes = remainingAfterHours % (60 * 1000);
                        
                        // Calculate seconds
                        const seconds = Math.floor(remainingAfterMinutes / 1000);
                        
                        // Total years for length of service (with decimals for precision)
                        const totalYears = diffTime / (365.25 * 24 * 60 * 60 * 1000);
                        
                        return {
                            years,
                            months,
                            weeks,
                            days,
                            hours,
                            minutes,
                            seconds,
                            totalYears,
                            expired: false
                        };
                    }
                    
                    function updateCountdown() {
                        const startDate = contractStartInput.value;
                        const endDate = contractEndInput.value;
                        
                        if (!startDate || !endDate) {
                            countdownDisplay.textContent = 'Enter Contract Start Date and Effectivity Date to see countdown';
                            countdownDisplay.className = 'text-muted';
                            countdownTimer.style.backgroundColor = '#f8f9fa';
                            lengthOfServiceInput.value = '';
                            return;
                        }
                        
                        const result = calculateLengthOfService(startDate, endDate);
                        
                        if (!result) {
                            countdownDisplay.textContent = 'Invalid date';
                            countdownDisplay.className = 'text-danger';
                            countdownTimer.style.backgroundColor = '#f8f9fa';
                            lengthOfServiceInput.value = '';
                            return;
                        }
                        
                        if (result.invalid) {
                            countdownDisplay.textContent = 'Effectivity Date must be after Contract Start Date';
                            countdownDisplay.className = 'text-danger fw-bold';
                            countdownTimer.style.backgroundColor = '#f8d7da';
                            lengthOfServiceInput.value = '0';
                        } else if (result.expired) {
                            countdownDisplay.textContent = 'Contract has expired';
                            countdownDisplay.className = 'text-danger fw-bold';
                            countdownTimer.style.backgroundColor = '#f8d7da';
                            lengthOfServiceInput.value = '0';
                        } else {
                            // Format countdown display with indicators: 2 yr)-6(mnths)-2(wks)-5(days)-20(hrs)-34(min)
                            const formatYears = (value) => `${value} yr)`;
                            const formatMonths = (value) => `${value}(mnths)`;
                            const formatWeeks = (value) => `${value}(wks)`;
                            const formatDays = (value) => `${value}(days)`;
                            const formatHours = (value) => `${value}(hrs)`;
                            const formatMinutes = (value) => `${value}(min)`;
                            
                            const display = [
                                formatYears(result.years),
                                formatMonths(result.months),
                                formatWeeks(result.weeks),
                                formatDays(result.days),
                                formatHours(result.hours),
                                formatMinutes(result.minutes)
                            ].join('-');
                            
                            countdownDisplay.textContent = display;
                            countdownDisplay.className = 'text-danger fw-bold';
                            countdownDisplay.style.color = '#dc3545';
                            countdownTimer.style.backgroundColor = '#f8f9fa';
                            
                            // Update length of service (years with 2 decimal places)
                            lengthOfServiceInput.value = result.totalYears.toFixed(2);
                        }
                    }
                    
                    function startCountdown() {
                        // Clear existing interval
                        if (countdownInterval) {
                            clearInterval(countdownInterval);
                        }
                        
                        // Update immediately
                        updateCountdown();
                        
                        // Update every second
                        countdownInterval = setInterval(updateCountdown, 1000);
                    }
                    
                    function stopCountdown() {
                        if (countdownInterval) {
                            clearInterval(countdownInterval);
                            countdownInterval = null;
                        }
                    }
                    
                    if (contractStartInput && contractEndInput && countdownDisplay) {
                        // Update on start date change
                        contractStartInput.addEventListener('change', function() {
                            if (this.value && contractEndInput.value) {
                                startCountdown();
                            } else {
                                stopCountdown();
                                updateCountdown();
                            }
                        });
                        
                        // Update on end date change
                        contractEndInput.addEventListener('change', function() {
                            if (this.value && contractStartInput.value) {
                                startCountdown();
                            } else {
                                stopCountdown();
                                updateCountdown();
                            }
                        });
                        
                        // Start countdown if both dates are already set
                        if (contractStartInput.value && contractEndInput.value) {
                            startCountdown();
                        }
                        
                        // Also handle input event for real-time updates
                        contractStartInput.addEventListener('input', function() {
                            if (this.value && contractEndInput.value && this.type === 'date') {
                                startCountdown();
                            }
                        });
                        
                        contractEndInput.addEventListener('input', function() {
                            if (this.value && contractStartInput.value && this.type === 'date') {
                                startCountdown();
                            }
                        });
                    }
                });
                </script>
                @endpush