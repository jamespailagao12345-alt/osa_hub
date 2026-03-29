<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckStaffDesignation
{
    public function handle(Request $request, Closure $next, string $requiredDesignation)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        // Admins always permitted
        if ((int)($user->role ?? 0) === 4) {
            return $next($request);
        }

        // Allow role 2 staff whose designation matches either on user, staff profile, or Staff table
        // Try to find staff record by email (case-insensitive) for better matching
        $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
        
        $userDesignation = $user->designation 
            ?? optional($user->staffProfile)->designation 
            ?? ($staffRecord ? $staffRecord->designation : null);
        
        $normalizedUserDesignation = trim($userDesignation ?? '');
        
        // Normalize "Guidance Counsellor" (British) to "Guidance Counselor" (American)
        if (strcasecmp($normalizedUserDesignation, 'Guidance Counsellor') === 0) {
            $normalizedUserDesignation = 'Guidance Counselor';
        }
        
        $normalizedRequiredDesignation = trim($requiredDesignation);
        
        // Normalize "Guidance Counsellor" (British) to "Guidance Counselor" (American)
        if (strcasecmp($normalizedRequiredDesignation, 'Guidance Counsellor') === 0) {
            $normalizedRequiredDesignation = 'Guidance Counselor';
        }
        
        if ((int)($user->role ?? 0) === 2 && $normalizedUserDesignation && strcasecmp($normalizedUserDesignation, $normalizedRequiredDesignation) === 0) {
            return $next($request);
        }

        return abort(403, 'Unauthorized: insufficient designation.');
    }
}

