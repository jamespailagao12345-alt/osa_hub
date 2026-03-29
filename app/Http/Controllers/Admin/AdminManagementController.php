<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\CacheService;

class AdminManagementController extends Controller
{
    /**
     * Create a new controller instance.
     * Ensure only admins (role 4) can access these methods.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || (int)auth()->user()->role !== 4) {
                abort(403, 'Unauthorized: Only administrators can access this page.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of admins
     */
    public function index(Request $request)
    {
        $query = User::with(['department', 'organization'])
            ->where('role', 4)
            ->orderBy('last_name');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('user_id', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $admins = $query->paginate(15)->appends($request->query());
        $departments = CacheService::getDepartments();

        return view('admin.admins.index', compact('admins', 'departments'));
    }

    /**
     * Show the form for creating a new admin
     */
    public function create()
    {
        $departments = CacheService::getDepartments();
        return view('admin.admins.create', compact('departments'));
    }

    /**
     * Store a newly created admin
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'string', 'max:50', 'unique:users,user_id', new \App\Rules\UserIdByRole(4)],
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:1|regex:/^[A-Za-z]$/',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'contact_number' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
        ]);

        // Use default password "password"
        $defaultPassword = 'password';
        
        $user = User::create([
            'user_id' => $validated['user_id'] ?? null,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($defaultPassword),
            'role' => 4, // Admin role
            'department_id' => $validated['department_id'] ?? null,
            'contact_number' => $validated['contact_number'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'email_verified_at' => now(),
        ]);

        // Send credentials email
        try {
            $name = $user->first_name . ' ' . $user->last_name;
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\AccountCredentialsMail(
                $user->email,
                $defaultPassword,
                $name,
                'Admin'
            ));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send credentials email to admin', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        // Clear user role cache
        CacheService::clearUserRoleCache();

        return redirect()->route('admins.index')
            ->with('success', 'Admin created successfully.');
    }

    /**
     * Show the form for editing an admin
     */
    public function edit($id)
    {
        $admin = User::where('role', 4)->findOrFail($id);
        $departments = CacheService::getDepartments();
        return view('admin.admins.edit', compact('admin', 'departments'));
    }

    /**
     * Update an admin
     */
    public function update(Request $request, $id)
    {
        $admin = User::where('role', 4)->findOrFail($id);

        $validated = $request->validate([
            'user_id' => ['nullable', 'string', 'max:50', 'unique:users,user_id,' . $admin->id, new \App\Rules\UserIdByRole(4)],
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:1|regex:/^[A-Za-z]$/',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'contact_number' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
        ]);

        $updateData = [
            'user_id' => $validated['user_id'] ?? null,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'department_id' => $validated['department_id'] ?? null,
            'contact_number' => $validated['contact_number'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $admin->update($updateData);

        return redirect()->route('admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    /**
     * Remove an admin
     */
    public function destroy($id)
    {
        $admin = User::where('role', 4)->findOrFail($id);
        
        // Prevent deleting yourself
        if ($admin->id === auth()->id()) {
            return redirect()->route('admins.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();
        CacheService::clearUserRoleCache();

        return redirect()->route('admins.index')
            ->with('success', 'Admin deleted successfully.');
    }
}

