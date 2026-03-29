@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        @if(auth()->user()->role == 4)
            @include('admin.partials.sidebar')
        @elseif(auth()->user()->role == 2)
            @include('admin.partials.sidebar')
        @elseif(auth()->user()->role == 1)
            @include('admin.partials.sidebar')
        @elseif(auth()->user()->role == 3)
            @include('admin.partials.sidebar')
        @endif
        
        <main class="col-md-10">
            <h2>Activity Reports</h2>
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <!-- My Activity Summary Card -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                    <h5 class="mb-0">My Activity Summary</h5>
                </div>
                <div class="card-body">
                    @if($role == 1)
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Appointments Made:</strong> <span class="badge bg-primary">{{ $myActivity['appointments'] ?? 0 }}</span></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Events Participated:</strong> <span class="badge bg-success">{{ $myActivity['event_participations'] ?? 0 }}</span></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Points Earned:</strong> <span class="badge bg-warning text-dark">{{ $myActivity['points_earned'] ?? 0 }}</span></p>
                            </div>
                        </div>
                    @elseif($role == 2)
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Appointments Assigned:</strong> <span class="badge bg-primary">{{ $myActivity['appointments_assigned'] ?? 0 }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Events Created:</strong> <span class="badge bg-success">{{ $myActivity['events_created'] ?? 0 }}</span></p>
                            </div>
                        </div>
                    @elseif($role == 3)
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Events Created:</strong> <span class="badge bg-primary">{{ $myActivity['events_created'] ?? 0 }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Organization Members:</strong> <span class="badge bg-info">{{ $myActivity['organization_members'] ?? 0 }}</span></p>
                            </div>
                        </div>
                    @elseif($role == 4)
                    @endif
                    
                    @if(isset($historicalData) && !empty($historicalData['datasets']))
                    <div class="mt-4">
                        <h6 class="mb-3">Activity Trends (Last 6 Months)</h6>
                        <canvas id="activityChart" style="max-height: 300px;"></canvas>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Appointment Reports</h5>
                            <p class="card-text">View appointment statistics and trends</p>
                            <a href="{{ route('reports.appointments') }}" class="btn btn-primary">View Report</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Event Reports</h5>
                            <p class="card-text">Analyze event creation and status</p>
                            <a href="{{ route('reports.events') }}" class="btn btn-primary">View Report</a>
                        </div>
                    </div>
                </div>
                
                @if($role == 1 || $role == 4)
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Student Reports</h5>
                            <p class="card-text">{{ $role == 1 ? 'View your student information' : 'Student statistics and demographics' }}</p>
                            <a href="{{ route('reports.students') }}" class="btn btn-primary">View Report</a>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($role == 1 || $role == 4)
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Scholar Reports</h5>
                            <p class="card-text">{{ $role == 1 ? 'View your scholarship information' : 'Scholarship and scholar statistics' }}</p>
                            <a href="{{ route('reports.scholars') }}" class="btn btn-primary">View Report</a>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($role == 4)
                <!-- Admin-only reports -->
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Suspension Reports</h5>
                            <p class="card-text">Student suspension statistics</p>
                            <a href="{{ route('reports.suspensions') }}" class="btn btn-primary">View Report</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Comprehensive Report</h5>
                            <p class="card-text">All statistics in one view</p>
                            <a href="{{ route('reports.comprehensive') }}" class="btn btn-primary">View Report</a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
@if(isset($historicalData) && !empty($historicalData['datasets']))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('activityChart');
    if (ctx) {
        const chartData = @json($historicalData);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>
@endif
@endpush
