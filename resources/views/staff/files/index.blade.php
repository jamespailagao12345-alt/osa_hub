@extends('layouts.app')

@section('title', 'My Files')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('staff.partials.sidebar')
        <main id="staffMain" class="col-md-10">
            @php
              $currentUser = auth()->user();
              $isAdmin = $currentUser && (int) $currentUser->role === 4;
              $isStaff = $currentUser && (int) $currentUser->role === 2;
              
              // Get designation from multiple sources
              $userDesignation = null;
              if ($currentUser) {
                $userDesignation = $currentUser->designation 
                  ?? optional($currentUser->staffProfile)->designation;
                
                // If still not found, check Staff table by email (case-insensitive)
                if (!$userDesignation) {
                  $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($currentUser->email))])->first();
                  $userDesignation = $staffRecord ? $staffRecord->designation : null;
                }
              }
            @endphp
            <div class="admin-back-btn-wrap mb-3">
              @if($isAdmin)
                <a href="{{ route('admin.staff.dashboard') }}" class="btn btn-secondary">Back</a>
              @elseif($isStaff && $userDesignation)
                <a href="{{ route('admin.staff.dashboard.designation', ['designation' => $userDesignation]) }}" class="btn btn-secondary">Back</a>
              @else
                <a href="{{ route('staff.organizations.index') }}" class="btn btn-secondary">Back</a>
              @endif
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>My Files</h3>
                <a href="{{ route('staff.files.create') }}" class="btn btn-primary">
                    <i class="bi bi-upload"></i> Upload File
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('staff.files.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="organization_id" class="form-label">Filter by Organization</label>
                            <select name="organization_id" id="organization_id" class="form-select">
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
                            <select name="file_type" id="file_type" class="form-select">
                                <option value="">All Types</option>
                                <option value="personal_data_sheet" {{ request('file_type') == 'personal_data_sheet' ? 'selected' : '' }}>Personal Data Sheet</option>
                                <option value="document" {{ request('file_type') == 'document' ? 'selected' : '' }}>Document</option>
                                <option value="other" {{ request('file_type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-secondary me-2">Filter</button>
                            <a href="{{ route('staff.files.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Files List Organized by Organization -->
            @if($allFiles->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-folder-x" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="mt-3 text-muted">No files uploaded yet.</p>
                        <a href="{{ route('staff.files.create') }}" class="btn btn-primary">Upload Your First File</a>
                    </div>
                </div>
            @else
                @foreach($filesByOrganization as $orgId => $files)
                    @php
                        $organization = \App\Models\Organization::find($orgId);
                        $orgName = $organization ? $organization->name : 'Unknown Organization';
                    @endphp
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-folder-fill"></i> {{ $orgName }}
                                    <span class="badge bg-light text-dark ms-2">{{ $files->count() }} {{ $files->count() === 1 ? 'file' : 'files' }}</span>
                                </h5>
                                <a href="{{ route('staff.files.create', ['organization_id' => $orgId]) }}" class="btn btn-sm btn-light">
                                    <i class="bi bi-plus-circle"></i> Upload File
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th>File Name</th>
                                            <th>Category</th>
                                            <th>Size</th>
                                            <th>Uploaded By</th>
                                            <th>Uploaded Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($files as $file)
                                            <tr>
                                                <td>
                                                    <i class="bi bi-file-earmark"></i>
                                                    {{ $file->file_name }}
                                                    @if($file->description)
                                                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($file->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $file->file_category ?? 'Other' }}</span>
                                                </td>
                                                <td>{{ $file->formatted_size }}</td>
                                                <td>{{ $file->uploader ? $file->uploader->first_name . ' ' . $file->uploader->last_name : 'N/A' }}</td>
                                                <td>{{ $file->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('staff.files.download', $file->id) }}" class="btn btn-primary" title="Download">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                        <form action="{{ route('staff.files.destroy', $file->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" title="Delete">
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
                @endforeach
            @endif
        </main>
    </div>
</div>
@endsection

