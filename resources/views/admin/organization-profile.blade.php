@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main class="col-md-10 py-4">
        @php
            $previousUrl = url()->previous();
            $currentUrl = url()->current();
            $backUrl = $previousUrl;
            if (!$backUrl || $backUrl === $currentUrl) {
                $backUrl = route('admin.organizations.index');
            }
        @endphp
        <div class="admin-back-btn-wrap mb-3">
            <a href="{{ $backUrl }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="py-3">
            <div class="mb-4">
                <h1 class="h4 mb-0">{{ $organization->name }}</h1>
            </div>
            
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Organization Details</h5>
                                <button type="button" class="btn btn-sm btn-light" data-toggle="collapse" data-target="#organizationDetailsCollapse" aria-expanded="false" aria-controls="organizationDetailsCollapse">
                                    <i class="bi bi-chevron-down" id="organizationDetailsIcon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="collapse" id="organizationDetailsCollapse">
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Name:</th>
                                    <td>{{ $organization->name }}</td>
                                </tr>
                                <tr>
                                    <th>Acronym:</th>
                                    <td>{{ $organization->acronym ?? 'N/A' }}</td>
                                </tr>
                                @if($organization->department)
                                <tr>
                                    <th>Department:</th>
                                    <td>{{ $organization->department->name }}</td>
                                </tr>
                                @endif
                                @if(!$organization->department)
                                <tr>
                                    <th>Type:</th>
                                    <td><span class="badge bg-info">Non-Academic Organization</span></td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Mailing Address:</th>
                                    <td>{{ $organization->mailing_address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Org. Email Address:</th>
                                    <td>{{ $organization->official_email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Date Established:</th>
                                    <td>
                                        @if($organization->date_established)
                                            {{ \Carbon\Carbon::parse($organization->date_established)->format('F j, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Students:</th>
                                    <td><strong>{{ $studentCount }}</strong> <small class="text-muted">(Members of this organization)</small></td>
                                </tr>
                                <tr>
                                    <th>Organization Moderator:</th>
                                    <td>
                                        @if($organization->staff->count() > 0)
                                            @foreach($organization->staff as $staff)
                                                {{ $staff->first_name }} {{ $staff->last_name }}
                                                @if(!$loop->last), @endif
                                            @endforeach
                                        @else
                                            <span class="text-muted">Not Assigned</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editOrganizationModal">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <a href="{{ route('admin.organizational-structure', ['organization_id' => $organization->id]) }}" class="btn btn-primary">
                                    <i class="bi bi-diagram-3"></i> Organizational Structure
                                </a>
                            </div>
                        </div>
                        </div>
                    </div>
                    
                    <!-- Edit Organization Modal -->
                    <div class="modal fade" id="editOrganizationModal" tabindex="-1" aria-labelledby="editOrganizationModalLabel" aria-hidden="true" data-backdrop="true">
                        <div class="modal-dialog modal-xl" id="editOrganizationModalDialog">
                            <div class="modal-content" id="editOrganizationModalContent">
                                <div class="modal-header bg-primary text-white" id="editOrganizationModalHeader">
                                    <h5 class="modal-title" id="editOrganizationModalLabel">
                                        <i class="bi bi-arrows-move me-2"></i>Edit Organization Details
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="cursor: pointer; z-index: 1;">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="POST" action="{{ route('admin.organizations.profile.update', $organization->id) }}" id="editOrganizationForm">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control w-100" id="name" name="name" value="{{ old('name', $organization->name) }}" placeholder="Organization Name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="department_id" class="form-label">Department</label>
                                            <select name="department_id" id="department_id" class="form-select w-100">
                                                <option value="">None (Non-Academic Organization)</option>
                                                @foreach($departments ?? [] as $dept)
                                                    <option value="{{ $dept->id }}" {{ old('department_id', $organization->department_id) == $dept->id ? 'selected' : '' }}>
                                                        {{ $dept->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">Select a department for academic organizations, or leave as "None" for non-academic organizations.</small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="acronym" class="form-label">Acronym</label>
                                            <input type="text" class="form-control w-100" id="acronym" name="acronym" value="{{ old('acronym', $organization->acronym) }}" placeholder="e.g., SCIT">
                                        </div>
                                        <div class="mb-3">
                                            <label for="mailing_address" class="form-label">Mailing Address</label>
                                            <textarea class="form-control w-100" id="mailing_address" name="mailing_address" rows="2" placeholder="Enter mailing address">{{ old('mailing_address', $organization->mailing_address) }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="official_email" class="form-label">Org. Email Address</label>
                                            <input type="email" class="form-control w-100" id="official_email" name="official_email" value="{{ old('official_email', $organization->official_email) }}" placeholder="organization@ustp.edu.ph">
                                            <small class="form-text text-muted">This email will be used to send notifications about events.</small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="date_established" class="form-label">Date Established</label>
                                            <input type="date" class="form-control w-100" id="date_established" name="date_established" value="{{ old('date_established', $organization->date_established ? \Carbon\Carbon::parse($organization->date_established)->format('Y-m-d') : '') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="staff_id" class="form-label">Student Org. Moderator (Optional)</label>
                                            <select name="staff_id" id="staff_id" class="form-select w-100">
                                                <option value="">Select Staff Member</option>
                                                @foreach($allStaff as $staff)
                                                    <option value="{{ $staff->id }}" 
                                                            data-department-id="{{ $staff->department_id ?? '' }}"
                                                            {{ old('staff_id', $organization->staff->contains('id', $staff->id) ? $staff->id : '') == $staff->id ? 'selected' : '' }}>
                                                        {{ $staff->first_name }} {{ $staff->last_name }} 
                                                        @if($staff->department)
                                                            ({{ $staff->department->name }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">All staff members can be selected as Student Org. Moderator.</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($allStudents->count() > 0)
            <div class="card">
                <div class="card-header bg-blue text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            Student Members List
                            @if(request('search') || request('year_level'))
                                <span class="badge bg-light text-dark ms-2">
                                    Showing {{ $allStudents->count() }} of {{ $studentCount }} students
                                </span>
                            @endif
                        </h5>
                        <button type="button" class="btn btn-sm btn-light" data-toggle="collapse" data-target="#studentMembersCollapse" aria-expanded="false" aria-controls="studentMembersCollapse">
                            <i class="bi bi-chevron-down" id="studentMembersIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="collapse" id="studentMembersCollapse">
                <div class="card-body">
                    <!-- Search Bar -->
                    <div class="mb-3 pb-3 border-bottom">
                        <form method="GET" action="{{ route('admin.organizations.profile', $organization->id) }}" class="d-flex align-items-center justify-content-between gap-3">
                            <div class="flex-grow-1" style="flex: 1 1 40%; max-width: 45%;">
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Enter student ID or name...">
                            </div>
                            <div class="flex-shrink-0" style="width: 200px; margin: 0 auto;">
                                <label for="year_level" class="form-label mb-0 small">Year Level</label>
                                <select class="form-select form-select-sm" id="year_level" name="year_level">
                                    <option value="">All Year Levels</option>
                                    <option value="1" {{ request('year_level') == '1' ? 'selected' : '' }}>1st Year</option>
                                    <option value="2" {{ request('year_level') == '2' ? 'selected' : '' }}>2nd Year</option>
                                    <option value="3" {{ request('year_level') == '3' ? 'selected' : '' }}>3rd Year</option>
                                    <option value="4" {{ request('year_level') == '4' ? 'selected' : '' }}>4th Year</option>
                                    <option value="5" {{ request('year_level') == '5' ? 'selected' : '' }}>5th Year</option>
                                </select>
                            </div>
                            <div class="flex-shrink-0 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm" title="Search">
                                    <i class="bi bi-search"></i>
                                </button>
                                <a href="{{ route('admin.organizations.profile', $organization->id) }}" class="btn btn-secondary btn-sm" title="Clear">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Course</th>
                                    <th>Year Level</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allStudents as $student)
                                <tr>
                                    <td>{{ $student->user_id ?? '-' }}</td>
                                    <td>{{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }}</td>
                                    <td>{{ $student->department->name ?? '-' }}</td>
                                    <td>{{ $student->course->name ?? '-' }}</td>
                                    <td>{{ $student->year_level ?? '-' }}</td>
                                    <td>
                                        @php
                                            $userId = $student->id ?? ($student->user->id ?? null);
                                            $totalPoints = $userId ? \App\Models\StudentPoint::where('user_id', $userId)->sum('points') : 0;
                                        @endphp
                                        <span class="badge bg-success">{{ $totalPoints }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>
            </div>
            @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                @if(request('search') || request('year_level'))
                    No students found matching your search criteria.
                    <a href="{{ route('admin.organizations.profile', $organization->id) }}" class="alert-link">Clear filters</a> to see all students.
                @else
                    This organization currently has no student members.
                @endif
            </div>
            @endif

            <!-- Organization Files Section -->
            <div class="card mt-4">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-folder"></i> Organization Files</h5>
                        <button type="button" class="btn btn-sm btn-light" data-toggle="collapse" data-target="#organizationFilesCollapse" aria-expanded="true" aria-controls="organizationFilesCollapse">
                            <i class="bi bi-chevron-up" id="organizationFilesIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="collapse show" id="organizationFilesCollapse">
                <div class="card-body">
                    @if(session('file_success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('file_success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if(session('file_error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('file_error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead style="background-color: #f0f0f0;">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 35%;">File Name</th>
                                    <th style="width: 15%;">File Size</th>
                                    <th style="width: 15%;">Uploaded By</th>
                                    <th style="width: 15%;">Uploaded At</th>
                                    <th style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $fileIndex = 0;
                                    $categoryOrder = [
                                        'accreditation_checklist' => 0,
                                        'application_letter' => 1,
                                        'accreditation_form' => 2,
                                        'concept_paper' => 3,
                                        'constitution' => 4,
                                        'organizational_profile' => 5.1,
                                        'officers_members_list' => 5.2,
                                        'personal_data_sheet' => 5.3,
                                        'organizational structure' => 5.4,
                                        'moderatorship_letter' => 7,
                                    ];
                                    
                                    // Check user access once
                                    $user = auth()->user();
                                    $isAdmin = (int)($user->role ?? 0) === 4;
                                    $isStaff = (int)($user->role ?? 0) === 2;
                                    $hasAccess = false;
                                    if ($isAdmin) {
                                        $hasAccess = true;
                                    } elseif ($isStaff) {
                                        $staff = \App\Models\Staff::where('email', $user->email)->first();
                                        if ($staff) {
                                            if ($staff->organization_id == $organization->id || 
                                                $staff->organizations()->where('organizations.id', $organization->id)->exists()) {
                                                $hasAccess = true;
                                            }
                                        }
                                        if (!$hasAccess && ($user->organization_id == $organization->id || 
                                            (method_exists($user, 'otherOrganizations') && $user->otherOrganizations()->where('organizations.id', $organization->id)->exists()))) {
                                            $hasAccess = true;
                                        }
                                    }
                                @endphp
                                @foreach($requiredFileCategories as $categoryKey => $categoryName)
                                    @php
                                        $categoryFiles = $files->get($categoryKey, collect());
                                        $displayName = $categoryName;
                                        if ($categoryKey === 'constitution') {
                                            $displayName = str_replace('(Org.Name)', $organization->name, $categoryName);
                                        }
                                    @endphp
                                    <tr style="background-color: #f8f9fa;">
                                        <td colspan="6" style="font-weight: bold; padding: 10px;">
                                            <i class="bi bi-file-earmark"></i> {{ $fileIndex }}. {{ $displayName }}
                                        </td>
                                    </tr>
                                    @if($categoryFiles->isEmpty())
                                        <tr>
                                            <td></td>
                                            <td colspan="5" class="text-muted">
                                                <em>No file uploaded</em>
                                                @if($hasAccess)
                                                    <button type="button" class="btn btn-sm btn-primary ml-2" data-toggle="modal" data-target="#uploadModal{{ $fileIndex }}">
                                                        <i class="bi bi-cloud-upload"></i> Upload
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        @foreach($categoryFiles as $file)
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <i class="bi bi-file-earmark"></i> {{ $file->file_name }}
                                                </td>
                                                <td>{{ $file->human_readable_size }}</td>
                                                <td>{{ $file->uploader->first_name ?? '' }} {{ $file->uploader->last_name ?? '' }}</td>
                                                <td>{{ $file->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <a href="{{ route('admin.organizations.profile.file.view', [$organization->id, $file->id]) }}" 
                                                           target="_blank" 
                                                           title="View Only"
                                                           class="file-action-icon"
                                                           style="background: transparent; border: none; padding: 0.25rem; color: black; text-decoration: none;">
                                                            <i class="bi bi-eye" style="font-size: 0.875rem;"></i>
                                                        </a>
                                                        <a href="{{ route('admin.organizations.profile.file.download', [$organization->id, $file->id]) }}" 
                                                           title="Download"
                                                           class="file-action-icon"
                                                           style="background: transparent; border: none; padding: 0.25rem; color: black; text-decoration: none;">
                                                            <i class="bi bi-download" style="font-size: 0.875rem;"></i>
                                                        </a>
                                                        @if($hasAccess)
                                                            <button type="button" 
                                                                    class="file-action-icon"
                                                                    data-toggle="modal" 
                                                                    data-target="#uploadModal{{ $fileIndex }}"
                                                                    title="Upload Another File"
                                                                    style="background: transparent; border: none; padding: 0.25rem; color: black; cursor: pointer;">
                                                                <i class="bi bi-cloud-upload" style="font-size: 0.875rem;"></i>
                                                            </button>
                                                            <form action="{{ route('admin.organizations.profile.file.delete', [$organization->id, $file->id]) }}" 
                                                                  method="POST" 
                                                                  class="d-inline" 
                                                                  onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" 
                                                                        title="Delete"
                                                                        class="file-action-icon"
                                                                        style="background: transparent; border: none; padding: 0.25rem; color: black; cursor: pointer;">
                                                                    <i class="bi bi-trash" style="font-size: 0.875rem;"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @php $fileIndex++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- All Upload Modals (placed outside table for proper display) -->
                    @php
                        $fileIndex = 0;
                    @endphp
                    @foreach($requiredFileCategories as $categoryKey => $categoryName)
                        @php
                            $displayName = $categoryName;
                            if ($categoryKey === 'constitution') {
                                $displayName = str_replace('(Org.Name)', $organization->name, $categoryName);
                            }
                        @endphp
                        @if($hasAccess)
                            <div class="modal fade" id="uploadModal{{ $fileIndex }}" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel{{ $fileIndex }}" aria-hidden="true" style="pointer-events: auto !important;">
                                <div class="modal-dialog" role="document" style="max-width: 500px; z-index: 1051; pointer-events: auto !important;">
                                    <div class="modal-content" style="background-color: #fff !important; z-index: 1052 !important; pointer-events: auto !important;">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="uploadModalLabel{{ $fileIndex }}">Upload: {{ $displayName }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="if(window.closeModalAndCleanup) window.closeModalAndCleanup(document.getElementById('uploadModal{{ $fileIndex }}'));" style="pointer-events: auto !important; cursor: pointer;">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{ route('admin.organizations.profile.file.upload', $organization->id) }}" method="POST" enctype="multipart/form-data" class="file-upload-form" data-modal-id="uploadModal{{ $fileIndex }}">
                                            @csrf
                                            <div class="modal-body">
                                                <div id="uploadMessage{{ $fileIndex }}" class="alert" style="display: none;"></div>
                                                <input type="hidden" name="file_category" value="{{ $categoryKey }}">
                                                <div class="form-group">
                                                    <label for="file{{ $fileIndex }}">Select File:</label>
                                                    <input type="file" class="form-control-file" id="file{{ $fileIndex }}" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls,.csv,.txt">
                                                    <small class="form-text text-muted">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX, XLS, CSV, TXT (Max: 20MB)</small>
                                                </div>
                                                <div class="form-group">
                                                    <label for="description{{ $fileIndex }}">Description (optional):</label>
                                                    <textarea class="form-control" id="description{{ $fileIndex }}" name="description" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="if(window.closeModalAndCleanup) window.closeModalAndCleanup(document.getElementById('uploadModal{{ $fileIndex }}'));">Cancel</button>
                                                <button type="submit" class="btn btn-primary" id="uploadBtn{{ $fileIndex }}">
                                                    <span class="upload-btn-text">Upload File</span>
                                                    <span class="upload-btn-spinner" style="display: none;">
                                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...
                                                    </span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @php $fileIndex++; @endphp
                    @endforeach
                </div>
                </div>
            </div>
        </div>
    </main>
  </div>
</div>

<style>
/* Hide back to top button on organization profile page */
.back-to-top {
    display: none !important;
}

/* File action icons styling */
.file-action-icon {
    transition: opacity 0.2s;
}
.file-action-icon:hover {
    opacity: 0.7;
}
.file-action-icon i {
    display: inline-block;
}

/* Fix modal-dialog-centered height issue */
.modal-dialog-centered {
    min-height: auto !important;
    height: auto !important;
}

.modal-dialog-centered::before {
    display: none !important;
    height: 0 !important;
    content: none !important;
}

/* Ensure modal dialog is visible and properly sized */
#uploadModal0 .modal-dialog,
#uploadModal1 .modal-dialog,
#uploadModal2 .modal-dialog,
#uploadModal3 .modal-dialog,
#uploadModal4 .modal-dialog,
#uploadModal5 .modal-dialog,
#uploadModal6 .modal-dialog,
#uploadModal7 .modal-dialog,
#uploadModal8 .modal-dialog,
#uploadModal9 .modal-dialog {
    max-width: 500px;
    margin: 0 !important; /* Remove auto margin to allow custom positioning */
    height: auto !important;
    min-height: auto !important;
    z-index: 1051 !important;
    position: fixed !important; /* Allow fixed positioning */
    top: auto !important; /* Override any default top positioning */
    left: auto !important; /* Override any default left positioning */
    transform: none !important; /* Remove Bootstrap's transform */
}

/* Ensure modal content is visible */
.modal.show .modal-dialog {
    z-index: 1051 !important;
    /* Position will be set by JavaScript for upload modals */
}

.modal.show .modal-content {
    z-index: 1052 !important;
    position: relative !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
}

/* Completely hide backdrop - no gray screen */
.modal-backdrop {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

/* Ensure modal is above backdrop */
.modal.show {
    z-index: 1050 !important;
}

.modal.show .modal-dialog {
    z-index: 1051 !important;
}

/* Force modal to be visible and clickable */
.modal.show {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
}

.modal.show .modal-dialog {
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
}

.modal.show .modal-content {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
    background-color: #fff !important;
}

/* Ensure backdrop is behind modal and doesn't block clicks */
.modal-backdrop {
    z-index: 1040 !important;
    pointer-events: none !important;
    opacity: 0.5 !important;
}

/* Ensure modal is above backdrop and visible */
.modal.show {
    z-index: 1050 !important;
    pointer-events: auto !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
}

/* Ensure modal dialog is clickable and interactive with proper z-index */
.modal.show .modal-dialog {
    pointer-events: auto !important;
    /* Position will be set by JavaScript for upload modals - don't force relative */
    z-index: 1051 !important;
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.modal.show .modal-content {
    pointer-events: auto !important;
    position: relative !important;
    z-index: 1052 !important;
    background-color: #fff !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 0.3rem !important;
}

.modal.show .modal-content * {
    pointer-events: auto !important;
    color: #000 !important;
}

/* Ensure modal header and body are visible */
.modal.show .modal-header,
.modal.show .modal-body,
.modal.show .modal-footer {
    background-color: #fff !important;
    color: #000 !important;
}

/* Force modal content to be visible with maximum priority */
#uploadModal0 .modal-content,
#uploadModal1 .modal-content,
#uploadModal2 .modal-content,
#uploadModal3 .modal-content,
#uploadModal4 .modal-content,
#uploadModal5 .modal-content,
#uploadModal6 .modal-content,
#uploadModal7 .modal-content,
#uploadModal8 .modal-content,
#uploadModal9 .modal-content {
    background-color: #ffffff !important;
    color: #000000 !important;
    z-index: 9999 !important;
    position: relative !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
}

#uploadModal0 .modal-dialog,
#uploadModal1 .modal-dialog,
#uploadModal2 .modal-dialog,
#uploadModal3 .modal-dialog,
#uploadModal4 .modal-dialog,
#uploadModal5 .modal-dialog,
#uploadModal6 .modal-dialog,
#uploadModal7 .modal-dialog,
#uploadModal8 .modal-dialog,
#uploadModal9 .modal-dialog {
    z-index: 9998 !important;
    position: fixed !important; /* Allow JavaScript to position it */
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    top: auto !important; /* Override any default top */
    left: auto !important; /* Override any default left */
    transform: none !important; /* Remove Bootstrap's transform */
}
</style>

<style>
/* Edit Organization Modal - Centered and Draggable */
#editOrganizationModal .modal-dialog {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    margin: 0 !important;
    max-width: 900px !important;
    width: 90% !important;
    max-height: 90vh !important;
    display: flex !important;
    flex-direction: column !important;
    z-index: 1050 !important;
}

#editOrganizationModal.show .modal-dialog {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    margin: 0 !important;
}

#editOrganizationModal .modal-content {
    max-height: 90vh !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    border-radius: 8px !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3) !important;
}

#editOrganizationModal .modal-body {
    overflow-y: auto !important;
    flex: 1 !important;
    max-height: calc(90vh - 150px) !important;
    padding: 1.5rem !important;
}

#editOrganizationModal .modal-header {
    flex-shrink: 0 !important;
    padding: 1rem 1.5rem !important;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2) !important;
}

#editOrganizationModal .modal-footer {
    flex-shrink: 0 !important;
    padding: 1rem 1.5rem !important;
    border-top: 1px solid #dee2e6 !important;
}

/* Draggable cursor on header */
#editOrganizationModalHeader {
    cursor: move !important;
    user-select: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
}

#editOrganizationModalHeader:hover {
    background-color: rgba(3, 1, 45, 0.95) !important;
}

#editOrganizationModalHeader .close {
    cursor: pointer !important;
    z-index: 1 !important;
    position: relative !important;
}

#editOrganizationModalHeader .modal-title {
    cursor: move !important;
    flex: 1 !important;
}

/* Ensure modal is visible and above backdrop */
#editOrganizationModal.show {
    display: block !important;
    z-index: 1050 !important;
}

#editOrganizationModal.show .modal-dialog {
    z-index: 1051 !important;
}

#editOrganizationModal.show .modal-content {
    z-index: 1052 !important;
}
</style>

@push('scripts')
<script>
// Make Edit Organization Modal draggable and centered
$(document).ready(function() {
    let isDragging = false;
    let startX, startY, initialX, initialY;
    let currentX = 0;
    let currentY = 0;
    
    const modal = $('#editOrganizationModal');
    const modalDialog = $('#editOrganizationModalDialog');
    const modalHeader = $('#editOrganizationModalHeader');
    
    // Center modal on show
    modal.on('show.bs.modal', function() {
        // Reset position to center
        currentX = 0;
        currentY = 0;
        
        // Wait for modal to be shown, then center it
        setTimeout(function() {
            const windowWidth = $(window).width();
            const windowHeight = $(window).height();
            const dialogWidth = modalDialog.outerWidth();
            const dialogHeight = modalDialog.outerHeight();
            
            const centerX = windowWidth / 2;
            const centerY = windowHeight / 2;
            
            modalDialog.css({
                'position': 'fixed',
                'top': centerY + 'px',
                'left': centerX + 'px',
                'transform': 'translate(-50%, -50%)',
                'margin': '0',
                'max-width': '900px',
                'width': '90%',
                'max-height': '90vh',
                'z-index': '1051'
            });
        }, 50);
    });
    
    // Make modal draggable by header
    modalHeader.on('mousedown', function(e) {
        // Don't drag if clicking on close button
        if ($(e.target).closest('.close').length || $(e.target).is('.close')) {
            return;
        }
        
        // Only start dragging if clicking on header or title
        if ($(e.target).closest('#editOrganizationModalHeader').length) {
            isDragging = true;
            
            // Get initial mouse position
            startX = e.clientX;
            startY = e.clientY;
            
            // Get current modal position
            const dialogOffset = modalDialog.offset();
            const dialogWidth = modalDialog.outerWidth();
            const dialogHeight = modalDialog.outerHeight();
            
            // Calculate center position
            initialX = dialogOffset.left + (dialogWidth / 2);
            initialY = dialogOffset.top + (dialogHeight / 2);
            
            // Prevent text selection while dragging
            e.preventDefault();
        }
    });
    
    $(document).on('mousemove', function(e) {
        if (isDragging && modal.hasClass('show')) {
            e.preventDefault();
            
            // Calculate mouse movement
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;
            
            // Calculate new center position
            const newX = initialX + deltaX;
            const newY = initialY + deltaY;
            
            // Keep modal within viewport bounds
            const windowWidth = $(window).width();
            const windowHeight = $(window).height();
            const dialogWidth = modalDialog.outerWidth();
            const dialogHeight = modalDialog.outerHeight();
            
            const minX = dialogWidth / 2;
            const maxX = windowWidth - (dialogWidth / 2);
            const minY = dialogHeight / 2;
            const maxY = windowHeight - (dialogHeight / 2);
            
            const constrainedX = Math.max(minX, Math.min(maxX, newX));
            const constrainedY = Math.max(minY, Math.min(maxY, newY));
            
            // Update modal position
            modalDialog.css({
                'position': 'fixed',
                'top': constrainedY + 'px',
                'left': constrainedX + 'px',
                'transform': 'translate(-50%, -50%)',
                'margin': '0'
            });
            
            // Store current position
            currentX = constrainedX - (windowWidth / 2);
            currentY = constrainedY - (windowHeight / 2);
        }
    });
    
    $(document).on('mouseup', function() {
        if (isDragging) {
            isDragging = false;
        }
    });
    
    // Reset position when modal is hidden
    modal.on('hidden.bs.modal', function() {
        currentX = 0;
        currentY = 0;
        isDragging = false;
    });
    
    // Handle window resize to keep modal centered or maintain position
    $(window).on('resize', function() {
        if (modal.hasClass('show')) {
            const windowWidth = $(window).width();
            const windowHeight = $(window).height();
            const dialogWidth = modalDialog.outerWidth();
            const dialogHeight = modalDialog.outerHeight();
            
            // If modal was moved, maintain relative position; otherwise center
            if (currentX === 0 && currentY === 0) {
                const centerX = windowWidth / 2;
                const centerY = windowHeight / 2;
                modalDialog.css({
                    'top': centerY + 'px',
                    'left': centerX + 'px'
                });
            } else {
                // Maintain relative position
                const newX = (windowWidth / 2) + currentX;
                const newY = (windowHeight / 2) + currentY;
                
                const minX = dialogWidth / 2;
                const maxX = windowWidth - (dialogWidth / 2);
                const minY = dialogHeight / 2;
                const maxY = windowHeight - (dialogHeight / 2);
                
                const constrainedX = Math.max(minX, Math.min(maxX, newX));
                const constrainedY = Math.max(minY, Math.min(maxY, newY));
                
                modalDialog.css({
                    'top': constrainedY + 'px',
                    'left': constrainedX + 'px'
                });
            }
        }
    });
});

// Override Bootstrap modal behavior to prevent blocking
(function() {
    // Intercept Bootstrap's modal show/hide to prevent body blocking
    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        const originalModal = jQuery.fn.modal;
        jQuery.fn.modal = function(option) {
            const result = originalModal.apply(this, arguments);
            
            // After modal operations, ensure body is never blocked
            setTimeout(function() {
                // Remove modal-open class if it blocks scrolling
                if (document.body.classList.contains('modal-open')) {
                    // Don't remove the class, but override the styles it applies
                    document.body.style.overflow = 'auto !important';
                    document.body.style.paddingRight = '';
                    document.body.style.pointerEvents = 'auto !important';
                }
                
                // Ensure all backdrops don't block
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(function(backdrop) {
                    backdrop.style.pointerEvents = 'none';
                    backdrop.style.zIndex = '1040';
                });
            }, 10);
            
            return result;
        };
    }
    
    // Use MutationObserver to watch for modal-open class and body style changes
    const bodyObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                // If modal-open is added, immediately override its effects
                if (document.body.classList.contains('modal-open')) {
                    document.body.style.setProperty('overflow', 'auto', 'important');
                    document.body.style.setProperty('pointer-events', 'auto', 'important');
                    document.body.style.paddingRight = '';
                }
            }
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                // If overflow is set to hidden, override it
                if (document.body.style.overflow === 'hidden') {
                    document.body.style.setProperty('overflow', 'auto', 'important');
                }
                if (document.body.style.pointerEvents === 'none') {
                    document.body.style.setProperty('pointer-events', 'auto', 'important');
                }
            }
        });
    });
    
    // Start observing body for class and style changes
    bodyObserver.observe(document.body, {
        attributes: true,
        attributeFilter: ['class', 'style']
    });
    
    // Watch for backdrop creation
    const backdropObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
                    // Immediately set backdrop to not block
                    node.style.setProperty('pointer-events', 'none', 'important');
                    node.style.setProperty('z-index', '1040', 'important');
                }
            });
        });
    });
    
    // Start observing document body for backdrop creation
    backdropObserver.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    function cleanupOnLoad() {
        // Remove all modal backdrops that might be left over
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(function(backdrop) {
            backdrop.remove();
        });
        
        // Remove modal-open class from body
        document.body.classList.remove('modal-open');
        document.body.style.setProperty('overflow', 'auto', 'important');
        document.body.style.paddingRight = '';
        document.body.style.setProperty('pointer-events', 'auto', 'important');
        
        // Close any open modals
        const openModals = document.querySelectorAll('.modal.show');
        openModals.forEach(function(modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');
        });
    }
    
    // Run cleanup immediately and after DOM is ready
    cleanupOnLoad();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', cleanupOnLoad);
    } else {
        setTimeout(cleanupOnLoad, 100);
    }
    
    // Also cleanup on page visibility change (in case page was hidden during upload)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(cleanupOnLoad, 100);
        }
    });
    
    // Emergency cleanup - allow user to press Ctrl+Shift+C to force cleanup
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'C') {
            cleanupOnLoad();
            alert('Modal cleanup forced. Page should be responsive now.');
        }
    });
})();

