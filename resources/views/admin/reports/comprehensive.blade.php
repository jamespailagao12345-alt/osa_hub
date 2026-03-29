@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Comprehensive Report</h2>
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
            
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['appointments']['total'] ?? 0 }}</h3>
                            <p class="mb-0">Total Appointments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['events']['total'] ?? 0 }}</h3>
                            <p class="mb-0">Total Events</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['students']['active_students'] ?? 0 }}</h3>
                            <p class="mb-0">Active Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['scholars']['active_scholars'] ?? 0 }}</h3>
                            <p class="mb-0">Active Scholars</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Sections -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Appointment Statistics</div>
                        <div class="card-body">
                            <p><strong>Total:</strong> {{ $data['appointments']['total'] ?? 0 }}</p>
                            <ul>
                                @foreach($data['appointments']['by_status'] ?? [] as $status => $count)
                                <li>{{ ucfirst($status) }}: {{ $count }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Event Statistics</div>
                        <div class="card-body">
                            <p><strong>Total:</strong> {{ $data['events']['total'] ?? 0 }}</p>
                            <p><strong>Approved:</strong> {{ $data['events']['status_breakdown']['approved'] ?? 0 }}</p>
                            <p><strong>Declined:</strong> {{ $data['events']['status_breakdown']['declined'] ?? 0 }}</p>
                            <p><strong>Cancelled:</strong> {{ $data['events']['status_breakdown']['cancelled'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Student Statistics</div>
                        <div class="card-body">
                            <p><strong>Active Students:</strong> {{ $data['students']['active_students'] ?? 0 }}</p>
                            <p><strong>Total Students:</strong> {{ $data['students']['total_students'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Suspension Statistics</div>
                        <div class="card-body">
                            <p><strong>Suspended Students:</strong> {{ $data['suspensions']['suspended_count'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
