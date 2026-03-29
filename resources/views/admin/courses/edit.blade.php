@extends('layouts.app')

@section('title', 'Edit Course')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main id="adminMain" class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            </div>
            
            <h2 class="mb-3">Edit Course</h2>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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
                    <form action="{{ route('admin.courses.update', $course) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $course->department_id) == $dept->id ? 'selected' : '' }}>
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
                                   id="name" name="name" value="{{ old('name', $course->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Sections</h5>
                        <p class="text-muted small">Manage sections for this course.</p>

                        @php
                            $currentSections = $course->sections ?? collect();
                        @endphp
                        @if($currentSections->count() > 0)
                            <div class="mb-3 p-3 bg-light rounded">
                                <strong>Current Sections:</strong>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @foreach($currentSections as $section)
                                        <span class="badge bg-primary">{{ $section->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <small><i class="bi bi-info-circle"></i> <strong>Note:</strong> You can edit the course name and department without affecting sections. Sections are only updated when you modify them below.</small>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="editSections" name="edit_sections" value="1">
                            <label class="form-check-label" for="editSections">
                                <strong>Edit Sections</strong> (uncheck to keep current sections unchanged)
                            </label>
                        </div>

                        <div id="sectionsContainer" style="display: none;">
                            <!-- Sections will be added here dynamically -->
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addSectionBtn" style="display: none;">
                            <i class="bi bi-plus-circle"></i> Add Section
                        </button>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Course</button>
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
    const editSectionsCheckbox = document.getElementById('editSections');
    const sectionsContainer = document.getElementById('sectionsContainer');
    const addSectionBtn = document.getElementById('addSectionBtn');

    function addSection(sectionName = '', sectionId = null) {
        const sectionHtml = `
            <div class="input-group mb-2 section-item" data-section-id="${sectionId || ''}">
                <input type="text" class="form-control section-name" 
                       name="sections[]" 
                       value="${sectionName}" 
                       placeholder="e.g., Section A">
                ${sectionId ? `<input type="hidden" name="section_ids[]" value="${sectionId}">` : ''}
                <button type="button" class="btn btn-outline-danger remove-section-btn">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = sectionHtml;
        sectionsContainer.appendChild(tempDiv.firstElementChild);

        // Attach remove listener
        tempDiv.querySelector('.remove-section-btn').addEventListener('click', function() {
            this.closest('.section-item').remove();
        });
    }

    // Store original sections when hiding
    let originalSectionsHtml = '';

    // Toggle sections editing
    editSectionsCheckbox.addEventListener('change', function() {
        if (this.checked) {
            sectionsContainer.style.display = 'block';
            addSectionBtn.style.display = 'block';
            // Restore sections if they were hidden
            if (originalSectionsHtml && sectionsContainer.children.length === 0) {
                sectionsContainer.innerHTML = originalSectionsHtml;
                // Reattach event listeners
                sectionsContainer.querySelectorAll('.remove-section-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        this.closest('.section-item').remove();
                    });
                });
            }
            // Make section inputs required when visible
            sectionsContainer.querySelectorAll('.section-name').forEach(input => {
                input.setAttribute('required', 'required');
            });
        } else {
            // Store current sections HTML before hiding
            originalSectionsHtml = sectionsContainer.innerHTML;
            sectionsContainer.style.display = 'none';
            addSectionBtn.style.display = 'none';
            // Remove required attribute when hiding (but keep HTML)
            sectionsContainer.querySelectorAll('.section-name').forEach(input => {
                input.removeAttribute('required');
            });
        }
    });

    // Add section button
    addSectionBtn.addEventListener('click', function() {
        addSection();
    });

    // Only load sections if edit_sections checkbox was checked or if there are validation errors
    @if(old('edit_sections') || (old('sections') && $errors->any()))
        @php
            $course->load('sections');
            $existingSections = $course->sections ?? collect();
        @endphp
        
        // Show sections container and checkbox
        editSectionsCheckbox.checked = true;
        sectionsContainer.style.display = 'block';
        addSectionBtn.style.display = 'block';
        
        // Load sections
        @if(old('sections') && $errors->any())
            @foreach(old('sections') as $index => $section)
                @php
                    $sectionId = old('section_ids.' . $index) ?? null;
                @endphp
                addSection({!! json_encode($section) !!}, {{ $sectionId ? "'$sectionId'" : 'null' }});
            @endforeach
        @else
            @if($existingSections->count() > 0)
                @foreach($existingSections as $section)
                    addSection({!! json_encode($section->name) !!}, {{ $section->id }});
                @endforeach
            @else
                addSection();
            @endif
        @endif
    @endif
});
</script>
@endpush