// Ensure Bootstrap modals work with jQuery - wait for full page load
(function() {
    function initModals() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.modal === 'undefined') {
            console.log('jQuery or Bootstrap modal not ready, retrying...');
            setTimeout(initModals, 100);
            return;
        }
        
        var $ = jQuery;
        console.log('Initializing upload modals');
        
        // Handle all buttons with data-toggle="modal"
        $(document).off('click', '[data-toggle="modal"]').on('click', '[data-toggle="modal"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Save current scroll position
            var scrollPos = window.pageYOffset || document.documentElement.scrollTop;
            
            var $button = $(this);
            var target = $button.attr('data-target') || $button.data('target');
            
            if (target) {
                console.log('Opening modal:', target);
                var $modal = $(target);
                
                if ($modal.length === 0) {
                    console.error('Modal not found:', target);
                    alert('Modal not found. Please refresh the page.');
                    return;
                }
                
                console.log('Modal found, element:', $modal[0]);
                console.log('Modal classes:', $modal.attr('class'));
                
                // Get button position relative to viewport (for fixed positioning)
                var buttonRect = $button[0].getBoundingClientRect();
                var buttonTop = buttonRect.top; // Position relative to viewport
                var buttonLeft = buttonRect.left;
                var buttonHeight = buttonRect.height;
                var buttonWidth = buttonRect.width;
                var viewportHeight = $(window).height();
                var viewportWidth = $(window).width();
                
                // Remove any existing modal instances
                $modal.removeData('bs.modal');
                
                // Try to show modal
                try {
                    // Ensure modal has proper positioning and z-index
                    $modal.css({
                        'position': 'fixed',
                        'top': '0',
                        'left': '0',
                        'z-index': '1050',
                        'width': '100%',
                        'height': '100%',
                        'overflow-x': 'hidden',
                        'overflow-y': 'auto',
                        'display': 'block',
                        'opacity': '1',
                        'visibility': 'visible'
                    });
                    
                    // First, ensure modal itself is visible BEFORE calling modal('show')
                    $modal.addClass('show');
                    $modal.css({
                        'display': 'block',
                        'opacity': '1',
                        'visibility': 'visible',
                        'position': 'fixed',
                        'top': '0',
                        'left': '0',
                        'z-index': '1050'
                    });
                    $modal.removeAttr('aria-hidden');
                    $modal.attr('aria-modal', 'true');
                    
                    // Cleanup any existing backdrops before showing
                    if (window.cleanupBackdrops) {
                        window.cleanupBackdrops();
                    }
                    
                    // Use Bootstrap 4 modal method
                    $modal.modal('show');
                    console.log('Modal show called');
                    console.log('Modal has show class:', $modal.hasClass('show'));
                    
                    // Immediately set backdrop to not block after modal shows
                    setTimeout(function() {
                        $('.modal-backdrop').css({
                            'pointer-events': 'none',
                            'z-index': '1040'
                        });
                        // Ensure body can still scroll and is not blocked
                        $('body').css({
                            'overflow': 'auto',
                            'pointer-events': 'auto'
                        });
                        document.documentElement.style.overflow = 'auto';
                        document.documentElement.style.pointerEvents = 'auto';
                    }, 50);
                    
                    // Position modal dialog near the button after modal is shown
                    $modal.one('shown.bs.modal', function() {
                        // Hide backdrop completely
                        $('.modal-backdrop').css({
                            'display': 'none',
                            'visibility': 'hidden',
                            'opacity': '0',
                            'pointer-events': 'none'
                        });
                        $('body').css({
                            'overflow': 'auto',
                            'pointer-events': 'auto'
                        });
                        
                        // Position modal dialog near the button
                        var $dialog = $modal.find('.modal-dialog');
                        if ($dialog.length > 0) {
                            // Function to position modal
                            function positionModal() {
                                // Recalculate button position relative to current viewport (in case page scrolled)
                                var currentButtonRect = $button[0].getBoundingClientRect();
                                var currentButtonTop = currentButtonRect.top;
                                var currentButtonLeft = currentButtonRect.left;
                                var currentButtonHeight = currentButtonRect.height;
                                var currentButtonWidth = currentButtonRect.width;
                                
                                // Get actual modal dimensions
                                var modalHeight = $dialog.outerHeight() || 400; // Fallback if not yet rendered
                                var modalWidth = $dialog.outerWidth() || 500; // Fallback if not yet rendered
                                var currentViewportHeight = $(window).height();
                                var currentViewportWidth = $(window).width();
                                
                                // Position below button with some spacing (20px)
                                var desiredTop = currentButtonTop + currentButtonHeight + 20;
                                
                                // Ensure modal doesn't go below viewport
                                if (desiredTop + modalHeight > currentViewportHeight - 20) {
                                    // Position above button instead
                                    desiredTop = currentButtonTop - modalHeight - 20;
                                    // If still doesn't fit, position at top of viewport
                                    if (desiredTop < 20) {
                                        desiredTop = 20;
                                    }
                                }
                                
                                // Center horizontally relative to button, but keep within viewport
                                var desiredLeft = currentButtonLeft + (currentButtonWidth / 2) - (modalWidth / 2);
                                if (desiredLeft < 20) desiredLeft = 20;
                                if (desiredLeft + modalWidth > currentViewportWidth - 20) {
                                    desiredLeft = currentViewportWidth - modalWidth - 20;
                                }
                                
                                // Apply positioning with !important to override any CSS
                                var dialogElement = $dialog[0];
                                dialogElement.style.setProperty('position', 'fixed', 'important');
                                dialogElement.style.setProperty('top', desiredTop + 'px', 'important');
                                dialogElement.style.setProperty('left', desiredLeft + 'px', 'important');
                                dialogElement.style.setProperty('margin', '0', 'important');
                                dialogElement.style.setProperty('margin-top', '0', 'important');
                                dialogElement.style.setProperty('margin-left', '0', 'important');
                                dialogElement.style.setProperty('margin-right', '0', 'important');
                                dialogElement.style.setProperty('margin-bottom', '0', 'important');
                                dialogElement.style.setProperty('transform', 'none', 'important');
                                dialogElement.style.setProperty('-webkit-transform', 'none', 'important');
                                dialogElement.style.setProperty('max-width', '500px', 'important');
                                dialogElement.style.setProperty('z-index', '1051', 'important');
                                
                                // Also remove any classes that might interfere
                                $dialog.removeClass('modal-dialog-centered');
                            }
                            
                            // Try positioning immediately
                            positionModal();
                            
                            // Also try after delays to ensure modal is fully rendered
                            setTimeout(positionModal, 10);
                            setTimeout(positionModal, 50);
                            setTimeout(positionModal, 150);
                            setTimeout(positionModal, 300);
                        }
                        
                        // Keep scroll position
                        window.scrollTo(0, scrollPos);
                    });
                    
                    // Ensure modal dialog is visible and positioned correctly
                    setTimeout(function() {
                        // Ensure modal still has show class
                        if (!$modal.hasClass('show')) {
                            $modal.addClass('show');
                        }
                        
                        var $dialog = $modal.find('.modal-dialog');
                        if ($dialog.length > 0) {
                            // Remove flexbox behavior that might be causing height issues
                            $dialog.css({
                                'position': 'relative',
                                'z-index': '1051',
                                'margin': '1.75rem auto',
                                'max-width': '500px',
                                'width': '90%',
                                'display': 'flex',
                                'flex-direction': 'column',
                                'align-items': 'center',
                                'justify-content': 'center',
                                'min-height': 'auto',
                                'height': 'auto',
                                'pointer-events': 'auto',
                                'opacity': '1',
                                'visibility': 'visible',
                                'transform': 'none'
                            });
                            
                            // Remove the ::before pseudo-element height by overriding it
                            $dialog.css('min-height', 'auto');
                            $dialog[0].style.setProperty('min-height', 'auto', 'important');
                            
                            console.log('Modal dialog positioned');
                            console.log('Dialog visibility:', $dialog.is(':visible'));
                            console.log('Dialog height:', $dialog.height());
                            console.log('Dialog position:', $dialog.css('position'));
                            console.log('Dialog opacity:', $dialog.css('opacity'));
                            console.log('Dialog top:', $dialog.offset().top);
                            console.log('Dialog left:', $dialog.offset().left);
                            console.log('Dialog computed height:', window.getComputedStyle($dialog[0]).height);
                        }
                        
                        // Ensure modal content is visible
                        var $content = $modal.find('.modal-content');
                        if ($content.length > 0) {
                            $content.css({
                                'display': 'block',
                                'position': 'relative',
                                'z-index': '1052',
                                'pointer-events': 'auto',
                                'opacity': '1',
                                'visibility': 'visible'
                            });
                            console.log('Modal content positioned');
                            console.log('Content visibility:', $content.is(':visible'));
                            console.log('Content height:', $content.height());
                            console.log('Content opacity:', $content.css('opacity'));
                        }
                        
                        // Ensure backdrop is behind modal (z-index 1040)
                        // Remove any duplicate backdrops
                        $('.modal-backdrop').not(':first').remove();
                        
                        $('.modal-backdrop').css({
                            'z-index': '1040',
                            'opacity': '0.5',
                            'pointer-events': 'none' // Don't block clicks - modal content should handle them
                        });
                        
                        // Ensure backdrop doesn't cover modal content
                        $('.modal-backdrop').css('z-index', '1040');
                        
                        // Set modal to be visible and clickable
                        $modal.css({
                            'z-index': '1050',
                            'pointer-events': 'auto', // Modal catches clicks
                            'display': 'block',
                            'opacity': '1',
                            'visibility': 'visible'
                        });
                        
                        // Force modal dialog to be on top and clickable (z-index 1051)
                        $dialog.css({
                            'z-index': '1051',
                            'pointer-events': 'auto', // Dialog catches clicks
                            'position': 'relative',
                            'display': 'flex',
                            'opacity': '1',
                            'visibility': 'visible'
                        });
                        
                        // Force modal content to be on top and clickable (z-index 1052) with white background
                        $content.css({
                            'z-index': '1052',
                            'pointer-events': 'auto', // Content catches clicks
                            'position': 'relative',
                            'background-color': '#fff',
                            'display': 'block',
                            'opacity': '1',
                            'visibility': 'visible',
                            'border': '1px solid #dee2e6',
                            'border-radius': '0.3rem'
                        });
                        
                        // Ensure all text is visible
                        $content.find('*').css('color', '#000');
                        
                        // Ensure all form elements are clickable
                        $content.find('input, button, textarea, select, label').css('pointer-events', 'auto');
                        
                        // Ensure modal is scrollable (but don't scroll page)
                        $modal.scrollTop(0);
                        
                        // Make sure dialog is visible by checking if it's in viewport
                        var dialogTop = $dialog.offset().top;
                        var viewportHeight = $(window).height();
                        console.log('Dialog top:', dialogTop, 'Viewport height:', viewportHeight);
                        
                        // Scroll modal to top (but not the page)
                        $modal.scrollTop(0);
                        
                        // Restore page scroll position (don't scroll page to top)
                        if (scrollPos > 0) {
                            window.scrollTo(0, scrollPos);
                        }
                        
                        console.log('Modal fully initialized');
                        console.log('Modal has show class:', $modal.hasClass('show'));
                        console.log('Modal display:', $modal.css('display'));
                        console.log('Modal opacity:', $modal.css('opacity'));
                        console.log('Modal visibility:', $modal.css('visibility'));
                        console.log('Backdrop z-index:', $('.modal-backdrop').css('z-index'));
                        console.log('Dialog z-index:', $dialog.css('z-index'));
                        console.log('Content z-index:', $content.css('z-index'));
                        console.log('Modal scrollTop:', $modal.scrollTop());
                        console.log('Window scrollTop:', $(window).scrollTop());
                        
                        // Final check - try to make modal more visible with explicit styles
                        $modal[0].style.setProperty('display', 'block', 'important');
                        $modal[0].style.setProperty('opacity', '1', 'important');
                        $modal[0].style.setProperty('visibility', 'visible', 'important');
                        $modal[0].style.setProperty('z-index', '1050', 'important');
                        
                        // Ensure dialog is visible
                        if ($dialog.length > 0) {
                            $dialog[0].style.setProperty('display', 'flex', 'important');
                            $dialog[0].style.setProperty('opacity', '1', 'important');
                            $dialog[0].style.setProperty('visibility', 'visible', 'important');
                            $dialog[0].style.setProperty('z-index', '1051', 'important');
                            $dialog[0].style.setProperty('background-color', 'transparent', 'important');
                        }
                        
                        // Ensure content is visible with white background - use maximum z-index
                        if ($content.length > 0) {
                            $content[0].style.setProperty('display', 'block', 'important');
                            $content[0].style.setProperty('opacity', '1', 'important');
                            $content[0].style.setProperty('visibility', 'visible', 'important');
                            $content[0].style.setProperty('z-index', '9999', 'important');
                            $content[0].style.setProperty('background-color', '#ffffff', 'important');
                            $content[0].style.setProperty('border', '1px solid #dee2e6', 'important');
                            $content[0].style.setProperty('pointer-events', 'auto', 'important');
                            $content[0].style.setProperty('position', 'relative', 'important');
                            $content[0].style.setProperty('color', '#000000', 'important');
                            
                            // Also set background on child elements
                            $content.find('.modal-header, .modal-body, .modal-footer').css({
                                'background-color': '#fff',
                                'color': '#000'
                            });
                        }
                        
                        // Also ensure dialog has proper background and high z-index
                        if ($dialog.length > 0) {
                            $dialog[0].style.setProperty('background-color', 'transparent', 'important');
                            $dialog[0].style.setProperty('pointer-events', 'auto', 'important');
                            $dialog[0].style.setProperty('z-index', '9998', 'important');
                            $dialog[0].style.setProperty('position', 'relative', 'important');
                        }
                        
                        // Ensure modal itself has high z-index
                        $modal[0].style.setProperty('z-index', '9997', 'important');
                        
                        // Double-check backdrop is behind and move it before modal if needed
                        var $backdrop = $('.modal-backdrop').first();
                        if ($backdrop.length > 0) {
                            // Ensure backdrop z-index is lower and doesn't block clicks
                            $backdrop[0].style.setProperty('z-index', '1040', 'important');
                            $backdrop[0].style.setProperty('pointer-events', 'none', 'important');
                            
                            // Move backdrop BEFORE modal in DOM to ensure proper stacking
                            // This ensures backdrop renders first, then modal renders on top
                            if ($backdrop.next().length === 0 || !$backdrop.next().is($modal)) {
                                $backdrop.insertBefore($modal);
                            }
                        }
                        
                        // Ensure modal is in body (it should be)
                        if ($modal.parent().length > 0 && !$modal.parent().is('body')) {
                            $modal.detach().appendTo('body');
                        }
                        
                        // Force modal content to be visible with inline styles on all elements
                        if ($content.length > 0) {
                            // Set styles directly on the element - use direct property assignment
                            var contentEl = $content[0];
                            contentEl.style.setProperty('z-index', '9999', 'important');
                            contentEl.style.setProperty('background-color', '#ffffff', 'important');
                            contentEl.style.setProperty('color', '#000000', 'important');
                            contentEl.style.setProperty('position', 'relative', 'important');
                            contentEl.style.setProperty('display', 'block', 'important');
                            contentEl.style.setProperty('opacity', '1', 'important');
                            contentEl.style.setProperty('visibility', 'visible', 'important');
                            contentEl.style.setProperty('border', '1px solid #dee2e6', 'important');
                            contentEl.style.setProperty('border-radius', '0.3rem', 'important');
                            contentEl.style.setProperty('box-shadow', '0 0.5rem 1rem rgba(0, 0, 0, 0.15)', 'important');
                            contentEl.style.setProperty('pointer-events', 'auto', 'important');
                            
                            // Also set on header, body, footer - use direct style assignment
                            var header = $content.find('.modal-header')[0];
                            var body = $content.find('.modal-body')[0];
                            var footer = $content.find('.modal-footer')[0];
                            
                            if (header) {
                                header.style.setProperty('background-color', '#fff', 'important');
                                header.style.setProperty('color', '#000', 'important');
                            }
                            if (body) {
                                body.style.setProperty('background-color', '#fff', 'important');
                                body.style.setProperty('color', '#000', 'important');
                            }
                            if (footer) {
                                footer.style.setProperty('background-color', '#fff', 'important');
                                footer.style.setProperty('color', '#000', 'important');
                            }
                        }
                        
                        console.log('Modal forced to be visible with inline styles');
                        console.log('Backdrop count:', $('.modal-backdrop').length);
                        console.log('Backdrop z-index:', $('.modal-backdrop').first().css('z-index'));
                        console.log('Modal z-index:', $modal.css('z-index'));
                        console.log('Content z-index:', $content.css('z-index'));
                        console.log('Content background:', $content.css('background-color'));
                        console.log('Content computed background:', window.getComputedStyle($content[0]).backgroundColor);
                        console.log('Content offset:', $content.offset());
                        console.log('Content width:', $content.width());
                        console.log('Content height:', $content.height());
                    }, 200);
                } catch (err) {
                    console.error('Error showing modal:', err);
                    // Fallback: manual show
                    $modal.addClass('show');
                    $modal.css('display', 'block');
                    $modal.css('z-index', '1050');
                    $modal.attr('aria-hidden', 'false');
                    $modal.removeAttr('aria-hidden');
                    // Don't add modal-open class - it blocks scrolling
                    // $('body').addClass('modal-open');
                    // Instead, ensure body remains scrollable
                    $('body').css({
                        'overflow': 'auto',
                        'pointer-events': 'auto'
                    });
                    
                    // Ensure modal dialog is visible
                    var $dialog = $modal.find('.modal-dialog');
                    $dialog.css('position', 'relative');
                    $dialog.css('z-index', '1051');
                    $dialog.css('margin', '1.75rem auto');
                    
                    if ($('.modal-backdrop').length === 0) {
                        $('body').append('<div class="modal-backdrop fade show" style="z-index: 1040;"></div>');
                    }
                }
            }
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModals);
    } else {
        initModals();
    }
})();

