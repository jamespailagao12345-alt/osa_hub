@extends('layouts.app')

@section('title', 'Manage Nationalities')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main id="adminMain" class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">Manage Nationalities</h2>
                <a href="{{ route('admin.nationalities.create') }}" class="btn btn-primary">Add New Nationality</a>
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
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Status</th>
                                    <th>Usage Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nationalities as $nationality)
                                    <tr>
                                        <td>{{ $nationality->id }}</td>
                                        <td>{{ $nationality->name }}</td>
                                        <td>{{ $nationality->code ?? 'N/A' }}</td>
                                        <td>
                                            @if($nationality->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $nationality->personalInformation()->count() }}</td>
                                        <td>
                                            <a href="{{ route('admin.nationalities.edit', $nationality) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('admin.nationalities.destroy', $nationality) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this nationality?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No nationalities found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $nationalities->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

