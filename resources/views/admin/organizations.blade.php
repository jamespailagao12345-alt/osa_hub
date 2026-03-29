@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main class="col-md-10 py-4">
        <div class="admin-back-btn-wrap mb-3">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
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

        <div class="py-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h4 mb-2">Organizations Management</h1>
                    <p class="text-muted small mb-0">Manage organizations and their official email addresses. Official emails are used to send notifications about events (approval, decline, missing requirements).</p>
                </div>
                <div>
                    <a href="{{ route('admin.organizations.create') }}" class="btn btn-success btn-lg" style="background-color: #28a745; border-color: #28a745; color: white; padding: 10px 20px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block; min-width: 200px;">
                        ➕ CREATE NEW ORGANIZATION
                    </a>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Organization Name</th>
                            <th>Department</th>
                            <th>Official Email</th>
                            <th>Organization Moderator</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($organizations as $organization)
                        <tr>
                            <td><strong>{{ $organization->name }}</strong></td>
                            <td>{{ $organization->department->name ?? 'N/A' }}</td>
                            <td>
                                @if($organization->official_email)
                                    <span class="text-success">{{ $organization->official_email }}</span>
                                @else
                                    <span class="text-danger">Not Set</span>
                                @endif
                            </td>
                            <td>
                                @if($organization->staff->count() > 0)
                                    @foreach($organization->staff as $staff)
                                        {{ $staff->first_name }} {{ $staff->last_name }}
                                        @if(!$loop->last), @endif
                                    @endforeach
                                @else
                                    <span class="text-muted">Not Assigned</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.organizations.profile', $organization->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-person-circle me-1"></i>View Profile
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No organizations found.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
  </div>
</div>

@endsection

