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
        $user = \Illuminate\Support\Facades\Auth::user();
        $role = $user->role ?? null;
        
        // Get profile image for student leader
        $profileImage = null;
        $profileName = '';
        $profileDesignation = 'STUDENT-LEADER';
        
        if ($user) {
            $student = \App\Models\Student::where('user_id', $user->id)->first();
            $profileImage = $user->image ?? ($student ? $student->personal_data_sheet_image : null);
            $profileName = trim(($user->first_name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? ''));
            
            // Get position for student leader, designation for staff
            if ($user->role == 3) {
                // Student leader: use position
                $position = $user->position ?? null;
                if ($position) {
                    $profileDesignation = strtoupper($position);
                }
            } else {
                // Staff: use designation
                $designation = $user->designation ?? optional($user->staffProfile)->designation ?? null;
                if ($designation) {
                    $profileDesignation = strtoupper($designation);
                }
            }
        }
        
        // Get initials for avatar
        $initials = '';
        if ($user) {
            $firstInitial = strtoupper(substr($user->first_name ?? '', 0, 1));
            $lastInitial = strtoupper(substr($user->last_name ?? '', 0, 1));
            $initials = $firstInitial . $lastInitial;
        }
    @endphp
    
    <!-- Profile Section -->
    <div class="profile-section">
        <div class="mb-2">
            @if($profileImage)
                @php
                    // Normalize image path
                    $imagePath = $profileImage;
                    $imagePath = ltrim($imagePath, '/');
                    $profileImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
                @endphp
                <img src="{{ $profileImageUrl }}" 
                     alt="{{ $profileName }}" 
                     class="profile-avatar">
            @else
                <div class="profile-initials">
                    {{ $initials ?: 'A' }}
                </div>
            @endif
        </div>
        <div class="profile-name">
            {{ $profileName ?: 'Assistant' }}
        </div>
        <div class="profile-designation">
            {{ $profileDesignation }}
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
            <a class="sidebar-link {{ request()->routeIs('student-leader.dashboard') ? 'active' : '' }}" href="{{ route('student-leader.dashboard') }}">
                <i class="mai-speedometer icon"></i>
                <span>Assistant Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="sidebar-link {{ request()->routeIs('student-leader.events.*') ? 'active' : '' }}" href="{{ route('student-leader.events.index') }}">
                <i class="mai-calendar icon"></i>
                <span>My Events</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="sidebar-link {{ request()->routeIs('student-leader.profile') ? 'active' : '' }}" href="{{ route('student-leader.profile') }}">
                <i class="mai-person icon"></i>
                <span>My Profile</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="sidebar-link {{ request()->routeIs('student-leader.qrscan') ? 'active' : '' }}" href="{{ route('student-leader.qrscan') }}">
                <i class="mai-camera icon"></i>
                <span>Scan QR Code</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="sidebar-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                <i class="mai-home icon"></i>
                <span>Student Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="mai-analytics icon"></i>
                <span>My Reports</span>
            </a>
        </li>
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

