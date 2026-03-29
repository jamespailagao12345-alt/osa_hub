@extends('layouts.app')

@section('title', 'Show Staff')

@section('content')
  <style>
    .staff-thumb {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 4px;
    }
  </style>
  <div class="container-fluid">
    <div class="row">
      @include('admin.partials.sidebar')
  <main class="col-md-10">
        <div class="admin-back-btn-wrap">
          <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
        </div>
        <style>
          /* Header underline style retained */
          .section-header { display:block; width:100%; box-sizing:border-box; background-color: midnightblue; color: white; padding:.5rem 1rem; border:none; border-radius:0; }
          /* Scrollable container for the staff table (vertical and horizontal as needed) */
          .table-scroll { max-height: 70vh; overflow: auto; }
          /* Allow horizontal scrolling when columns exceed viewport, but keep at least container width */
          .table-scroll table { width: max-content; min-width: 100%; }
          /* White rectangular background to visually separate table area */
          .staff-table-card { background:#ffffff; border:1px solid rgba(0,0,0,0.08); border-radius:.375rem; padding:1rem; }
          /* Slightly smaller table for comfortable fit */
          .staff-table { font-size: .93rem; }
          .staff-table th, .staff-table td { padding: .5rem .75rem; }
          /* Make rows from row 2 onward white; keep the first row (header) styling intact */
          .staff-table tbody tr + tr { background-color: #ffffff; color: #000; }
        </style>
        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
            <h3 class="mb-0"><span class="section-header">Staff</span></h3>
            <a href="{{ route('admin.add-staff') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Add Staff
            </a>
        </div>
        <div class="staff-table-card">
        <div class="table-scroll">
        <table class="table table-bordered staff-table align-middle mb-0">
    <tr align="center" style="background-color:midnightblue; color:white">
  <th>Image</th><th>Staff ID</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Contact Number</th><th>Email</th><th>Designation</th><th>Organizations</th><th>Birth Date</th><th>Sex</th><th>Age</th><th>Contract Ends</th><th>Time Left</th><th>Service Order</th><th>Status</th><th>Delete</th><th>Update</th><th>Resend Email</th>
    </tr>
    @foreach($staff as $staffMember)
      <tr id="staff-{{ $staffMember->id }}" align="left">
  <td>
    @php
      $imageUrl = null;
      if (!empty($staffMember->image)) {
        $imagePath = $staffMember->image;
        // Check if it's a default image path or stored image
        if (strpos($imagePath, 'defaults/') === 0) {
          $imageUrl = asset('storage/' . $imagePath);
        } else {
          $imageUrl = \Illuminate\Support\Facades\Storage::url($imagePath);
        }
      } else {
        $imageUrl = asset('storage/defaults/default-profile.png');
      }
    @endphp
    <img class="staff-thumb" src="{{ $imageUrl }}" alt="{{ $staffMember->first_name }} {{ $staffMember->last_name }}" onerror="this.src='{{ asset('storage/defaults/default-profile.png') }}'">
  </td>
  <td>{{ $staffMember->staff_id ?? '-' }}</td>
  <td>{{$staffMember->first_name}}</td>
  <td>{{$staffMember->middle_name ?? ''}}</td>
  <td>{{$staffMember->last_name}}</td>
  <td>{{$staffMember->contact_number ?? ''}}</td>
  <td>{{$staffMember->email}}</td>
  <td>{{$staffMember->designation ?? optional($staffMember->user)->designation ?? ''}}</td>
  <td>
    @if($staffMember->organizations && $staffMember->organizations->count())
      <div style="display: flex; flex-direction: column; gap: 4px;">
        @foreach($staffMember->organizations->sortBy('name') as $org)
          <span style="display: block; width: fit-content; color: black; background: none;">{{ $org->name }}</span>
        @endforeach
      </div>
    @else
      <span class="text-muted">None</span>
    @endif
  </td>
  <td>{{$staffMember->birth_date ?? '-'}}</td>
  <td>{{$staffMember->gender ?? '-'}}</td>
  <td>{{$staffMember->age ?? '-'}}</td>
  <td>
    @if($staffMember->contract_end_at)
      {{ \Carbon\Carbon::parse($staffMember->contract_end_at)->format('Y/m/d/') }}
    @else
      -
    @endif
  </td>
  <td>
    @if($staffMember->contract_end_at)
      <span class="badge bg-info">{{ \Carbon\Carbon::parse($staffMember->contract_end_at)->format('Y/m/d/') }}</span>
    @else
      <span class="text-muted">-</span>
    @endif
  </td>
  <td>
    @if(!empty($staffMember->service_order))
      <a href="{{ \Illuminate\Support\Facades\Storage::url($staffMember->service_order) }}" target="_blank" class="btn btn-info btn-sm">Download S.O.</a>
    @else
      <span class="text-muted">No S.O.</span>
    @endif
  </td>
  <td>
    @php($st = strtolower($staffMember->employment_status ?? ''))
    @if($st === 'active')
      <span class="badge bg-success">Active</span>
    @elseif($st === 'inactive')
      <span class="badge bg-secondary">Inactive</span>
    @elseif($st === 'ended')
      <span class="badge bg-danger">Ended</span>
    @else
      <span class="badge bg-light text-dark">-</span>
    @endif
  </td>
        <td>
          <form method="POST" action="{{ route('admin.staff.destroy', $staffMember->id) }}" style="display:inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this staff member?')">Delete</button>
          </form>
        </td>
        <td>
          <a href="{{ route('admin.staff.edit', $staffMember->id) }}" class="btn btn-warning btn-sm">Update</a>
        </td>
        <td>
          @php
            $user = $staffMember->user ?? \App\Models\User::where('email', $staffMember->email)->first();
            $verificationEmailCount = $user ? ($user->verification_email_count ?? 0) : 0;
          @endphp
          <div class="d-flex flex-column gap-2 align-items-center">
            <small class="text-muted">Sent: {{ $verificationEmailCount }}</small>
            <form action="{{ route('admin.staff.resend-verification', $staffMember->id) }}" method="POST" style="display: inline-block;">
              @csrf
              <button type="submit" class="btn btn-info btn-sm" onclick="return confirm('Are you sure you want to resend the verification email to {{ $staffMember->email }}?')">
                <i class="bi bi-envelope"></i> Resend
              </button>
            </form>
          </div>
        </td>
      </tr>
  @endforeach
  </table>
  </div>
  </div>
  </div>
  
      </main>
    </div>
  </div>
@endsection
@push('scripts')
<script>
// Auto-scroll and highlight the updated staff row if URL has a hash like #staff-123
document.addEventListener('DOMContentLoaded', function(){
  if (location.hash) {
    const el = document.querySelector(location.hash);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      el.classList.add('table-warning');
      setTimeout(()=> el.classList.remove('table-warning'), 3000);
    }
  }
});
</script>
@endpush