@extends('layouts.app')

@section('title', 'Address Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">&larr; Back</a>
            </div>
            
            <h3 class="mt-4"><span class="d-block w-100 px-3 py-2" style="background-color: midnightblue; color: white; border-radius: 4px;">Address Management</span></h3>
            
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

            <!-- User Addresses Section -->
            <div class="card mb-3">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">User Addresses (from addresses table)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Province</th>
                                    <th>City/Municipality</th>
                                    <th>Barangay</th>
                                    <th>Street</th>
                                    <th>Zip Code</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($userAddresses as $address)
                                    <tr>
                                        <td>
                                            @if($address->addressable)
                                                <strong>{{ $address->addressable->first_name }} {{ $address->addressable->last_name }}</strong><br>
                                                <small class="text-muted">{{ $address->addressable->email ?? 'N/A' }}</small>
                                            @else
                                                <span class="text-muted">User not found</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($address->type) }}</span>
                                        </td>
                                        <td>{{ $address->province ?? '-' }}</td>
                                        <td>{{ $address->city_municipality ?? '-' }}</td>
                                        <td>{{ $address->barangay ?? '-' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($address->street, 30) ?? '-' }}</td>
                                        <td>{{ $address->zip_code ?? '-' }}</td>
                                        <td>{{ $address->created_at ? $address->created_at->format('M d, Y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No user addresses found in the addresses table.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($userAddresses->hasPages())
                        <div class="mt-3">
                            {{ $userAddresses->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Reference Data Section -->
            <div class="card mb-3">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">Address Reference Data (Provinces, Cities, Barangays)</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.addresses.provinces') }}" class="btn btn-primary mb-3">
                        <i class="bi bi-plus-circle"></i> Manage Provinces
                    </a>
                    
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
                                                <i class="bi bi-building"></i> View Cities
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No provinces found. <a href="{{ route('admin.addresses.provinces') }}">Add one</a></td>
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
