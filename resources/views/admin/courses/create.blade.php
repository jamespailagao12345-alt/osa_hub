@extends('layouts.app')

@section('title', 'Create Course')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main id="adminMain" class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            </div>
            
            <h2 class="mb-3">Create New Course</h2>

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
                    <form action="{{ route('admin.courses.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', request('department_id')) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Course Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', request('course_name')) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Sections</h5>
                        <p class="text-muted small">Add sections for this course.</p>

                        <div id="sectionsContainer">
                            <!-- Sections will be added here dynamically -->
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addSectionBtn">
                            <i class="bi bi-plus-circle"></i> Add Section
                        </button>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Create Course</button>
                            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let sectionIndex = 0;

    function addSection(sectionName = '') {
        const sectionHtml = `
            <div class="input-group mb-2 section-item">
                <input type="text" class="form-control section-name" 
                       name="sections[]" 
                       value="${sectionName}" 
                       placeholder="e.g., Section A" required>
                <button type="button" class="btn btn-outline-danger remove-section-btn">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
        
        const container = document.getElementById('sectionsContainer');
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = sectionHtml;
        container.appendChild(tempDiv.firstElementChild);

        // Attach remove listener
        tempDiv.querySelector('.remove-section-btn').addEventListener('click', function() {
            this.closest('.section-item').remove();
        });
    }

    // Add section button
    document.getElementById('addSectionBtn').addEventListener('click', function() {
        addSection();
    });

    // Add initial sections if needed
    @if(old('sections'))
        @foreach(old('sections') as $section)
            addSection('{{ $section }}');
        @endforeach
    @else
        addSection();
    @endif
});
</script>
@endpush

