@extends('layouts.app')

@section('title', 'Create Event')

@section('content')
<div class="container-fluid">
  <main class="col-12">
    <div class="card mb-3">
      <div class="card-body">
        <h2 class="mb-3">Create Event (Student Org. Moderator)</h2>
        <form action="{{ route('admin.staff.dashboard.StudentOrgModerator.event.store') }}" method="POST">


            <main class="col-12">
              <div class="card mb-3">
                <div class="container-fluid">
                  <div class="row">
                    <!-- Sidebar -->
                    <div class="col-md-3 col-lg-2">
                      <div class="card mb-4">
                        <div class="card-body">
                          <h5 class="mb-3">Navigation</h5>
                          <a href="{{ route('admin.staff.dashboard.StudentOrgModerator.view-events') }}" class="btn btn-outline-success w-100 mb-2">Back to Event List</a>
                          <a href="{{ route('admin.staff.dashboard.StudentOrgModerator.create-event') }}" class="btn btn-outline-success w-100">Create New Event</a>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-9 col-lg-10">
                      <div class="card">
                        <div class="card-header d-flex align-items-center" style="background: #00d6b2; color: #fff;">
                          <h3 class="mb-0" style="font-weight: 500;">Create Event</h3>
                        </div>
                        <div class="card-body">
                          <div class="row">
                            <div class="col-md-8">
                              <form action="{{ route('admin.staff.dashboard.StudentOrgModerator.event.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <table class="table table-borderless">
                                  <tr>
                                    <td><label for="title">Name</label></td>
                                    <td><input type="text" name="title" id="title" class="form-control" required></td>
                                  </tr>
                                  <tr>
                                    <td><label for="event_date">Date Started</label></td>
                                    <td><input type="date" name="event_date" id="event_date" class="form-control" required></td>
                                  </tr>
                                  <tr>
                                    <td><label for="end_date">Date Ended</label></td>
                                    <td><input type="date" name="end_date" id="end_date" class="form-control"></td>
                                  </tr>
                                  <tr>
                                    <td><label for="start_time">Time Started</label></td>
                                    <td><input type="time" name="start_time" id="start_time" class="form-control" required></td>
                                  </tr>
                                  <tr>
                                    <td><label for="end_time">Time Ended</label></td>
                                    <td><input type="time" name="end_time" id="end_time" class="form-control" required></td>
                                  </tr>
                                  <tr>
                                    <td><label for="location">Location</label></td>
                                    <td><input type="text" name="location" id="location" class="form-control" required></td>
                                  </tr>
                                  <tr>
                                    <td><label for="organization_id">Organization</label></td>
                                    <td>
                                      <select name="organization_id" id="organization_id" class="form-control" required>
                                        <option value="">Select Organization*</option>
                                        @foreach($organizations as $org)
                                          <option value="{{ $org->id }}">{{ $org->name }}</option>
                                        @endforeach
                                      </select>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td><label for="description">Description</label></td>
                                    <td><textarea name="description" id="description" class="form-control" rows="3"></textarea></td>
                                  </tr>
                                  <tr>
                                    <td><label for="event_files">Event Files (Optional)</label></td>
                                    <td>
                                      <input type="file" name="event_files[]" id="event_files" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls,.csv,.txt">
                                      <small class="form-text text-muted">You can upload multiple files. Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX, XLS, CSV, TXT (Max 10MB per file). These files will be visible to Admin and OSA Staff.</small>
                                      <div id="file-list" class="mt-2"></div>
                                    </td>
                                  </tr>
                                </table>
                                <button type="submit" class="btn" style="background: #ffe600; color: #222; min-width: 100px;">Create Event</button>
                              </form>
                            </div>
                            <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                              <h5 class="mb-3">QR Code for Attendance</h5>
                              <span style="color: #ff4d4d; margin-bottom: 1rem;">QR code will be generated after event is created.</span>
                              <button class="btn" style="background: #00d6b2; color: #fff; min-width: 180px;" disabled>Open Camera to Scan</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </main>
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
