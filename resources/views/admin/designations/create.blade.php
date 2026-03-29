@extends('layouts.app')

@section('title', 'Create Designation')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main id="adminMain" class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.designations.index') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            </div>
            
            <h2 class="mb-3">Create New Designation</h2>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.designations.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Designation Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            <small class="text-muted">e.g., "Student Org. Moderator", "Admission Services Officer"</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Available Features</label>
                            <small class="text-white d-block mb-2">Select the features this designation can access:</small>
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                @php
                                    $availableFeatures = [
                                        'Appointments' => 'Manage and view appointments',
                                        'Events' => 'Create, edit, and manage events',
                                        'Organizations' => 'Manage organizations and their members',
                                        'Participants' => 'Track and manage event participants',
                                        'QR Code Scanning' => 'Scan QR codes for event attendance',
                                        'Student Management' => 'View and manage student records',
                                        'File Management' => 'Upload and manage organization files',
                                        'Reports' => 'Generate and view reports',
                                        'Calendar' => 'View and manage calendar events',
                                    ];
                                    $oldFeatures = old('features', []);
                                @endphp
                                @foreach($availableFeatures as $feature => $description)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" 
                                               id="feature_{{ str_replace(' ', '_', strtolower($feature)) }}" 
                                               value="{{ $feature }}" 
                                               {{ in_array($feature, $oldFeatures) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="feature_{{ str_replace(' ', '_', strtolower($feature)) }}">
                                            <strong>{{ $feature }}</strong>
                                            <small class="text-white d-block">{{ $description }}</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('features')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Create Designation</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

