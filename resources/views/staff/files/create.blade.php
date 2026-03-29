@extends('layouts.app')

@section('title', 'Upload File')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('staff.partials.sidebar')
        <main id="staffMain" class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Upload File</h3>
                <a href="{{ route('staff.files.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Files
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Please fix the following errors:
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('staff.files.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="file" class="form-label">File <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="file" class="form-control" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls,.csv">
                            <small class="form-text text-muted">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX, XLS, CSV (Max: 20MB)</small>
                        </div>

                        <div class="mb-3">
                            <label for="organization_id" class="form-label">Organization <span class="text-danger">*</span></label>
                            <select name="organization_id" id="organization_id" class="form-select" required>
                                <option value="">Select Organization</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" {{ old('organization_id', $selectedOrganizationId ?? null) == $org->id ? 'selected' : '' }}>
                                        {{ $org->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Files are organized by organization. Select the organization this file belongs to.</small>
                        </div>

                        <div class="mb-3">
                            <label for="file_type" class="form-label">File Type</label>
                            <select name="file_type" id="file_type" class="form-select">
                                <option value="other" {{ old('file_type') == 'other' ? 'selected' : '' }}>Other</option>
                                <option value="personal_data_sheet" {{ old('file_type') == 'personal_data_sheet' ? 'selected' : '' }}>Personal Data Sheet</option>
                                <option value="document" {{ old('file_type') == 'document' ? 'selected' : '' }}>Document</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="file_category" class="form-label">Category</label>
                            <input type="text" name="file_category" id="file_category" class="form-control" value="{{ old('file_category', 'Other') }}" placeholder="e.g., Personal Data Sheet, Meeting Minutes, etc.">
                            <small class="form-text text-muted">Optional category description</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Optional description of the file...">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('staff.files.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

