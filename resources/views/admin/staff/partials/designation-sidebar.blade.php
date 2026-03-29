<aside id="staffSidebar" class="col-md-2 sidebar staff-sidebar-custom" style="background-color:midnightblue; color:#fff; min-height: 100vh; padding-top:0;">
    <style>
        .staff-sidebar-custom { background-color: midnightblue; color: #fff; }
        .staff-sidebar-custom h4 { color: #fff; text-align: left; margin-top: .75rem; }
        .staff-sidebar-custom .nav { margin: 0; padding: 0; }
        .staff-sidebar-custom .nav-item { list-style: none; border-bottom: 1px solid rgba(255,255,255,0.25); }
        .staff-sidebar-custom .nav-item:last-child { border-bottom: none; }
        .staff-sidebar-custom .sidebar-link {
            display: flex;
            align-items: center;
            gap: .5rem;
            width: 100%;
            color: #fff; /* ensure white text */
            text-decoration: none;
            padding: .6rem .75rem;
            background: transparent;
            border: 0;
            text-align: left;
        }
        .staff-sidebar-custom .sidebar-link:hover,
        .staff-sidebar-custom .sidebar-link:focus {
            background-color: rgba(255,255,255,0.12);
            color: #fff;
        }
        .staff-sidebar-custom .sidebar-link .icon { color: #FFD700; } /* yellow icons */

        /* Sidebar toggle placement and collapsed styling */
        .staff-sidebar-custom .sidebar-header { padding: .25rem .5rem; }
        /* Compact, borderless hamburger button */
        .staff-sidebar-custom .sidebar-toggle-btn {
            background: transparent;
            border: 0;
            padding: .25rem;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .staff-sidebar-custom .sidebar-toggle-btn i { color: #fff; font-size: 1.1rem; line-height: 1; }
        .staff-sidebar-custom .sidebar-toggle-btn:focus { outline: 2px solid rgba(255,255,255,0.35); outline-offset: 2px; }
        .staff-sidebar-custom.collapsed {
            width: 2.5rem !important;
            max-width: 2.5rem !important;
            flex: 0 0 2.5rem !important;
            padding-left: 0;
            padding-right: 0;
        }
        .staff-sidebar-custom.collapsed .sidebar-body { display: none; }
        .staff-sidebar-custom.collapsed .sidebar-header h4 { display: none; }

        /* Smoothen layout adjustments */
        #staffSidebar { transition: max-width .2s ease, flex-basis .2s ease; }
        #staffMain { transition: max-width .2s ease, flex-basis .2s ease; }

        /* Center content background (staff pages only) */
        .staff-sidebar-custom ~ main,
        .staff-sidebar-custom ~ #staffMain {
            background-color: #eceff4; /* light blue */
        }
        /* Remove top padding gap only on staff pages with sidebar */
        .staff-with-sidebar #main-content { padding-top: 0 !important; }

        /* Slight upward nudge for header and staff content to tighten spacing
           Only applied on staff pages (scoped by body.staff-with-sidebar) */
        .staff-with-sidebar header,
        .staff-with-sidebar #staffSidebar,
        .staff-with-sidebar #staffMain,
        .staff-with-sidebar .container-fluid > .row {
            transform: translateY(-6px);
            transition: transform .22s ease;
        }

        /* Ensure we don't accidentally overlap content: keep a small min-top offset */
        @media (min-width: 768px) {
            .staff-with-sidebar header { z-index: 1020; }
        }

        /* Consistent spacing: back buttons + dashboard header */
        .staff-back-btn-wrap { 
            margin: .5rem 0 1rem; 
            display: flex;
            justify-content: flex-end;
        }
        /* Align standalone Back buttons to the right */
        .mb-3 > .btn-secondary:first-child,
        .container-fluid > .mb-3:first-child > .btn-secondary,
        main > .mb-3:first-child > .btn-secondary {
            margin-left: auto;
            display: block;
            width: fit-content;
        }
        .staff-with-sidebar .dashboard-header { margin-top: .5rem; margin-bottom: 1.25rem; }
        /* Ensure tables appear white inside center content */
        .staff-sidebar-custom ~ main .table,
        .staff-sidebar-custom ~ #staffMain .table,
        .staff-sidebar-custom ~ main table,
        .staff-sidebar-custom ~ #staffMain table {
            background-color: #ffffff;
        }
        /* Profile section styling */
        .staff-sidebar-custom .profile-section {
            padding: 1.5rem 0.75rem 1rem;
            margin-top: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.25);
            text-align: center;
        }
        .staff-sidebar-custom .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.3);
            margin-bottom: 0.75rem;
        }
        .staff-sidebar-custom .profile-initials {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.2);
            border: 3px solid rgba(255,255,255,0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            color: #fff;
            font-size: 2rem;
            font-weight: bold;
        }
        .staff-sidebar-custom .profile-name {
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            line-height: 1.3;
            margin-bottom: 0.25rem;
        }
        .staff-sidebar-custom .profile-designation {
            color: rgba(255,255,255,0.85);
            font-size: 0.75rem;
            line-height: 1.2;
        }
        .staff-sidebar-custom.collapsed .profile-section {
            display: none;
        }
    </style>
    @php
        $currentUser = auth()->user();
        $currentUserStaff = null;
        $currentUserImage = null;
        $currentUserName = '';
        $currentUserDesignation = $designation->name ?? '';
        
        // Get staff record for current user
        if ($currentUser) {
          $currentUserStaff = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($currentUser->email))])->first();
          $currentUserImage = $currentUserStaff->image ?? $currentUser->image ?? null;
          $currentUserName = trim(($currentUser->first_name ?? '') . ' ' . ($currentUser->middle_name ?? '') . ' ' . ($currentUser->last_name ?? ''));
          if (!$currentUserDesignation) {
            $currentUserDesignation = $currentUser->designation ?? optional($currentUser->staffProfile)->designation ?? $currentUserStaff->designation ?? '';
          }
        }
        
        // Get initials for avatar
        $initials = '';
        if ($currentUser) {
          $firstInitial = strtoupper(substr($currentUser->first_name ?? '', 0, 1));
          $lastInitial = strtoupper(substr($currentUser->last_name ?? '', 0, 1));
          $initials = $firstInitial . $lastInitial;
        }
        
        // Use relative path instead of full URL to avoid double-encoding issues
        // Build path with raw designation name - Laravel will encode the query parameter
        $returnPath = '/admin/staff/dashboard/' . $designation->name;
        // Normalize designation name to handle both spellings (standardize on American spelling)
        // This handles backward compatibility with existing data that may use British spelling
        $normalizedDesignationName = trim($designation->name);
        if (strcasecmp($normalizedDesignationName, 'Guidance Counsellor') === 0) {
          $normalizedDesignationName = 'Guidance Counselor';
        }
        $isGuidanceCounselor = strcasecmp($normalizedDesignationName, 'Guidance Counselor') === 0;
        $isPrefectOfDiscipline = strcasecmp($normalizedDesignationName, 'Prefect of Discipline') === 0;
        $isNurse = strcasecmp($normalizedDesignationName, 'Nurse') === 0;
        // Hide "All Events", "Participants History", and "Reports" for all staff
        $hideEventsAndReports = true;
    @endphp
    
    <!-- Profile Section -->
    <div class="profile-section">
        <div class="mb-2">
            @if($currentUserImage)
                @php
                    // Normalize image path and generate URL (similar to Admission Services Officer fix)
                    $imagePath = $currentUserImage;
                    $imagePath = ltrim($imagePath, '/');
                    // Normalize image path - try multiple possible locations
                    $possiblePaths = [
                      $imagePath, // Original path
                      'staff-image/' . basename($imagePath),
                      'profile_images/' . basename($imagePath),
                    ];
                    
                    $foundPath = null;
                    foreach ($possiblePaths as $path) {
                      if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                        $foundPath = $path;
                        break;
                      }
                    }
                    
                    // Generate URL - use found path or original as fallback
                    if ($foundPath) {
                      $profileImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($foundPath);
                    } else {
                      // Fallback: generate URL from original path (might work if symlinked)
                      $profileImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
                    }
                @endphp
                <img src="{{ $profileImageUrl }}" 
                     alt="{{ $currentUserName }}" 
                     class="profile-avatar"
                     onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='inline-flex');">
                <div class="profile-initials" style="display: none;">
                    {{ $initials ?: 'S' }}
                </div>
            @else
                <div class="profile-initials">
                    {{ $initials ?: 'S' }}
                </div>
            @endif
        </div>
        <div class="profile-name">
            {{ $currentUserName ?: 'Staff' }}
        </div>
        <div class="profile-designation">
            {{ strtoupper($currentUserDesignation) ?: 'STAFF' }}
        </div>
    </div>
    
    <div class="sidebar-header d-flex align-items-center justify-content-between mb-2">
        <h4 class="mb-0">Quick Actions</h4>
        <button id="toggleStaffSidebarBtn" class="sidebar-toggle-btn" type="button" aria-expanded="true" aria-controls="staffSidebar" aria-label="Hide sidebar" title="Hide sidebar">
            <i class="mai-menu" aria-hidden="true"></i>
        </button>
    </div>
    <div class="sidebar-body">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="sidebar-link {{ request()->routeIs('admin.staff.dashboard.designation') ? 'active' : '' }}" href="{{ route('admin.staff.dashboard.designation', ['designation' => $designation->name]) }}">
                <i class="mai-speedometer icon"></i>
                <span>{{ $designation->name }} Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="sidebar-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['return_to' => $returnPath]) }}">
                <i class="mai-calendar icon"></i>
                <span>Assigned Appointments</span>
            </a>
        </li>
        @if($isGuidanceCounselor)
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.staff.dashboard.guidance-counselor.clients') ? 'active' : '' }}" href="{{ route('admin.staff.dashboard.guidance-counselor.clients', ['designation' => $designation->name]) }}">
                    <i class="mai-people icon"></i>
                    <span>Clients List</span>
                </a>
            </li>
        @endif
        @if(isset($isStaff) && $isStaff)
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('staff.organizations.*') ? 'active' : '' }}" href="{{ route('staff.organizations.index') }}">
                    <i class="mai-building icon"></i>
                    <span>My Organization</span>
                </a>
            </li>
        @endif
        @php
          $canCreateStaffEvent = (isset($isStaff) && $isStaff) || (isset($isAdmin) && $isAdmin);
        @endphp
        @if($canCreateStaffEvent && !$isGuidanceCounselor && !$isNurse)
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.staff.dashboard.StudentOrgModerator.create-event') ? 'active' : '' }}" href="{{ route('admin.staff.dashboard.StudentOrgModerator.create-event') }}">
                    <i class="mai-calendar-plus icon"></i>
                    <span>Create Event</span>
                </a>
            </li>
        @endif
        @unless($hideEventsAndReports)
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}" href="{{ route('admin.events.index', ['return_to' => urlencode(route('admin.staff.dashboard.designation', ['designation' => $designation->name]))]) }}">
                    <i class="mai-calendar icon"></i>
                    <span>All Events</span>
                </a>
            </li>
            @if(isset($isAdmin) && $isAdmin)
                <li class="nav-item">
                    <a class="sidebar-link {{ request()->routeIs('admin.events.create') ? 'active' : '' }}" href="{{ route('admin.events.index', ['return_to' => urlencode(route('admin.staff.dashboard.designation', ['designation' => $designation->name]))]) }}#create">
                        <i class="mai-add icon"></i>
                        <span>Create Event</span>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.participants.export') ? 'active' : '' }}" href="{{ route('admin.participants.export', ['return_to' => urlencode(route('admin.staff.dashboard.designation', ['designation' => $designation->name]))]) }}">
                    <i class="mai-download icon"></i>
                    <span>Participants History</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.staff.dashboard.report') ? 'active' : '' }}" href="{{ route('admin.staff.dashboard.report', ['designation' => $designation->name]) }}">
                    <i class="mai-document icon"></i>
                    <span>Reports</span>
                </a>
            </li>
        @endunless
        @if(!$isGuidanceCounselor && !$isNurse)
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.organizational-structure') ? 'active' : '' }}" href="{{ route('admin.organizational-structure') }}">
                    <i class="mai-diagram icon"></i>
                    <span>Organizational Structure</span>
                </a>
            </li>
        @endif
        @if (strcasecmp($designation->name, 'Admission Services Officer') === 0)
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.staff.dashboard.AdmissionServicesOfficer.*') ? 'active' : '' }}" href="{{ route('admin.staff.dashboard.AdmissionServicesOfficer.student-management') }}">
                    <i class="mai-book icon"></i>
                    <span>Student Management</span>
                </a>
            </li>
        @endif
    </ul>
    </div>
