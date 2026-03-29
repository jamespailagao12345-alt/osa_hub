@extends('layouts.app')

@section('title', 'Create Event - Guidance Counselor')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main class="col-md-10 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Create Counseling Event</h2>
        <a href="{{ route('admin.staff.dashboard.designation', ['designation' => 'Guidance Counsellor']) }}" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Back
        </a>
      </div>

      <!-- Event Management Buttons -->
      <div class="row mb-4">
        <div class="col-md-4 mb-3">
          <a href="{{ route('admin.staff.dashboard.GuidanceCounselor.pending-events') }}" class="btn btn-warning btn-lg w-100">
            <i class="bi bi-clock-history"></i> Pending Events
          </a>
        </div>
        <div class="col-md-4 mb-3">
          <a href="{{ route('admin.staff.dashboard.GuidanceCounselor.approved-events') }}" class="btn btn-success btn-lg w-100">
            <i class="bi bi-check-circle"></i> Approved Events
          </a>
        </div>
        <div class="col-md-4 mb-3">
          <a href="{{ route('admin.staff.dashboard.GuidanceCounselor.events-history') }}" class="btn btn-info btn-lg w-100">
            <i class="bi bi-calendar-check"></i> Events History
          </a>
        </div>
      </div>

      <div class="card">
        <div class="card-header" style="background-color: midnightblue; color: white;">
          <h5 class="mb-0">Event Information</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.staff.dashboard.GuidanceCounselor.event.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 mb-3">
              <div class="col-md-12">
                <label for="title" class="form-label">Event Name <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}" required>
              </div>
              <div class="col-md-3">
                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}" required>
              </div>
              <div class="col-md-3">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="time" name="start_time" id="start_time" class="form-control" value="{{ old('start_time', '00:00') }}">
              </div>
              <div class="col-md-3">
                <label for="end_time" class="form-label">End Time</label>
                <input type="time" name="end_time" id="end_time" class="form-control" value="{{ old('end_time', '23:59') }}">
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="location" class="form-label">Location</label>
                <input type="text" name="location" id="location" class="form-control" value="{{ old('location') }}" placeholder="Event Location">
              </div>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea name="description" id="description" class="form-control" rows="3" placeholder="Event Description">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
              <label for="event_files" class="form-label">Event Files (Optional)</label>
              <input type="file" name="event_files[]" id="event_files" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls,.csv,.txt">
              <small class="form-text text-muted">You can upload multiple files. Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX, XLS, CSV, TXT (Max 10MB per file). These files will be visible to Admin and OSA Staff.</small>
              <div id="file-list" class="mt-2"></div>
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">Create Event</button>
              <a href="{{ route('admin.staff.dashboard.designation', ['designation' => 'Guidance Counsellor']) }}" class="btn btn-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('event_files');
    const fileList = document.getElementById('file-list');
    
    if (fileInput && fileList) {
        fileInput.addEventListener('change', function(e) {
            const files = e.target.files;
            fileList.innerHTML = '';
            
            if (files.length > 0) {
                const list = document.createElement('ul');
                list.className = 'list-group list-group-flush';
                
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const listItem = document.createElement('li');
                    listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                    
                    const fileInfo = document.createElement('span');
                    fileInfo.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-sm btn-danger';
                    removeBtn.textContent = 'Remove';
                    removeBtn.onclick = function() {
                        removeFileFromInput(i);
                    };
                    
                    listItem.appendChild(fileInfo);
                    listItem.appendChild(removeBtn);
                    list.appendChild(listItem);
                }
                
                fileList.appendChild(list);
            }
        });
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    function removeFileFromInput(index) {
        const dt = new DataTransfer();
        const input = document.getElementById('event_files');
        const files = input.files;
        
        for (let i = 0; i < files.length; i++) {
            if (i !== index) {
                dt.items.add(files[i]);
            }
        }
        
        input.files = dt.files;
        input.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection

