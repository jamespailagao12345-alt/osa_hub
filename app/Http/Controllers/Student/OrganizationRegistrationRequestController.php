<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrganizationRegistrationRequest;
use Illuminate\Support\Facades\Auth;

class OrganizationRegistrationRequestController extends Controller
{
    // Show all requests for student leader approval
    public function index()
    {
        $requests = OrganizationRegistrationRequest::with('student', 'organization')->where('status', 'pending')->get();
        return view('assistant.organization-requests', compact('requests'));
    }

    // Store a new organization registration request
    public function store(Request $request)
    {
        $user = Auth::user();
        // Allow students (role 1) and student leaders (role 3) - students with role 3 are still students
        $userRole = (int) $user->role;
        if ($userRole !== 1 && $userRole !== 3) {
            abort(403, 'Only students can submit organization registration requests.');
        }
        // Removed the 3 organization limit - students can now submit as many requests as they want
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'details' => 'nullable|string|max:1000',
            'position' => 'nullable|string|max:255',
        ]);
        
        // Ensure the organization is non-academic (no department_id)
        // Students are automatically members of their department's organization
        $organization = \App\Models\Organization::findOrFail($request->organization_id);
        if ($organization->department_id) {
            return back()->with('error', 'You cannot apply for a department-related organization. You are automatically a member of your department\'s organization.')->withInput();
        }
        
        // Allow multiple pending requests (removed duplicate check to allow multiple memberships)
        OrganizationRegistrationRequest::create([
            'student_id' => $user->id,
            'organization_id' => $request->organization_id,
            'status' => 'pending',
            'details' => $request->details ?? null,
            'position' => $request->position ?? null,
        ]);
        return back()->with('success', 'Organization registration request submitted.');
    }

    // Student leader approves a request
    public function approve($id)
    {
        $request = OrganizationRegistrationRequest::findOrFail($id);
        $request->status = 'approved';
        $request->save();
        // Attach organization to student via pivot table (many-to-many relationship)
        // Allow multiple memberships - attach with position if provided
        $request->student->otherOrganizations()->attach($request->organization_id, [
            'position' => $request->position ?? null
        ]);
        return back()->with('success', 'Request approved.');
    }

    // Student leader declines a request
    public function decline($id)
    {
        $request = OrganizationRegistrationRequest::findOrFail($id);
        $request->status = 'declined';
        $request->save();
        return back()->with('success', 'Request declined.');
    }
}
