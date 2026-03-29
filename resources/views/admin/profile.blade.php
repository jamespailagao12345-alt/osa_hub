@extends('layouts.app')

@section('title', 'Admin Profile')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Admin Profile</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Back</a>
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
                $currentUser = auth()->user();
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
                $age = $user->age ?? null;
                if (!$age && $user->birth_date) {
                    try {
                        $age = \Carbon\Carbon::parse($user->birth_date)->age;
                    } catch (\Exception $e) {
                        $age = null;
                    }
                }
            @endphp
            
            <!-- Profile Photo and Info Section -->
            <div class="row mb-4">
                <div class="col-md-9">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8">{{ $user->first_name }} {{ $user->middle_name ?? '' }} {{ $user->last_name }}</dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>
                        
                        <dt class="col-sm-4">Role:</dt>
                        <dd class="col-sm-8">Admin</dd>
                        
                        @if($user->user_id)
                        <dt class="col-sm-4">User ID:</dt>
                        <dd class="col-sm-8">{{ $user->user_id }}</dd>
                        @endif
                        
                        @if($user->department)
                        <dt class="col-sm-4">Department:</dt>
                        <dd class="col-sm-8">{{ $user->department->name }}</dd>
                        @endif
                        
                        @if($user->birth_date)
                        <dt class="col-sm-4">Birthdate:</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($user->birth_date)->format('F d, Y') }}</dd>
                        @endif
                        
                        @if($age)
                        <dt class="col-sm-4">Age:</dt>
                        <dd class="col-sm-8">{{ $age }} years old</dd>
                        @endif
                        
                        @if($user->contact_number)
                        <dt class="col-sm-4">Contact Number:</dt>
                        <dd class="col-sm-8">{{ $user->contact_number }}</dd>
                        @endif
                        
                        @if($user->organization)
                        <dt class="col-sm-4">Organization:</dt>
                        <dd class="col-sm-8">{{ $user->organization->name }}</dd>
                        @endif
                    </dl>
                </div>
                <div class="col-md-3 d-flex flex-column justify-content-start align-items-center">
                    <div class="position-relative mb-3">
                        @if($currentUserImage)
                            <img id="profileImage" src="{{ \Illuminate\Support\Facades\Storage::url($currentUserImage) }}" 
                                 alt="{{ $currentUserName }}" 
                                 class="rounded-circle" 
                                 style="width: 150px; height: 150px; object-fit: cover; border: 3px solid midnightblue;">
                        @else
                            <div id="profileImagePlaceholder" class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px; border: 3px solid midnightblue;">
                                <span class="text-white" style="font-size: 3rem; font-weight: bold;">{{ $initials ?: 'A' }}</span>
                            </div>
                        @endif
                        <div class="position-absolute bottom-0 end-0">
                            <label for="profileImageInput" class="btn btn-primary btn-sm rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" title="{{ $currentUserImage ? 'Edit/Replace Profile Image' : 'Upload Profile Image' }}">
                                <i class="bi {{ $currentUserImage ? 'bi-pencil-fill' : 'bi-camera-fill' }}" style="font-size: 1.2rem;"></i>
                            </label>
                            <input type="file" id="profileImageInput" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" style="display: none;">
                        </div>
                    </div>
                    <button type="button" id="removeImageBtn" class="btn btn-outline-danger btn-sm rounded-circle {{ !$currentUserImage ? 'd-none' : '' }}" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" title="Remove Profile Image">
                        <i class="bi bi-trash-fill" style="font-size: 1.2rem;"></i>
                    </button>
                </div>
            </div>
            
            <hr>
            <h6 class="small mb-3">Profile Image</h6>
            <div class="alert alert-info alert-dismissible fade show d-none" id="imageUploadAlert" role="alert">
                <span id="imageUploadMessage"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle"></i> You can upload <strong>one profile image</strong>. Click the camera icon to upload or replace your current image. 
                Supported formats: JPEG, PNG, JPG, GIF, WEBP (Max: 10MB). Uploading a new image will automatically replace the existing one.
            </p>
            
            <hr>
            <h6 class="small">Change Password</h6>
            <form method="POST" action="{{ route('admin.change-password') }}">
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
                
                // Profile Image Upload
                const profileImageInput = document.getElementById('profileImageInput');
                const profileImage = document.getElementById('profileImage');
                const profileImagePlaceholder = document.getElementById('profileImagePlaceholder');
                const removeImageBtn = document.getElementById('removeImageBtn');
                const imageUploadAlert = document.getElementById('imageUploadAlert');
                const imageUploadMessage = document.getElementById('imageUploadMessage');
                
                if (profileImageInput) {
                    profileImageInput.addEventListener('change', function(e) {
                        // Ensure only one file is selected
                        if (e.target.files.length > 1) {
                            showImageAlert('Please select only one image file.', 'danger');
                            e.target.value = ''; // Clear the input
                            return;
                        }
                        
                        const file = e.target.files[0];
                        if (!file) return;
                        
                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                        if (!validTypes.includes(file.type)) {
                            showImageAlert('Please select a valid image file (JPEG, PNG, JPG, GIF, or WEBP).', 'danger');
                            e.target.value = ''; // Clear the input
                            return;
                        }
                        
                        // Validate file size (10MB)
                        if (file.size > 10 * 1024 * 1024) {
                            showImageAlert('Image size must be less than 10MB.', 'danger');
                            e.target.value = ''; // Clear the input
                            return;
                        }
                        
                        // Check if there's an existing image BEFORE showing preview
                        const hasExistingImage = (profileImage && profileImage.style.display !== 'none') || 
                                                 (profileImagePlaceholder && profileImagePlaceholder.style.display === 'none');
                        
                        // Show preview
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (profileImage) {
                                profileImage.src = e.target.result;
                                profileImage.style.display = 'block';
                            } else {
                                // Create image element if placeholder exists
                                if (profileImagePlaceholder) {
                                    profileImagePlaceholder.style.display = 'none';
                                    const img = document.createElement('img');
                                    img.id = 'profileImage';
                                    img.src = e.target.result;
                                    img.className = 'rounded-circle';
                                    img.style.cssText = 'width: 150px; height: 150px; object-fit: cover; border: 3px solid midnightblue;';
                                    profileImagePlaceholder.parentElement.insertBefore(img, profileImagePlaceholder);
                                }
                            }
                            if (removeImageBtn) {
                                removeImageBtn.classList.remove('d-none');
                            }
                        };
                        reader.readAsDataURL(file);
                        
                        // Upload image (this will replace the existing image if one exists)
                        const formData = new FormData();
                        formData.append('image', file);
                        formData.append('_token', '{{ csrf_token() }}');
                        
                        // Show loading
                        showImageAlert(hasExistingImage ? 'Replacing image...' : 'Uploading image...', 'info');
                        
                        fetch('{{ route("profile.update-image") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showImageAlert('Profile image ' + (hasExistingImage ? 'replaced' : 'uploaded') + ' successfully!', 'success');
                                // Update image source with new URL
                                if (profileImage) {
                                    profileImage.src = data.image_url + '?t=' + new Date().getTime();
                                    profileImage.style.display = 'block';
                                } else {
                                    // Create image element if placeholder exists
                                    if (profileImagePlaceholder) {
                                        profileImagePlaceholder.style.display = 'none';
                                        const img = document.createElement('img');
                                        img.id = 'profileImage';
                                        img.src = data.image_url + '?t=' + new Date().getTime();
                                        img.className = 'rounded-circle';
                                        img.style.cssText = 'width: 150px; height: 150px; object-fit: cover; border: 3px solid midnightblue;';
                                        profileImagePlaceholder.parentElement.insertBefore(img, profileImagePlaceholder);
                                    }
                                }
                                // Show remove button
                                if (removeImageBtn) {
                                    removeImageBtn.classList.remove('d-none');
                                }
                                // Update camera icon to pencil icon
                                const cameraLabel = document.querySelector('label[for="profileImageInput"]');
                                if (cameraLabel) {
                                    const icon = cameraLabel.querySelector('i');
                                    if (icon) {
                                        icon.classList.remove('bi-camera-fill');
                                        icon.classList.add('bi-pencil-fill');
                                    }
                                    cameraLabel.title = 'Edit/Replace Profile Image';
                                }
                            } else {
                                showImageAlert(data.message || 'Failed to update image.', 'danger');
                            }
                            // Clear the input
                            e.target.value = '';
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showImageAlert('An error occurred while uploading the image.', 'danger');
                        });
                    });
                }
                
                // Remove Image
                if (removeImageBtn) {
                    removeImageBtn.addEventListener('click', function() {
                        if (confirm('Are you sure you want to remove your profile image? This action cannot be undone.')) {
                            // Show loading
                            showImageAlert('Removing image...', 'info');
                            removeImageBtn.disabled = true;
                            
                            fetch('{{ route("profile.delete-image") }}', {
                                method: 'DELETE',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Hide image and show placeholder
                                    if (profileImage) {
                                        profileImage.style.display = 'none';
                                    }
                                    if (profileImagePlaceholder) {
                                        profileImagePlaceholder.style.display = 'flex';
                                    }
                                    removeImageBtn.classList.add('d-none');
                                    showImageAlert('Profile image removed successfully.', 'success');
                                } else {
                                    showImageAlert(data.message || 'Failed to remove image.', 'danger');
                                    removeImageBtn.disabled = false;
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showImageAlert('An error occurred while removing the image.', 'danger');
                                removeImageBtn.disabled = false;
                            });
                        }
                    });
                }
                
                function showImageAlert(message, type) {
                    if (imageUploadAlert && imageUploadMessage) {
                        imageUploadAlert.className = 'alert alert-' + type + ' alert-dismissible fade show';
                        imageUploadMessage.textContent = message;
                        imageUploadAlert.classList.remove('d-none');
                        
                        // Auto-hide after 5 seconds for success/info
                        if (type === 'success' || type === 'info') {
                            setTimeout(() => {
                                imageUploadAlert.classList.add('d-none');
                            }, 5000);
                        }
                    }
                }
            </script>
        </div>
    </div>
</div>
@endsection
