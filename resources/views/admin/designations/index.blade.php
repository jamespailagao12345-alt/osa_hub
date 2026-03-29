@extends('layouts.app')

@section('title', 'Manage Designations')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main id="adminMain" class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">Manage Designations</h2>
                <a href="{{ route('admin.designations.create') }}" class="btn btn-primary">Add New Designation</a>
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
                                    <th>Name</th>
                                    <th>Features</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($designations as $designation)
                                    <tr>
                                        <td>{{ $designation->name }}</td>
                                        <td>
                                            @if($designation->features && count($designation->features) > 0)
                                                <div class="text-dark">
                                                    @foreach($designation->features as $index => $feature)
                                                        {{ $feature }}@if($index < count($designation->features) - 1), @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">No features assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.designations.edit', $designation) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('admin.designations.destroy', $designation) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this designation? This action cannot be undone if it is being used by staff members.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No designations found.</td>
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

