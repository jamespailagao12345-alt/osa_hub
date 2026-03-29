@extends('layouts.app')

@php
  $orgName = auth()->user()->organization->name ?? null;
  $title = $orgName ? ($orgName . ' Student Leaders') : 'Student Leaders';
@endphp
@section('title', $title)

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main id="adminMain" class="col-md-10">
      <div class="admin-back-btn-wrap">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
      </div>
      <style>
        .section-header { display:block; width:100%; box-sizing:border-box; background-color: midnightblue; color: white; padding:.5rem 1rem; border:none; border-radius:0; }
        .assistant-thumb {
          width: 80px;
          height: 80px;
          object-fit: cover;
          border-radius: 4px;
        }
      </style>
      <h2 class="mb-3"><span class="section-header">Student Leaders</span></h2>
      <div class="mb-3 d-flex justify-content-end">
        <a href="{{ route('admin.student-leaders.create') }}" class="btn btn-primary">Add New Student Leader</a>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
            <tr align="center" style="background-color: midnightblue; color: white;">
              <th>Image</th>
              <th>Student ID</th>
              <th>First Name</th>
              <th>Middle Name</th>
              <th>Last Name</th>
              <th>Contact No.</th>
              <th>Email</th>
              <th>Position</th>
              <th>Organization</th>
              <th>Student Org. Moderator</th>
              <th>Birth Date</th>
              <th>Sex</th>
              <th>Age</th>
              <th>Account Status</th>
              <th>Update</th>
            </tr>
          </thead>
          <tbody>
            @forelse($studentLeaders as $a)
              <tr>
                <td>
                  @if(!empty($a->image))
                    <img class="assistant-thumb" src="{{ \Illuminate\Support\Facades\Storage::url($a->image) }}" alt="{{ $a->first_name }} {{ $a->last_name }}">
                  @else
                    <span class="text-muted">No image</span>
                  @endif
                </td>
                <td>{{ $a->user_id ?? '-' }}</td>
                <td>{{ $a->first_name }}</td>
                <td>{{ $a->middle_name ?? '-' }}</td>
                <td>{{ $a->last_name }}</td>
                <td>{{ $a->contact_number ?? '-' }}</td>
                <td>{{ $a->email }}</td>
                <td>{{ $a->position ?? 'Student Leader' }}</td>
                <td>
                  @php
                    $orgs = collect();
                    if ($a->organization) {
                      $orgs->push($a->organization);
                    }
                    if (isset($a->otherOrganizations) && $a->otherOrganizations) {
                      $orgs = $orgs->merge($a->otherOrganizations);
                    }
                  @endphp
                  @if($orgs->isNotEmpty())
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                      @foreach($orgs as $org)
                        <span style="display: block; width: fit-content; color: black; background: none;">{{ $org->name }}</span>
                      @endforeach
                    </div>
                  @else
                    <span class="text-muted">None</span>
                  @endif
                </td>
                <td>
                  @if($a->supervisor)
                    {{ $a->supervisor->first_name }} {{ $a->supervisor->middle_name ?? '' }} {{ $a->supervisor->last_name }}
                    @if($a->supervisor->designation)
                      <br><small class="text-muted">({{ $a->supervisor->designation }})</small>
                    @endif
                  @else
                    <span class="text-muted">Not assigned</span>
                  @endif
                </td>
                <td>{{ $a->birth_date ? \Carbon\Carbon::parse($a->birth_date)->format('Y-m-d') : '-' }}</td>
                <td>{{ $a->gender ?? '-' }}</td>
                <td>{{ $a->age ?? ($a->birth_date ? \Carbon\Carbon::parse($a->birth_date)->age : '-') }}</td>
                <td>
                  @if($a->suspended)
                    <span class="badge bg-warning text-dark">Suspended</span>
                  @else
                    <span class="badge bg-success">Active</span>
                  @endif
                </td>
                <td>
                  <a href="{{ route('admin.student-leaders.edit', $a->id) }}" class="btn btn-sm btn-warning">Update</a>
                </td>
              </tr>
            @empty
              <tr><td colspan="15" class="text-center text-muted">No assistants found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>
@endsection
