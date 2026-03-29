@extends('layouts.app')

@section('title', 'Create Organization')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main id="adminMain" class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.organizations.index') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            </div>
            
            <h2 class="mb-3">Create New Organization</h2>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.organizations.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Organization Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="acronym" class="form-label">Acronym</label>
                                <input type="text" class="form-control @error('acronym') is-invalid @enderror" 
                                       id="acronym" name="acronym" value="{{ old('acronym') }}">
                                @error('acronym')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="staff_id" class="form-label">Student Org. Moderator (Optional)</label>
                                <select name="staff_id" id="staff_id" class="form-select @error('staff_id') is-invalid @enderror">
                                    <option value="">Select Staff Member</option>
                                    @foreach($allStaff as $staff)
                                        <option value="{{ $staff->id }}" 
                                                data-department-id="{{ $staff->department_id ?? '' }}"
                                                {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                            {{ $staff->first_name }} {{ $staff->last_name }} 
                                            @if($staff->department)
                                                ({{ $staff->department->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Selecting a staff member will auto-fill the department field below.</small>
                                @error('staff_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                    <option value="">None (Non-Academic Organization)</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Select a department for academic organizations, or leave as "None" for non-academic organizations.</small>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="official_email" class="form-label">Official Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('official_email') is-invalid @enderror" 
                                       id="official_email" name="official_email" value="{{ old('official_email') }}" 
                                       placeholder="example@organization.com" required>
                                @error('official_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="mailing_address" class="form-label">Mailing Address</label>
                            <textarea class="form-control @error('mailing_address') is-invalid @enderror" 
                                      id="mailing_address" name="mailing_address" rows="3">{{ old('mailing_address') }}</textarea>
                            @error('mailing_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_established" class="form-label">Date Established</label>
                                <input type="date" class="form-control @error('date_established') is-invalid @enderror" 
                                       id="date_established" name="date_established" value="{{ old('date_established') }}">
                                @error('date_established')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Special Organization</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_special" id="is_special" value="1" {{ old('is_special') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_special">
                                        Mark as special organization
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Organization</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const staffSelect = document.getElementById('staff_id');
    const departmentSelect = document.getElementById('department_id');
    
    if (staffSelect && departmentSelect) {
        // Auto-fill department when staff is selected (optional - user can still change it)
        staffSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const departmentId = selectedOption.getAttribute('data-department-id');
            
            // Auto-fill department if staff has one and department is not already selected
            if (departmentId && departmentId !== '' && !departmentSelect.value) {
                departmentSelect.value = departmentId;
            }
        });
    }
});
</script>
@endpush
@endsection

