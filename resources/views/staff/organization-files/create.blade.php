@extends('layouts.app')

@section('title', 'Upload File')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="admin-back-btn-wrap">
        <a href="{{ route('staff.organization-files.index', $organization->id) }}" class="btn btn-secondary">&larr; Back to Files</a>
      </div>
    </div>
  </div>

  <div class="row">
    @include('staff.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <div class="card">
        <div class="card-header" style="background-color: midnightblue; color: white;">
          <h4 class="mb-0">
            <i class="bi bi-cloud-upload"></i> Upload File - {{ $organization->name }}
          </h4>
        </div>
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form action="{{ route('staff.organization-files.store', $organization->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
              <label for="file" class="form-label">
                File <span class="text-danger">*</span>
              </label>
              <input type="file" name="file" id="file" class="form-control" required>
              <small class="form-text text-muted">Maximum file size: 50MB. Supported formats: PDF, DOC, DOCX, images, spreadsheets, etc.</small>
            </div>

            <div class="mb-3">
              <label for="file_type" class="form-label">File Type</label>
              <select name="file_type" id="file_type" class="form-select">
                <option value="">Auto-detect</option>
                <option value="personal_data_sheet">Personal Data Sheet</option>
                <option value="document">Document</option>
                <option value="image">Image</option>
                <option value="pdf">PDF</option>
                <option value="spreadsheet">Spreadsheet</option>
                <option value="other">Other</option>
              </select>
              <small class="form-text text-muted">Select the file type or leave as "Auto-detect" to automatically detect based on file content.</small>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description (Optional)</label>
              <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter a description for this file...">{{ old('description') }}</textarea>
            </div>

            <div class="d-flex justify-content-between">
              <a href="{{ route('staff.organization-files.index', $organization->id) }}" class="btn btn-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-cloud-upload"></i> Upload File
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Upload Tips -->
      <div class="card mt-3">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-lightbulb"></i> Upload Tips</h5>
          <ul class="mb-0">
            <li>Personal Data Sheets should be saved with descriptive filenames (e.g., "John_Doe_PDS_2025.pdf")</li>
            <li>Files are stored in your personal folder: <code>staff/{{ auth()->id() }}/organizations/{{ $organization->id }}/</code></li>
            <li>Each staff member has their own folder for each organization they handle</li>
            <li>Files are automatically organized and can be downloaded later</li>
          </ul>
        </div>
      </div>
    </main>
  </div>
</div>
@endsection

