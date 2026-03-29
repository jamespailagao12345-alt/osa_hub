@extends('layouts.app')

@section('title', 'Organization Registration Requests')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('assistant.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <div class="admin-back-btn-wrap">
        <a href="{{ route('student-leader.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
      </div>
      <div class="py-4">
    <h2 class="mb-4">Pending Organization Registration Requests</h2>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="card">
        <div class="card-body">
            @if($requests->isEmpty())
                <p class="text-muted">No pending requests.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Organization</th>
                                <th>Date Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                            <tr>
                                <td>{{ $req->student_name }}</td>
                                <td>{{ $req->organization_name }}</td>
                                <td>{{ $req->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('student-leader.organization-requests.approve', $req->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('student-leader.organization-requests.decline', $req->id) }}" class="d-inline ms-2">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Decline</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
      </div>
    </main>
  </div>
</div>
@endsection
