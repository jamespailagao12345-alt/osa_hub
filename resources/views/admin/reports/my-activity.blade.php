@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        @php
            $role = auth()->user()->role ?? 4;
        @endphp
        @if($role == 4)
            @include('admin.partials.sidebar')
        @elseif($role == 2)
            @include('staff.partials.sidebar')
        @elseif($role == 1)
            @include('admin.partials.sidebar')
        @elseif($role == 3)
            @include('student-leader.partials.sidebar')
        @endif
        <main class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>My Activity Summary</h2>
                <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back to Reports</a>
            </div>
            
            <!-- Period Filter -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label>Period</label>
                            <select name="period" class="form-select">
                                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Activity Summary Cards -->
            <div class="row mb-4">
                @if($data['role'] == 1)
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['appointments'] ?? 0 }}</h3>
                            <p class="mb-0">Appointments Made</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['event_participations'] ?? 0 }}</h3>
                            <p class="mb-0">Events Participated</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['points_earned'] ?? 0 }}</h3>
                            <p class="mb-0">Points Earned</p>
                        </div>
                    </div>
                </div>
                @elseif($data['role'] == 2)
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['appointments_assigned'] ?? 0 }}</h3>
                            <p class="mb-0">Appointments Assigned</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['events_created'] ?? 0 }}</h3>
                            <p class="mb-0">Events Created</p>
                        </div>
                    </div>
                </div>
                @elseif($data['role'] == 3)
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['events_created'] ?? 0 }}</h3>
                            <p class="mb-0">Events Created</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['organization_members'] ?? 0 }}</h3>
                            <p class="mb-0">Organization Members</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Quick Links -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">
                            <h5 class="mb-0">Quick Links to Detailed Reports</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('reports.appointments') }}" class="btn btn-primary w-100">Appointment Reports</a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('reports.events') }}" class="btn btn-primary w-100">Event Reports</a>
                                </div>
                                @if($data['role'] == 1 || $data['role'] == 4)
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('reports.students') }}" class="btn btn-primary w-100">Student Reports</a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('reports.scholars') }}" class="btn btn-primary w-100">Scholar Reports</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
