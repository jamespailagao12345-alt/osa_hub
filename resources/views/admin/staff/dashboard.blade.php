@extends('layouts.app')

@php
  $currentUser = auth()->user();
  $designation = trim(($currentUser?->designation ?? $currentUser?->staffProfile?->designation ?? ''));
  $firstName = trim(($currentUser?->first_name ?? ''));
  $pageTitle = ($designation !== '' && $firstName !== '')
    ? ($designation . ' — ' . $firstName)
    : ($designation . $firstName);
  $shouldShowBackButton = (int)($currentUser?->role ?? 0) === 4;
@endphp

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main id="adminMain" class="col-md-10">
      @if($shouldShowBackButton)
      <div class="admin-back-btn-wrap">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
      </div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      <style>
        /* Align Staff Dashboards header with global section header style */
        .section-header { display:block; width:100%; box-sizing:border-box; background-color: midnightblue; color: white; padding:.5rem 1rem; border:none; border-radius:0; }
        /* Subtle divider under section labels, slightly lower than text */
        .section-divider { position: relative; }
        .section-divider::after {
          content: "";
          display: block;
          border-bottom: 1px solid #e0e0e0;
          margin-top: 4px; /* slight offset below the label */
        }
      </style>
      <style>
        /* Make staff rows clickable with hover effect */
        .staff-row-clickable:hover {
          background-color: rgba(25, 25, 112, 0.06) !important;
        }
        .staff-row-clickable:not([data-href="#"]):hover {
          cursor: pointer;
        }
      </style>
  <h2 class="mb-3"><span class="section-header">Staff Dashboards</span></h2>
      
      <div class="card">
        <div class="card-body">
          <h5 class="mb-3 section-divider">All Staff</h5>
          <style>
            /* All Staff table row styling: white rows and navy horizontal separators */
            .all-staff-table tbody tr { background-color: #ffffff; color: #000; }
            /* Apply navy blue horizontal line between data rows (row 2 onward) */
            .all-staff-table tbody tr + tr td { border-top: 1px solid midnightblue !important; }
          </style>
          <div class="table-responsive">
            <table class="table table-bordered all-staff-table">
              <thead>
                <tr align="center" style="background-color: midnightblue; color: white;">
                  <th>Image</th>
                  <th>Name</th>
                  <th>Department</th>
                  <th>Designation</th>
                  <th>Organization</th>
                </tr>
              </thead>
              <tbody>
                @forelse($staff as $s)
                @php
                  $currentUser = auth()->user();
                  $isAdmin = $currentUser && (int)($currentUser->role ?? 0) === 4;
                  
                  // Match by email (case-insensitive, trim whitespace)
                  $currentUserEmail = trim(strtolower($currentUser->email ?? ''));
                  $staffEmail = trim(strtolower($s->email ?? ''));
                  $isCurrentUser = $currentUser && $currentUserEmail === $staffEmail && $currentUserEmail !== '';
                  
                  // Check if this is the current user's staff record (by ID if we have it)
                  $isCurrentUserStaffRecord = false;
                  if (isset($currentUserStaffRecord) && $currentUserStaffRecord) {
                    $isCurrentUserStaffRecord = $currentUserStaffRecord->id === $s->id;
                  }
                  
                  // Check if current user is a Student Org. Moderator
                  $isCurrentUserStudentOrgModerator = isset($currentUserDesignation) && $currentUserDesignation && strcasecmp($currentUserDesignation, 'Student Org. Moderator') === 0;
                  
                  // Check if this row is Student Org Moderator and matches current user's designation
                  $isStudentOrgModeratorRow = strcasecmp($s->designation, 'Student Org. Moderator') === 0;
                  
                  // Make clickable if:
                  // 1. It's the current user's row (email match OR staff record ID match) OR
                  // 2. Admin is viewing
                  $dashboardUrl = '#';
                  $isClickable = false;
                  
                  if ($s->designation && ($isCurrentUser || $isCurrentUserStaffRecord || $isAdmin)) {
                    $isClickable = true;
                    $normalizedDesignation = $s->designation;
                    $isStudentOrgModerator = strcasecmp($s->designation, 'Student Org. Moderator') === 0;
                    if ($isStudentOrgModerator) {
                      // If admin viewing another staff member, pass staff email to show their organizations
                      if ($isAdmin && !$isCurrentUser) {
                        $dashboardUrl = route('admin.staff.dashboard.StudentOrgModerator') . '?staff_email=' . urlencode($s->email);
                      } else {
                        $dashboardUrl = route('admin.staff.dashboard.StudentOrgModerator');
                      }
                    } else {
                      // Use array format for route parameters to ensure proper URL encoding
                      $dashboardUrl = route('admin.staff.dashboard.designation', ['designation' => $normalizedDesignation]);
                    }
                  }
                @endphp
                @php
                  $displayDesignation = $s->designation ?? '';
                @endphp
                <tr align="left" class="{{ $isClickable ? 'staff-row-clickable' : '' }}" data-href="{{ $dashboardUrl }}" data-designation="{{ $displayDesignation }}" data-current-user="{{ $isCurrentUser ? '1' : '0' }}" style="{{ $isClickable ? 'cursor: pointer;' : '' }}">
                  <td align="center">
                    @if($s->image)
                      <img src="{{ \Illuminate\Support\Facades\Storage::url($s->image) }}" alt="{{ $s->first_name }} {{ $s->last_name }}" class="img-thumbnail" width="60" height="60" style="object-fit: cover;">
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>{{ $s->first_name }} {{ $s->last_name }}</td>
                  <td>{{ $s->department->name ?? '-' }}</td>
                  <td>{{ $displayDesignation ?: '-' }}</td>
                  <td>
                    @php
                      $allOrgs = collect();
                      // Add single organization if exists
                      if ($s->organization) {
                        $allOrgs->push($s->organization->name);
                      }
                      // Add multiple organizations from pivot table
                      if ($s->organizations && $s->organizations->isNotEmpty()) {
                        foreach ($s->organizations as $org) {
                          if (!$allOrgs->contains($org->name)) {
                            $allOrgs->push($org->name);
                          }
                        }
                      }
                    @endphp
                    @if($allOrgs->isNotEmpty())
                      @foreach($allOrgs as $orgName)
                        <div>{{ $orgName }}</div>
                      @endforeach
                    @else
                      <span>-</span>
                    @endif
                  </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">No staff</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script>
  // Make staff rows clickable (only rows with the class staff-row-clickable)
  document.addEventListener('DOMContentLoaded', function() {
    // Refresh session periodically to prevent expiration (every 30 minutes)
    setInterval(function() {
      fetch(window.location.href, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin'
      }).catch(function(error) {
        console.warn('Session refresh check failed:', error);
      });
    }, 30 * 60 * 1000); // Every 30 minutes
    
    const staffRows = document.querySelectorAll('.staff-row-clickable');
    console.log('Found clickable rows:', staffRows.length);
    
    staffRows.forEach((row, index) => {
      const href = row.getAttribute('data-href');
      const designation = row.getAttribute('data-designation');
      const isCurrentUser = row.getAttribute('data-current-user') === '1';
      console.log(`Row ${index + 1}: designation="${designation}", href="${href}", isCurrentUser="${isCurrentUser}"`);
      
      if (href && href !== '#') {
        // Add click handler to the entire row
        row.addEventListener('click', function(e) {
          console.log('Row clicked:', designation, href);
          
          // Allow clicks on links and buttons to work normally
          if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') {
            console.log('Click on link/button, ignoring');
            return;
          }
          
          // Navigate when clicking anywhere else on the row (including images)
          console.log('Navigating to:', href);
          e.preventDefault();
          e.stopPropagation();
          window.location.href = href;
        });
        
        // Make sure the row shows it's clickable
        row.style.cursor = 'pointer';
        
        // Also make child elements non-selectable and show pointer cursor
        const cells = row.querySelectorAll('td');
        cells.forEach(cell => {
          cell.style.cursor = 'pointer';
          cell.style.userSelect = 'none';
        });
      } else {
        // Remove clickable class if href is invalid
        row.classList.remove('staff-row-clickable');
        row.style.cursor = 'default';
        console.log(`Row ${index + 1} is not clickable (invalid href: "${href}")`);
      }
    });
    
    // Debug: Log all rows in the table
    console.log('Total rows in table:', document.querySelectorAll('.all-staff-table tbody tr').length);
  });
</script>
@endsection
