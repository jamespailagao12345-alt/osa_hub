@extends('layouts.app')

@section('title', 'Create Department')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main id="adminMain" class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            </div>
            
            <h2 class="mb-3">Create New Department</h2>

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
                    <form action="{{ route('admin.departments.store') }}" method="POST" id="departmentForm">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Courses and Sections</h5>
                        <p class="text-muted small">Add courses for this department. Each course can have multiple sections.</p>

                        <div id="coursesContainer">
                            <!-- Courses will be added here dynamically -->
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addCourseBtn">
                            <i class="bi bi-plus-circle"></i> Add Course
                        </button>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Create Department</button>
                            <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            @push('scripts')
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                let courseIndex = 0;

                function addCourse(courseName = '', sections = []) {
                    const courseId = `course_${courseIndex++}`;
                    const courseHtml = `
                        <div class="card mb-3 course-item" data-course-index="${courseIndex - 1}">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <strong>Course ${courseIndex}</strong>
                                <button type="button" class="btn btn-sm btn-danger remove-course-btn">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Course Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control course-name" 
                                           name="courses[${courseIndex - 1}][name]" 
                                           value="${courseName}" 
                                           placeholder="e.g., Bachelor of Science in Information Technology" required>
                                </div>
                                
                                <div class="sections-container mb-2">
                                    <label class="form-label small">Sections</label>
                                    <div class="sections-list">
                                        ${sections.map((section, idx) => `
                                            <div class="input-group mb-2 section-item">
                                                <input type="text" class="form-control section-name" 
                                                       name="courses[${courseIndex - 1}][sections][]" 
                                                       value="${section}" 
                                                       placeholder="e.g., Section A" required>
                                                <button type="button" class="btn btn-outline-danger remove-section-btn">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        `).join('')}
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary add-section-btn">
                                        <i class="bi bi-plus"></i> Add Section
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    const container = document.getElementById('coursesContainer');
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = courseHtml;
                    container.appendChild(tempDiv.firstElementChild);

                    // Attach event listeners
                    const courseItem = container.querySelector(`[data-course-index="${courseIndex - 1}"]`);
                    attachCourseListeners(courseItem);
                }

                function attachCourseListeners(courseItem) {
                    // Remove course button
                    courseItem.querySelector('.remove-course-btn').addEventListener('click', function() {
                        if (confirm('Are you sure you want to remove this course and all its sections?')) {
                            courseItem.remove();
                        }
                    });

                    // Add section button
                    courseItem.querySelector('.add-section-btn').addEventListener('click', function() {
                        const sectionsList = courseItem.querySelector('.sections-list');
                        const courseIndex = courseItem.dataset.courseIndex;
                        const sectionHtml = `
                            <div class="input-group mb-2 section-item">
                                <input type="text" class="form-control section-name" 
                                       name="courses[${courseIndex}][sections][]" 
                                       placeholder="e.g., Section A" required>
                                <button type="button" class="btn btn-outline-danger remove-section-btn">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        `;
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = sectionHtml;
                        sectionsList.appendChild(tempDiv.firstElementChild);

                        // Attach remove section listener
                        tempDiv.querySelector('.remove-section-btn').addEventListener('click', function() {
                            this.closest('.section-item').remove();
                        });
                    });

                    // Remove section buttons
                    courseItem.querySelectorAll('.remove-section-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            this.closest('.section-item').remove();
                        });
                    });
                }

                // Add first course button
                document.getElementById('addCourseBtn').addEventListener('click', function() {
                    addCourse();
                });

                // Add initial course if needed
                @if(old('courses'))
                    @foreach(old('courses') as $courseIndex => $course)
                        addCourse('{{ $course['name'] ?? '' }}', {{ json_encode($course['sections'] ?? []) }});
                    @endforeach
                @else
                    addCourse();
                @endif
            });
            </script>
            @endpush
        </main>
    </div>
</div>
@endsection

