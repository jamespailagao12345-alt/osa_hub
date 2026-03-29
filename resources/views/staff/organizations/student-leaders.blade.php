@extends('layouts.app')

@section('title', $organization->name . ' - My Student Leaders')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="admin-back-btn-wrap">
        <a href="{{ route('staff.organizations.index') }}" class="btn btn-secondary">&larr; Back to Organizations</a>
      </div>
    </div>
  </div>
  <div class="row">
    @include('staff.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <h2 class="mb-3">{{ $organization->name }} - My Student Leaders</h2>
      
      <div class="mb-3">
        <a href="{{ route('staff.student-leaders.create', ['organization_id' => $organization->id]) }}" class="btn btn-success">
          <i class="bi bi-person-plus"></i> Add Assistant
        </a>
      </div>
      
      @if($studentLeaders->isEmpty())
        <div class="alert alert-info">
          <p>No student leaders assigned to this organization yet.</p>
        </div>
      @else
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($studentLeaders as $assistant)
                <tr>
                  <td>{{ $assistant->user_id }}</td>
                  <td>{{ $assistant->first_name }} {{ $assistant->middle_name ?? '' }} {{ $assistant->last_name }}</td>
                  <td>{{ $assistant->email }}</td>
                  <td>
                    @if($assistant->suspended)
                      <span class="badge bg-danger">Suspended</span>
                    @else
                      <span class="badge bg-success">Active</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('staff.student-leaders.edit', $assistant->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    @if($assistant->suspended)
                      <form action="{{ route('staff.student-leaders.resume', $assistant->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-success">Resume</button>
                      </form>
                    @else
                      <form action="{{ route('staff.student-leaders.suspend', $assistant->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-warning">Suspend</button>
                      </form>
                    @endif
                    <form action="{{ route('staff.student-leaders.destroy', $assistant->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this student leader?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </main>
  </div>
</div>
@endsection

