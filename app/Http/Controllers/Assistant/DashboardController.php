<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrganizationRegistrationRequest;

class DashboardController extends Controller
{
    public function index()
    {
        // Load dashboard data for student leaders (role 3)
        // Fetch pending organization registration requests with organization data
        // Note: student() relationship returns User model directly, not Student model
        $pendingRequests = OrganizationRegistrationRequest::with(['student', 'organization'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get current user's organizations
        $user = auth()->user();
        $user->load(['organization', 'otherOrganizations']);
        
        // Combine primary organization and additional organizations
        $allOrganizations = collect();
        
        // Add primary organization if exists
        if ($user->organization) {
            $allOrganizations->push([
                'id' => $user->organization->id,
                'name' => $user->organization->name,
                'type' => $user->organization->department_id ? 'Academic' : 'Non-Academic',
                'position' => null, // Primary organization doesn't have position in pivot
                'is_primary' => true
            ]);
        }
        
        // Add additional organizations with their positions
        foreach ($user->otherOrganizations as $org) {
            $allOrganizations->push([
                'id' => $org->id,
                'name' => $org->name,
                'type' => $org->department_id ? 'Academic' : 'Non-Academic',
                'position' => $org->pivot->position ?? null,
                'is_primary' => false
            ]);
        }
        
        return view('student-leader-dashboard', compact('pendingRequests', 'allOrganizations'));
    }
}
