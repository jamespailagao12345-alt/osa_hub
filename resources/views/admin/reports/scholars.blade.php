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
                <h2>{{ $data['is_admin'] ? 'Scholar Reports' : 'My Scholarship Information' }}</h2>
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
                            <h3>{{ $data['active_scholars'] }}</h3>
                            <p class="mb-0">Active Scholars</p>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($data['is_admin'] && $data['by_scholarship'])
            <!-- Admin-only: Scholars by Scholarship Type -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header" style="background-color: midnightblue; color: white;">Scholars by Scholarship Type</div>
                        <div class="card-body">
                            <div style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Scholarship</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['by_scholarship'] as $scholar)
                                        <tr>
                                            <td>{{ $scholar['scholarship'] }}</td>
                                            <td>{{ $scholar['count'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </main>
    </div>
</div>
@endsection
