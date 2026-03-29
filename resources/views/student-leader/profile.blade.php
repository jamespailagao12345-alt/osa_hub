@extends('layouts.app')

@section('title', 'Assistant Profile')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('assistant.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Assistant Profile</h3>
        <a href="{{ route('student-leader.dashboard') }}" class="btn btn-secondary">Back</a>
      </div>
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  <div class="card">
    <div class="card-body">
      @php
        $currentUser = $user ?? auth()->user();
        $currentUserImage = $currentUser->image ?? null;
        $currentUserName = trim(($currentUser->first_name ?? '') . ' ' . ($currentUser->middle_name ?? '') . ' ' . ($currentUser->last_name ?? ''));
        
        // Get initials for avatar
        $initials = '';
        if ($currentUser) {
            $firstInitial = strtoupper(substr($currentUser->first_name ?? '', 0, 1));
            $lastInitial = strtoupper(substr($currentUser->last_name ?? '', 0, 1));
            $initials = $firstInitial . $lastInitial;
        }
        
        // Calculate age from birth_date if available
        $age = $currentUser->age ?? null;
        if (!$age && $currentUser->birth_date) {
            try {
                $age = \Carbon\Carbon::parse($currentUser->birth_date)->age;
            } catch (\Exception $e) {
                $age = null;
            }
        }
        
        // Collect all organizations assigned to this user
        $allOrganizations = collect();
        
        // Add single organization if exists
        if ($currentUser->organization) {
            $allOrganizations->push($currentUser->organization);
        }
        
        // Add many-to-many organizations if exists
        if (method_exists($currentUser, 'otherOrganizations') && $currentUser->otherOrganizations) {
            foreach ($currentUser->otherOrganizations as $org) {
                // Avoid duplicates
                if (!$allOrganizations->contains('id', $org->id)) {
                    $allOrganizations->push($org);
                }
            }
        }
      @endphp
      
      <!-- Profile Photo and Info Section -->
      <div class="row mb-4">
        <div class="col-md-9">
          <dl class="row mb-0">
            <dt class="col-sm-4">Name:</dt>
            <dd class="col-sm-8">{{ $currentUser->first_name }} {{ $currentUser->middle_name ?? '' }} {{ $currentUser->last_name }}</dd>
            
            <dt class="col-sm-4">Email:</dt>
            <dd class="col-sm-8">{{ $currentUser->email }}</dd>
            
            @if($currentUser->user_id)
            <dt class="col-sm-4">User ID:</dt>
            <dd class="col-sm-8">{{ $currentUser->user_id }}</dd>
            @endif
            
            @if($currentUser->department)
            <dt class="col-sm-4">Department:</dt>
            <dd class="col-sm-8">{{ $currentUser->department->name }}</dd>
            @endif
            
            @if($currentUser->organization)
            <dt class="col-sm-4">Organization:</dt>
            <dd class="col-sm-8">{{ $currentUser->organization->name }}</dd>
            @endif
            
            @if($currentUser->position)
            <dt class="col-sm-4">Position:</dt>
            <dd class="col-sm-8">{{ $currentUser->position }}</dd>
            @endif
            
            @if($currentUser->birth_date)
            <dt class="col-sm-4">Birthdate:</dt>
            <dd class="col-sm-8">{{ \Carbon\Carbon::parse($currentUser->birth_date)->format('F d, Y') }}</dd>
            @endif
            
            @if($age)
            <dt class="col-sm-4">Age:</dt>
            <dd class="col-sm-8">{{ $age }} years old</dd>
            @endif
            
            @if($currentUser->contact_number)
            <dt class="col-sm-4">Contact Number:</dt>
            <dd class="col-sm-8">{{ $currentUser->contact_number }}</dd>
            @endif
            
            @if(!$allOrganizations->isEmpty())
            <dt class="col-sm-4">Organization(s):</dt>
            <dd class="col-sm-8">
                <ul class="list-unstyled mb-0">
                    @foreach($allOrganizations as $org)
                        <li>• {{ $org->name }}</li>
                    @endforeach
                </ul>
            </dd>
            @endif
          </dl>
        </div>
        <div class="col-md-3 d-flex justify-content-center align-items-start">
          @if($currentUserImage)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($currentUserImage) }}" 
                 alt="{{ $currentUserName }}" 
                 class="rounded-circle" 
                 style="width: 150px; height: 150px; object-fit: cover; border: 3px solid midnightblue;">
          @else
            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                 style="width: 150px; height: 150px; border: 3px solid midnightblue;">
              <span class="text-white" style="font-size: 3rem; font-weight: bold;">{{ $initials ?: 'A' }}</span>
            </div>
          @endif
        </div>
      </div>
      
      <hr>
      <h6 class="small">Change Password</h6>
      <form method="POST" action="{{ route('student-leader.change-password') }}">
        @csrf
        <div class="mb-3">
          <label for="current_password" class="form-label small">Current Password</label>
          <div class="position-relative">
            <input type="password" name="current_password" id="current_password" class="form-control form-control-sm" required style="padding-right: 2.5rem;">
            <button type="button" class="btn btn-link position-absolute p-0" id="toggle_current_password" style="border: none; background: none; cursor: pointer; z-index: 10; right: 0.75rem; top: 50%; transform: translateY(-50%); height: auto; line-height: 1;">
              <i class="bi bi-eye" id="currentPasswordIcon" style="font-size: 0.875rem; vertical-align: middle; color: black;"></i>
            </button>
          </div>
        </div>
        <div class="mb-3">
          <label for="new_password" class="form-label small">New Password</label>
          <div class="position-relative">
            <input type="password" name="new_password" id="new_password" class="form-control form-control-sm" required style="padding-right: 2.5rem;">
            <button type="button" class="btn btn-link position-absolute p-0" id="toggle_new_password" style="border: none; background: none; cursor: pointer; z-index: 10; right: 0.75rem; top: 50%; transform: translateY(-50%); height: auto; line-height: 1;">
              <i class="bi bi-eye" id="newPasswordIcon" style="font-size: 0.875rem; vertical-align: middle; color: black;"></i>
            </button>
          </div>
        </div>
        <div class="mb-3">
          <label for="new_password_confirmation" class="form-label small">Confirm New Password</label>
          <div class="position-relative">
            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control form-control-sm" required style="padding-right: 2.5rem;">
            <button type="button" class="btn btn-link position-absolute p-0" id="toggle_new_password_confirmation" style="border: none; background: none; cursor: pointer; z-index: 10; right: 0.75rem; top: 50%; transform: translateY(-50%); height: auto; line-height: 1;">
              <i class="bi bi-eye" id="newPasswordConfirmationIcon" style="font-size: 0.875rem; vertical-align: middle; color: black;"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm small">Update Password</button>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Current password toggle
    const toggleCurrentPassword = document.getElementById('toggle_current_password');
    const currentPasswordField = document.getElementById('current_password');
    const currentPasswordIcon = document.getElementById('currentPasswordIcon');
    
    if (toggleCurrentPassword && currentPasswordField) {
        toggleCurrentPassword.addEventListener('click', function() {
            const type = currentPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
            currentPasswordField.setAttribute('type', type);
            currentPasswordIcon.classList.toggle('bi-eye');
            currentPasswordIcon.classList.toggle('bi-eye-slash');
        });
    }
    
    // New password toggle
    const toggleNewPassword = document.getElementById('toggle_new_password');
    const newPasswordField = document.getElementById('new_password');
    const newPasswordIcon = document.getElementById('newPasswordIcon');
    
    if (toggleNewPassword && newPasswordField) {
        toggleNewPassword.addEventListener('click', function() {
            const type = newPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
            newPasswordField.setAttribute('type', type);
            newPasswordIcon.classList.toggle('bi-eye');
            newPasswordIcon.classList.toggle('bi-eye-slash');
        });
    }
    
    // Confirm password toggle
    const toggleNewPasswordConfirmation = document.getElementById('toggle_new_password_confirmation');
    const newPasswordConfirmationField = document.getElementById('new_password_confirmation');
    const newPasswordConfirmationIcon = document.getElementById('newPasswordConfirmationIcon');
    
    if (toggleNewPasswordConfirmation && newPasswordConfirmationField) {
        toggleNewPasswordConfirmation.addEventListener('click', function() {
            const type = newPasswordConfirmationField.getAttribute('type') === 'password' ? 'text' : 'password';
            newPasswordConfirmationField.setAttribute('type', type);
            newPasswordConfirmationIcon.classList.toggle('bi-eye');
            newPasswordConfirmationIcon.classList.toggle('bi-eye-slash');
        });
    }
});
</script>
@endpush
      </main>
    </div>
  </div>
@endsection
