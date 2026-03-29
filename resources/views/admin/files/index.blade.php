@extends('layouts.app')

@section('title', 'Files Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">Files Management</h2>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Admin Files -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">
                        <i class="bi bi-folder-fill"></i> Admin Files
                        <span class="badge badge-light">{{ $adminFiles->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Upload Form -->
                    <div class="mb-4 p-3 border rounded">
                        <h6 class="mb-3">Upload File</h6>
                        <form method="POST" action="{{ route('admin.files.admin.upload') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="admin_file" class="form-label">Select File</label>
                                    <input type="file" class="form-control" id="admin_file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.xlsx,.xls,.csv,.txt">
                                    <small class="form-text text-muted">Max size: 50MB. Allowed: PDF, DOC, DOCX, JPG, PNG, GIF, XLSX, XLS, CSV, TXT</small>
                                </div>
                                <div class="col-md-3">
                                    <label for="admin_file_category" class="form-label">Category (Optional)</label>
                                    <input type="text" class="form-control" id="admin_file_category" name="file_category" placeholder="e.g., Documents, Images">
                                </div>
                                <div class="col-md-3">
                                    <label for="admin_file_description" class="form-label">Description (Optional)</label>
                                    <input type="text" class="form-control" id="admin_file_description" name="description" placeholder="Brief description">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-upload"></i> Upload
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Files List -->
                    @if($adminFiles->isEmpty())
                        <p class="text-muted">No admin files uploaded yet.</p>
                    @else
                        <div class="list-group">
                            @foreach($adminFiles as $file)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark me-2"></i>
                                            <div>
                                                <strong>{{ $file->file_name }}</strong>
                                                @if($file->file_category)
                                                    <span class="badge badge-secondary ml-2">{{ $file->file_category }}</span>
                                                @endif
                                                @if($file->file_type)
                                                    <span class="badge badge-info ml-1">{{ ucfirst($file->file_type) }}</span>
                                                @endif
                                                <br>
                                                <small class="text-muted">
                                                    Uploaded by {{ $file->uploader ? $file->uploader->first_name . ' ' . $file->uploader->last_name : 'Unknown' }} 
                                                    on {{ $file->created_at->format('M d, Y H:i') }}
                                                    @if($file->description)
                                                        • {{ $file->description }}
                                                    @endif
                                                </small>
                                                <br>
                                                <small class="text-muted">Size: {{ $file->human_readable_size ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.files.admin.download', $file->id) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.files.admin.approve', $file->id) }}" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.files.admin.delete', $file->id) }}" 
                                              onsubmit="return confirm('Are you sure you want to delete this file?');" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.files.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="organization_id" class="form-label">Filter by Organization</label>
                            <select name="organization_id" id="organization_id" class="form-control">
                                <option value="">All Organizations</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                                        {{ $org->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="file_type" class="form-label">Filter by File Type</label>
                            <select name="file_type" id="file_type" class="form-control">
                                <option value="">All File Types</option>
                                @foreach($fileTypes as $type)
                                    <option value="{{ $type }}" {{ request('file_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.files.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Organization Files (Combined) -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">
                        <i class="mai-folder"></i> Organization Files
                        <span class="badge badge-light">{{ $totalOrgFiles }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($organizationFilesGrouped->isEmpty())
                        <p class="text-muted">No organization files found.</p>
                    @else
                        <div class="mb-3">
                            <ul class="list-unstyled mb-0">
                                @foreach($organizationFilesGrouped as $orgId => $group)
                                    @php
                                        $org = $group['organization'];
                                        $collapseId = 'org-' . ($org ? $org->id : 'no-org');
                                    @endphp
                                    @if($org)
                                        <li class="mb-2">
                                            <a href="#" 
                                               class="org-name-link" 
                                               data-target="#{{ $collapseId }}"
                                               style="color: black; background: transparent; text-decoration: none; cursor: pointer; display: inline-block; padding: 0.25rem 0;"
                                               onmouseover="this.style.textDecoration='underline'"
                                               onmouseout="this.style.textDecoration='none'">
                                                {{ $org->name }}
                                                <span class="badge badge-secondary ml-2">{{ $group['count'] }} file(s)</span>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        
                        <!-- Files Display Area -->
                        <div id="orgFilesDisplay" class="mt-4" style="display: none;">
                            <hr>
                            <h6 class="mb-3" id="orgFilesTitle"></h6>
                            <div id="orgFilesContent"></div>
                        </div>
                        
                        <!-- Hidden template for organization files -->
                        @foreach($organizationFilesGrouped as $orgId => $group)
                            @php
                                $org = $group['organization'];
                                $files = $group['files'];
                                $collapseId = 'org-' . ($org ? $org->id : 'no-org');
                            @endphp
                            @if($org)
                                <div id="{{ $collapseId }}" class="org-files-section" style="display: none;" data-org-name="{{ $org->name }}">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>File Name</th>
                                                    <th>Type</th>
                                                    <th>Category</th>
                                                    <th>Size</th>
                                                    <th>Uploaded By</th>
                                                    <th>Uploaded At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($files as $file)
                                                    <tr>
                                                        <td>{{ $file->file_name }}</td>
                                                        <td><span class="badge badge-info">{{ $file->file_type ?? 'N/A' }}</span></td>
                                                        <td>{{ $file->file_category ?? '-' }}</td>
                                                        <td>{{ $file->human_readable_size ?? ($file->formatted_size ?? 'N/A') }}</td>
                                                        <td>
                                                            @if($file->uploader)
                                                                {{ $file->uploader->first_name }} {{ $file->uploader->last_name }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>{{ $file->created_at->format('M d, Y H:i') }}</td>
                                                        <td>
                                                            <div class="d-flex gap-1">
                                                                @if(isset($file->is_staff_org_file) && $file->is_staff_org_file)
                                                                    <a href="{{ route('admin.files.staff.download', $file->id) }}" 
                                                                       class="btn btn-sm btn-primary" 
                                                                       title="Download">
                                                                        <i class="bi bi-download"></i>
                                                                    </a>
                                                                    <form method="POST" action="{{ route('admin.files.staff.approve', $file->id) }}" 
                                                                          class="d-inline">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                                            <i class="bi bi-check-circle"></i>
                                                                        </button>
                                                                    </form>
                                                                    <form method="POST" action="{{ route('admin.files.staff.delete', $file->id) }}" 
                                                                          onsubmit="return confirm('Are you sure you want to delete this file?');" 
                                                                          class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                @else
                                                                    <a href="{{ route('admin.files.organization.download', $file->id) }}" 
                                                                       class="btn btn-sm btn-primary" 
                                                                       title="Download">
                                                                        <i class="bi bi-download"></i>
                                                                    </a>
                                                                    <form method="POST" action="{{ route('admin.files.organization.approve', $file->id) }}" 
                                                                          class="d-inline">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                                            <i class="bi bi-check-circle"></i>
                                                                        </button>
                                                                    </form>
                                                                    <form method="POST" action="{{ route('admin.files.organization.delete', $file->id) }}" 
                                                                          onsubmit="return confirm('Are you sure you want to delete this file?');" 
                                                                          class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Staff Files (Grouped by Staff Member) -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #ffc107; color: white;">
                    <h5 class="mb-0">
                        <i class="mai-folder"></i> Staff Files
                        <span class="badge badge-light">{{ $totalStaffFiles }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($staffFilesGrouped->isEmpty())
                        <p class="text-muted">No staff files found.</p>
                    @else
                        <div class="accordion" id="staffFilesAccordion">
                            @foreach($staffFilesGrouped as $key => $group)
                                @php
                                    $staff = $group['staff'];
                                    $designation = $group['designation'];
                                    $files = $group['files'];
                                    $collapseId = 'staff-' . ($staff ? $staff->id : 'no-staff');
                                    $displayName = $staff ? ($staff->first_name . ' ' . $staff->last_name) : 'Unknown Staff';
                                    if ($designation) {
                                        $displayName .= ' (' . $designation . ')';
                                    }
                                @endphp
                                <div class="card mb-2">
                                    <div class="card-header p-0" style="background-color: #f8f9fa;">
                                        <button class="btn btn-link text-left w-100 p-3 text-decoration-none" type="button" 
                                                data-toggle="collapse" data-target="#{{ $collapseId }}" 
                                                aria-expanded="false" aria-controls="{{ $collapseId }}"
                                                style="color: midnightblue; font-weight: 600;">
                                            <i class="bi bi-chevron-right" id="icon-{{ $collapseId }}"></i>
                                            {{ $displayName }}
                                            <span class="badge badge-primary ml-2">{{ $group['count'] }} file(s)</span>
                                        </button>
                                    </div>
                                    <div id="{{ $collapseId }}" class="collapse" data-parent="#staffFilesAccordion">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>File Name</th>
                                                            <th>Organization</th>
                                                            <th>Type</th>
                                                            <th>Category</th>
                                                            <th>Size</th>
                                                            <th>Uploaded By</th>
                                                            <th>Uploaded At</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($files as $file)
                                                            <tr>
                                                                <td>{{ $file->file_name }}</td>
                                                                <td>{{ $file->organization ? $file->organization->name : '-' }}</td>
                                                                <td><span class="badge badge-info">{{ $file->file_type ?? 'N/A' }}</span></td>
                                                                <td>{{ $file->file_category ?? '-' }}</td>
                                                                <td>{{ $file->formatted_size ?? 'N/A' }}</td>
                                                                <td>
                                                                    @if($file->uploader)
                                                                        {{ $file->uploader->first_name }} {{ $file->uploader->last_name }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td>{{ $file->created_at->format('M d, Y H:i') }}</td>
                                                                <td>
                                                                    <div class="d-flex gap-1">
                                                                        <a href="{{ route('admin.files.staff.download', $file->id) }}" 
                                                                           class="btn btn-sm btn-primary" 
                                                                           title="Download">
                                                                            <i class="bi bi-download"></i>
                                                                        </a>
                                                                        <form method="POST" action="{{ route('admin.files.staff.approve', $file->id) }}" 
                                                                              class="d-inline">
                                                                            @csrf
                                                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                                                <i class="bi bi-check-circle"></i>
                                                                            </button>
                                                                        </form>
                                                                        <form method="POST" action="{{ route('admin.files.staff.delete', $file->id) }}" 
                                                                              onsubmit="return confirm('Are you sure you want to delete this file?');" 
                                                                              class="d-inline">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle organization name clicks
    const orgNameLinks = document.querySelectorAll('.org-name-link');
    const orgFilesDisplay = document.getElementById('orgFilesDisplay');
    const orgFilesTitle = document.getElementById('orgFilesTitle');
    const orgFilesContent = document.getElementById('orgFilesContent');
    let currentOrgId = null;
    
    orgNameLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Check if clicking the same organization (toggle)
                if (currentOrgId === targetId) {
                    // Hide the files display
                    orgFilesDisplay.style.display = 'none';
                    currentOrgId = null;
                } else {
                    // Show the selected organization's files
                    const orgName = targetElement.getAttribute('data-org-name');
                    orgFilesTitle.textContent = orgName + ' - Files';
                    orgFilesContent.innerHTML = targetElement.innerHTML;
                    orgFilesDisplay.style.display = 'block';
                    currentOrgId = targetId;
                    
                    // Scroll to the files display
                    setTimeout(() => {
                        orgFilesDisplay.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 100);
                }
            }
        });
    });
});
</script>
@endpush
@endsection
