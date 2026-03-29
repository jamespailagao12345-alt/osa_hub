@extends('layouts.app')

@section('title', 'Manage Barangays - ' . $city->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.addresses.cities', $province) }}" class="btn btn-secondary">&larr; Back to Cities</a>
            </div>
            
            <h3 class="mt-4"><span class="d-block w-100 px-3 py-2" style="background-color: midnightblue; color: white; border-radius: 4px;">Manage Barangays - {{ $city->name }}, {{ $province->name }}</span></h3>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">Add New Barangay</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.addresses.barangays.store', [$province, $city]) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <label for="name" class="form-label">Barangay Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Add Barangay</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">All Barangays in {{ $city->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($barangays as $barangay)
                                    <tr>
                                        <td><strong>{{ $barangay->name }}</strong></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editBarangayModal{{ $barangay->id }}">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.addresses.barangays.destroy', [$province, $city, $barangay]) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this barangay?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editBarangayModal{{ $barangay->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.addresses.barangays.update', [$province, $city, $barangay]) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Barangay</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="edit_name{{ $barangay->id }}" class="form-label">Barangay Name</label>
                                                            <input type="text" class="form-control" id="edit_name{{ $barangay->id }}" 
                                                                   name="name" value="{{ $barangay->name }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No barangays found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $barangays->links() }}
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
