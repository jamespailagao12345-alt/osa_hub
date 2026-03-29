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
                <h2>Event Reports</h2>
                <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back to Reports</a>
            </div>
            
            <!-- Period Filter -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-2">
                            <label>Period</label>
                            <select name="period" class="form-select">
                                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                        @if($data['is_admin'])
                        <div class="col-md-2">
                            <label>Filter By</label>
                            <select name="filter_by" class="form-select">
                                <option value="all" {{ $filterBy == 'all' ? 'selected' : '' }}>All</option>
                                <option value="org" {{ $filterBy == 'org' ? 'selected' : '' }}>By Organization</option>
                                <option value="staff" {{ $filterBy == 'staff' ? 'selected' : '' }}>By Staff</option>
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['total'] }}</h3>
                            <p class="mb-0">Total Events</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['status_breakdown']['approved'] ?? 0 }}</h3>
                            <p class="mb-0">Approved</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['status_breakdown']['declined'] ?? 0 }}</h3>
                            <p class="mb-0">Declined</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['status_breakdown']['cancelled'] ?? 0 }}</h3>
                            <p class="mb-0">Cancelled</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts and Tables -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Events by Status</div>
                        <div class="card-body">
                            <canvas id="eventsStatusChart"></canvas>
                        </div>
                    </div>
                </div>
                @if($data['is_admin'] && $data['by_organization'])
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Events by Organization</div>
                        <div class="card-body">
                            <div style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Organization</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['by_organization'] as $org)
                                        <tr>
                                            <td>{{ $org['org_name'] }}</td>
                                            <td>{{ $org['count'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('eventsStatusChart').getContext('2d');
    const statusData = {!! json_encode($data['by_status']) !!};
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });
</script>
@endsection
