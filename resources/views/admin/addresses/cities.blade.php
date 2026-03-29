@extends('layouts.app')

@section('title', 'Manage Cities - ' . $province->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.addresses.provinces') }}" class="btn btn-secondary">&larr; Back to Provinces</a>
            </div>
            
            <h3 class="mt-4"><span class="d-block w-100 px-3 py-2" style="background-color: midnightblue; color: white; border-radius: 4px;">Manage Cities - {{ $province->name }}</span></h3>
            
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
                    <h5 class="mb-0">Add New City</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.addresses.cities.store', $province) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <label for="name" class="form-label">City/Municipality Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="zip_code" class="form-label">Zip Code</label>
                                <input type="text" class="form-control @error('zip_code') is-invalid @enderror" 
                                       id="zip_code" name="zip_code" value="{{ old('zip_code') }}" maxlength="10">
                                @error('zip_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Add City</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">All Cities in {{ $province->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Zip Code</th>
                                    <th>Barangays</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cities as $city)
                                    <tr>
                                        <td><strong>{{ $city->name }}</strong></td>
                                        <td>{{ $city->zip_code ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $city->barangays_count }} barangays</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.addresses.barangays', [$province, $city]) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-geo-alt"></i> Barangays
                                            </a>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCityModal{{ $city->id }}">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.addresses.cities.destroy', [$province, $city]) }}" style="display:inline" onsubmit="return confirm('Are you sure? This will delete all barangays in this city.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editCityModal{{ $city->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.addresses.cities.update', [$province, $city]) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit City</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="edit_name{{ $city->id }}" class="form-label">City Name</label>
                                                            <input type="text" class="form-control" id="edit_name{{ $city->id }}" 
                                                                   name="name" value="{{ $city->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="edit_zip_code{{ $city->id }}" class="form-label">Zip Code</label>
                                                            <input type="text" class="form-control" id="edit_zip_code{{ $city->id }}" 
                                                                   name="zip_code" value="{{ $city->zip_code }}" maxlength="10">
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
                                        <td colspan="4" class="text-center text-muted">No cities found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $cities->links() }}
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
