@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10">
        <h2>Student Leaders List</h2>
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label for="role_type" class="form-label">Role Type</label>
                <select name="role_type" id="role_type" class="form-select">
                    <option value="">All</option>
                    <option value="student-leader" {{ (isset($filters['role_type']) && $filters['role_type']==='student-leader') ? 'selected' : '' }}>Student Leader (role 3)</option>
                    <option value="student" {{ (isset($filters['role_type']) && $filters['role_type']==='student') ? 'selected' : '' }}>Student (role 1)</option>
                    <option value="none" {{ (isset($filters['role_type']) && $filters['role_type']==='none') ? 'selected' : '' }}>No Role</option>
                </select>
            </div>
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
                <a href="{{ route('admin.show-student-leaders') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr class="text-center" style="background-color:midnightblue; color:white">
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Primary Organization</th>
                        <th>Other Organizations</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentLeaders as $staff)
                    <tr class="text-center">
                        <td>{{ $staff->id }}</td>
                        <td>{{ $staff->first_name }} {{ $staff->last_name }}</td>
                        <td>{{ $staff->email }}</td>
                        <td>{{ $staff->department->name ?? '-' }}</td>
                        <td>{{ $staff->organization->name ?? '-' }}</td>
                        <td>
                            @php($others = $staff->otherOrganizations->pluck('name')->filter()->values())
                            {{ $others->isNotEmpty() ? $others->implode(', ') : '-' }}
                        </td>
                        <td>
                            <!-- Action buttons here -->
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $studentLeaders->links() }}
        </div>
        </main>
    </div>
</div>
@endsection