</aside>
@push('scripts')
<script>
    (function(){
        // Mark body to allow staff-specific layout tweaks (e.g., remove main top padding)
        document.body.classList.add('staff-with-sidebar');
        const sidebar = document.getElementById('staffSidebar');
        const main = document.getElementById('staffMain');
        const btn = document.getElementById('toggleStaffSidebarBtn');
        if (!sidebar || !btn) return;

        function applyCollapsedUI(collapsed){
            if (collapsed) {
                sidebar.classList.add('collapsed');
                if (main) { main.classList.remove('col-md-10'); main.classList.add('flex-grow-1'); }
                btn.setAttribute('aria-expanded','false');
                btn.setAttribute('aria-label','Show sidebar');
                btn.title = 'Show sidebar';
            } else {
                sidebar.classList.remove('collapsed');
                if (main) { main.classList.remove('flex-grow-1'); main.classList.add('col-md-10'); }
                btn.setAttribute('aria-expanded','true');
                btn.setAttribute('aria-label','Hide sidebar');
                btn.title = 'Hide sidebar';
            }
        }

        const saved = localStorage.getItem('staffSidebarCollapsed') === '1';
        applyCollapsedUI(saved);

        btn.addEventListener('click', function(){
            const collapsed = btn.getAttribute('aria-expanded') === 'true';
            applyCollapsedUI(collapsed);
            localStorage.setItem('staffSidebarCollapsed', collapsed ? '1' : '0');
        });
    })();
</script>
@endpush

