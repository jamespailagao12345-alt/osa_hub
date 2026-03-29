@extends('layouts.app')

@section('title', 'My Organization Files')

@section('content')
<div class="container-fluid">
  <div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
      <div class="admin-back-btn-wrap" style="margin: 0;">
        <a href="{{ route('staff.organizations.index') }}" class="btn btn-secondary">&larr; Back to Organizations</a>
      </div>
      <a href="{{ route('staff.organization-files.create', $organization->id) }}" class="btn btn-primary">
        <i class="bi bi-cloud-upload"></i> Upload File
      </a>
    </div>
  </div>

  <div class="row">
    @include('staff.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <div class="card">
        <div class="card-header" style="background-color: midnightblue; color: white;">
          <h4 class="mb-0">
            <i class="bi bi-folder"></i> My Files - {{ $organization->name }}
          </h4>
          @if($organization->department)
            <small>{{ $organization->department->name }} - Academic Organization</small>
          @else
            <small>Non-Academic Organization</small>
          @endif
        </div>
        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <strong>Success!</strong> {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if($files->isEmpty())
            <div class="alert alert-info">
              <p class="mb-0">No files uploaded yet. <a href="{{ route('staff.organization-files.create', $organization->id) }}">Upload your first file</a></p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>File Name</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Size</th>
                    <th>Uploaded By</th>
                    <th>Uploaded At</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($files as $file)
                    <tr>
                      <td>
                        <i class="bi bi-file-earmark"></i>
                        {{ $file->file_name }}
                      </td>
                      <td>
                        @php
                          $fileTypeLabels = [
                            'personal_data_sheet' => 'Personal Data Sheet',
                            'image' => 'Image',
                            'pdf' => 'PDF',
                            'document' => 'Document',
                            'spreadsheet' => 'Spreadsheet',
                            'other' => 'Other'
                          ];
                          $typeLabel = $fileTypeLabels[$file->file_type] ?? ucfirst($file->file_type ?? 'Other');
                        @endphp
                        <span class="badge bg-secondary">{{ $typeLabel }}</span>
                      </td>
                      <td>{{ $file->description ?? '-' }}</td>
                      <td>{{ $file->human_readable_size }}</td>
                      <td>{{ $file->uploader->first_name ?? '' }} {{ $file->uploader->last_name ?? '' }}</td>
                      <td>{{ $file->created_at->format('M d, Y g:i A') }}</td>
                      <td>
                        <div class="btn-group" role="group">
                          <a href="{{ route('staff.organization-files.download', [$organization->id, $file->id]) }}" class="btn btn-sm btn-primary" title="Download">
                            <i class="bi bi-download"></i>
                          </a>
                          <form action="{{ route('staff.organization-files.destroy', [$organization->id, $file->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this file?');">
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
          @endif
        </div>
      </div>
    </main>
  </div>
</div>
@endsection
