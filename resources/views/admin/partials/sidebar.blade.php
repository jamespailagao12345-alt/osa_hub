<aside id="adminSidebar" class="col-md-2 sidebar admin-sidebar-custom" style="background-color:midnightblue; color:#fff; min-height: 100vh; padding-top:0;">
    <style>
        .admin-sidebar-custom { background-color: midnightblue; color: #fff; }
        .admin-sidebar-custom h4 { color: #fff; text-align: left; margin-top: .75rem; }
        .admin-sidebar-custom .nav { margin: 0; padding: 0; }
        .admin-sidebar-custom .nav-item { list-style: none; border-bottom: 1px solid rgba(255,255,255,0.25); }
        .admin-sidebar-custom .nav-item:last-child { border-bottom: none; }
        .admin-sidebar-custom .sidebar-link {
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
        .admin-sidebar-custom .sidebar-link:hover,
        .admin-sidebar-custom .sidebar-link:focus {
            background-color: rgba(255,255,255,0.12);
            color: #fff;
        }
        .admin-sidebar-custom .sidebar-link .icon { color: #FFD700; } /* yellow icons */

        /* Sidebar toggle placement and collapsed styling */
        .admin-sidebar-custom .sidebar-header { padding: .25rem .5rem; }
        /* Compact, borderless hamburger button */
        .admin-sidebar-custom .sidebar-toggle-btn {
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
        .admin-sidebar-custom .sidebar-toggle-btn i { color: #fff; font-size: 1.1rem; line-height: 1; }
        .admin-sidebar-custom .sidebar-toggle-btn:focus { outline: 2px solid rgba(255,255,255,0.35); outline-offset: 2px; }
        .admin-sidebar-custom.collapsed {
            width: 2.5rem !important;
            max-width: 2.5rem !important;
            flex: 0 0 2.5rem !important;
            padding-left: 0;
            padding-right: 0;
        }
        .admin-sidebar-custom.collapsed .sidebar-body { display: none; }
        .admin-sidebar-custom.collapsed .sidebar-header h4 { display: none; }

        /* Smoothen layout adjustments */
        #adminSidebar { transition: max-width .2s ease, flex-basis .2s ease; }
        #adminMain { transition: max-width .2s ease, flex-basis .2s ease; }

        /* Center content background (admin pages only) */
        .admin-sidebar-custom ~ main,
        .admin-sidebar-custom ~ #adminMain {
            background-color: #eceff4; /* light blue */
        }
        /* Remove top padding gap only on admin pages with sidebar */
        .admin-with-sidebar #main-content { padding-top: 0 !important; }

        /* Slight upward nudge for header and admin content to tighten spacing
           Only applied on admin pages (scoped by body.admin-with-sidebar) */
        .admin-with-sidebar header,
        .admin-with-sidebar #adminSidebar,
        .admin-with-sidebar #adminMain,
        .admin-with-sidebar .container-fluid > .row {
            transform: translateY(-6px);
            transition: transform .22s ease;
        }

        /* Ensure we don't accidentally overlap content: keep a small min-top offset */
        @media (min-width: 768px) {
            .admin-with-sidebar header { z-index: 1020; }
        }

        /* Consistent spacing: back buttons + dashboard header */
        .admin-back-btn-wrap { 
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
        .admin-with-sidebar .dashboard-header { margin-top: .5rem; margin-bottom: 1.25rem; }
        /* Ensure tables appear white inside center content */
        .admin-sidebar-custom ~ main .table,
        .admin-sidebar-custom ~ #adminMain .table,
        .admin-sidebar-custom ~ main table,
        .admin-sidebar-custom ~ #adminMain table {
            background-color: #ffffff;
        }
        /* Profile section styling */
        .admin-sidebar-custom .profile-section {
            padding: 1.5rem 0.75rem 1rem;
            margin-top: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.25);
            text-align: center;
        }
        .admin-sidebar-custom .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.3);
            margin-bottom: 0.75rem;
        }
        .admin-sidebar-custom .profile-initials {
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
        .admin-sidebar-custom .profile-name {
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            line-height: 1.3;
            margin-bottom: 0.25rem;
        }
        .admin-sidebar-custom .profile-designation {
            color: rgba(255,255,255,0.85);
            font-size: 0.75rem;
            line-height: 1.2;
        }
        .admin-sidebar-custom.collapsed .profile-section {
            display: none;
        }
    </style>
    @php
        $currentUser = auth()->user();
        $currentUserImage = $currentUser->image ?? null;
        $currentUserName = trim(($currentUser->first_name ?? '') . ' ' . ($currentUser->middle_name ?? '') . ' ' . ($currentUser->last_name ?? ''));
        $currentUserDesignation = $currentUser->designation ?? 'ADMIN';
        
        // Get initials for avatar
        $initials = '';
        if ($currentUser) {
            $firstInitial = strtoupper(substr($currentUser->first_name ?? '', 0, 1));
            $lastInitial = strtoupper(substr($currentUser->last_name ?? '', 0, 1));
            $initials = $firstInitial . $lastInitial;
        }
    @endphp
    
    <!-- Profile Section -->
    <div class="profile-section">
        <div class="mb-2">
            @if($currentUserImage)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($currentUserImage) }}" 
                     alt="{{ $currentUserName }}" 
                     class="profile-avatar">
            @else
                <div class="profile-initials">
                    {{ $initials ?: 'A' }}
                </div>
            @endif
        </div>
        <div class="profile-name">
            {{ $currentUserName ?: 'Admin' }}
        </div>
        <div class="profile-designation">
            {{ strtoupper($currentUserDesignation) }}
        </div>
    </div>
    
    <div class="sidebar-header d-flex align-items-center justify-content-between mb-2">
        <h4 class="mb-0">Quick Actions</h4>
        <button id="toggleSidebarBtn" class="sidebar-toggle-btn" type="button" aria-expanded="true" aria-controls="adminSidebar" aria-label="Hide sidebar" title="Hide sidebar">
            <i class="mai-menu" aria-hidden="true"></i>
        </button>
    </div>
    <div class="sidebar-body">
    <ul class="nav flex-column">
        @php 
            $isAdmin = auth()->user()?->role === 4;
            $currentUser = auth()->user();
            $isStudentOrgModerator = false;
            if ($currentUser) {
                $designation = $currentUser->designation ?? optional($currentUser->staffProfile)->designation ?? '';
                $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($currentUser->email))])->first();
                $staffDesignation = $staffRecord ? $staffRecord->designation : '';
                $isStudentOrgModerator = strcasecmp($designation, 'Student Org. Moderator') === 0 || 
                                         strcasecmp($staffDesignation, 'Student Org. Moderator') === 0;
            }
        @endphp
        @if($isAdmin)
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}" href="{{ route('admin.appointments.index') }}">
                    <i class="mai-calendar icon"></i>
                    <span>Appointments</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}" href="{{ route('admin.events.index') }}">
                    <i class="mai-calendar icon"></i>
                    <span>Events</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.calendar') ? 'active' : '' }}" href="{{ route('admin.calendar') }}">
                    <i class="mai-calendar icon"></i>
                    <span>Calendar</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.events.create') ? 'active' : '' }}" href="{{ route('admin.events.create') }}">
                    <i class="mai-add icon"></i>
                    <span>Create Event</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.show-staff') ? 'active' : '' }}" href="{{ route('admin.show-staff') }}">
                    <i class="mai-people icon"></i>
                    <span>Staff List</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.student-leaders.*') ? 'active' : '' }}" href="{{ route('admin.student-leaders.index') }}">
                    <i class="mai-people icon"></i>
                    <span>Show Student Leaders</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.nationalities.*') ? 'active' : '' }}" href="{{ route('admin.nationalities.index') }}">
                    <i class="mai-globe icon"></i>
                    <span>Manage Nationalities</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.addresses.*') ? 'active' : '' }}" href="{{ route('admin.addresses.index') }}">
                    <i class="mai-map icon"></i>
                    <span>Manage Addresses</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admins.*') ? 'active' : '' }}" href="{{ route('admins.index') }}">
                    <i class="mai-person icon"></i>
                    <span>Manage Admins</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.designations.*') ? 'active' : '' }}" href="{{ route('admin.designations.index') }}">
                    <i class="mai-briefcase icon"></i>
                    <span>Manage Designations</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}">
                    <i class="mai-business icon"></i>
                    <span>Manage Departments</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.organizations.*') ? 'active' : '' }}" href="{{ route('admin.organizations.index') }}">
                    <i class="mai-people icon"></i>
                    <span>Manage Organizations</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.show-students-list') ? 'active' : '' }}" href="{{ route('admin.show-students-list') }}">
                    <i class="mai-book icon"></i>
                    <span>Show Students</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="mai-analytics icon"></i>
                    <span>Reports & Analytics</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.qrscan') ? 'active' : '' }}" href="{{ route('admin.qrscan') }}">
                    <i class="mai-camera icon"></i>
                    <span>Scan QR Code</span>
                </a>
            </li>
        @endif
        @if($isStudentOrgModerator)
            <li class="nav-item">
                <a class="sidebar-link {{ request()->routeIs('admin.qrscan') ? 'active' : '' }}" href="{{ route('admin.qrscan') }}">
                    <i class="mai-camera icon"></i>
                    <span>Scan QR Code</span>
                </a>
            </li>
        @endif
        @if(!$isAdmin)
        <li class="nav-item">
            <a class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="mai-analytics icon"></i>
                <span>My Reports</span>
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="sidebar-link {{ request()->routeIs('admin.staff.dashboard') ? 'active' : '' }}" href="{{ route('admin.staff.dashboard') }}">
                <i class="mai-speedometer icon"></i>
                <span>Staff Dashboards</span>
            </a>
        </li>
    </ul>
    </div>
</aside>
@push('scripts')
<script>
    (function(){
        // Mark body to allow admin-specific layout tweaks (e.g., remove main top padding)
        document.body.classList.add('admin-with-sidebar');
        const sidebar = document.getElementById('adminSidebar');
        const main = document.getElementById('adminMain');
        const btn = document.getElementById('toggleSidebarBtn');
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

        const saved = localStorage.getItem('adminSidebarCollapsed') === '1';
        applyCollapsedUI(saved);

        btn.addEventListener('click', function(){
            const collapsed = btn.getAttribute('aria-expanded') === 'true';
            applyCollapsedUI(collapsed);
            localStorage.setItem('adminSidebarCollapsed', collapsed ? '1' : '0');
        });
    })();
</script>
@endpush
