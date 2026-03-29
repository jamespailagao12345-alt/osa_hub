@extends('layouts.app')

@section('title', 'Manage Organizations')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main id="adminMain" class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            </div>
            
            <!-- Create Button - Standalone and Highly Visible -->
            <div class="mb-3" style="text-align: right;">
                <a href="{{ route('admin.organizations.create') }}" class="btn btn-success btn-lg" style="background-color: #28a745; border-color: #28a745; color: white; padding: 10px 20px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block; min-width: 200px;">
                    ➕ CREATE NEW ORGANIZATION
                </a>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Manage Organizations</h2>
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

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #0d6efd; color: white;">
                    <h5 class="mb-0" style="color: white; font-weight: 600;">Organizations List</h5>
                    <a href="{{ route('admin.organizations.create') }}" class="btn btn-warning btn-sm" style="background-color: #ffc107; border-color: #ffc107; color: #000; font-weight: bold; padding: 5px 15px; text-decoration: none;">
                        ➕ CREATE ORGANIZATION
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Acronym</th>
                                    <th>Department</th>
                                    <th>Official Email</th>
                                    <th>Special</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($organizations as $organization)
                                    <tr>
                                        <td>{{ $organization->id }}</td>
                                        <td>{{ $organization->name }}</td>
                                        <td>{{ $organization->acronym ?? 'N/A' }}</td>
                                        <td>{{ $organization->department->name ?? 'N/A' }}</td>
                                        <td>{{ $organization->official_email }}</td>
                                        <td>
                                            @if($organization->is_special)
                                                <span class="badge bg-warning">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 align-items-center">
                                                <a href="{{ route('admin.organizations.edit', $organization) }}" class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.organizations.destroy', $organization) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this organization? This action cannot be undone if it has associated users or members.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No organizations found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

