@extends('layouts.app')

@section('title', 'Add Assistant')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card mt-4">
        <div class="card-header" style="background-color: midnightblue; color: white;">Add Assistant</div>
        <div class="card-body">
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          <form method="POST" action="{{ route('admin.student-leaders.store') }}">
            @csrf
            <div class="mb-3">
              <label for="user_id" class="form-label">Student ID</label>
              <input type="text" class="form-control" id="user_id" name="user_id" value="{{ old('user_id') }}" required>
            </div>
            <div class="mb-3">
              <label for="first_name" class="form-label">First Name</label>
              <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
            </div>
            <div class="mb-3">
              <label for="middle_name" class="form-label">Middle Initial</label>
              <input type="text" class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" maxlength="1" pattern="[A-Za-z]" title="Enter only one alphabet character">
            </div>
            <div class="mb-3">
              <label for="last_name" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
            </div>
            <div class="mb-3">
              <label for="contact_number" class="form-label">Contact Number</label>
              <input type="text" class="form-control" id="contact_number" name="contact_number" value="{{ old('contact_number') }}">
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Primary Organization</label>
              <select name="organization_id" id="organization_id" class="form-select">
                <option value="">Select Primary Organization (Optional)</option>
                @isset($organizations)
                  @foreach($organizations as $org)
                    <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                      {{ $org->name }}
                    </option>
                  @endforeach
                @endisset
              </select>
              <small class="text-muted">Select one primary organization (optional)</small>
            </div>
            <div class="mb-3">
              <label class="form-label">Organizations <span class="text-danger">*</span></label>
              <div class="border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                @isset($organizations)
                  @foreach($organizations as $org)
                    <div class="form-check">
                      <input class="form-check-input org-checkbox" type="checkbox" name="organization_ids[]" id="org{{ $org->id }}" value="{{ $org->id }}"
                        {{ old('organization_ids') ? (in_array($org->id, old('organization_ids')) ? 'checked' : '') : '' }}>
                      <label class="form-check-label" for="org{{ $org->id }}">{{ $org->name }}</label>
                    </div>
                  @endforeach
                @endisset
              </div>
              <small class="text-muted d-block mt-2" id="org-count-message">Assistant must belong to at least 1 organization (maximum 5 total). Selected: <span id="selected-count">0</span></small>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <div class="position-relative">
                <input type="password" class="form-control" id="password" name="password" minlength="8" required style="padding-right: 2.5rem;">
                <button type="button" class="btn btn-link position-absolute p-0" id="togglePassword" style="border: none; background: none; cursor: pointer; z-index: 10; right: 0.75rem; top: 50%; transform: translateY(-50%); height: auto; line-height: 1;">
                  <i class="bi bi-eye" id="passwordIcon" style="font-size: 0.875rem; vertical-align: middle; color: #6c757d;"></i>
                </button>
              </div>
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-success" id="submit-btn">Add Assistant</button>
              <a href="{{ route('admin.student-leaders.index') }}" class="btn btn-secondary">View All Assistants</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const orgCheckboxes = document.querySelectorAll('.org-checkbox');
  const primaryOrgSelect = document.getElementById('organization_id');
  const selectedCountSpan = document.getElementById('selected-count');
  const orgCountMessage = document.getElementById('org-count-message');
  const submitBtn = document.getElementById('submit-btn');
  
  function updateCount() {
    const checkedBoxes = Array.from(orgCheckboxes).filter(cb => cb.checked);
    const primaryOrg = primaryOrgSelect.value;
    let totalCount = checkedBoxes.length;
    if (primaryOrg && !checkedBoxes.find(cb => cb.value === primaryOrg)) {
      totalCount++;
    }
    
    selectedCountSpan.textContent = totalCount;
    
    if (totalCount === 0) {
      orgCountMessage.classList.remove('text-success');
      orgCountMessage.classList.add('text-danger');
      orgCountMessage.textContent = 'Assistant must belong to at least 1 organization (maximum 5 total). Selected: 0';
      submitBtn.disabled = true;
    } else if (totalCount > 5) {
      orgCountMessage.classList.remove('text-success');
      orgCountMessage.classList.add('text-danger');
      orgCountMessage.textContent = `Maximum 5 organizations allowed. Currently selected: ${totalCount}`;
      submitBtn.disabled = true;
    } else {
      orgCountMessage.classList.remove('text-danger');
      orgCountMessage.classList.add('text-success');
      orgCountMessage.textContent = `Assistant must belong to at least 1 organization (maximum 5 total). Selected: ${totalCount}`;
      submitBtn.disabled = false;
    }
  }
  
  orgCheckboxes.forEach(cb => {
    cb.addEventListener('change', function() {
      const checkedCount = Array.from(orgCheckboxes).filter(c => c.checked).length;
      const primaryOrg = primaryOrgSelect.value;
      let totalChecked = checkedCount;
      if (primaryOrg && !Array.from(orgCheckboxes).find(c => c.value === primaryOrg && c.checked)) {
        totalChecked++;
      }
      
      if (totalChecked >= 5 && this.checked) {
        alert('Maximum 5 organizations allowed (including primary organization).');
        this.checked = false;
      }
      updateCount();
    });
  });
  
  primaryOrgSelect.addEventListener('change', function() {
    const checkedCount = Array.from(orgCheckboxes).filter(c => c.checked).length;
    if (this.value && checkedCount >= 5) {
      alert('Maximum 5 organizations allowed. Please uncheck some organizations first.');
      this.value = '';
      return;
    }
    updateCount();
  });
  
  updateCount();
});
</script>
@endpush
