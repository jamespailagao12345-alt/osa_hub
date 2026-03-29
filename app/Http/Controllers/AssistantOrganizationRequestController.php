<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrganizationRegistrationRequest;

class AssistantOrganizationRequestController extends Controller
{
    public function index()
    {
        $requests = OrganizationRegistrationRequest::where('status', 'pending')->get();
        return view('assistant.organization_requests', [
            'requests' => $requests
        ]);
    }

    public function approve($id)
    {
        $req = OrganizationRegistrationRequest::findOrFail($id);
        $req->status = 'approved';
        $req->save();
        return redirect()->route('student-leader.organization-requests.index')->with('success', 'Request approved.');
    }

    public function decline($id)
    {
        $req = OrganizationRegistrationRequest::findOrFail($id);
        $req->status = 'declined';
        $req->save();
        return redirect()->route('student-leader.organization-requests.index')->with('success', 'Request declined.');
    }
}
