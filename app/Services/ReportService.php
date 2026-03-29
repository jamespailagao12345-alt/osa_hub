<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Event;
use App\Models\User;
use App\Models\Student;
use App\Models\EventParticipant;
use App\Models\Attendance;
use App\Models\StudentPoint;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    protected $currentUser;
    protected $userRole;

    public function __construct()
    {
        $this->currentUser = auth()->user();
        $this->userRole = $this->currentUser ? (int)$this->currentUser->role : null;
    }

    /**
     * Get date range based on period
     */
    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();
        
        if ($startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay()
            ];
        }

        switch ($period) {
            case 'daily':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case 'weekly':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek()
                ];
            case 'monthly':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'yearly':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear()
                ];
            default:
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
        }
    }

    /**
     * Check if user is admin
     */
    private function isAdmin()
    {
        return $this->userRole === 4;
    }

    /**
     * Get appointment statistics (role-based)
     */
    public function getAppointmentStats($period = 'monthly', $startDate = null, $endDate = null)
    {
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        $query = Appointment::whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        
        // Role-based filtering
        if (!$this->isAdmin()) {
            if ($this->userRole === 1) {
                // Students see only their own appointments
                $query->where('user_id', $this->currentUser->id);
            } elseif ($this->userRole === 2) {
                // Staff see appointments assigned to them
                $query->where('assigned_staff_id', $this->currentUser->id);
            } elseif ($this->userRole === 3) {
                // Student leaders see appointments for their organization members
                $organizationIds = collect();
                if ($this->currentUser->organization_id) {
                    $organizationIds->push($this->currentUser->organization_id);
                }
                if ($this->currentUser->otherOrganizations) {
                    $organizationIds = $organizationIds->merge($this->currentUser->otherOrganizations->pluck('id'));
                }
                $organizationIds = $organizationIds->filter()->unique();
                
                if ($organizationIds->isNotEmpty()) {
                    $userIds = User::whereIn('organization_id', $organizationIds)
                        ->orWhereHas('otherOrganizations', function($q) use ($organizationIds) {
                            $q->whereIn('organizations.id', $organizationIds->toArray());
                        })
                        ->pluck('id');
                    
                    $query->whereIn('user_id', $userIds);
                } else {
                    $query->where('id', 0); // No access
                }
            }
        }
        
        $total = $query->count();
        
        $byStatus = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $byDate = $query->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        $byStaff = null;
        if ($this->isAdmin()) {
            $byStaff = $query->whereNotNull('assigned_staff_id')
                ->with('assignedStaff')
                ->select('assigned_staff_id', DB::raw('count(*) as count'))
                ->groupBy('assigned_staff_id')
                ->get()
                ->map(function($item) {
                    return [
                        'staff_name' => $item->assignedStaff ? ($item->assignedStaff->first_name . ' ' . $item->assignedStaff->last_name) : 'N/A',
                        'count' => $item->count
                    ];
                });
        }
        
        return [
            'total' => $total,
            'by_status' => $byStatus,
            'by_date' => $byDate,
            'by_staff' => $byStaff,
            'period' => $period,
            'date_range' => $dateRange,
            'user_role' => $this->userRole,
            'is_admin' => $this->isAdmin()
        ];
    }

    /**
     * Get event statistics (role-based)
     */
    public function getEventStats($period = 'monthly', $startDate = null, $endDate = null, $filterBy = 'all')
    {
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        $query = Event::whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        
        // Role-based filtering
        if (!$this->isAdmin()) {
            if ($this->userRole === 1) {
                // Students see events they participated in
                $eventIds = EventParticipant::where('user_id', $this->currentUser->id)
                    ->pluck('event_id');
                $query->whereIn('id', $eventIds);
            } elseif ($this->userRole === 2) {
                // Staff see events they created
                $query->where('created_by', $this->currentUser->id);
            } elseif ($this->userRole === 3) {
                // Student leaders see events for their organizations
                $organizationIds = collect();
                if ($this->currentUser->organization_id) {
                    $organizationIds->push($this->currentUser->organization_id);
                }
                if ($this->currentUser->otherOrganizations) {
                    $organizationIds = $organizationIds->merge($this->currentUser->otherOrganizations->pluck('id'));
                }
                $organizationIds = $organizationIds->filter()->unique();
                
                if ($organizationIds->isNotEmpty()) {
                    $query->whereIn('organization_id', $organizationIds->toArray());
                } else {
                    $query->where('id', 0); // No access
                }
            }
        } else {
            // Admin filtering
            if ($filterBy === 'org') {
                $query->whereNotNull('organization_id');
            } elseif ($filterBy === 'staff') {
                $query->whereNotNull('created_by');
            }
        }
        
        $total = $query->count();
        
        $byStatus = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $byOrganization = null;
        $byCreator = null;
        
        if ($this->isAdmin()) {
            $byOrganization = $query->whereNotNull('organization_id')
                ->with('organization')
                ->select('organization_id', DB::raw('count(*) as count'))
                ->groupBy('organization_id')
                ->get()
                ->map(function($item) {
                    return [
                        'org_name' => $item->organization ? $item->organization->name : 'N/A',
                        'count' => $item->count
                    ];
                });
            
            $byCreator = $query->whereNotNull('created_by')
                ->with('creator')
                ->select('created_by', DB::raw('count(*) as count'))
                ->groupBy('created_by')
                ->get()
                ->map(function($item) {
                    return [
                        'creator_name' => $item->creator ? ($item->creator->first_name . ' ' . $item->creator->last_name) : 'N/A',
                        'count' => $item->count
                    ];
                });
        }
        
        $statusBreakdown = [
            'created' => $query->whereIn('status', ['pending', 'approved', 'declined'])->count(),
            'approved' => Event::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'approved')
                ->when(!$this->isAdmin(), function($q) {
                    if ($this->userRole === 1) {
                        $eventIds = EventParticipant::where('user_id', $this->currentUser->id)->pluck('event_id');
                        $q->whereIn('id', $eventIds);
                    } elseif ($this->userRole === 2) {
                        $q->where('created_by', $this->currentUser->id);
                    } elseif ($this->userRole === 3) {
                        $orgIds = collect();
                        if ($this->currentUser->organization_id) {
                            $orgIds->push($this->currentUser->organization_id);
                        }
                        if ($this->currentUser->otherOrganizations) {
                            $orgIds = $orgIds->merge($this->currentUser->otherOrganizations->pluck('id'));
                        }
                        $q->whereIn('organization_id', $orgIds->filter()->unique()->toArray());
                    }
                })
                ->count(),
            'declined' => Event::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'declined')
                ->when(!$this->isAdmin(), function($q) {
                    if ($this->userRole === 1) {
                        $eventIds = EventParticipant::where('user_id', $this->currentUser->id)->pluck('event_id');
                        $q->whereIn('id', $eventIds);
                    } elseif ($this->userRole === 2) {
                        $q->where('created_by', $this->currentUser->id);
                    } elseif ($this->userRole === 3) {
                        $orgIds = collect();
                        if ($this->currentUser->organization_id) {
                            $orgIds->push($this->currentUser->organization_id);
                        }
                        if ($this->currentUser->otherOrganizations) {
                            $orgIds = $orgIds->merge($this->currentUser->otherOrganizations->pluck('id'));
                        }
                        $q->whereIn('organization_id', $orgIds->filter()->unique()->toArray());
                    }
                })
                ->count(),
            'cancelled' => Event::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'cancelled')
                ->when(!$this->isAdmin(), function($q) {
                    if ($this->userRole === 1) {
                        $eventIds = EventParticipant::where('user_id', $this->currentUser->id)->pluck('event_id');
                        $q->whereIn('id', $eventIds);
                    } elseif ($this->userRole === 2) {
                        $q->where('created_by', $this->currentUser->id);
                    } elseif ($this->userRole === 3) {
                        $orgIds = collect();
                        if ($this->currentUser->organization_id) {
                            $orgIds->push($this->currentUser->organization_id);
                        }
                        if ($this->currentUser->otherOrganizations) {
                            $orgIds = $orgIds->merge($this->currentUser->otherOrganizations->pluck('id'));
                        }
                        $q->whereIn('organization_id', $orgIds->filter()->unique()->toArray());
                    }
                })
                ->count(),
            'postponed' => Event::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'postponed')
                ->when(!$this->isAdmin(), function($q) {
                    if ($this->userRole === 1) {
                        $eventIds = EventParticipant::where('user_id', $this->currentUser->id)->pluck('event_id');
                        $q->whereIn('id', $eventIds);
                    } elseif ($this->userRole === 2) {
                        $q->where('created_by', $this->currentUser->id);
                    } elseif ($this->userRole === 3) {
                        $orgIds = collect();
                        if ($this->currentUser->organization_id) {
                            $orgIds->push($this->currentUser->organization_id);
                        }
                        if ($this->currentUser->otherOrganizations) {
                            $orgIds = $orgIds->merge($this->currentUser->otherOrganizations->pluck('id'));
                        }
                        $q->whereIn('organization_id', $orgIds->filter()->unique()->toArray());
                    }
                })
                ->count() ?? 0,
        ];
        
        return [
            'total' => $total,
            'by_status' => $byStatus,
            'status_breakdown' => $statusBreakdown,
            'by_organization' => $byOrganization,
            'by_creator' => $byCreator,
            'period' => $period,
            'filter_by' => $filterBy,
            'date_range' => $dateRange,
            'user_role' => $this->userRole,
            'is_admin' => $this->isAdmin()
        ];
    }

    /**
     * Get student statistics (admin only or own stats for students)
     */
    public function getStudentStats($period = 'monthly', $startDate = null, $endDate = null)
    {
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        if (!$this->isAdmin() && $this->userRole !== 1) {
            return [
                'error' => 'Access denied. This report is only available to admins and students.',
                'user_role' => $this->userRole
            ];
        }
        
        $query = User::where('role', 1)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        
        // Students see only their own stats
        if (!$this->isAdmin() && $this->userRole === 1) {
            $query->where('id', $this->currentUser->id);
        }
        
        $activeStudents = (clone $query)->where('suspended', false)->count();
        $totalStudents = $query->count();
        
        $byDepartment = null;
        $byYearLevel = null;
        
        if ($this->isAdmin()) {
            $byDepartment = (clone $query)->with('department')
                ->select('department_id', DB::raw('count(*) as count'))
                ->groupBy('department_id')
                ->get()
                ->map(function($item) {
                    return [
                        'department' => $item->department ? $item->department->name : 'N/A',
                        'count' => $item->count
                    ];
                });
            
            $byYearLevel = (clone $query)->select('year_level', DB::raw('count(*) as count'))
                ->groupBy('year_level')
                ->orderBy('year_level')
                ->pluck('count', 'year_level')
                ->toArray();
        }
        
        $newRegistrations = (clone $query)->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        return [
            'active_students' => $activeStudents,
            'total_students' => $totalStudents,
            'by_department' => $byDepartment,
            'by_year_level' => $byYearLevel,
            'new_registrations' => $newRegistrations,
            'period' => $period,
            'date_range' => $dateRange,
            'user_role' => $this->userRole,
            'is_admin' => $this->isAdmin()
        ];
    }

    /**
     * Get scholar statistics (role-based)
     */
    public function getScholarStats($period = 'monthly', $startDate = null, $endDate = null)
    {
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        $query = User::where('role', 1)
            ->whereNotNull('scholarship_id')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        
        // Students see only their own scholarship info
        if (!$this->isAdmin() && $this->userRole === 1) {
            $query->where('id', $this->currentUser->id);
        }
        
        $activeScholars = (clone $query)->where('is_active_scholar', true)->count();
        
        $byScholarship = null;
        if ($this->isAdmin()) {
            $byScholarship = (clone $query)->with('scholarship')
                ->select('scholarship_id', DB::raw('count(*) as count'))
                ->groupBy('scholarship_id')
                ->get()
                ->map(function($item) {
                    return [
                        'scholarship' => $item->scholarship ? $item->scholarship->name : 'N/A',
                        'count' => $item->count
                    ];
                });
        }
        
        return [
            'active_scholars' => $activeScholars,
            'by_scholarship' => $byScholarship,
            'period' => $period,
            'date_range' => $dateRange,
            'user_role' => $this->userRole,
            'is_admin' => $this->isAdmin()
        ];
    }

    /**
     * Get suspension statistics (admin only)
     */
    public function getSuspensionStats($period = 'monthly', $startDate = null, $endDate = null)
    {
        if (!$this->isAdmin()) {
            return [
                'error' => 'Access denied. This report is only available to administrators.',
                'user_role' => $this->userRole
            ];
        }
        
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        $query = User::where('role', 1)
            ->where('suspended', true)
            ->whereBetween('updated_at', [$dateRange['start'], $dateRange['end']]);
        
        $suspendedStudents = $query->count();
        
        $suspensionsByDate = (clone $query)->select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('count(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        $suspensionReasons = (clone $query)->whereNotNull('suspension_reason')
            ->select('suspension_reason', DB::raw('count(*) as count'))
            ->groupBy('suspension_reason')
            ->pluck('count', 'suspension_reason')
            ->toArray();
        
        return [
            'suspended_count' => $suspendedStudents,
            'by_date' => $suspensionsByDate,
            'by_reason' => $suspensionReasons,
            'period' => $period,
            'date_range' => $dateRange,
            'user_role' => $this->userRole,
            'is_admin' => $this->isAdmin()
        ];
    }

    /**
     * Get user's own activity summary
     */
    public function getMyActivitySummary($period = 'monthly', $startDate = null, $endDate = null)
    {
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        $summary = [
            'user' => $this->currentUser,
            'role' => $this->userRole,
            'period' => $period,
            'date_range' => $dateRange
        ];
        
        if ($this->userRole === 1) {
            // Student activities
            $summary['appointments'] = Appointment::where('user_id', $this->currentUser->id)
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count();
            
            $summary['event_participations'] = EventParticipant::where('user_id', $this->currentUser->id)
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count();
            
            $summary['points_earned'] = StudentPoint::where('user_id', $this->currentUser->id)
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->sum('points') ?? 0;
            
        } elseif ($this->userRole === 2) {
            // Staff activities
            $summary['appointments_assigned'] = Appointment::where('assigned_staff_id', $this->currentUser->id)
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count();
            
            $summary['events_created'] = Event::where('created_by', $this->currentUser->id)
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count();
            
        } elseif ($this->userRole === 3) {
            // Student leader activities
            $organizationIds = collect();
            if ($this->currentUser->organization_id) {
                $organizationIds->push($this->currentUser->organization_id);
            }
            if ($this->currentUser->otherOrganizations) {
                $organizationIds = $organizationIds->merge($this->currentUser->otherOrganizations->pluck('id'));
            }
            $organizationIds = $organizationIds->filter()->unique();
            
            $summary['events_created'] = Event::whereIn('organization_id', $organizationIds->toArray())
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count();
            
            $summary['organization_members'] = User::whereIn('organization_id', $organizationIds->toArray())
                ->orWhereHas('otherOrganizations', function($q) use ($organizationIds) {
                    $q->whereIn('organizations.id', $organizationIds->toArray());
                })
                ->count();
        }
        
        return $summary;
    }

    /**
     * Get comprehensive statistics (admin only)
     */
    public function getComprehensiveStats($period = 'monthly', $startDate = null, $endDate = null)
    {
        if (!$this->isAdmin()) {
            return [
                'error' => 'Access denied. This report is only available to administrators.',
                'user_role' => $this->userRole
            ];
        }
        
        return [
            'appointments' => $this->getAppointmentStats($period, $startDate, $endDate),
            'events' => $this->getEventStats($period, $startDate, $endDate),
            'students' => $this->getStudentStats($period, $startDate, $endDate),
            'scholars' => $this->getScholarStats($period, $startDate, $endDate),
            'suspensions' => $this->getSuspensionStats($period, $startDate, $endDate),
            'period' => $period,
        ];
    }

    /**
     * Get historical activity data for line graphs
     */
    public function getHistoricalActivityData($months = 6)
    {
        $data = [
            'labels' => [],
            'datasets' => []
        ];
        
        // Generate labels and date ranges for the last N months
        $labels = [];
        $dateRanges = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $dateRanges[] = [
                'start' => $date->copy()->startOfMonth(),
                'end' => $date->copy()->endOfMonth()
            ];
        }
        $data['labels'] = $labels;
        
        if ($this->userRole === 1) {
            // Student: Appointments, Event Participations, Points
            $appointmentsData = [];
            $eventsData = [];
            $pointsData = [];
            
            foreach ($dateRanges as $range) {
                $start = $range['start'];
                $end = $range['end'];
                
                $appointmentsData[] = Appointment::where('user_id', $this->currentUser->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
                
                $eventsData[] = EventParticipant::where('user_id', $this->currentUser->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
                
                $pointsData[] = StudentPoint::where('user_id', $this->currentUser->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('points') ?? 0;
            }
            
            $data['datasets'] = [
                [
                    'label' => 'Appointments Made',
                    'data' => $appointmentsData,
                    'borderColor' => 'rgb(13, 110, 253)',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Events Participated',
                    'data' => $eventsData,
                    'borderColor' => 'rgb(25, 135, 84)',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Points Earned',
                    'data' => $pointsData,
                    'borderColor' => 'rgb(255, 193, 7)',
                    'backgroundColor' => 'rgba(255, 193, 7, 0.1)',
                    'tension' => 0.4
                ]
            ];
            
        } elseif ($this->userRole === 2) {
            // Staff: Appointments Assigned, Events Created
            $appointmentsData = [];
            $eventsData = [];
            
            foreach ($dateRanges as $range) {
                $start = $range['start'];
                $end = $range['end'];
                
                $appointmentsData[] = Appointment::where('assigned_staff_id', $this->currentUser->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
                
                $eventsData[] = Event::where('created_by', $this->currentUser->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
            }
            
            $data['datasets'] = [
                [
                    'label' => 'Appointments Assigned',
                    'data' => $appointmentsData,
                    'borderColor' => 'rgb(13, 110, 253)',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Events Created',
                    'data' => $eventsData,
                    'borderColor' => 'rgb(25, 135, 84)',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.1)',
                    'tension' => 0.4
                ]
            ];
            
        } elseif ($this->userRole === 3) {
            // Student Leader: Events Created, Organization Members
            $eventsData = [];
            $membersData = [];
            
            $organizationIds = collect();
            if ($this->currentUser->organization_id) {
                $organizationIds->push($this->currentUser->organization_id);
            }
            if ($this->currentUser->otherOrganizations) {
                $organizationIds = $organizationIds->merge($this->currentUser->otherOrganizations->pluck('id'));
            }
            $organizationIds = $organizationIds->filter()->unique();
            
            foreach ($dateRanges as $range) {
                $start = $range['start'];
                $end = $range['end'];
                
                $eventsData[] = Event::whereIn('organization_id', $organizationIds->toArray())
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
                
                // Count members at the end of each month
                $membersData[] = User::whereIn('organization_id', $organizationIds->toArray())
                    ->orWhereHas('otherOrganizations', function($q) use ($organizationIds) {
                        $q->whereIn('organizations.id', $organizationIds->toArray());
                    })
                    ->where('created_at', '<=', $end)
                    ->count();
            }
            
            $data['datasets'] = [
                [
                    'label' => 'Events Created',
                    'data' => $eventsData,
                    'borderColor' => 'rgb(13, 110, 253)',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Organization Members',
                    'data' => $membersData,
                    'borderColor' => 'rgb(23, 162, 184)',
                    'backgroundColor' => 'rgba(23, 162, 184, 0.1)',
                    'tension' => 0.4
                ]
            ];
        }
        
        return $data;
    }
}
