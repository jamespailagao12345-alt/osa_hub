@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Suspension Reports</h2>
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
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $data['suspended_count'] }}</h3>
                            <p class="mb-0">Suspended Students</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts and Tables -->
            <div class="row">
                @if(!empty($data['by_date']))
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Suspensions by Date</div>
                        <div class="card-body">
                            <canvas id="suspensionsChart"></canvas>
                        </div>
                    </div>
                </div>
                @endif
                
                @if(!empty($data['by_reason']))
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Suspensions by Reason</div>
                        <div class="card-body">
                            <div style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Reason</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['by_reason'] as $reason => $count)
                                        <tr>
                                            <td>{{ $reason }}</td>
                                            <td>{{ $count }}</td>
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
    @if(!empty($data['by_date']))
    const ctx = document.getElementById('suspensionsChart').getContext('2d');
    const dates = {!! json_encode(array_keys($data['by_date'])) !!};
    const counts = {!! json_encode(array_values($data['by_date'])) !!};
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: 'Suspensions',
                data: counts,
                backgroundColor: 'rgba(255, 99, 132, 0.8)'
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
