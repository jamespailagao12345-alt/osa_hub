
@extends('layouts.app')

@section('title', 'Make Appointment with OSA Balubal')

@php
    // Ensure $concerns is defined when this view is included from pages
    // that don't pass it (e.g., welcome page). Use designations as default.
    if (! isset($concerns)) {
        try {
            $concerns = \App\Models\Designation::orderBy('name')->pluck('name')->toArray();
        } catch (\Throwable $e) {
            $concerns = [];
        }
    }
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar: Quick Actions -->
        <aside class="col-md-3 d-flex align-items-start">
            <div class="card mb-4 w-100" style="margin-top: 3.5rem;">
                <div class="card-header bg-primary text-white" style="text-align: center; font-size: 1.5rem; padding-top: 0.7rem; padding-bottom: 0.7rem;">Quick Actions</div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5>Book Appointment</h5>
                        <a href="{{ route('student.make-appointment') }}" class="btn btn-primary w-100">Book an Appointment</a>
                    </div>
                    <div class="mb-3">
                        <h5>View Events</h5>
                        <a href="{{ route('student.events.index') }}" class="btn btn-secondary w-100">See Upcoming</a>
                    </div>
                    <div class="mb-3">
                        <h5>Organization Registration Request</h5>
                        <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#orgRegModal">Request Organization Registration</button>
                    </div>
                    <div class="mb-3">
                        <h5>Organizational Dashboard</h5>
                        <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#assistantSwitchModal">Open</button>
                    </div>
                    <div class="mb-3">
                        <h5>My QR Code</h5>
                        <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#qrModal">View QR</button>
                    </div>
                </div>
            </div>
        </aside>
        <!-- Main Content -->
        <main class="col-md-9">
            <div class="dashboard-header text-center mb-4">
                <h1 class="wow fadeInUp">Make an Appointment with OSA Balubal</h1>
                <a href="{{ route('student.dashboard') }}" class="btn btn-secondary mt-3">&larr; Return to Dashboard</a>
            </div>
            <div class="page-section">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('appointments.store') }}" class="main-form mt-4">
                    @csrf
                    @php
                        $user = auth()->user();
                        $fullName = trim(($user->first_name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? '') . ' ' . ($user->ext_name ?? ''));
                        $fullName = preg_replace('/\s+/', ' ', $fullName); // Remove extra spaces
                    @endphp
                    <div class="row">
                        <div class="col-12 col-sm-6 py-2">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $fullName) }}" autocomplete="name" class="form-control" placeholder="Full name" required readonly style="background-color: #e9ecef; cursor: not-allowed;">
                        </div>
                        <div class="col-12 col-sm-6 py-2">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" autocomplete="email" class="form-control" placeholder="Email address" required readonly style="background-color: #e9ecef; cursor: not-allowed;">
                        </div>
                        <div class="col-12 col-sm-6 py-2">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number ?? '') }}" autocomplete="tel" class="form-control" placeholder="Contact number" required readonly style="background-color: #e9ecef; cursor: not-allowed;">
                        </div>
                        <div class="col-12 col-sm-6 py-2">
                            <label for="appointment_date" class="form-label">Set Appointment Date</label>
                            <input type="date" id="appointment_date" name="appointment_date" value="{{ old('appointment_date') }}" autocomplete="off" class="form-control" required>
                        </div>
                        <div class="col-12 col-sm-6 py-2">
                            <label for="appointment_time" class="form-label">Set Appointment Time</label>
                            <select id="appointment_time" name="appointment_time" class="form-control" required>
                                <option value="">Select Time</option>
                                @php
                                    $startTime = strtotime('08:00');
                                    $endTime = strtotime('15:00');
                                    $interval = 30 * 60; // 30 minutes in seconds
                                @endphp
                                @for($time = $startTime; $time <= $endTime; $time += $interval)
                                    @php
                                        $timeValue = date('H:i', $time);
                                        $timeDisplay = date('g:i A', $time);
                                    @endphp
                                    <option value="{{ $timeValue }}" {{ old('appointment_time') == $timeValue ? 'selected' : '' }}>
                                        {{ $timeDisplay }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 py-2">
                            <label for="concern" class="form-label">Staff to Address your Concern</label>
                            <select id="concern" name="concern" class="form-control" required>
                                <option value="">Chose your staff?</option>
                                @foreach($concerns as $designation)
                                    <option value="{{ $designation }}" @selected(old('concern')==$designation)>{{ $designation }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Guidance Counselor Additional Fields -->
                    <div id="guidance-counselor-fields" style="display: none;">
                        <div class="row">
                            <div class="col-12 col-sm-6 py-2">
                                <label for="reason_for_counseling" class="form-label">Reason for Counseling</label>
                                <select id="reason_for_counseling" name="reason_for_counseling" class="form-control">
                                    <option value="">Select Reason</option>
                                    <option value="Initial Interview" {{ old('reason_for_counseling') == 'Initial Interview' ? 'selected' : '' }}>Initial Interview</option>
                                    <option value="Information Services" {{ old('reason_for_counseling') == 'Information Services' ? 'selected' : '' }}>Information Services</option>
                                    <option value="Counseling Services" {{ old('reason_for_counseling') == 'Counseling Services' ? 'selected' : '' }}>Counseling Services</option>
                                    <option value="External Referral" {{ old('reason_for_counseling') == 'External Referral' ? 'selected' : '' }}>External Referral</option>
                                    <option value="Internal Referral" {{ old('reason_for_counseling') == 'Internal Referral' ? 'selected' : '' }}>Internal Referral</option>
                                    <option value="Exit Interview" {{ old('reason_for_counseling') == 'Exit Interview' ? 'selected' : '' }}>Exit Interview</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 py-2">
                                <label for="category" class="form-label">Category</label>
                                <select id="category" name="category" class="form-control">
                                    <option value="">Select Category</option>
                                    <option value="Red" data-color="#dc3545" style="background-color: #dc3545; color: white;" {{ old('category') == 'Red' ? 'selected' : '' }}>Red = Urgent/Personal</option>
                                    <option value="Blue" data-color="#0d6efd" style="background-color: #0d6efd; color: white;" {{ old('category') == 'Blue' ? 'selected' : '' }}>Blue = Academic Related</option>
                                    <option value="Yellow" data-color="#ffc107" style="background-color: #ffc107; color: #212529;" {{ old('category') == 'Yellow' ? 'selected' : '' }}>Yellow = Family/Peer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary mt-3">Submit Request</button>
                </form>
            </div>
        </main>
    </div>
</div>

    <!-- Organization Registration Modal -->
    <div class="modal fade" id="orgRegModal" tabindex="-1" aria-labelledby="orgRegModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orgRegModalLabel">Organization Registration Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">{{ __('Register Organization') }}</div>
                                    <div class="card-body">
                                        <form method="POST" action="/student/organization-registration-request">
                                            @csrf
                                            <!-- Organization Selection -->
                                            <div class="row mb-3">
                                                <label class="col-md-4 col-form-label text-md-end">Organization <span class="text-danger">*</span></label>
                                                <div class="col-md-6">
                                                    <select name="organization_id" id="organization_id" class="form-control" required>
                                                        <option value="">Select Organization</option>
                                                        @foreach($nonAcademicOrganizations ?? [] as $org)
                                                            <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                                                {{ $org->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted d-block mt-1">You are automatically a member of your department's organization.</small>
                                                </div>
                                            </div>
                                            <!-- Position -->
                                            <div class="row mb-3">
                                                <label class="col-md-4 col-form-label text-md-end">Position (Optional)</label>
                                                <div class="col-md-6">
                                                    <input type="text" name="position" class="form-control" value="{{ old('position') }}" placeholder="e.g., President, Member, Secretary">
                                                    <small class="text-muted d-block mt-1">Leave blank if no specific position.</small>
                                                </div>
                                            </div>
                                            <!-- Details -->
                                            <div class="row mb-3">
                                                <label class="col-md-4 col-form-label text-md-end">Why do you want to join this organization?</label>
                                                <div class="col-md-6">
                                                    <textarea name="details" class="form-control" rows="3" required>{{ old('details') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="row mb-0">
                                                <div class="col-md-6 offset-md-4">
                                                    <button type="submit" class="btn btn-warning">
                                                        Submit Request
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="qrModalLabel">My QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center" style="min-height: 300px;">
                    <div id="studentQrCodeSvg" style="width:80%; min-height:200px; text-align:center;"></div>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-center">
                    <button type="button" class="btn btn-primary" id="qrModalOkBtn" data-bs-dismiss="modal">Okay</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assistant Switch Modal -->
    <div class="modal fade" id="assistantSwitchModal" tabindex="-1" aria-labelledby="assistantSwitchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('student.switch-to-assistant') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="assistantSwitchModalLabel">Enter Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">To access the Organizational Dashboard, confirm your password.</p>
                    <input type="password" name="assistant_password" class="form-control" placeholder="Password" required />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Continue</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* Style category dropdown options with colors */
    #category option[value="Red"] {
        background-color: #dc3545 !important;
        color: white !important;
    }
    
    #category option[value="Blue"] {
        background-color: #0d6efd !important;
        color: white !important;
    }
    
    #category option[value="Yellow"] {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    
    /* Ensure selected option shows color in the dropdown */
    #category option:checked {
        background-color: inherit !important;
        color: inherit !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Restrict date picker to weekdays only
    var dateInput = document.getElementById('appointment_date');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            var d = new Date(this.value);
            var day = d.getDay();
            if (day === 0 || day === 6) { // Sunday=0, Saturday=6
                alert('Please select a weekday (Monday to Friday).');
                this.value = '';
                return;
            }
        });
    }
    
    // Show/hide Guidance Counselor fields based on staff selection
    const concernSelect = document.getElementById('concern');
    const guidanceFields = document.getElementById('guidance-counselor-fields');
    const reasonForCounseling = document.getElementById('reason_for_counseling');
    const categorySelect = document.getElementById('category');
    
    function toggleGuidanceFields() {
        console.log('toggleGuidanceFields called');
        if (concernSelect && guidanceFields) {
            const selectedConcern = concernSelect.value;
            const selectedText = concernSelect.options[concernSelect.selectedIndex] ? 
                                 concernSelect.options[concernSelect.selectedIndex].text : '';
            console.log('Selected concern value:', selectedConcern);
            console.log('Selected concern text:', selectedText);
            
            // Check for Guidance Counselor (American spelling - standard)
            // Also handle "Guidance Counsellor" (British spelling) for backward compatibility
            const concernLower = selectedConcern ? selectedConcern.toLowerCase() : '';
            const textLower = selectedText ? selectedText.toLowerCase() : '';
            
            // Check both value and text
            const isGuidanceCounselor = (concernLower && (
                (concernLower.includes('guidance') && (concernLower.includes('counselor') || concernLower.includes('counsellor'))) ||
                concernLower === 'guidance counselor' ||
                concernLower === 'guidance counsellor'
            )) || (textLower && (
                (textLower.includes('guidance') && (textLower.includes('counselor') || textLower.includes('counsellor'))) ||
                textLower === 'guidance counselor' ||
                textLower === 'guidance counsellor'
            ));
            
            console.log('Is Guidance Counselor:', isGuidanceCounselor, '| Value:', concernLower, '| Text:', textLower);
            
            if (isGuidanceCounselor) {
                guidanceFields.style.display = 'block';
                console.log('Showing Guidance Counselor fields');
                if (reasonForCounseling) {
                    reasonForCounseling.required = true;
                    reasonForCounseling.disabled = false;
                }
                if (categorySelect) {
                    categorySelect.required = true;
                    // Disable category until reason is selected
                    categorySelect.disabled = true;
                    categorySelect.value = '';
                    categorySelect.style.backgroundColor = '#e9ecef';
                    categorySelect.style.cursor = 'not-allowed';
                }
            } else {
                guidanceFields.style.display = 'none';
                console.log('Hiding Guidance Counselor fields');
                if (reasonForCounseling) {
                    reasonForCounseling.required = false;
                    reasonForCounseling.value = '';
                    reasonForCounseling.disabled = false;
                }
                if (categorySelect) {
                    categorySelect.required = false;
                    categorySelect.value = '';
                    categorySelect.disabled = false;
                    categorySelect.style.backgroundColor = '';
                    categorySelect.style.cursor = '';
                }
            }
        } else {
            console.log('concernSelect or guidanceFields not found:', {
                concernSelect: concernSelect,
                guidanceFields: guidanceFields
            });
        }
    }
    
    // Style category dropdown with colors
    function styleCategorySelect() {
        if (categorySelect) {
            // Update selected option display
            function updateCategoryStyle() {
                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                const color = selectedOption ? selectedOption.getAttribute('data-color') : null;
                if (color) {
                    categorySelect.style.backgroundColor = color;
                    categorySelect.style.color = 'white';
                    categorySelect.style.fontWeight = 'bold';
                } else {
                    categorySelect.style.backgroundColor = '';
                    categorySelect.style.color = '';
                    categorySelect.style.fontWeight = '';
                }
            }
            
            categorySelect.addEventListener('change', updateCategoryStyle);
            
            // Set initial style
            updateCategoryStyle();
        }
    }
    
    if (concernSelect) {
        console.log('Setting up Guidance Counselor field toggle');
        concernSelect.addEventListener('change', toggleGuidanceFields);
        // Check on page load
        toggleGuidanceFields();
    } else {
        console.error('concernSelect not found!');
    }
    
    // Also check if elements exist
    console.log('Elements check:', {
        concernSelect: document.getElementById('concern'),
        guidanceFields: document.getElementById('guidance-counselor-fields'),
        reasonForCounseling: document.getElementById('reason_for_counseling'),
        categorySelect: document.getElementById('category')
    });
    
    // Initialize category styling
    styleCategorySelect();
    
    // Enable category dropdown only after reason is selected
    if (reasonForCounseling && categorySelect) {
        // Function to enable/disable category based on reason
        function updateCategoryAvailability() {
            const reasonValue = reasonForCounseling.value;
            if (reasonValue && reasonValue.trim() !== '') {
                // Enable category dropdown when reason is selected
                categorySelect.disabled = false;
                categorySelect.style.backgroundColor = '';
                categorySelect.style.cursor = 'pointer';
                console.log('Category dropdown enabled - reason selected:', reasonValue);
            } else {
                // Disable category dropdown when reason is cleared
                categorySelect.disabled = true;
                categorySelect.value = '';
                categorySelect.style.backgroundColor = '#e9ecef';
                categorySelect.style.cursor = 'not-allowed';
                // Reset category styling
                categorySelect.style.color = '';
                categorySelect.style.fontWeight = '';
                console.log('Category dropdown disabled - no reason selected');
            }
        }
        
        // Check on page load if reason is already selected
        updateCategoryAvailability();
        
        // Update when reason changes
        reasonForCounseling.addEventListener('change', updateCategoryAvailability);
    }
    
    // QR Code Modal Script
    var qrModal = document.getElementById('qrModal');
    var qrBtn = document.querySelector('[data-bs-target="#qrModal"]');
    
    function loadQRCode() {
        var qrDiv = document.getElementById('studentQrCodeSvg');
        if (qrDiv) {
            qrDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading QR code...</span></div><p class="text-muted mt-2">Loading QR code...</p></div>';
            fetch("{{ route('student.qr-code') }}", {
                headers: {
                    'Accept': 'image/svg+xml, text/html, application/xhtml+xml, */*',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.text();
                })
                .then(function(svg) {
                    qrDiv.innerHTML = svg;
                })
                .catch(function(error) {
                    console.error('Error loading QR code:', error);
                    qrDiv.innerHTML = '<div class="text-center py-4"><span class="text-danger">Failed to load QR code. Please try again.</span></div>';
                });
        }
    }
    
    if (qrBtn && qrModal) {
        qrBtn.addEventListener('click', function(e) {
            // Manually trigger the modal if Bootstrap didn't
            setTimeout(function() {
                if (!qrModal.classList.contains('show')) {
                    try {
                        var bsModal = new bootstrap.Modal(qrModal);
                        bsModal.show();
                    } catch(err) {
                        console.error('Error opening modal:', err);
                    }
                }
            }, 100);
        });
    }
    
    if (qrModal) {
        qrModal.addEventListener('show.bs.modal', function() {
            loadQRCode();
        });
        qrModal.addEventListener('hidden.bs.modal', function() {
            var qrDiv = document.getElementById('studentQrCodeSvg');
            if (qrDiv) qrDiv.innerHTML = '';
        });
    }
});
</script>
@endpush