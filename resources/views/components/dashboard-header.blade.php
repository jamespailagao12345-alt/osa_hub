@props([
    'name' => null,
    'designation' => null,
    'roleLabel' => null,
    'align' => 'left', // 'left', 'center', 'right'
])

@php
    // If name is not provided, try to get it from authenticated user
    if (!$name && auth()->check()) {
        $user = auth()->user();
        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
    }
    
    // If designation is not provided, try to get it from authenticated user
    if (!$designation && auth()->check()) {
        $user = auth()->user();
        
        // Student leader (role 3) use position, not designation
        if ($user->role == 3) {
            $designation = $user->position ?? null;
        } else {
            // Staff use designation
            $designation = $user->designation ?? optional($user->staffProfile)->designation ?? null;
            
            // If still not found, check Staff table by email (case-insensitive)
            if (!$designation && $user) {
                $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
                $designation = $staffRecord ? $staffRecord->designation : null;
            }
        }
    }
    
    // Determine alignment class
    $alignClass = 'text-' . $align;
@endphp

<div class="dashboard-header mb-4 {{ $alignClass }}">
    <h2 class="mb-2" style="color: midnightblue; font-weight: 600;">
        Welcome, {{ $name ?? 'User' }}!
    </h2>
    @if($designation)
        <p class="text-muted mb-1" style="font-size: 0.95rem;">
            {{ $designation }}
        </p>
    @endif
    @if($roleLabel)
        <p class="mb-0" style="font-size: 0.9rem; font-weight: 600; color: midnightblue;">
            {{ $roleLabel }}
        </p>
    @endif
</div>
