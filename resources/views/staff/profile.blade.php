@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Staff Profile</h2>
        <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary">Back</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($staff)
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <div class="card-body">
            @php
                $initials = '';
                if ($staff) {
                    $firstInitial = strtoupper(substr($staff->first_name ?? '', 0, 1));
                    $lastInitial = strtoupper(substr($staff->last_name ?? '', 0, 1));
                    $initials = $firstInitial . $lastInitial;
                }
                
                // Calculate age from birth_date if available
                $age = $staff->age ?? null;
                if (!$age && $staff->birth_date) {
                    try {
                        $age = \Carbon\Carbon::parse($staff->birth_date)->age;
                    } catch (\Exception $e) {
                        $age = null;
                    }
                }
                
                // Collect all organizations assigned to this staff
                $allOrganizations = collect();
                
                // Add single organization if exists
                if ($staff->organization) {
                    $allOrganizations->push($staff->organization);
                }
                
                // Add many-to-many organizations if exists
                if (method_exists($staff, 'organizations') && $staff->organizations) {
                    foreach ($staff->organizations as $org) {
                        // Avoid duplicates
                        if (!$allOrganizations->contains('id', $org->id)) {
                            $allOrganizations->push($org);
                        }
                    }
                }
            @endphp
            <div class="row mb-3">
                <div class="col-md-9">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8">{{ $staff->first_name ?? '' }} {{ $staff->middle_name ?? '' }} {{ $staff->last_name ?? '' }}</dd>
                        
                        <dt class="col-sm-4">Designation:</dt>
                        <dd class="col-sm-8">{{ strtoupper($staff->designation ?? 'N/A') }}</dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $staff->email ?? 'N/A' }}</dd>
                        
                        @if($staff->user_id)
                        <dt class="col-sm-4">Staff ID:</dt>
                        <dd class="col-sm-8">{{ $staff->user_id }}</dd>
                        @endif
                        
                        @if($staff->department)
                        <dt class="col-sm-4">Department:</dt>
                        <dd class="col-sm-8">{{ $staff->department->name }}</dd>
                        @endif
                        
                        @if($staff->birth_date)
                        <dt class="col-sm-4">Birthdate:</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($staff->birth_date)->format('F d, Y') }}</dd>
                        @endif
                        
                        @if($age)
                        <dt class="col-sm-4">Age:</dt>
                        <dd class="col-sm-8">{{ $age }} years old</dd>
                        @endif
                        
                        @if($staff->contact_number)
                        <dt class="col-sm-4">Contact Number:</dt>
                        <dd class="col-sm-8">{{ $staff->contact_number }}</dd>
                        @endif
                        
                        @if(!$allOrganizations->isEmpty())
                        <dt class="col-sm-4">Organization(s):</dt>
                        <dd class="col-sm-8">
                            <ul class="list-unstyled mb-0">
                                @foreach($allOrganizations as $org)
                                    <li>• {{ $org->name }}</li>
                                @endforeach
                            </ul>
                        </dd>
                        @endif
                    </dl>
                </div>
                <div class="col-md-3 d-flex justify-content-center align-items-start">
                    @if($staff->image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($staff->image) }}" 
                         alt="{{ $staff->first_name }} {{ $staff->last_name }}" 
                         class="rounded-circle" 
                         style="width: 150px; height: 150px; object-fit: cover; border: 3px solid midnightblue;">
                    @else
                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                         style="width: 150px; height: 150px; border: 3px solid midnightblue;">
                        <span class="text-white" style="font-size: 3rem; font-weight: bold;">{{ $initials ?: 'S' }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        <p class="mb-0">No profile information available.</p>
    </div>
    @endif
</div>
@endsection
