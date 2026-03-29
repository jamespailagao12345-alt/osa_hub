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
                <h2>{{ $data['is_admin'] ? 'Student Reports' : 'My Student Information' }}</h2>
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
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['active_students'] }}</h3>
                            <p class="mb-0">Active Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['total_students'] }}</h3>
                            <p class="mb-0">Total Students</p>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($data['is_admin'])
            <!-- Admin-only tables -->
            <div class="row">
                @if($data['by_department'])
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Students by Department</div>
                        <div class="card-body">
                            <div style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Department</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['by_department'] as $dept)
                                        <tr>
                                            <td>{{ $dept['department'] }}</td>
                                            <td>{{ $dept['count'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($data['by_year_level'])
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Students by Year Level</div>
                        <div class="card-body">
                            <canvas id="yearLevelChart"></canvas>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif
            
            <!-- New Registrations Chart -->
            @if(!empty($data['new_registrations']))
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">New Registrations</div>
                        <div class="card-body">
                            <canvas id="registrationsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if($data['is_admin'] && $data['by_year_level'])
    const yearLevelCtx = document.getElementById('yearLevelChart').getContext('2d');
    const yearLevelData = {!! json_encode($data['by_year_level']) !!};
    
    new Chart(yearLevelCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(yearLevelData),
            datasets: [{
                label: 'Students',
                data: Object.values(yearLevelData),
                backgroundColor: 'rgba(54, 162, 235, 0.8)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    @endif
    
    @if(!empty($data['new_registrations']))
    const regCtx = document.getElementById('registrationsChart').getContext('2d');
    const regDates = {!! json_encode(array_keys($data['new_registrations'])) !!};
    const regCounts = {!! json_encode(array_values($data['new_registrations'])) !!};
    
    new Chart(regCtx, {
        type: 'line',
        data: {
            labels: regDates,
            datasets: [{
                label: 'New Registrations',
                data: regCounts,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    @endif
</script>
@endsection
