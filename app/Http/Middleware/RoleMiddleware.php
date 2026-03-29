<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Staff;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (! $request->user()) {
            Log::info('RoleMiddleware: No authenticated user');
            return redirect('/login');
        }

        // Support multiple allowed roles: role:1,2,3 or role:1,2 (variadic)
        $allowed = [];
        foreach ($roles as $r) {
            foreach (explode(',', (string) $r) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $allowed[] = $part;
                }
            }
        }
        $userRole = (string) $request->user()->role;

        if (! in_array($userRole, $allowed, true)) {
            Log::info('RoleMiddleware: Role not allowed', ['user_id'=>$request->user()->id,'role'=>$userRole,'allowed'=>$allowed]);
            return redirect('/login')->with('error', 'Unauthorized access.');
        }

        // If the current user IS staff (role 2), ensure they have a staff record.
        // Accept either a legacy Staff row (matched by email) OR a Staff record linked to the user.
        // Note: Do NOT force this check for admins (role 4) even if '2' is included in allowed roles.
        if ($userRole === '2') {
            $staff = Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($request->user()->email))])->first();
            $hasLegacyStaff = (bool) $staff;
            $hasProfile = Staff::where('user_id', $request->user()->id)->exists();
            if (! ($hasLegacyStaff || $hasProfile)) {
                \Log::info('RoleMiddleware: Staff record not found', ['user_id'=>$request->user()->id,'email'=>$request->user()->email]);
                return redirect('/login')->with('error', 'Unauthorized access: staff record not found.');
            }

            // Auto-deactivate when contract ends; block inactive/ended statuses
            if ($staff) {
                $expired = $staff->contract_end_at && now()->greaterThanOrEqualTo($staff->contract_end_at);
                if ($expired && $staff->employment_status !== 'ended') {
                    $staff->employment_status = 'ended';
                    $staff->save();
                }
                if (in_array($staff->employment_status, ['inactive','ended'], true)) {
                    \Log::info('RoleMiddleware: Staff inactive or ended', ['user_id'=>$request->user()->id,'staff_id'=>$staff->id,'employment_status'=>$staff->employment_status]);
                    // Block access without mutating the user's role to avoid DB NOT NULL constraint errors
                    return redirect('/login')->with('error', 'Your account is not active. Please contact the admin.');
                }
            }
        }

        return $next($request);
    }
}
