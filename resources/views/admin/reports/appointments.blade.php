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
                <h2>Appointment Reports</h2>
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
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['total'] }}</h3>
                            <p class="mb-0">Total Appointments</p>
                        </div>
                    </div>
                </div>
                @foreach($data['by_status'] as $status => $count)
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $count }}</h3>
                            <p class="mb-0">{{ ucfirst($status) }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Charts and Tables -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Appointments by Date</div>
                        <div class="card-body">
                            <canvas id="appointmentsChart"></canvas>
                        </div>
                    </div>
                </div>
                @if($data['is_admin'] && $data['by_staff'])
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Appointments by Staff</div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Staff</th>
                                        <th>Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['by_staff'] as $staff)
                                    <tr>
                                        <td>{{ $staff['staff_name'] }}</td>
                                        <td>{{ $staff['count'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
    const ctx = document.getElementById('appointmentsChart').getContext('2d');
    const dates = {!! json_encode(array_keys($data['by_date'])) !!};
    const counts = {!! json_encode(array_values($data['by_date'])) !!};
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Appointments',
                data: counts,
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
</script>
@endsection
