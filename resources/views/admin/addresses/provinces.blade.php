@extends('layouts.app')

@section('title', 'Manage Provinces')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.addresses.index') }}" class="btn btn-secondary">&larr; Back</a>
            </div>
            
            <h3 class="mt-4"><span class="d-block w-100 px-3 py-2" style="background-color: midnightblue; color: white; border-radius: 4px;">Manage Provinces</span></h3>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">Add New Province</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.addresses.provinces.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <label for="code" class="form-label">Province Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code') }}" 
                                       placeholder="e.g., MISOR" required maxlength="10">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Province Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="e.g., Misamis Oriental" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">All Provinces</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Cities</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($provinces as $province)
                                    <tr>
                                        <td><strong>{{ $province->code }}</strong></td>
                                        <td>{{ $province->name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $province->cities_count }} cities</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.addresses.cities', $province) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-building"></i> Cities
                                            </a>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProvinceModal{{ $province->id }}">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.addresses.provinces.destroy', $province) }}" style="display:inline" onsubmit="return confirm('Are you sure? This will delete all cities and barangays in this province.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editProvinceModal{{ $province->id }}" tabindex="-1" aria-labelledby="editProvinceModalLabel{{ $province->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.addresses.provinces.update', $province) }}" id="editProvinceForm{{ $province->id }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editProvinceModalLabel{{ $province->id }}">Edit Province</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @if($errors->any() && old('_method') === 'PUT' && old('province_id') == $province->id)
                                                            <div class="alert alert-danger">
                                                                <ul class="mb-0">
                                                                    @foreach($errors->all() as $error)
                                                                        <li>{{ $error }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif
                                                        
                                                        <input type="hidden" name="province_id" value="{{ $province->id }}">
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_code{{ $province->id }}" class="form-label">Province Code <span class="text-danger">*</span></label>
                                                            <input type="text" 
                                                                   class="form-control @error('code') is-invalid @enderror" 
                                                                   id="edit_code{{ $province->id }}" 
                                                                   name="code" 
                                                                   value="{{ old('code', $province->code) }}" 
                                                                   required 
                                                                   maxlength="10"
                                                                   placeholder="e.g., MISOR">
                                                            @error('code')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="edit_name{{ $province->id }}" class="form-label">Province Name <span class="text-danger">*</span></label>
                                                            <input type="text" 
                                                                   class="form-control @error('name') is-invalid @enderror" 
                                                                   id="edit_name{{ $province->id }}" 
                                                                   name="name" 
                                                                   value="{{ old('name', $province->name) }}" 
                                                                   required
                                                                   placeholder="e.g., Misamis Oriental">
                                                            @error('name')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-check-circle"></i> Update Province
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No provinces found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $provinces->links() }}
                </div>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-open edit modal if there are validation errors for a specific province
    @if($errors->any() && old('_method') === 'PUT' && old('province_id'))
        const provinceId = {{ old('province_id') }};
        const editModal = new bootstrap.Modal(document.getElementById('editProvinceModal' + provinceId));
        editModal.show();
    @endif
    
    // Clear form validation errors when modal is closed
    document.querySelectorAll('[id^="editProvinceModal"]').forEach(function(modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
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
