<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Designation;

class RedirectController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/');
        }

        // Check if user has staff organizations assigned (even if role is 4/admin)
        // This handles cases where organizations were reassigned from admin to staff
        $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
        if ($staffRecord && $staffRecord->organizations()->count() > 0) {
            // User has staff organizations assigned, redirect to staff dashboard
            $designation = $user->designation
                ?? $staffRecord->designation
                ?? optional($user->staffProfile)->designation
                ?? null;

            if ($designation) {
                $exists = Designation::where('name', $designation)->exists();
                if ($exists) {
                    // Student Org. Moderator goes to admin staff dashboard
                    if (strcasecmp($designation, 'Student Org. Moderator') === 0) {
                        return redirect()->route('admin.staff.dashboard');
                    }
                }
            }
            // All other staff with organizations go to /admin/staff/dashboard
            return redirect()->route('admin.staff.dashboard');
        }

        // Role-based landing
        switch ((int) $user->role) {
            case 1:
                return redirect()->route('student.dashboard');
            case 2:
                // Check designation for special handling
                $designation = $user->designation
                    ?? optional($user->staffProfile)->designation
                    ?? null;

                if ($designation) {
                    $exists = Designation::where('name', $designation)->exists();
                    if ($exists) {
                        // Student Org. Moderator goes to admin staff dashboard
                        if (strcasecmp($designation, 'Student Org. Moderator') === 0) {
                            return redirect()->route('admin.staff.dashboard');
                        }
                    }
                }
                // All other staff go to /admin/staff/dashboard where they can only click on their own row
                return redirect()->route('admin.staff.dashboard');
            case 3:
                return redirect()->route('student-leader.dashboard');
            case 4:
                return redirect()->route('admin.dashboard');
            default:
                return redirect('/');
        }
    }
}
