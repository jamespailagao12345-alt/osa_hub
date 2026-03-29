<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AssistantAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }
        // Allow if assistant (role 3) or student (role 1) with 1-3 active assignments
        if (($user->role ?? null) === 3) {
            return $next($request);
        }
        if (($user->role ?? null) === 1) {
            $count = $user->assistantAssignments()->where('is_active', true)->count();
            if ($count >= 1 && $count <= 3) {
                return $next($request);
            }
        }
        return redirect()->route('home')->with('error', 'You do not have assistant access.');
    }
}
