@extends('layouts.app')

@section('content')
    <div class="admin-back-btn-wrap d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.show-staff') }}" class="btn btn-secondary">Back to Staff List</a>
        @php
          $user = $staff->user ?? \App\Models\User::where('email', $staff->email)->first();
          $verificationEmailCount = $user ? ($user->verification_email_count ?? 0) : 0;
        @endphp
        <div class="d-flex align-items-center gap-3">
          <div class="text-muted">
            <small>Verification emails sent: <strong>{{ $verificationEmailCount }}</strong></small>
          </div>
          <form action="{{ route('admin.staff.resend-verification', $staff->id) }}" method="POST" style="display: inline-block;">
            @csrf
            <button type="submit" class="btn btn-info btn-sm" onclick="return confirm('Are you sure you want to resend the verification email to {{ $staff->email }}?')">
              <i class="bi bi-envelope"></i> Resend Verification Email
            </button>
          </form>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            @include('admin.partials.sidebar')
            <main class="col-md-10">
                <h3 class="mt-4"><span class="d-block w-100 px-3 py-2" style="background-color: midnightblue; color: white; border-radius: 4px;">Edit Staff</span></h3>
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('admin.staff.update', $staff->id) }}" enctype="multipart/form-data" class="card p-4 mb-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="employment_status" id="employment_status" value="{{ $staff->employment_status }}">
                    <table class="table table-borderless">
                        <tbody>
                <tr>
                    <td colspan="2">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="user_id" id="user_id" class="form-control" placeholder="Employee ID" value="{{ old('user_id', $staff->user_id) }}" required>
                                <small class="text-muted">Employee ID</small>
                            </div>
                            <div class="col-md-4">
                                <input type="email" name="email" id="email" class="form-control" placeholder="Email" value="{{ old('email', $staff->email) }}" required>
                                <small class="text-muted">Email</small>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="contact_number" id="contact_number" class="form-control" placeholder="Contact" value="{{ old('contact_number', $staff->contact_number) }}">
                                <small class="text-muted">Contact Number</small>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First" value="{{ old('first_name', $staff->first_name) }}" required>
                                <small class="text-muted">First Name</small>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="middle_name" id="middle_name" class="form-control" placeholder="M" value="{{ old('middle_name', $staff->middle_name) }}" maxlength="1" pattern="[A-Za-z]" title="Enter only one alphabet character">
                                <small class="text-muted">Middle Initial</small>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last" value="{{ old('last_name', $staff->last_name) }}" required>
                                <small class="text-muted">Last Name</small>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="row g-2">
                            <div class="col-md-6" data-birth-age-pair>
                                @include('components.birthdate_with_age', ['name' => 'birth_date', 'ageName' => 'age', 'value' => old('birth_date', $staff->birth_date)])
                                <small class="text-muted">Birth Date</small>
                            </div>
                            <div class="col-md-6">
                                <select name="gender" id="gender" class="form-select">
                                    <option value="">Select Sex</option>
                                    <option value="male" @selected(old('gender', $staff->gender) === 'male')>Male</option>
                                    <option value="female" @selected(old('gender', $staff->gender) === 'female')>Female</option>
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
                                    <label class="form-label">Organizations</label>
                                    <div id="organizations-container" class="border p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                                        @isset($organizations)
                                            @php
                                                $assignedOrgIds = $assignedOrgIds ?? [];
                                                $currentStaffOrgIds = $staff->organizations->pluck('id')->toArray();
                                            @endphp
                                            @foreach($organizations->sortBy('name') as $org)
                                                @php
                                                    $isAssignedToOther = in_array($org->id, $assignedOrgIds) && !in_array($org->id, $currentStaffOrgIds);
                                                    $isChecked = collect(old('organization_ids', $currentStaffOrgIds))->contains($org->id);
                                                @endphp
                                                <div class="form-check mb-2" data-org-id="{{ $org->id }}" data-org-dept-id="{{ $org->department_id ?? '' }}">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="organization_ids[]" 
                                                           id="org{{ $org->id }}" 
                                                           value="{{ $org->id }}"
                                                           @if($isChecked) checked @endif
                                                           @if($isAssignedToOther) disabled @endif>
                                                    <label class="form-check-label @if($isAssignedToOther) text-muted @endif" 
                                                           for="org{{ $org->id }}">
                                                        {{ $org->name }}
                                                        @if($isAssignedToOther)
                                                            <small class="text-danger">(Already assigned)</small>
                                                        @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        @endisset
                                    </div>
                                    <small class="text-muted d-block mt-2">Non-academic organizations are always available. Department-related organizations appear when a department is selected. Organizations already assigned to other staff are unavailable.</small>
                                </div>
                            </div>
                            <div class="col-md-4 ms-1">
                                <select name="department_id" id="department_id" class="form-select">
                                    <option value="">Select Dept</option>
                                    @isset($departments)
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" @selected(old('department_id', $staff->department_id) == $dept->id)>{{ $dept->name }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                                <small class="text-muted">Department</small>
                            </div>
                            <div class="col-md-4">
                                <select name="designation" id="designation" class="form-select" required>
                                    <option value="">Select Desig</option>
                                    @php($designations = \App\Models\Designation::all())
                                    @foreach($designations as $designation)
                                        <option value="{{ $designation->name }}" @selected(old('designation', $staff->designation) === $designation->name)>{{ $designation->name }}</option>
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
                                @if($staff->service_order)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($staff->service_order) }}" target="_blank" class="btn btn-info btn-sm mb-2">Download S.O.</a>
                                @endif
                                <input type="file" name="service_order" id="service_order" class="form-control" accept=".pdf,.doc,.docx">
                                <small class="text-muted">S.O. (Service Order)</small>
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="length_of_service" id="length_of_service" class="form-control" min="0" placeholder="Yrs" value="{{ old('length_of_service', $staff->length_of_service) }}">
                                <small class="text-muted">Length of Service</small>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="contract_end_at" id="contract_end_at" class="form-control" placeholder="MM/DD/YYYY" value="{{ $staff->contract_end_at ? \Carbon\Carbon::parse($staff->contract_end_at)->format('m/d/Y') : '' }}" pattern="^\d{2}\/\d{2}\/\d{4}$" inputmode="numeric">
                                <small class="text-muted">End of Contract (MM/DD/YYYY)</small>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-2">Leave blank to keep current password.</small>
                                <div class="position-relative mb-2">
                                    <input type="password" name="new_password" id="new_password" class="form-control" placeholder="New Password (optional)" style="padding-right: 2.5rem;">
                                    <button type="button" class="btn btn-link position-absolute p-0" id="toggleNewPassword" style="border: none; background: none; cursor: pointer; z-index: 10; right: 0.75rem; top: 50%; transform: translateY(-50%); height: auto; line-height: 1;">
                                        <i class="bi bi-eye" id="newPasswordIcon" style="font-size: 0.875rem; vertical-align: middle; color: black;"></i>
                                    </button>
                                </div>
                                <div class="position-relative">
                                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="Confirm New Password (optional)" style="padding-right: 2.5rem;">
                                    <button type="button" class="btn btn-link position-absolute p-0" id="toggleNewPasswordConfirmation" style="border: none; background: none; cursor: pointer; z-index: 10; right: 0.75rem; top: 50%; transform: translateY(-50%); height: auto; line-height: 1;">
                                        <i class="bi bi-eye" id="newPasswordConfirmationIcon" style="font-size: 0.875rem; vertical-align: middle; color: black;"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if($staff->image)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($staff->image) }}" alt="Profile Image" class="img-thumbnail mb-2" width="120">
                                @endif
                                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                                <small class="text-muted">Profile Image</small>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-danger" type="button" id="btnEndContract">Terminate</button>
                            <button class="btn btn-outline-secondary" type="button" id="btnSuspend">Suspend</button>
                            <button class="btn btn-outline-success" type="button" id="btnActivate">Restart</button>
                        </div>
                    </td>
                </tr>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-success">Update Staff</button>
                </form>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const deptSel = document.getElementById('department_id');
  const orgContainer = document.getElementById('organizations-container');
  const currentStaffOrgIds = @json($staff->organizations->pluck('id')->toArray());
  const assignedOrgIds = @json($assignedOrgIds ?? []);

  // Save currently selected organizations before reloading
  function saveSelectedOrganizations() {
    const checkedBoxes = orgContainer.querySelectorAll('input[type="checkbox"]:checked:not(:disabled)');
    return Array.from(checkedBoxes).map(cb => cb.value);
  }

  // Restore selected organizations after reloading
  function restoreSelectedOrganizations(selectedIds) {
    selectedIds.forEach(orgId => {
      const checkbox = orgContainer.querySelector(`input[type="checkbox"][value="${orgId}"]:not(:disabled)`);
      if (checkbox) {
        checkbox.checked = true;
      }
    });
  }

  async function updateOrganizationsList(deptId){
    if (!orgContainer) return;
    
    // Save current selections
    const selectedIds = saveSelectedOrganizations();
    
    try {
      // Always fetch non-academic organizations (unassigned)
      const nonAcademicRes = await fetch('/api/organizations?unassigned=1');
      const nonAcademicOrgs = await nonAcademicRes.json();
      
      let allOrgs = [...nonAcademicOrgs];
      
      // If department is selected, also fetch department-related organizations
      if (deptId) {
        const deptRes = await fetch(`/api/organizations?department_id=${encodeURIComponent(deptId)}`);
        const deptOrgs = await deptRes.json();
        // Merge and remove duplicates
        const byId = new Map();
        [...allOrgs, ...deptOrgs].forEach(org => byId.set(org.id, org));
        allOrgs = Array.from(byId.values());
      }
      
      // Sort organizations by name (case-insensitive)
      allOrgs.sort((a, b) => a.name.toLowerCase().localeCompare(b.name.toLowerCase()));
      
      // Clear container
      orgContainer.innerHTML = '';
      
      // Create checkboxes for each organization
      allOrgs.forEach(org => {
        const isAssignedToOther = assignedOrgIds.includes(org.id) && !currentStaffOrgIds.includes(org.id);
        const isChecked = currentStaffOrgIds.includes(org.id) || selectedIds.includes(String(org.id));
        
        const div = document.createElement('div');
        div.className = 'form-check mb-2';
        div.setAttribute('data-org-id', org.id);
        div.setAttribute('data-org-dept-id', org.department_id || '');
        
        const checkbox = document.createElement('input');
        checkbox.className = 'form-check-input';
        checkbox.type = 'checkbox';
        checkbox.name = 'organization_ids[]';
        checkbox.id = `org${org.id}`;
        checkbox.value = org.id;
        if (isChecked) checkbox.checked = true;
        if (isAssignedToOther) checkbox.disabled = true;
        
        const label = document.createElement('label');
        label.className = `form-check-label ${isAssignedToOther ? 'text-muted' : ''}`;
        label.htmlFor = `org${org.id}`;
        label.textContent = org.name;
        if (isAssignedToOther) {
          const small = document.createElement('small');
          small.className = 'text-danger';
          small.textContent = ' (Already assigned)';
          label.appendChild(small);
        }
        
        div.appendChild(checkbox);
        div.appendChild(label);
        orgContainer.appendChild(div);
      });
      
      // Restore selected organizations
      restoreSelectedOrganizations(selectedIds);
      
    } catch (e) {
      console.error('Error loading organizations:', e);
    }
  }

  if (deptSel) {
    deptSel.addEventListener('change', ()=>{
      const id = deptSel.value || '';
      updateOrganizationsList(id);
    });
    // Don't update on page load - use server-rendered organizations initially
    // The list will update when user changes the department
  }

  // Action buttons wire-up using hidden employment_status and MM/DD/YYYY date
  const statusInput = document.getElementById('employment_status');
  const endBtn = document.getElementById('btnEndContract');
  const suspendBtn = document.getElementById('btnSuspend');
  const restartBtn = document.getElementById('btnActivate');
  const endInput = document.getElementById('contract_end_at');

  function todayMMDDYYYY(){
    const d = new Date();
    const mm = String(d.getMonth()+1).padStart(2,'0');
    const dd = String(d.getDate()).padStart(2,'0');
    const yyyy = d.getFullYear();
    return `${mm}/${dd}/${yyyy}`;
  }

  endBtn?.addEventListener('click', ()=>{
    if (statusInput) statusInput.value = 'ended';
    if (endInput) endInput.value = todayMMDDYYYY();
  });
  suspendBtn?.addEventListener('click', ()=>{
    if (statusInput) statusInput.value = 'inactive';
  });
  restartBtn?.addEventListener('click', ()=>{
    if (statusInput) statusInput.value = 'active';
  });

  // Password visibility toggle
  const toggleNewPasswordBtn = document.getElementById('toggleNewPassword');
  const toggleNewPasswordConfirmationBtn = document.getElementById('toggleNewPasswordConfirmation');
  const newPasswordField = document.getElementById('new_password');
  const newPasswordConfirmationField = document.getElementById('new_password_confirmation');
  const newPasswordIcon = document.getElementById('newPasswordIcon');
  const newPasswordConfirmationIcon = document.getElementById('newPasswordConfirmationIcon');
  
  if (toggleNewPasswordBtn && newPasswordField) {
    toggleNewPasswordBtn.addEventListener('click', function() {
      const type = newPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
      newPasswordField.setAttribute('type', type);
      newPasswordIcon.classList.toggle('bi-eye');
      newPasswordIcon.classList.toggle('bi-eye-slash');
    });
  }
  
  if (toggleNewPasswordConfirmationBtn && newPasswordConfirmationField) {
    toggleNewPasswordConfirmationBtn.addEventListener('click', function() {
      const type = newPasswordConfirmationField.getAttribute('type') === 'password' ? 'text' : 'password';
      newPasswordConfirmationField.setAttribute('type', type);
      newPasswordConfirmationIcon.classList.toggle('bi-eye');
      newPasswordConfirmationIcon.classList.toggle('bi-eye-slash');
    });
  }
});
</script>
@endpush
