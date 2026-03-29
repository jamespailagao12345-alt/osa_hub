@extends('layouts.app')

@php
  $orgName = auth()->user()->organization->name ?? null;
  $title = $orgName ? ($orgName . ' Student Leaders') : 'Student Leaders';
@endphp
@section('title', $title)

@section('content')
<style>
  .assistant-thumb {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
  }
</style>
<div class="container">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  <div class="admin-back-btn-wrap mb-3">
    <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary">Back</a>
  </div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><span class="px-2 py-1" style="background-color: midnightblue; color: white; border-radius: 4px;">Student Leaders</span></h3>
    <a href="{{ route('staff.student-leaders.create') }}" class="btn btn-primary">Add Student Leader</a>
  </div>
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div></div>
        <form method="GET" class="d-flex align-items-center" action="{{ route('staff.student-leaders.index') }}">
          <label for="status" class="me-2 mb-0">Show:</label>
          <select id="status" name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 180px;">
            <option value="all" {{ ($status ?? 'all')==='all' ? 'selected' : '' }}>All</option>
            <option value="active" {{ ($status ?? 'all')==='active' ? 'selected' : '' }}>Active</option>
            <option value="suspended" {{ ($status ?? 'all')==='suspended' ? 'selected' : '' }}>Suspended</option>
          </select>
        </form>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr align="center" style="background-color: midnightblue; color: white;">
              <th>Image</th>
              <th>User ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Position</th>
              <th style="width: 200px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($studentLeaders as $a)
            <tr style="background-color:#5DFFBF; color:black">
              <td align="center">
                @php
                  $imageUrl = null;
                  if (!empty($a->image)) {
                    // Check if image exists in storage before generating URL
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($a->image)) {
                      $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($a->image);
                    }
                  }
                @endphp
                @if($imageUrl)
                  <img class="assistant-thumb" src="{{ $imageUrl }}" alt="{{ $a->first_name }} {{ $a->last_name }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                  <span class="text-muted" style="display:none;">No image</span>
                @else
                  <span class="text-muted">No image</span>
                @endif
              </td>
              <td>{{ $a->user_id }}</td>
              <td>{{ $a->first_name }} {{ $a->last_name }}</td>
              <td>{{ $a->email }}</td>
              <td>
                {{ $a->position ?? '—' }}
                @if($a->suspended)
                  <span class="badge bg-warning text-dark ms-1">Suspended</span>
                @endif
              </td>
              <td>
                <a href="{{ route('staff.student-leaders.edit', $a->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                @if(!$a->suspended)
                  <form action="{{ route('staff.student-leaders.suspend', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Suspend this assistant?');">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-sm btn-outline-warning">Suspend</button>
                  </form>
                @else
                  <form action="{{ route('staff.student-leaders.resume', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Resume this assistant?');">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-sm btn-outline-success">Resume</button>
                  </form>
                @endif
                @if(!($a->suspended))
                  <form action="{{ route('staff.student-leaders.destroy', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this assistant?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted">No assistants yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
