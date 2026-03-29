@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10">
        <h2>Admins List</h2>
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label for="department_id" class="form-label">Department</label>
                <select name="department_id" id="department_id" class="form-select">
                    <option value="">All</option>
                    @isset($departments)
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (isset($filters['department_id']) && (string)$filters['department_id']===(string)$dept->id) ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <div class="col-auto">
                <label for="organization_id" class="form-label">Organization</label>
                <select name="organization_id" id="organization_id" class="form-select">
                    <option value="">All</option>
                    @isset($organizations)
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ (isset($filters['organization_id']) && (string)$filters['organization_id']===(string)$org->id) ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.show-admins') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr class="text-center" style="background-color:midnightblue; color:white">
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admins as $admin)
                        @if(isset($admin->user_id) && $admin->user_id === 'admin001')
                            @continue
                        @endif
                    <tr class="text-center">
                        <td>{{ $admin->first_name }} {{ $admin->last_name }}</td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->role }}</td>
                        <td>
                            <!-- Action buttons here -->
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $admins->links() }}
        </div>
        </main>
    </div>
</div>
@endsection
