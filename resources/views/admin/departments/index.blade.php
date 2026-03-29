@extends('layouts.app')

@section('title', 'Manage Departments')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main id="adminMain" class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">Manage Departments</h2>
                <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">Add New Department</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    @forelse($departments as $department)
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">{{ $department->name }}</h5>
                                    <small>ID: {{ $department->id }} | Users: {{ $department->users()->count() }}</small>
                                </div>
                                <div>
                                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-light me-2">Edit</a>
                                    <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this department? This action cannot be undone if it has associated courses or users.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                @php
                                    $courses = $department->courses()->with('sections')->get();
                                @endphp
                                @if($courses->count() > 0)
                                    @foreach($courses as $course)
                                        <div class="mb-3 p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">{{ $course->name }}</strong>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editCourseModal{{ $course->id }}">
                                                        <i class="bi bi-pencil"></i> Edit Course
                                                    </button>
                                                    <form action="{{ route('admin.departments.courses.destroy', [$department, $course]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this course? This will also delete all sections.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            @if($course->sections->count() > 0)
                                                <div class="ms-3">
                                                    <small class="text-muted d-block mb-1">Sections:</small>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($course->sections as $section)
                                                            <span class="badge bg-secondary">{{ $section->name }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <small class="text-muted ms-3">No sections assigned</small>
                                            @endif
                                        </div>

                                        <!-- Edit Course Modal -->
                                        <div class="modal fade" id="editCourseModal{{ $course->id }}" tabindex="-1" aria-labelledby="editCourseModalLabel{{ $course->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.departments.courses.update', [$department, $course]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editCourseModalLabel{{ $course->id }}">Edit Course: {{ $course->name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @if($errors->any() && old('_method') === 'PUT' && old('course_id') == $course->id)
                                                                <div class="alert alert-danger">
                                                                    <ul class="mb-0">
                                                                        @foreach($errors->all() as $error)
                                                                            <li>{{ $error }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @endif

                                                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                                                            
                                                            <div class="mb-3">
                                                                <label for="course_name{{ $course->id }}" class="form-label">Course Name <span class="text-danger">*</span></label>
                                                                <input type="text" 
                                                                       class="form-control @error('name') is-invalid @enderror" 
                                                                       id="course_name{{ $course->id }}" 
                                                                       name="name" 
                                                                       value="{{ old('name', $course->name) }}" 
                                                                       required>
                                                                @error('name')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>

                                                            <hr>

                                                            <h6 class="mb-3">Sections</h6>
                                                            <div id="sectionsContainer{{ $course->id }}">
                                                                @php
                                                                    $courseSections = $course->sections ?? collect();
                                                                @endphp
                                                                @if($courseSections->count() > 0)
                                                                    @foreach($courseSections as $index => $section)
                                                                        <div class="input-group mb-2 section-item">
                                                                            <input type="text" class="form-control section-name" 
                                                                                   name="sections[]" 
                                                                                   value="{{ old('sections.' . $index, $section->name) }}" 
                                                                                   placeholder="e.g., Section A">
                                                                            <input type="hidden" name="section_ids[]" value="{{ $section->id }}">
                                                                            <button type="button" class="btn btn-outline-danger remove-section-btn">
                                                                                <i class="bi bi-x"></i>
                                                                            </button>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <div class="input-group mb-2 section-item">
                                                                        <input type="text" class="form-control section-name" 
                                                                               name="sections[]" 
                                                                               placeholder="e.g., Section A">
                                                                        <input type="hidden" name="section_ids[]" value="">
                                                                        <button type="button" class="btn btn-outline-danger remove-section-btn">
                                                                            <i class="bi bi-x"></i>
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" id="addSectionBtn{{ $course->id }}">
                                                                <i class="bi bi-plus-circle"></i> Add Section
                                                            </button>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="bi bi-check-circle"></i> Update Course
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted mb-0">No courses assigned to this department.</p>
                                @endif
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal{{ $department->id }}">
                                        <i class="bi bi-plus-circle"></i> Add Course
                                    </button>
                                </div>

                                <!-- Add Course Modal -->
                                <div class="modal fade" id="addCourseModal{{ $department->id }}" tabindex="-1" aria-labelledby="addCourseModalLabel{{ $department->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.departments.courses.store', $department) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="addCourseModalLabel{{ $department->id }}">Add New Course to {{ $department->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @if($errors->any() && old('_method') !== 'PUT')
                                                        <div class="alert alert-danger">
                                                            <ul class="mb-0">
                                                                @foreach($errors->all() as $error)
                                                                    <li>{{ $error }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif

                                                    <div class="mb-3">
                                                        <label for="new_course_name{{ $department->id }}" class="form-label">Course Name <span class="text-danger">*</span></label>
                                                        <input type="text" 
                                                               class="form-control @error('name') is-invalid @enderror" 
                                                               id="new_course_name{{ $department->id }}" 
                                                               name="name" 
                                                               value="{{ old('name') }}" 
                                                               placeholder="e.g., Bachelor of Science in Computer Science"
                                                               required>
                                                        @error('name')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <hr>

                                                    <h6 class="mb-3">Sections (Optional)</h6>
                                                    <div id="newSectionsContainer{{ $department->id }}">
                                                        <div class="input-group mb-2 section-item">
                                                            <input type="text" class="form-control section-name" 
                                                                   name="sections[]" 
                                                                   placeholder="e.g., Section A">
                                                            <button type="button" class="btn btn-outline-danger remove-section-btn">
                                                                <i class="bi bi-x"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addNewSectionBtn{{ $department->id }}">
                                                        <i class="bi bi-plus-circle"></i> Add Section
                                                    </button>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="bi bi-check-circle"></i> Create Course
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">
                            <p class="mb-0">No departments found. <a href="{{ route('admin.departments.create') }}">Create your first department</a></p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to add section input
    function addSection(containerId, sectionName = '', sectionId = '') {
        const container = document.getElementById(containerId);
        const sectionHtml = `
            <div class="input-group mb-2 section-item">
                <input type="text" class="form-control section-name" 
                       name="sections[]" 
                       value="${sectionName}" 
                       placeholder="e.g., Section A">
                ${sectionId ? `<input type="hidden" name="section_ids[]" value="${sectionId}">` : '<input type="hidden" name="section_ids[]" value="">'}
                <button type="button" class="btn btn-outline-danger remove-section-btn">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = sectionHtml;
        container.appendChild(tempDiv.firstElementChild);

        // Attach remove listener
        tempDiv.querySelector('.remove-section-btn').addEventListener('click', function() {
            this.closest('.section-item').remove();
        });
    }

    // Handle add section buttons for edit modals
    @foreach($departments as $department)
        @foreach($department->courses as $course)
            const addSectionBtn{{ $course->id }} = document.getElementById('addSectionBtn{{ $course->id }}');
            if (addSectionBtn{{ $course->id }}) {
                addSectionBtn{{ $course->id }}.addEventListener('click', function() {
                    addSection('sectionsContainer{{ $course->id }}');
                });
            }

            // Attach remove listeners for existing sections in edit modal
            const sectionsContainer{{ $course->id }} = document.getElementById('sectionsContainer{{ $course->id }}');
            if (sectionsContainer{{ $course->id }}) {
                sectionsContainer{{ $course->id }}.querySelectorAll('.remove-section-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        this.closest('.section-item').remove();
                    });
                });
            }
        @endforeach

        // Handle add section button for new course modal
        const addNewSectionBtn{{ $department->id }} = document.getElementById('addNewSectionBtn{{ $department->id }}');
        if (addNewSectionBtn{{ $department->id }}) {
            addNewSectionBtn{{ $department->id }}.addEventListener('click', function() {
                addSection('newSectionsContainer{{ $department->id }}');
            });
        }

        // Attach remove listeners for sections in new course modal
        const newSectionsContainer{{ $department->id }} = document.getElementById('newSectionsContainer{{ $department->id }}');
        if (newSectionsContainer{{ $department->id }}) {
            newSectionsContainer{{ $department->id }}.querySelectorAll('.remove-section-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.section-item').remove();
                });
            });
        }
    @endforeach

    // Clear form validation errors when modals are closed
    document.querySelectorAll('[id^="editCourseModal"], [id^="addCourseModal"]').forEach(function(modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                // Clear validation classes
                form.querySelectorAll('.is-invalid').forEach(function(input) {
                    input.classList.remove('is-invalid');
                });
                form.querySelectorAll('.invalid-feedback').forEach(function(feedback) {
                    feedback.remove();
                });
            }
        });
    });
});
</script>
@endpush
@endsection