// Prevent page scroll when clicking action icons
(function() {
    // Use sessionStorage to persist scroll position across page reloads
    const SCROLL_STORAGE_KEY = 'org_profile_scroll_position';
    
    // Save scroll position to sessionStorage
    function saveScrollPosition() {
        const scrollPos = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
        if (scrollPos > 0) {
            sessionStorage.setItem(SCROLL_STORAGE_KEY, scrollPos.toString());
        }
    }
    
    // Restore scroll position from sessionStorage
    function restoreScrollPosition() {
        const savedPos = sessionStorage.getItem(SCROLL_STORAGE_KEY);
        if (savedPos) {
            const scrollPos = parseInt(savedPos, 10);
            if (scrollPos > 0) {
                // Use requestAnimationFrame for smooth restoration
                requestAnimationFrame(function() {
                    window.scrollTo(0, scrollPos);
                    // Also try after a short delay to ensure it works
                    setTimeout(function() {
                        window.scrollTo(0, scrollPos);
                    }, 50);
                });
            }
        }
    }
    
    // Only restore scroll position on page load if we're coming from a form submission
    // Check if there's a form submission indicator (like a success message) AND we have a saved position
    const hasFormSubmission = document.querySelector('.alert-success, .alert-danger, [data-form-submitted]');
    const hasSavedPosition = sessionStorage.getItem(SCROLL_STORAGE_KEY);
    
    // Only restore if we have both a form submission indicator AND a saved position
    // This prevents restoring scroll on initial page load
    if (hasFormSubmission && hasSavedPosition) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(restoreScrollPosition, 100);
                // Clear the saved position after restoring
                sessionStorage.removeItem(SCROLL_STORAGE_KEY);
            });
        } else {
            setTimeout(function() {
                restoreScrollPosition();
                // Clear the saved position after restoring
                sessionStorage.removeItem(SCROLL_STORAGE_KEY);
            }, 100);
        }
    } else {
        // Clear any saved position if there's no form submission
        sessionStorage.removeItem(SCROLL_STORAGE_KEY);
    }
    
    // Handle all action icon clicks - prevent any default scrolling behavior
    document.addEventListener('click', function(e) {
        const actionIcon = e.target.closest('.file-action-icon');
        if (!actionIcon) return;
        
        // Save scroll position immediately
        saveScrollPosition();
        
        // Prevent any focus-related scrolling
        if (actionIcon.focus) {
            const originalFocus = actionIcon.focus;
            actionIcon.focus = function() {
                // Don't call original focus to prevent scroll
            };
            setTimeout(function() {
                actionIcon.focus = originalFocus;
            }, 1000);
        }
    }, true);
    
    // Handle download links - prevent scroll and preserve position, show success message
    document.addEventListener('click', function(e) {
        const downloadLink = e.target.closest('a.file-action-icon[href*="/file/"][href*="/download"]');
        if (downloadLink) {
            e.preventDefault();
            e.stopPropagation();
            saveScrollPosition();
            
            // Show success message
            showFileSuccessMessage('File download started successfully.');
            
            // Open download in a way that doesn't affect current page
            const url = downloadLink.getAttribute('href');
            const link = document.createElement('a');
            link.href = url;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Restore scroll only once after a short delay
            setTimeout(restoreScrollPosition, 100);
        }
    }, true);
    
    // Function to show success message
    function showFileSuccessMessage(message) {
        // Remove any existing success messages
        const existingAlert = document.querySelector('.alert-file-success');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Create success alert
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show alert-file-success';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = message + 
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        
        // Insert at the top of the Organization Files section
        const filesSection = document.querySelector('.card.mt-4 .card-body');
        if (filesSection) {
            // Insert before the first child (before existing alerts or table)
            filesSection.insertBefore(alertDiv, filesSection.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                if (alertDiv && alertDiv.parentNode) {
                    alertDiv.classList.remove('show');
                    setTimeout(function() {
                        if (alertDiv && alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }
    }
    
    // Handle modal buttons - prevent scroll
    document.addEventListener('click', function(e) {
        const modalButton = e.target.closest('[data-toggle="modal"].file-action-icon');
        if (modalButton) {
            e.preventDefault();
            e.stopPropagation();
            saveScrollPosition();
            
            // Use jQuery if available
            if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                var $ = jQuery;
                var target = modalButton.getAttribute('data-target') || modalButton.dataset.target;
                if (target) {
                    var $modal = $(target);
                    if ($modal.length > 0) {
                        var currentScrollPos = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
                        $modal.modal('show');
                        
                        // Restore scroll multiple times to ensure it sticks
                        $modal.on('shown.bs.modal', function() {
                            if (currentScrollPos > 0) {
                                window.scrollTo(0, currentScrollPos);
                                setTimeout(function() {
                                    window.scrollTo(0, currentScrollPos);
                                }, 100);
                                setTimeout(function() {
                                    window.scrollTo(0, currentScrollPos);
                                }, 300);
                            }
                        });
                        
                        // Also restore immediately
                        setTimeout(function() {
                            if (currentScrollPos > 0) {
                                window.scrollTo(0, currentScrollPos);
                            }
                        }, 50);
                    }
                }
            }
        }
    }, true);
    
    // Handle delete form submissions - save scroll for page reload
    document.addEventListener('submit', function(e) {
        const deleteForm = e.target.closest('form[action*="/file/"]');
        if (deleteForm && deleteForm.querySelector('.file-action-icon[type="submit"]')) {
            saveScrollPosition();
            // Mark that a form was submitted so we can restore scroll on reload
            document.body.setAttribute('data-form-submitted', 'true');
        }
    }, true);
    
    // Prevent any hash navigation from scrolling
    window.addEventListener('hashchange', function(e) {
        restoreScrollPosition();
    }, false);
    
    // Don't prevent focus - let normal focus behavior work
    // Only prevent scroll when clicking action icons, not during normal interactions
    
    // Only restore scroll position when explicitly needed (after actions), not during normal scrolling
    // Remove the aggressive monitoring that was causing issues
})();

// Ensure edit organization modal works
document.addEventListener('DOMContentLoaded', function() {
    const editButton = document.querySelector('[data-target="#editOrganizationModal"]');
    if (editButton) {
        editButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                jQuery('#editOrganizationModal').modal('show');
            } else {
                // Fallback if jQuery is not available
                const modal = document.getElementById('editOrganizationModal');
                if (modal) {
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    // Don't add modal-open class - it blocks scrolling
                    // document.body.classList.add('modal-open');
                    // Ensure body remains scrollable
                    document.body.style.overflow = 'auto';
                    document.body.style.pointerEvents = 'auto';
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.style.pointerEvents = 'none';
                    backdrop.style.zIndex = '1040';
                    backdrop.id = 'editOrgModalBackdrop';
                    document.body.appendChild(backdrop);
                }
            }
        });
    }
    
    // Handle collapse toggle for Student Members List
    const studentMembersCollapse = document.getElementById('studentMembersCollapse');
    const studentMembersIcon = document.getElementById('studentMembersIcon');
    const studentMembersButton = document.querySelector('[data-target="#studentMembersCollapse"]');
    
    if (studentMembersCollapse && studentMembersIcon && studentMembersButton) {
        // Bootstrap 4/5 collapse events
        studentMembersCollapse.addEventListener('show.bs.collapse', function() {
            studentMembersIcon.classList.remove('bi-chevron-down');
            studentMembersIcon.classList.add('bi-chevron-up');
        });
        studentMembersCollapse.addEventListener('hide.bs.collapse', function() {
            studentMembersIcon.classList.remove('bi-chevron-up');
            studentMembersIcon.classList.add('bi-chevron-down');
        });
        
        // Fallback: handle click directly if Bootstrap events don't fire
        studentMembersButton.addEventListener('click', function() {
            setTimeout(function() {
                if (studentMembersCollapse.classList.contains('show')) {
                    studentMembersIcon.classList.remove('bi-chevron-down');
                    studentMembersIcon.classList.add('bi-chevron-up');
                } else {
                    studentMembersIcon.classList.remove('bi-chevron-up');
                    studentMembersIcon.classList.add('bi-chevron-down');
                }
            }, 100);
        });
    }
    
    // Handle collapse toggle for Organization Details
    const organizationDetailsCollapse = document.getElementById('organizationDetailsCollapse');
    const organizationDetailsIcon = document.getElementById('organizationDetailsIcon');
    const organizationDetailsButton = document.querySelector('[data-target="#organizationDetailsCollapse"]');
    
    if (organizationDetailsCollapse && organizationDetailsIcon && organizationDetailsButton) {
        // Bootstrap 4/5 collapse events
        organizationDetailsCollapse.addEventListener('show.bs.collapse', function() {
            organizationDetailsIcon.classList.remove('bi-chevron-down');
            organizationDetailsIcon.classList.add('bi-chevron-up');
        });
        organizationDetailsCollapse.addEventListener('hide.bs.collapse', function() {
            organizationDetailsIcon.classList.remove('bi-chevron-up');
            organizationDetailsIcon.classList.add('bi-chevron-down');
        });
        
        // Fallback: handle click directly if Bootstrap events don't fire
        organizationDetailsButton.addEventListener('click', function() {
            setTimeout(function() {
                if (organizationDetailsCollapse.classList.contains('show')) {
                    organizationDetailsIcon.classList.remove('bi-chevron-down');
                    organizationDetailsIcon.classList.add('bi-chevron-up');
                } else {
                    organizationDetailsIcon.classList.remove('bi-chevron-up');
                    organizationDetailsIcon.classList.add('bi-chevron-down');
                }
            }, 100);
        });
    }
    
    // Handle collapse toggle for Organization Files
    const organizationFilesCollapse = document.getElementById('organizationFilesCollapse');
    const organizationFilesIcon = document.getElementById('organizationFilesIcon');
    const organizationFilesButton = document.querySelector('[data-target="#organizationFilesCollapse"]');
    
    if (organizationFilesCollapse && organizationFilesIcon && organizationFilesButton) {
        // Bootstrap 4/5 collapse events
        organizationFilesCollapse.addEventListener('show.bs.collapse', function() {
            organizationFilesIcon.classList.remove('bi-chevron-down');
            organizationFilesIcon.classList.add('bi-chevron-up');
        });
        organizationFilesCollapse.addEventListener('hide.bs.collapse', function() {
            organizationFilesIcon.classList.remove('bi-chevron-up');
            organizationFilesIcon.classList.add('bi-chevron-down');
        });
        
        // Fallback: handle click directly if Bootstrap events don't fire
        organizationFilesButton.addEventListener('click', function() {
            setTimeout(function() {
                if (organizationFilesCollapse.classList.contains('show')) {
                    organizationFilesIcon.classList.remove('bi-chevron-down');
                    organizationFilesIcon.classList.add('bi-chevron-up');
                } else {
                    organizationFilesIcon.classList.remove('bi-chevron-up');
                    organizationFilesIcon.classList.add('bi-chevron-down');
                }
            }, 100);
        });
    }
    
    // Handle file upload forms with AJAX
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('form.file-upload-form');
        if (!form) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const modalId = form.getAttribute('data-modal-id');
        const modal = document.getElementById(modalId);
        const messageDiv = form.querySelector('[id^="uploadMessage"]');
        const submitBtn = form.querySelector('button[type="submit"]');
        const btnText = submitBtn.querySelector('.upload-btn-text');
        const btnSpinner = submitBtn.querySelector('.upload-btn-spinner');
        const fileInput = form.querySelector('input[type="file"]');
        
        // Cleanup any existing backdrops before starting upload
        window.cleanupBackdrops();
        
        // Validate file is selected
        if (!fileInput.files || fileInput.files.length === 0) {
            showUploadMessage(messageDiv, 'Please select a file to upload.', 'danger');
            return;
        }
        
        // Show loading state but keep modal interactive
        submitBtn.disabled = true;
        if (btnText) btnText.style.display = 'none';
        if (btnSpinner) btnSpinner.style.display = 'inline-block';
        hideUploadMessage(messageDiv);
        
        // Ensure modal and content remain clickable during upload
        if (modal) {
            modal.style.pointerEvents = 'auto';
            modal.style.zIndex = '1050';
            const modalDialog = modal.querySelector('.modal-dialog');
            const modalContent = modal.querySelector('.modal-content');
            if (modalDialog) {
                modalDialog.style.pointerEvents = 'auto';
                modalDialog.style.zIndex = '1051';
            }
            if (modalContent) {
                modalContent.style.pointerEvents = 'auto';
                modalContent.style.zIndex = '1052';
            }
        }
        
        // Ensure backdrop doesn't block - set it to pointer-events: none
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(function(backdrop) {
            backdrop.style.pointerEvents = 'none';
            backdrop.style.zIndex = '1040';
        });
        
        // Create FormData
        const formData = new FormData(form);
        
        // Get CSRF token
        const csrfToken = document.querySelector('input[name="_token"]')?.value || 
                         document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Submit via AJAX
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json().then(data => {
                    if (data.success) {
                        showUploadMessage(messageDiv, data.message || 'File uploaded successfully!', 'success');
                        
                        // Close modal after short delay
                        setTimeout(function() {
                            window.closeModalAndCleanup(modal);
                            
                            // Reset form
                            form.reset();
                            submitBtn.disabled = false;
                            if (btnText) btnText.style.display = 'inline';
                            if (btnSpinner) btnSpinner.style.display = 'none';
                            
                            // Refresh file list without reloading page
                            window.refreshFileList({{ $organization->id }});
                        }, 1000);
                    } else {
                        showUploadMessage(messageDiv, data.message || 'Upload failed. Please try again.', 'danger');
                        submitBtn.disabled = false;
                        if (btnText) btnText.style.display = 'inline';
                        if (btnSpinner) btnSpinner.style.display = 'none';
                    }
                });
            } else if (response.redirected) {
                // If redirected, it means success (Laravel redirects on success for non-AJAX)
                showUploadMessage(messageDiv, 'File uploaded successfully!', 'success');
                
                // Close modal after short delay
                setTimeout(function() {
                    window.closeModalAndCleanup(modal);
                    
                    // Reset form
                    form.reset();
                    submitBtn.disabled = false;
                    if (btnText) btnText.style.display = 'inline';
                    if (btnSpinner) btnSpinner.style.display = 'none';
                    
                    // Refresh file list without reloading page
                    window.refreshFileList({{ $organization->id }});
                }, 1000);
            } else {
                // Handle other response types
                return response.text().then(text => {
                    console.error('Unexpected response format:', text);
                    showUploadMessage(messageDiv, 'An error occurred. Please try again or refresh the page.', 'danger');
                    submitBtn.disabled = false;
                    if (btnText) btnText.style.display = 'inline';
                    if (btnSpinner) btnSpinner.style.display = 'none';
                });
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            showUploadMessage(messageDiv, 'Network error. Please check your connection and try again.', 'danger');
            submitBtn.disabled = false;
            if (btnText) btnText.style.display = 'inline';
            if (btnSpinner) btnSpinner.style.display = 'none';
            
            // Ensure modal can still be closed even on error
            // Ensure modal remains interactive
            if (modal) {
                modal.style.pointerEvents = 'auto';
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.style.pointerEvents = 'auto';
                }
            }
        });
    }, true);
    
    // Helper function to cleanup all backdrops (make it global)
    window.cleanupBackdrops = function() {
        // Remove all modal backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(function(backdrop) {
            backdrop.remove();
        });
        
        // Remove modal-open class from body
        document.body.classList.remove('modal-open');
        
        // Remove any inline styles that might block interaction
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        document.body.style.pointerEvents = '';
        
        // Ensure body is scrollable
        document.documentElement.style.overflow = '';
        document.documentElement.style.pointerEvents = '';
    };
    
    // Helper function to close modal and cleanup backdrop (make it global)
    window.closeModalAndCleanup = function(modal) {
        if (!modal) return;
        
        // Use jQuery if available
        if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            jQuery(modal).modal('hide');
            
            // Wait for modal to fully close, then cleanup
            jQuery(modal).on('hidden.bs.modal', function() {
                window.cleanupBackdrops();
                jQuery(modal).off('hidden.bs.modal'); // Remove event listener
            });
            
            // Force cleanup after a delay if event doesn't fire
            setTimeout(function() {
                window.cleanupBackdrops();
            }, 500);
        } else {
            // Fallback: manual cleanup
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            window.cleanupBackdrops();
        }
    };
    
    // Helper function to show upload messages
    function showUploadMessage(messageDiv, message, type) {
        if (!messageDiv) return;
        messageDiv.className = 'alert alert-' + (type || 'info');
        messageDiv.textContent = message;
        messageDiv.style.display = 'block';
        
        // Scroll to message
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Helper function to hide upload messages
    function hideUploadMessage(messageDiv) {
        if (!messageDiv) return;
        messageDiv.style.display = 'none';
        messageDiv.textContent = '';
    }
    
    // Reset modal forms when modal is closed
    document.addEventListener('hidden.bs.modal', function(e) {
        const modal = e.target;
        const form = modal.querySelector('form.file-upload-form');
        if (form) {
            form.reset();
            const messageDiv = form.querySelector('[id^="uploadMessage"]');
            if (messageDiv) {
                hideUploadMessage(messageDiv);
            }
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                const btnText = submitBtn.querySelector('.upload-btn-text');
                const btnSpinner = submitBtn.querySelector('.upload-btn-spinner');
                if (btnText) btnText.style.display = 'inline';
                if (btnSpinner) btnSpinner.style.display = 'none';
            }
        }
        
        // Cleanup backdrops when modal closes
        window.cleanupBackdrops();
    });
    
    // Also cleanup on modal show to prevent multiple backdrops
    document.addEventListener('show.bs.modal', function(e) {
        // Remove any existing backdrops before showing new modal
        window.cleanupBackdrops();
        
        // Ensure the new modal's backdrop doesn't block
        setTimeout(function() {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function(backdrop) {
                backdrop.style.pointerEvents = 'none';
            });
        }, 100);
    });
    
    // Cleanup when modal is shown (Bootstrap event)
    document.addEventListener('shown.bs.modal', function(e) {
        // Ensure backdrop doesn't block after modal is shown
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(function(backdrop) {
            backdrop.style.pointerEvents = 'none';
            backdrop.style.zIndex = '1040';
        });
        
        // Ensure modal content is above backdrop
        const modal = e.target;
        if (modal) {
            modal.style.pointerEvents = 'auto';
            modal.style.zIndex = '1050';
            const modalDialog = modal.querySelector('.modal-dialog');
            const modalContent = modal.querySelector('.modal-content');
            if (modalDialog) {
                modalDialog.style.pointerEvents = 'auto';
                modalDialog.style.zIndex = '1051';
            }
            if (modalContent) {
                modalContent.style.pointerEvents = 'auto';
                modalContent.style.zIndex = '1052';
            }
        }
    });
    
    // Handle ESC key to close modal and cleanup
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(function(modal) {
                window.closeModalAndCleanup(modal);
            });
        }
    });
    
    // Continuous monitoring to ensure backdrop is always hidden
    setInterval(function() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(function(backdrop) {
            // Always hide backdrop completely
            backdrop.style.display = 'none';
            backdrop.style.visibility = 'hidden';
            backdrop.style.opacity = '0';
            backdrop.style.pointerEvents = 'none';
        });
        
        // Ensure body is never blocked
        if (document.body.style.pointerEvents === 'none') {
            document.body.style.pointerEvents = 'auto';
        }
        if (document.body.style.overflow === 'hidden') {
            document.body.style.overflow = 'auto';
        }
    }, 200); // Check every 200ms
    
    // Handle modal close
    const editModal = document.getElementById('editOrganizationModal');
    if (editModal) {
        const closeButtons = editModal.querySelectorAll('[data-dismiss="modal"], .close');
        closeButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                    jQuery('#editOrganizationModal').modal('hide');
                } else {
                    editModal.style.display = 'none';
                    editModal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.getElementById('editOrgModalBackdrop');
                    if (backdrop) backdrop.remove();
                }
            });
        });
    }
    
    // Handle staff selection auto-fill for department (if organization doesn't have one)
    const staffSelect = document.getElementById('staff_id');
    if (staffSelect) {
        staffSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const departmentId = selectedOption.getAttribute('data-department-id');
            
            // Note: This is informational only since the edit modal doesn't have a department field
            // The department is set when creating the organization
            if (departmentId) {
                console.log('Selected staff department ID:', departmentId);
            }
        });
    }
});
</script>
@endpush

@endsection

